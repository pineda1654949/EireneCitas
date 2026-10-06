# 00 - Equivalencias y hallazgos (leer primero)

> Aplica a todos los documentos de esta carpeta. Proyecto EireneCitas sobre **Laravel 12** (v12.69.3, PHP 8.2+), con sesión web y roles `administrador`, `recepcionista`, `psicologo` y `paciente`.
>
> **Cambio respecto de la versión anterior de estos documentos:** antes decían *Laravel 8.83.29*. El proyecto se **migró a Laravel 12** porque Laravel 8 dejó de recibir correcciones de seguridad en enero de 2023. Las marcas **[VERIFICAR]** de la versión anterior se resolvieron contra el código real: en esta versión ya no queda ninguna pendiente.

## 0.1 Equivalencias técnicas

| Los PDF dicen (Node) | En EireneCitas (Laravel 12) |
|---|---|
| JWT, Bearer, HS256, 12 h | Sesión con cookie cifrada (`SESSION_ENCRYPT=true`), expiración en `SESSION_LIFETIME` (120 min) |
| `authenticateToken`, `requireRole` | Middleware `auth` y `rol:...` (`App\Http\Middleware\VerificarRol`) + Policies |
| Roles ADMIN / PSYCHOLOGIST | `administrador`, `recepcionista`, `psicologo` y `paciente` (enum `App\Enums\Rol`) |
| Sequelize, migraciones | Eloquent, `database/migrations`, factories y seeders |
| `Sequelize.transaction` + bloqueo | `DB::transaction()` + `lockForUpdate()` (`App\Services\CitaService`) |
| Multer (voucher) | Regla `mimes:jpg,jpeg,png,pdf\|max:5120` + disco privado `Storage::disk('local')` |
| Nodemailer | `Notification` de Laravel (correos en cola, `ShouldQueue`) |
| Jest + Supertest | PHPUnit 11 + pruebas Feature (`$this->post(...)`) |
| SQLite en memoria | `phpunit.xml` con `sqlite` y `:memory:`; también se ejecuta en MySQL |
| Stryker (mutación) | Infection PHP: **no ejecutado** (trabajo futuro, ver la Fase 6) |
| Cypress | **Laravel Dusk 8** con Chrome real |
| `/api/v1/...` | Rutas web reales (sección 0.2) |
| Códigos 400/401/409 | En rutas web: 302 (redirección con errores) y 403. Ver 0.3 |

**Cambios de Laravel 8 a Laravel 12 que afectan a la documentación:**

| Tema | Laravel 8 (documentos anteriores) | Laravel 12 (actual) |
|---|---|---|
| Middleware y excepciones | `app/Http/Kernel.php`, `app/Exceptions/Handler.php` | `bootstrap/app.php` |
| Tareas programadas | `app/Console/Kernel.php` | `routes/console.php` (`Schedule::command`) |
| Middleware de roles | `role:` (`RoleMiddleware`) | `rol:` (`VerificarRol`) + Policies por pertenencia |
| Autenticación | `Auth\AuthController` | `SesionController`, `RegistroController`, `RecuperarContrasenaController` y `RestablecerContrasenaController` |
| Traducciones | `resources/lang` | `lang/es` |
| Frontend | Bootstrap 5 por CDN | Tailwind CSS 4 + Vite 7 (assets compilados, sin CDN) |
| PHP mínimo | 7.3 | 8.2 |

## 0.2 Mapa de rutas reales a módulos del plan

| Módulo | Rutas reales | Roles permitidos |
|---|---|---|
| Autenticación | `login`, `logout`, `registro`, `recuperar-contrasena`, `restablecer-contrasena/{token}` | guest / auth |
| Pacientes | `admin/pacientes` (CRUD) | administrador, recepcionista |
| Derivación (RF-05) | `admin/derivaciones`, `admin/pacientes/{paciente}/derivaciones`, `psicologo/derivaciones` (aceptar/rechazar) | personal / psicólogo |
| Promociones | `admin/promociones` (CRUD) | administrador |
| Psicólogos | `admin/psicologos` (CRUD) | administrador |
| Citas | `citas` (index, crear, store, show) | autenticado + `CitaPolicy` (solo citas propias o del personal) |
| Reprogramación | `citas/{cita}/reprogramar` (GET y PUT) | autenticado + `CitaPolicy` |
| Cancelación | `citas/{cita}/cancelar` (GET y PUT) | autenticado + `CitaPolicy` |
| Confirmación | `PUT citas/{cita}/confirmar` | `CitaPolicy::confirmar` → recepcionista, administrador |
| Pagos | `citas/{cita}/pagos` (personal o el propio paciente, con voucher); `pagos`, `pagos/{pago}/validar`, `pagos/{pago}/rechazar` (personal); `pagos/{pago}/comprobante` | según el caso |
| Disponibilidad | `api/agenda-del-dia` (matriz de colores), `api/horas-disponibles`, `api/especialidades/{id}/psicologos` | autenticado |
| Horarios del psicólogo | `psicologo/horarios` (index, store, alternar, destroy) | psicologo |
| Historial clínico | `psicologo/citas/{cita}/historial`, `psicologo/pacientes/{paciente}/historial` | psicologo + `PacientePolicy` |
| Reportes | `reportes`, `reportes/exportar` (CSV) | recepcionista, administrador |
| Auditoría | `admin/auditoria` | administrador |

## 0.3 Diferencias de comportamiento que cambian los resultados esperados

| Situación | Resultado en EireneCitas (verificado en pruebas) |
|---|---|
| Sin sesión en ruta protegida | 302 a `/login` (no 401). En peticiones JSON: 401 |
| Rol no permitido | **403** (`VerificarRol` usa `abort(403)`; las Policies también responden 403) |
| Validación fallida en formulario web | 302 de vuelta con errores en sesión; 422 solo con petición JSON |
| Recurso inexistente | 404 |
| Formulario sin token CSRF | 419 ("La sesión expiró") |
| Demasiadas peticiones | 429 (límite por IP) y bloqueo por usuario tras 5 intentos fallidos de login |

## 0.4 Brechas entre los PDF y el código: estado actual

| Función de los PDF | Estado en la versión anterior | Estado actual (Laravel 12) |
|---|---|---|
| Stepper de 5 pasos | [VERIFICAR] | **Formulario guiado por secciones numeradas** en `citas/crear` (paciente → profesional → fecha y hora → detalles). No es un *stepper* con páginas separadas: es una sola página con secciones. La ficha completa del paciente está en `admin/pacientes/create` |
| Derivación Aceptar/Rechazar (RF-05) | Pendiente | ✅ **Implementada**: modelo `Derivacion`, `DerivacionService`, aviso por correo al psicólogo y a la clínica |
| Matriz de slots Verde/Rojo/Gris | [VERIFICAR] | ✅ **Implementada**: `api/agenda-del-dia` devuelve `libre`, `ocupado` o `bloqueado`, y el formulario lo pinta en verde, rojo y gris con su leyenda |
| Enlace Google Meet | Pendiente | ⏳ **Trabajo futuro**: existe la columna `citas.enlace_meet` y se muestra si tiene valor, pero no hay integración con la API de Google |
| Recordatorio por correo 24 h | [VERIFICAR] | ✅ **Implementado**: comando `citas:enviar-recordatorios`, programado a las 08:00 en `routes/console.php` |
| Cuotas y vouchers | [VERIFICAR] | ✅ **Implementados**: promociones con `permite_cuotas` y `max_cuotas`; pagos con `numero_cuota`, `total_cuotas` y voucher (`comprobante_path`) |
| Exportación PDF/Excel | [VERIFICAR] | ✅ **CSV** compatible con Excel (`reportes/exportar`), protegido contra inyección de fórmulas. PDF: trabajo futuro |
| Dashboard | [VERIFICAR] | ✅ Un panel distinto por rol con indicadores (`home`) |
| Ciclo de vida del paciente | [VERIFICAR] | ✅ `pacientes.estado_atencion`: Inscripto, En proceso, Finalizado, Cancelado |

## 0.5 Puntos de seguridad señalados en la versión anterior

1. *"Las rutas de citas solo exigen `auth`, sin `role`"*: **resuelto**. `CitaPolicy` limita ver, reprogramar y cancelar a la cita propia (paciente), a la propia agenda (psicólogo) o al personal. Verificado en CP-SYS-15 y en la matriz de `ControlDeAccesoTest`.
2. *"Las rutas de pagos repiten el middleware `role` dos veces"*: **resuelto**. Cada ruta se declara una sola vez en `routes/web.php`.
3. *"Laravel 8 sin parches de seguridad"*: **resuelto** con la migración a Laravel 12. `composer audit` no reporta vulnerabilidades (CP-SYS-33).

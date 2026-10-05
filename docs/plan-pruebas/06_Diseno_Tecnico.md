# 06 - Diseño Técnico y Arquitectura (Laravel 12)

> Adapta el PDF *06 Diseño Técnico Sistema Eirene React Node Express*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Introducción

El sistema sigue una arquitectura MVC en tres capas (presentación, negocio y persistencia) con **Laravel 12**, **Blade + Tailwind CSS 4** y **MySQL** administrado con **Eloquent**. Resuelve la dependencia de WhatsApp, Excel y Drive del modelo AS-IS y apunta a ISO/IEC 25010 e ISO/IEC 27001.

## 2. Arquitectura

| Capa | Tecnología | Componentes |
|---|---|---|
| Presentación | Blade + Tailwind CSS 4 + JavaScript propio, compilados con Vite 7 (sin CDN, compatible con una CSP estricta) | Vistas por rol, componentes reutilizables, formularios con `@csrf` |
| Negocio | PHP 8.2+ / Laravel 12 | `routes/web.php`, controladores, middleware, FormRequest, Policies, Services, notificaciones |
| Persistencia | MySQL / MariaDB + Eloquent | Modelos con enums, migraciones y transacciones |

## 3. Estructura de directorios

```
EireneCitas/
 |-- app/
 |    |-- Enums/                Rol, EstadoCita, EstadoPago, MetodoPago, EstadoAtencion, EstadoDerivacion
 |    |-- Http/Controllers/     {Admin, Api, Auth, Psicologo}/
 |    |-- Http/Middleware/      VerificarRol, AsegurarUsuarioActivo, CabecerasDeSeguridad
 |    |-- Http/Requests/        validacion (FormRequest)
 |    |-- Models/               Paciente, Cita, Pago, Derivacion, ... + trait Auditable
 |    |-- Notifications/        correos de cita, derivacion y recuperacion
 |    |-- Policies/             CitaPolicy, PacientePolicy, DerivacionPolicy
 |    |-- Services/             AgendaService, CitaService, DerivacionService
 |-- bootstrap/app.php          middleware y manejo de excepciones (Laravel 12)
 |-- database/migrations/, seeders/, factories/
 |-- resources/views/, resources/js/, resources/css/
 |-- routes/web.php, routes/console.php (tareas programadas)
 |-- tests/{Unit, Feature, Fase6, Fase7, Fase8, Browser, Carga}/
```

## 4. Rutas principales (reemplazan el catálogo `/api/v1`)

| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| POST | `login` | guest | Inicia sesión (límite de intentos) |
| GET/POST | `admin/pacientes` | administrador, recepcionista | Lista y registra pacientes |
| POST | `admin/pacientes/{paciente}/derivaciones` | administrador, recepcionista | Deriva al paciente (RF-05) |
| GET | `api/agenda-del-dia` | autenticado | Matriz de horas libre/ocupado/bloqueado |
| POST | `citas` | autenticado (Policy) | Crea la cita (RN-03) |
| PUT | `citas/{cita}/reprogramar` | autenticado (Policy) | Reprograma (RN-01) |
| PUT | `citas/{cita}/confirmar` | recepcionista, administrador | Confirma (RN-02) |
| POST | `citas/{cita}/pagos` | personal o paciente | Registra el pago con voucher y cuota |
| PUT | `pagos/{pago}/validar` | recepcionista, administrador | Valida el comprobante |
| PUT | `psicologo/derivaciones/{derivacion}/aceptar` | psicólogo destinatario | Acepta la derivación |
| POST | `psicologo/citas/{cita}/historial` | psicólogo de la cita | Registra el historial |

El listado completo (62 rutas) se obtiene con `php artisan route:list`.

## 5. Seguridad

1. **Contraseñas:** bcrypt. Laravel 12 usa 12 rondas por defecto (`BCRYPT_ROUNDS=12`); el PDF indicaba 10 y Laravel 8 usaba 10. Política: mínimo 8 caracteres, con letras y números.
2. **Sesión:** cookie cifrada (`SESSION_ENCRYPT=true`), regenerada al iniciar sesión, expiración por `SESSION_LIFETIME`, `SESSION_SECURE_COOKIE` en producción. Sustituye al JWT HS256 de 12 h del PDF.
3. **Control de acceso:** middleware `auth` y `rol:`. El psicólogo no accede a pagos ni a reportes (403). La recepción y el administrador **no ven ni editan** las notas clínicas: solo las ve el psicólogo tratante (CP-SYS-07).
4. **CSRF y XSS:** `@csrf` en todos los formularios y escape `{{ }}` en Blade. Content-Security-Policy `script-src 'self'`, sin scripts en línea.
5. **Autorización por pertenencia:** ✅ Policies para citas (`CitaPolicy`), historiales (`PacientePolicy`) y derivaciones (`DerivacionPolicy`).
6. **Datos sensibles:** notas clínicas cifradas con `APP_KEY`; los vouchers se guardan en un disco privado y se sirven con `nosniff` y una CSP aislada.
7. **Trazabilidad:** bitácora de auditoría (creación, cambios, eliminación, inicios de sesión e intentos fallidos) que nunca guarda contraseñas ni notas clínicas.

## 6. Transacciones e integraciones

- **Inscripción atómica:** `DB::transaction()` en el registro (cuenta + ficha), en la reserva, en la reprogramación y en la validación de pagos. Si algo falla, se revierte todo (CP-IT-06).
- **Reserva sin colisión:** `lockForUpdate()` sobre la fila del psicólogo al reservar y al reprogramar (RN-03). Verificado con 30 reservas simultáneas (CP-SYS-25).
- **Correo:** `Notification` de Laravel (reemplaza Nodemailer), encolada. Un fallo del servidor de correo no revierte la operación (CP-IT-38).
- **Tareas programadas** (`routes/console.php`): cola de correos cada minuto, recordatorios a las 08:00, respaldos diarios y depuración de la auditoría.
- **Google Meet:** ⏳ pendiente. Requeriría la API de Google Calendar.

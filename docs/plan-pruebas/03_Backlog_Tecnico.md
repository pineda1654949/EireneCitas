# 03 - Backlog Técnico de Implementación (Laravel 12)

> Adapta el PDF *03 Backlog Técnico de Implementación*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Estructura

6 épicas / sprints bajo V-Bounce, en la transición de un entorno manual a una plataforma web en **Laravel 12 + MySQL**. El PDF estima 103 SP en 6 sprints de 2 semanas (12 semanas). Se mantiene como estimación; la columna "Estado" refleja el avance real.

## 2. Definition of Done

- Código revisado con **Laravel Pint** (formato) y **Larastan** nivel 6 (análisis estático), sin observaciones.
- Persistencia verificada en MySQL mediante migraciones reversibles (`migrate`, `migrate:reset`, `migrate`).
- Pruebas unitarias, Feature y E2E en verde (`php artisan test`, `php artisan dusk`) con las respuestas correctas (200, 302, 403, 404, 419, 422, 429).
- Control de acceso por rol con middleware `rol:` y autorización por pertenencia con Policies.
- Integración continua en verde (GitHub Actions: `.github/workflows/ci.yml`).
- Validación de aceptación por el usuario final (Fase 9).

## 3. Épicas, historias y tareas

**Épica 1 / Sprint 1: Arquitectura base, modelos y datos (13 SP)**

| HU | Historia | SP | Tarea en Laravel 12 | Estado |
|---|---|---|---|---|
| HU-01 | Configuración del servidor | 3 | Laravel 12, `.env`, `bootstrap/app.php` (middleware y manejo de excepciones), MySQL | ✅ |
| HU-02 | Modelos y migraciones | 5 | Modelos Eloquent con enums y migraciones para pacientes, citas, pagos, promociones, horarios, historiales, derivaciones, auditoría | ✅ |
| HU-03 | CRUD de pacientes y psicólogos | 5 | `Admin\PacienteController` y `Admin\PsicologoController` con FormRequests | ✅ |

**Épica 2 / Sprint 2: Autenticación y roles (15 SP)**

| HU | Historia | SP | Tarea en Laravel 12 | Estado |
|---|---|---|---|---|
| HU-04 | Login | 5 | `Auth\SesionController` + `LoginRequest` (bcrypt, límite de intentos, cuentas inactivas) | ✅ |
| HU-05 | Protección de rutas | 5 | Middleware `auth` y `guest` | ✅ |
| HU-06 | Control por rol | 5 | Middleware `rol:` + `CitaPolicy`, `PacientePolicy` y `DerivacionPolicy` | ✅ |

**Épica 3 / Sprint 3: Inscripción de pacientes (21 SP)**

| HU | Historia | SP | Tarea en Laravel 12 | Estado |
|---|---|---|---|---|
| HU-07 | Formulario guiado | 8 | `citas/crear` en secciones numeradas y ficha `admin/pacientes/create` | ✅ (una página con secciones, no 5 pantallas) |
| HU-08 | Validaciones | 5 | FormRequest con reglas de DNI (8 a 12 dígitos, único), correo (único, minúsculas) y celular (`^9\d{8}$`) | ✅ |
| HU-09 | Persistencia atómica | 8 | `DB::transaction` en registro, reservas, reprogramaciones y pagos | ✅ (CP-IT-06) |

**Épica 4 / Sprint 4: Psicólogo, derivación y fichas (18 SP)**

| HU | Historia | SP | Tarea en Laravel 12 | Estado |
|---|---|---|---|---|
| HU-10 | Aceptar/rechazar derivación | 5 | `DerivacionService`, controladores y vistas de derivación | ✅ |
| HU-11 | Expedientes y documentos | 8 | `Psicologo\HistorialController` con notas cifradas | ✅ Notas. ⏳ Adjuntos de sesión: trabajo futuro (existe la columna `documento_path`) |
| HU-12 | Ciclo de vida del paciente | 5 | `pacientes.estado_atencion` (enum `EstadoAtencion`) | ✅ |

**Épica 5 / Sprint 5: Agenda y reglas (20 SP)**

| HU | Historia | SP | Tarea en Laravel 12 | Estado |
|---|---|---|---|---|
| HU-13 | Agenda por colores | 8 | `AgendaService::agendaDelDia`, `api/agenda-del-dia`, matriz Verde/Rojo/Gris | ✅ |
| HU-14 | RN-01 (3 reprogramaciones) | 5 | `CitaService::reprogramar` + autorización del administrador | ✅ |
| HU-15 | Prevención de colisiones | 7 | `DB::transaction` + `lockForUpdate()` | ✅ (verificado con k6) |

**Épica 6 / Sprint 6: Notificaciones, Meet y reportes (16 SP)**

| HU | Historia | SP | Tarea en Laravel 12 | Estado |
|---|---|---|---|---|
| HU-16 | Enlace de Google Meet | 5 | Integración con la API de Google Calendar | ⏳ Trabajo futuro |
| HU-17 | Recordatorios por correo 24 h | 5 | `Notification` + comando `citas:enviar-recordatorios` programado en `routes/console.php` | ✅ |
| HU-18 | Dashboard y exportación | 6 | `HomeController` (un panel por rol) y `ReporteController` (CSV) | ✅ |

**Historias agregadas al migrar a Laravel 12 (no estaban en el PDF):** recuperación de contraseña, auditoría de acciones, respaldos automáticos, voucher subido por el propio paciente, cifrado de notas clínicas y cabeceras de seguridad.

## 4. Matriz de esfuerzo

| Sprint | Módulo | SP | Entregable | Estado |
|---|---|---|---|---|
| 1 | Arquitectura y modelos | 13 | Laravel 12 base y migraciones | ✅ |
| 2 | Seguridad y roles | 15 | Login, middleware y Policies | ✅ |
| 3 | Inscripción | 21 | Formularios y persistencia | ✅ |
| 4 | Psicólogo y fichas | 18 | Derivación e historial | ✅ (sin adjuntos) |
| 5 | Agenda y reglas | 20 | Horarios, matriz de colores y RN-01 | ✅ |
| 6 | Meet, correos, UAT | 16 | Notificaciones y pruebas finales | ✅ Correos. ⏳ Meet y UAT |
| | **Total** | **103** | | 98 SP completados (HU-16 pendiente; HU-11 sin adjuntos) |

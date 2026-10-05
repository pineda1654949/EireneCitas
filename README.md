# Eirene · Sistema de Gestión de Citas

Sistema web para la gestión de citas de la **Clínica Psicológica Eirene**: reserva con disponibilidad en tiempo real, reprogramación y cancelación con reglas de negocio, validación de pagos, historia clínica cifrada, reportes, auditoría y notificaciones por correo.

| | |
|---|---|
| **Backend** | PHP 8.2+ · Laravel 12 |
| **Frontend** | Blade · Tailwind CSS 4 · Vite 7 (sin dependencias de CDN) |
| **Base de datos** | MySQL 8 / MariaDB 10.4+ (SQLite en pruebas) |
| **Calidad** | PHPUnit 11 (210 pruebas) · PHPStan/Larastan nivel 6 · Laravel Pint · GitHub Actions |

---

## Requerimientos funcionales cubiertos

| RF | Descripción | Implementación principal |
|---|---|---|
| RF-01 | Registro de citas | `CitaController@store` → `CitaService::registrar` |
| RF-02 | Disponibilidad de horarios en tiempo real | `AgendaService`, `Api\DisponibilidadController` |
| RF-03 | Reprogramación (máx. 3) y cancelación | `CitaService::reprogramar` / `cancelar` |
| RF-04 | Confirmación de citas con pago validado | `CitaService::confirmar` / `validarPago` |
| RF-05 | Registro y actualización de pacientes | `Admin\PacienteController`, registro público |
| RF-06 | Historia clínica | `Psicologo\HistorialController`, `PacientePolicy` |
| RF-07 | Control de acceso por rol | middleware `rol`, `CitaPolicy`, `PacientePolicy` |
| RF-08 | Reportes e indicadores | `Admin\ReporteController` (incluye exportación CSV) |

**Funciones adicionales para producción:** recuperación de contraseña por correo, notificaciones encoladas (registro, confirmación, reprogramación, cancelación y recordatorio del día anterior), auditoría de acciones, respaldos automáticos de la base de datos, cabeceras de seguridad, cifrado de notas clínicas y límite de intentos de inicio de sesión.

---

## Instalación local (XAMPP)

**Requisitos:** XAMPP con PHP 8.2 o superior, [Composer](https://getcomposer.org/) y [Node.js 20+](https://nodejs.org/).

```bash
# 1. Dependencias
composer install
npm install

# 2. Configuración
copy .env.example .env          # en Linux/Mac: cp .env.example .env
php artisan key:generate

# 3. Base de datos: crea "eirene_citas" en phpMyAdmin (utf8mb4_unicode_ci) y luego:
php artisan migrate --seed

# 4. Compilar el frontend
npm run build                   # o "npm run dev" mientras desarrollas

# 5. Levantar el sistema
php artisan serve               # http://localhost:8000
```

> Si actualizas una instalación anterior (Laravel 8), basta con `composer install`, `npm install && npm run build` y `php artisan migrate`. Las migraciones nuevas conservan tus datos.

### Cuentas de demostración

Solo se crean fuera de producción. La contraseña de todas es **`contraseña`**.

| Rol | Usuario |
|---|---|
| Administrador | `admin` |
| Recepcionista | `recepcion@eirene.test` |
| Psicóloga (Ansiedad, Depresión, Autoestima) | `psicologo1@eirene.test` |
| Psicólogo (Pareja, Infantil) | `psicologo2@eirene.test` |
| Paciente | `paciente@eirene.test` |

En desarrollo los correos no se envían: quedan escritos en `storage/logs/laravel.log`.

---

## Calidad y pruebas

```bash
composer test        # suite completa de pruebas (PHPUnit)
composer lint        # verificación de estilo (Pint)
composer analyse     # análisis estático (PHPStan nivel 6)
composer calidad     # las tres anteriores en secuencia
composer format      # corrige el estilo automáticamente
```

La estrategia de pruebas, la matriz de trazabilidad requerimiento → prueba y los defectos detectados están en **[docs/PLAN_DE_PRUEBAS.md](docs/PLAN_DE_PRUEBAS.md)**.

Cada *push* ejecuta la integración continua ([.github/workflows/ci.yml](.github/workflows/ci.yml)): estilo, análisis estático, auditoría de dependencias, pruebas en PHP 8.2/8.3/8.4 con cobertura, pruebas contra MySQL 8 y compilación del frontend.

---

## Despliegue a producción

La guía paso a paso (hosting compartido y VPS, cron, colas, respaldos, HTTPS y lista de verificación de seguridad) está en **[docs/DESPLIEGUE.md](docs/DESPLIEGUE.md)**.

---

## Estructura del proyecto

```
app/
  Console/Commands/     citas:enviar-recordatorios, eirene:crear-admin
  Enums/                Rol, EstadoCita, EstadoPago, MetodoPago
  Exceptions/           ReglaDeNegocioException
  Http/Controllers/     Controladores delgados por módulo (Auth, Admin, Psicologo, Api)
  Http/Middleware/      VerificarRol, AsegurarUsuarioActivo, CabecerasDeSeguridad
  Http/Requests/        Validación de cada formulario (FormRequest)
  Listeners/            Auditoría de inicios de sesión
  Models/               Modelos Eloquent + trait Auditable
  Notifications/        Correos de citas y de recuperación de contraseña
  Policies/             Autorización: CitaPolicy, PacientePolicy
  Services/             Lógica de negocio: AgendaService, CitaService
config/eirene.php       Reglas de negocio configurables (reprogramaciones, duración, etc.)
database/               Migraciones, factories y seeders
docs/                   Plan de pruebas y guía de despliegue
resources/views/        Vistas Blade y componentes reutilizables (x-card, x-form.input, ...)
resources/js/           Interacciones sin scripts en línea (compatibles con CSP)
routes/console.php      Tareas programadas (cola, recordatorios, respaldos)
tests/                  Pruebas unitarias y de funcionalidad
```

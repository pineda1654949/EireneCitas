# 04 - Diccionario de Datos (MySQL + Eloquent, Laravel 12)

> Adapta el PDF *04 Diccionario de Datos MySQL Sequelize*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).
>
> **Los nombres de esta versión salen de la base de datos real** (`information_schema` de MySQL tras ejecutar todas las migraciones), no del PDF. Donde el PDF usaba otro nombre, se indica en la columna "En el PDF".

## 1. Convenciones de Laravel

- Tablas en plural y minúscula: `pacientes`, `citas`, `pagos`.
- Campos de auditoría `created_at` y `updated_at` (en el PDF: `createdAt` y `updatedAt`).
- Claves primarias `id` (bigint) y foráneas `{modelo}_id`.
- Los estados se guardan como `enum` en MySQL y se leen como **enums de PHP** (`App\Enums\...`).

## 2. Entidades

**users** (modelo `User`): cuentas de acceso de los cuatro roles. *No existe una tabla `psicologos` separada*: un psicólogo es un `User` con `role = psicologo`.

| Campo | Tipo | Nulo / Key | Regla | En el PDF |
|---|---|---|---|---|
| id | bigint | NO / PK | | |
| name, apellidos | varchar(255) | NO / SÍ | | nombres, apellidos |
| email | varchar(255) | NO / UNIQUE | Correo o usuario (por ejemplo `admin`) | username o correo |
| password | varchar(255) | NO | Hash bcrypt (`$2y$`) | |
| dni | varchar(15) | SÍ / UNIQUE | | |
| telefono | varchar(20) | SÍ | `^9\d{8}$` | celular |
| role | enum(administrador, recepcionista, psicologo, paciente) | NO | Por defecto `paciente` | 3 roles |
| activo | tinyint(1) | NO | Por defecto 1 | estado |

**pacientes** (modelo `Paciente`)

| Campo | Tipo | Nulo / Key | Regla | En el PDF |
|---|---|---|---|---|
| id | bigint | NO / PK | | |
| user_id | bigint | SÍ / FK users | Cuenta web del paciente (si se registró) | — |
| psicologo_id | bigint | SÍ / FK users | Asignado al aceptar una derivación | psicologo_id |
| nombres, apellidos | varchar(255) | NO | | |
| dni | varchar(15) | SÍ / **UNIQUE** | 8 a 12 dígitos | dni_pasaporte |
| edad | tinyint unsigned | SÍ | 1 a 120 | edad (> 0) |
| correo | varchar(255) | SÍ / **UNIQUE** | Se guarda en minúsculas | correo |
| telefono | varchar(20) | SÍ | `^9\d{8}$` | celular |
| direccion | varchar(255) | SÍ | | — |
| motivo_consulta | text | SÍ | Síntomas reportados | sintomas |
| estado_atencion | enum(inscripto, en_proceso, finalizado, cancelado) | NO | Por defecto `inscripto` | estado_atencion |

No implementados del PDF (trabajo futuro): `contacto_emergencia_*`, `terapia_previa`, `promocion_id` en el paciente (la promoción se asocia a cada cita).

**citas** (modelo `Cita`)

| Campo | Tipo | Nulo / Key | Regla | En el PDF |
|---|---|---|---|---|
| id | bigint | NO / PK | | |
| paciente_id | bigint | NO / FK pacientes | | |
| psicologo_id | bigint | NO / FK users | | |
| especialidad_id, promocion_id | bigint | SÍ / FK | | |
| fecha | date | NO | Desde hoy | fecha_hora |
| hora | time | NO | Hora libre del horario del psicólogo; se guarda como HH:MM:SS | fecha_hora |
| motivo_consulta | text | SÍ | | — |
| estado | enum(pendiente, confirmada, atendida, cancelada, reprogramada) | NO | Por defecto `pendiente` | programada, reprogramada, completada, cancelada, confirmada |
| numero_reprogramaciones | tinyint unsigned | NO | Por defecto 0; máximo 3 (RN-01) | num_reprogramaciones |
| enlace_meet | varchar(255) | SÍ | Trabajo futuro | link_meet |
| creado_por | bigint | SÍ / FK users | Quién registró la cita | — |

**pagos** (modelo `Pago`): ligado a la **cita** (el PDF lo ligaba al paciente).

| Campo | Tipo | Nulo / Key | Regla | En el PDF |
|---|---|---|---|---|
| cita_id | bigint | NO / FK citas | | paciente_id |
| monto | decimal(8,2) | NO | > 0; dos decimales | decimal(10,2) |
| metodo_pago | enum(tarjeta, yape_plin, transferencia, efectivo) | NO | | Yape, Plin, Transferencia |
| numero_cuota / total_cuotas | tinyint unsigned | NO | 1 por defecto; total ≤ máximo de la promoción | número de cuota / total de cuotas |
| estado | enum(pendiente, confirmado, rechazado) | NO | Por defecto `pendiente` | pendiente, validado, rechazado |
| numero_comprobante | varchar(255) | SÍ | N.º de operación | — |
| comprobante_path / comprobante_nombre | varchar(255) | SÍ | Voucher en disco privado (`storage/app/private`) | comprobante |
| fecha_pago | timestamp | SÍ | Al validarse | fecha de pago |
| validado_por / registrado_por | bigint | SÍ / FK users | Trazabilidad | — |

**promociones** (modelo `Promocion`): `nombre`, `descripcion`, `numero_sesiones` (1 a 50), `precio` decimal(8,2) > 0, `permite_cuotas` (bool), `max_cuotas` (1 a 12) y `activa` (bool).

**horarios** (modelo `Horario`): `psicologo_id` (FK users), `dia_semana` (0 = domingo … 6 = sábado), `hora_inicio` y `hora_fin` (time), `activo` (bool; un bloque pausado se muestra en gris). Sustituye al campo JSON `horario_atencion` del PDF.

**historiales_clinicos** (modelo `HistorialClinico`; "historiales" en el PDF): `cita_id`, `paciente_id`, `psicologo_id`, `notas_sesion` (text, **cifrado** con APP_KEY; equivale a "observaciones"), `avance` y `documento_path` (adjunto; trabajo futuro). No hay un campo "diagnóstico presuntivo" separado.

**derivaciones** (modelo `Derivacion`; nueva): `paciente_id`, `psicologo_id`, `especialidad_id`, `derivado_por`, `observaciones`, `estado` enum(pendiente, aceptada, rechazada), `motivo_rechazo` y `respondida_at`.

**Otras tablas:** `especialidades` y `especialidad_psicologo` (N:N), `reprogramaciones` (historial de cambios y cancelaciones con su motivo), `auditorias` (bitácora), y las tablas técnicas de Laravel 12 (`sessions`, `cache`, `jobs`, `password_reset_tokens`).

## 3. Relaciones Eloquent

| Origen | Relación | Destino | Clave foránea |
|---|---|---|---|
| Paciente | belongsTo | User (psicólogo asignado) | psicologo_id |
| Paciente | belongsTo | User (cuenta web) | user_id |
| Paciente | hasMany | Cita, Derivacion, HistorialClinico | paciente_id |
| User (psicólogo) | hasMany | Cita, Horario, Derivacion | psicologo_id |
| User (psicólogo) | belongsToMany | Especialidad | especialidad_psicologo |
| Cita | belongsTo | Promocion, Especialidad | promocion_id, especialidad_id |
| Cita | hasMany | Pago, Reprogramacion | cita_id |
| Cita | hasOne | HistorialClinico | cita_id |

**Comportamiento de borrado:** las citas, pagos e historiales se borran en cascada con su paciente (`cascadeOnDelete`). Por eso **la aplicación impide** eliminar un paciente con citas, y un psicólogo con citas se **desactiva** en lugar de borrarse. La promoción y la especialidad usan `nullOnDelete`.

## 4. Verificación realizada

```powershell
php artisan migrate:fresh --seed     # crea las tablas
php artisan migrate:reset            # revierte las 19 migraciones
php artisan migrate                  # las vuelve a aplicar
```

Ejecutado en MariaDB 10.4 (XAMPP) y en SQLite. Las 19 migraciones se aplican y se revierten sin errores (`MigracionesYTraduccionesTest`). Durante esta verificación se encontró y corrigió el defecto DEF-007.

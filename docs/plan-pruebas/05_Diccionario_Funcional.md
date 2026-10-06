# 05 - Diccionario de Datos Funcional (Laravel 12)

> Adapta el PDF *05 Diccionario de Datos Sistema Eirene Gestión Clínica*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Propósito

Define los campos desde la perspectiva del proceso clínico: etiqueta en pantalla, tipo de control, regla de validación y atributo en la base de datos. Las reglas de validación pasan de Formik/Yup y Sequelize a **FormRequest de Laravel 12** (`app/Http/Requests`). Las reglas de esta tabla son las **reales**.

## 2. Gestión clínica e inscripción

| Campo / etiqueta | Control | Regla de validación (Laravel 12) | Atributo en BD |
|---|---|---|---|
| Nombres / Apellidos | Text | `required\|string\|max:100` / `max:150` | `pacientes.nombres`, `apellidos` |
| DNI | Text numérico | `nullable\|digits_between:8,12\|unique:pacientes,dni` | `pacientes.dni` |
| Edad | Number | `nullable\|integer\|min:1\|max:120` | `pacientes.edad` |
| Correo electrónico | Email | `nullable\|email\|unique:pacientes,correo` (se guarda en minúsculas) | `pacientes.correo` |
| Celular | Tel (`pattern="9[0-9]{8}"`) | `nullable\|regex:/^9\d{8}$/` | `pacientes.telefono` |
| Motivo de consulta / síntomas | Textarea | `nullable\|string\|max:2000` | `pacientes.motivo_consulta` |
| Estado de atención | Select | `Rule::enum(EstadoAtencion)` | `pacientes.estado_atencion` |
| Notas de la sesión | Textarea | `required\|string\|min:10\|max:10000` (privadas del psicólogo; cifradas) | `historiales_clinicos.notas_sesion` |
| Avance del tratamiento | Select | `nullable` (Inicial, En progreso, Estable, Alta) | `historiales_clinicos.avance` |
| Contacto de emergencia, antecedentes de terapia, diagnóstico presuntivo, adjunto de sesión | — | No implementados | Trabajo futuro |

## 3. Agenda y control de horarios

| Atributo | Control | Regla | Atributo en BD |
|---|---|---|---|
| Fecha de la cita | Date | `required\|date_format:Y-m-d\|after_or_equal:today` | `citas.fecha` |
| Hora de la cita | Botones de la matriz de horas | `required\|date_format:H:i` + debe estar **libre** en el horario del psicólogo (`AgendaService`) | `citas.hora` |
| Estado Libre / Ocupado / Bloqueado | Botón de color | Verde, Rojo, Gris (`api/agenda-del-dia`) | Derivado de `horarios` y `citas` |
| Reprogramaciones | Contador | RN-01: máximo 3, salvo autorización del administrador | `citas.numero_reprogramaciones` |
| Motivo de reprogramación | Textarea | `required\|min:5` desde el 1.er cambio | `reprogramaciones.motivo` |
| Bloque de horario | Día + hora inicio/fin | `hora_fin` posterior a `hora_inicio` y sin cruzarse con otro bloque del mismo día | `horarios.*` |
| Enlace Google Meet | URL | Trabajo futuro | `citas.enlace_meet` |

## 4. Seguimiento, derivación y pagos

| Campo | Valores / reglas | Estado en el código |
|---|---|---|
| Estado de la derivación | Pendiente, Aceptada, Rechazada | ✅ `derivaciones.estado` |
| Motivo de rechazo | Obligatorio al rechazar, mínimo 10 caracteres | ✅ `RechazarDerivacionRequest` |
| Estado de atención | Inscripto, En proceso, Finalizado, Cancelado | ✅ `pacientes.estado_atencion` |
| Modalidad de pago | Al contado o de 2 a N cuotas (N = `max_cuotas` de la promoción) | ✅ `pagos.total_cuotas`, `numero_cuota` |
| Estado del comprobante | Pendiente, Confirmado (validado), Rechazado | ✅ `pagos.validar` / `pagos.rechazar` |
| Voucher | `file\|mimes:jpg,jpeg,png,pdf\|max:5120`; obligatorio si lo sube el paciente | ✅ `pagos.comprobante_path` (disco privado) |

## 5. Matriz de validación por capa

| Campo | Frontend (HTML) | Backend (Laravel 12) |
|---|---|---|
| DNI | `inputmode="numeric"`, `pattern="[0-9]{8,12}"`, `maxlength="12"` | `digits_between:8,12`, `unique` |
| Correo | `type="email"` | `email`, `unique`, normalizado a minúsculas |
| Celular | `pattern="9[0-9]{8}"`, `maxlength="9"` | `regex:/^9\d{8}$/` |
| Reserva de horario | Horas ocupadas y bloqueadas deshabilitadas (rojo y gris) | `DB::transaction` + `lockForUpdate()` + nueva comprobación de disponibilidad |
| Voucher | `accept=".jpg,.jpeg,.png,.pdf"` | `mimes`, `max:5120`, almacenamiento en disco privado |
| Reprogramación | Botón oculto con aviso al llegar a 3 | Regla en `CitaService::reprogramar` |

Las dos capas se prueban por separado: el backend con PHPUnit (CP-UT-20 a 30) y el frontend con Dusk (CP-SYS-01, 06, 09).

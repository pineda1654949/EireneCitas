# 02 - Análisis Funcional del MVP (Laravel 12)

> Adapta el PDF *02 Análisis Funcional del MVP Sistema Eirene*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Alcance funcional

El sistema reemplaza WhatsApp, Excel y Drive. Interactúan cuatro roles: administrador, recepcionista, psicólogo y paciente. El PDF original solo menciona dos.

## 2. Módulo de gestión clínica e inscripción

El PDF describe un **stepper de 5 pasos**. En EireneCitas el contenido se reparte así:

| Paso del PDF | Contenido | Dónde está en el sistema |
|---|---|---|
| 1 | DNI, nombres, apellidos, edad, correo, celular | Ficha del paciente (`admin/pacientes/create`) o registro público (`registro`) |
| 2 | Motivo de consulta y síntomas | Ficha del paciente (`motivo_consulta`) y sección 4 de `citas/crear` |
| 3 | Promoción y psicólogo por especialidad | Secciones 2 y 4 de `citas/crear` (vía `api/especialidades/{id}/psicologos`) |
| 4 | Método de pago y cuotas | `citas/{cita}/pagos/registrar` (método, modalidad en cuotas y voucher) |
| 5 | Resumen y confirmación | Detalle de la cita (`citas/{cita}`), con el estado y los pagos |

**Decisión de diseño:** `citas/crear` es un **formulario guiado en una sola página**, con secciones numeradas (1. Paciente, 2. Profesional, 3. Fecha y hora, 4. Detalles), en lugar de un *stepper* de 5 pantallas. Se eligió así porque cada sección depende de la anterior en tiempo real y el usuario ve todo el contexto a la vez. Los datos de antecedentes de terapia y contacto de emergencia del PDF **no** se implementaron (trabajo futuro).

## 3. Módulo de agenda y disponibilidad

| Estado | Color | Significado | Acción |
|---|---|---|---|
| Libre | Verde | Horario disponible del psicólogo | Se puede reservar |
| Ocupado | Rojo (tachado) | Hay una cita pendiente, confirmada o reprogramada | Bloqueado |
| Bloqueado | Gris | Bloque pausado por el psicólogo u hora ya pasada o sin la anticipación mínima | Bloqueado |

Los horarios se gestionan en `psicologo/horarios`: el psicólogo agrega, **pausa o reactiva** y elimina bloques semanales. La matriz se consulta en `api/agenda-del-dia` y se dibuja en `citas/crear` y `citas/{cita}/reprogramar`, con su leyenda (CP-UT-43, captura `CP-SYS-01b-matriz-de-horas.png`).

**RN-01 (crítica):** máximo 3 reprogramaciones por cita. Al 4.º cambio el sistema rechaza y exige la autorización del administrador.

## 4. Seguimiento clínico y estados

- **Derivación:** ✅ implementada. La recepción o el administrador derivan desde la ficha del paciente. El psicólogo recibe un correo y responde en `psicologo/derivaciones`. Si acepta, el paciente queda asignado (`pacientes.psicologo_id`); si rechaza, el motivo es obligatorio (mínimo 10 caracteres).
- **Expediente por sesión:** `psicologo/citas/{cita}/historial/crear` y `psicologo/pacientes/{paciente}/historial`. Las notas se guardan **cifradas** en la base de datos.
- **Ciclo de vida del paciente:** ✅ `pacientes.estado_atencion` con Inscripto (por defecto), En proceso (automático con la primera sesión atendida), Finalizado y Cancelado (los fija la recepción en la ficha).

## 5. Catálogo de requerimientos: un solo catálogo

Se adopta como único el catálogo de la [Fase 1](01_Fase1_Requirements_Gathering.md). Equivalencias:

| Concepto de este PDF | ID en el catálogo de la Fase 1 | Numeración antigua del `README.md` |
|---|---|---|
| Registro completo de paciente | RF-02 | RF-05 |
| Asignación y derivación | RF-05 | — |
| Generación de citas por promoción | RF-02 / RF-03 | RF-01 |
| Plan de cuotas y pagos | RF-03 / RF-04 | RF-04 |
| Filtros y búsqueda | RF-02 (listado `admin/pacientes`) | RF-05 |
| Gestión de agenda | RF-06 | RF-02 |
| Expediente terapéutico | RF-08 | RF-06 |
| Control de reprogramaciones | RF-07 | RF-03 |
| Control de acceso | RF-01 | RF-07 |
| Reportes e indicadores | (no estaba en la Fase 1) | RF-08 |

## 6. Impacto TO-BE

El PDF afirma pasar de 16 tareas manuales a 3 pasos de supervisión. Debe **medirse** con el flujo real en la UAT antes de citarlo como resultado (plantilla en la [Fase 9](09_Fase9_Acceptance_Testing.md)).

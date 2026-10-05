# Fase 1: Requirements Gathering (Laravel 12)

> Adapta el PDF *01 Fase 1 Requirements Gathering*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Propósito

Capturar y formalizar los requerimientos funcionales y las reglas de negocio (modelo V-Bounce, Rama I). El ciclo de 6 pasos (Input, AI Generation, Human Review, Refinement, Approval, Knowledge Capture) no depende del lenguaje y se mantiene.

## 2. Problemas AS-IS y solución TO-BE

| Problema AS-IS | Solución TO-BE en Laravel 12 | RF |
|---|---|---|
| Agendamiento por WhatsApp/Excel | Citas y disponibilidad en tiempo real (matriz de colores) | RF-02, RF-06 |
| Verificación manual de vouchers | Voucher adjunto al pago; la recepción lo valida o rechaza | RF-03, RF-04 |
| Reprogramaciones ilimitadas | Límite de 3 con autorización del administrador (RN-01) | RF-07 |
| Expedientes dispersos en Drive | Historial clínico cifrado, visible solo para el psicólogo tratante | RF-01, RF-08 |
| Derivación informal | Derivación con aceptación o rechazo justificado | RF-05 |

## 3. Catálogo de requerimientos (catálogo único del proyecto)

Este es el **catálogo único**. El documento [02 Análisis Funcional](02_Analisis_Funcional.md) y el `README.md` del proyecto usaban otra numeración; la tabla de equivalencias está en ese documento.

| ID | Requerimiento | Actor | Implementación en Laravel 12 | Estado |
|---|---|---|---|---|
| RF-01 | Autenticación y control de acceso por roles | Todos | `SesionController`, middleware `auth` y `rol`, `CitaPolicy`, `PacientePolicy`, `DerivacionPolicy` | ✅ Operativo |
| RF-02 | Inscripción guiada de pacientes | Admin / Recepción / Paciente | `admin/pacientes`, `citas/crear` (secciones numeradas), registro público | ✅ Operativo |
| RF-03 | Catálogo de promociones y cuotas | Administrador | `admin/promociones` (`permite_cuotas`, `max_cuotas`) | ✅ Operativo |
| RF-04 | Gestión financiera y verificación de pagos | Recepción / Admin / Paciente | `PagoController`, `CitaService::registrarPago` y `validarPago` | ✅ Operativo |
| RF-05 | Derivación de pacientes | Admin / Recepción / Psicólogo | `DerivacionService`, `Admin\DerivacionController`, `Psicologo\DerivacionController` | ✅ Operativo |
| RF-06 | Agenda y control de horarios | Psicólogo | `HorarioController`, `AgendaService`, `api/agenda-del-dia` | ✅ Operativo |
| RF-07 | Control de reprogramaciones (RN-01) | Todos | `CitaService::reprogramar`, `Cita::puedeReprogramarsePor` | ✅ Límite verificado |
| RF-08 | Expediente clínico | Psicólogo | `HistorialController`, notas cifradas (`encrypted`) | ✅ Operativo |

## 4. Matriz de trazabilidad (requerimiento → prueba)

| RF | Atributo ISO 25010 | Módulo Laravel 12 | Casos de prueba que lo verifican | Resultado |
|---|---|---|---|---|
| RF-01 | Seguridad | `VerificarRol`, Policies, `LoginRequest` | CP-UT-31 a 38, CP-IT-01 a 04, CP-IT-30 a 35, CP-SYS-11 a 23 | ✅ |
| RF-02 | Adecuación funcional | `PacienteController`, `CitaController@store` | CP-UT-10 a 14, 20 a 26, 39 a 42, CP-IT-05 a 08, CP-SYS-01, CP-SYS-09 | ✅ |
| RF-03 | Adecuación funcional | `PromocionController` | CP-UT-17, CP-IT-09 a 11 | ✅ |
| RF-04 | Confiabilidad | `PagoController`, `CitaService` | CP-UT-04 a 06, 16, 27 a 29, CP-IT-12 a 16, CP-SYS-02, CP-SYS-18, CP-SYS-34 | ✅ |
| RF-05 | Eficiencia operativa | `DerivacionService` | CP-UT-49 a 51, CP-IT-23 a 25, CP-SYS-04, CP-SYS-05 | ✅ |
| RF-06 | Usabilidad | `HorarioController`, `AgendaService` | CP-UT-07, 08, 43 a 45, CP-IT-17, CP-IT-21, CP-SYS-06, CP-SYS-25 | ✅ |
| RF-07 | Confiabilidad | `CitaService::reprogramar` | CP-UT-01 a 03, 15, 46, CP-IT-18 a 20, CP-SYS-03 | ✅ |
| RF-08 | Seguridad / Privacidad | `HistorialController`, `PacientePolicy` | CP-UT-30, CP-IT-26, CP-IT-27, CP-SYS-07, CP-SYS-17 | ✅ |

## 5. Reglas de negocio

- **RN-01:** una cita se reprograma como máximo 3 veces (configurable con `CITAS_MAX_REPROGRAMACIONES`). Al 4.º intento el sistema rechaza el cambio con el mensaje *"Se alcanzó el límite de 3 reprogramaciones. Un nuevo cambio requiere la autorización del administrador."* Solo el **administrador** puede autorizar un cambio adicional (CP-IT-19b). Cada cambio exige un **motivo** (CP-UT-03), que queda en la tabla `reprogramaciones`. En web la respuesta es una redirección con error (422 si la petición es JSON).
- **RN-02:** una cita no pasa a *confirmada* sin un pago en estado *confirmado*. Se verifica en `PUT citas/{cita}/confirmar`. Al validar el pago, la cita se confirma automáticamente (CP-UT-04 a 06, CP-IT-12 y 16).
- **RN-03:** dos usuarios no pueden reservar la misma hora. Se resuelve con `DB::transaction()` y `lockForUpdate()` sobre la fila del psicólogo. Verificado con 30 reservas simultáneas reales en k6: 1 éxito y 29 rechazos (CP-SYS-25). Además, un mismo paciente no puede tener dos citas a la misma hora con psicólogos distintos.

## 6. Criterios de aprobación

- [x] Todos los procesos TO-BE tienen al menos un RF.
- [x] Las reglas tienen límites cuantitativos definidos (3 reprogramaciones, 50 min por sesión, voucher de 5 MB, 2 a 12 cuotas).
- [ ] Catálogo revisado por la dirección de la clínica (pendiente de firma).
- [x] Trazabilidad completa hacia las pruebas (sección 4).

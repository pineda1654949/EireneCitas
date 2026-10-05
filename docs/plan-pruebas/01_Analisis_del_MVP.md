# 01 - Análisis del MVP (Laravel 12)

> Adapta el PDF *01 Análisis del MVP Sistema Eirene*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)). Las marcas [VERIFICAR] de la versión anterior están resueltas.

## 1. Resumen ejecutivo y diagnóstico AS-IS

La Clínica Psicológica Eirene gestionaba todo con un único administrador mediante WhatsApp, hojas de Excel y Google Drive. Esto generaba cuellos de botella, pérdida de información, falta de control de pagos y ausencia de trazabilidad clínica. El MVP es una plataforma web en **Laravel 12 + MySQL** que centraliza captación, agenda, derivación y control financiero.

## 2. Definición y propósito del MVP

Plataforma web cliente-servidor (vistas Blade renderizadas en el servidor, con Tailwind CSS 4 y JavaScript propio sin dependencias externas) que conecta a paciente, administración y psicólogos, con persistencia de datos, precisión en la agenda y transparencia en los pagos.

Objetivos estratégicos:

- Sustituir WhatsApp + Excel + Drive por un entorno único con roles diferenciados.
- Registrar pacientes y citas de forma guiada y validada.
- Controlar los pagos ligados a las citas, con voucher adjunto y pago en cuotas.
- Dar al psicólogo el control de su agenda, de sus derivaciones y del historial clínico.

## 3. Alcance operativo por rol (rutas reales)

| Rol | Módulos | Rutas |
|---|---|---|
| Administrador | Todo lo de recepción + psicólogos, promociones y auditoría | `admin/*`, `pagos`, `reportes` |
| Recepcionista | Pacientes, derivaciones, citas, validación de pagos, reportes | `admin/pacientes`, `admin/derivaciones`, `citas`, `pagos`, `reportes` |
| Psicólogo | Su agenda, horarios, derivaciones recibidas e historial clínico | `psicologo/*`, `citas` (solo las suyas) |
| Paciente | Solicitar, ver, reprogramar y cancelar **sus** citas; subir el voucher de pago | `citas/*`, `citas/{cita}/pagos` |

Diferencia con el PDF original: el documento solo habla de Administrador y Psicólogo. El sistema tiene además los roles **recepcionista** y **paciente** (este último para el autoregistro y la reserva en línea).

## 4. Macroprocesos cubiertos

| # | Proceso | Cobertura |
|---|---|---|
| 1 | Captación e inscripción | `admin/pacientes/create` (ficha completa) y `citas/crear` (formulario guiado por secciones). Registro público en `registro` |
| 2 | Asignación y derivación | ✅ `admin/pacientes/{paciente}/derivaciones` y `psicologo/derivaciones` (aceptar/rechazar con motivo) |
| 3 | Gestión de agenda | `psicologo/horarios` y la matriz de colores `api/agenda-del-dia` |
| 4 | Seguimiento clínico | `psicologo/citas/{cita}/historial` y `psicologo/pacientes/{paciente}/historial` |
| 5 | Control de pagos | `citas/{cita}/pagos` (voucher y cuotas), `pagos/{pago}/validar`, `pagos/{pago}/rechazar` |
| 6 | Administración del equipo | `admin/psicologos`, `admin/promociones`, `admin/auditoria` |

## 5. Estado técnico: operativo y pendiente

| Funcionalidad | Estado | Evidencia |
|---|---|---|
| Login, logout, registro y recuperación de contraseña | ✅ Operativo | CP-IT-01 a 04, CP-UT-35, CP-UT-48 |
| Roles con middleware `rol:` y Policies | ✅ Operativo | CP-UT-31 a 33, CP-SYS-11, CP-SYS-15 |
| CRUD de pacientes, promociones y psicólogos | ✅ Operativo | CP-IT-05 a 08, CP-UT-12/13/17 |
| Citas: crear, ver, reprogramar, cancelar, confirmar | ✅ Operativo, límite de 3 reprogramaciones verificado | CP-UT-01 a 03, CP-IT-18 a 20, CP-SYS-03 |
| Pagos: registrar (con voucher y cuotas), validar, rechazar | ✅ Operativo | CP-UT-27 a 29, CP-IT-09 a 16 |
| Historial clínico (notas cifradas) | ✅ Operativo | CP-IT-26, CP-IT-27, CP-SYS-07 |
| Derivación aceptar/rechazar | ✅ Operativo | CP-UT-49 a 51, CP-IT-23 a 25, CP-SYS-04/05 |
| Recordatorios por correo 24 h | ✅ Comando programado `citas:enviar-recordatorios` | CP-IT-37 |
| Exportación | ✅ CSV (Excel). PDF pendiente | `ReporteYAuditoriaTest` |
| Enlace Google Meet automático | ⏳ Trabajo futuro | — |

## 6. Valor operativo e impacto

El PDF original afirma que la inscripción baja de 15 minutos a menos de 3. Ese dato debe **medirse** en la UAT (Fase 9) y no presentarse como resultado obtenido mientras no se mida. La plantilla de medición está en [09_Fase9_Acceptance_Testing.md](09_Fase9_Acceptance_Testing.md).

## 7. Conclusión

El MVP en Laravel 12 cubre los ocho requerimientos funcionales del catálogo (ver [Fase 1](01_Fase1_Requirements_Gathering.md)). Solo quedan como trabajo futuro la integración con Google Meet, la exportación a PDF y la medición de impacto con usuarios reales.

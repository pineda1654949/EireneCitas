# Documento de Sustento Tecnológico y Stack (Laravel 12)

> Adapta el PDF *Doc Sustento Tecnológico y Stack Eirene*. Proyecto EireneCitas sobre **Laravel 12** (v12.69.3; antes Laravel 8.83, ver [00](00_Equivalencias_Laravel.md)).

## 1. Por qué este documento es el que más cambia

El original justifica **React + Node.js + Express + MySQL + bcrypt + JWT** con Beltrán et al. (2025), y las funciones de agenda, recordatorios y consentimiento con Olive et al. (2025). El sistema real usa **Laravel 12 + Blade + MySQL**. Esas fuentes **no respaldan Laravel**: no las presentes como respaldo del framework.

## 2. Matriz de tecnologías

| Tecnología | Función | ¿Respaldada por las fuentes del PDF? | Qué hacer |
|---|---|---|---|
| Arquitectura en 3 capas | Separación de capas | Sí (Beltrán et al.); el concepto aplica | Mantener |
| MySQL / MariaDB | Base de datos relacional | Sí (Beltrán et al.) | Mantener |
| bcrypt (`Hash` de Laravel) | Hash de contraseñas | Sí (Beltrán et al.) | Mantener |
| Control de acceso por roles | RBAC | El concepto sí; la implementación es distinta (sesión + `role` + Policies, no JWT) | Mantener y aclarar la diferencia |
| **Laravel 12 + Eloquent** | Backend y ORM | **No** | Justificar con otra fuente (sección 3) |
| **Blade + Tailwind CSS 4** | Capa de presentación | **No** | Justificar con otra fuente |
| React / Node / Express / JWT | Stack original | Sí, pero **no se usa** | Quitar del documento |
| Recordatorios 24 h | Reducir el ausentismo | Sí (Olive et al.) | ✅ **Implementado** (`citas:enviar-recordatorios`): mantener |
| Enlace de videollamada | Teleatención | Sí, pero pendiente | Marcar como trabajo futuro |
| Dashboard ROM, consentimiento digital | Seguimiento y ética | Sí (Olive et al.) | Trabajo futuro (no implementados) |

## 3. Cómo justificar Laravel 12

**Fuente técnica oficial** (verificable y citable):

- Documentación oficial de Laravel 12: <https://laravel.com/docs/12.x>. Capítulos aplicables: *Authentication*, *Authorization* (Policies), *Validation* (FormRequest), *Eloquent ORM*, *Database: Migrations*, *Notifications*, *Task Scheduling*, *Testing* y *Laravel Dusk*.
- Política de versiones y soporte de seguridad de Laravel: <https://laravel.com/docs/12.x/releases>. Respalda el riesgo RSG-07 y la decisión de migrar desde Laravel 8.

**Fuente académica:** busca al menos un artículo o una tesis que use **Laravel/PHP + MySQL** en un sistema de salud o de gestión de citas, y cítalo en el formato de tu universidad. Términos de búsqueda sugeridos en Google Scholar, Scielo o repositorios universitarios: *"sistema de citas" Laravel*, *"Laravel" "historia clínica"*, *Laravel framework web application healthcare appointment*.

Fuentes del equipo: **[FUENTE ACADÉMICA A COMPLETAR]**.

> No se incluyen referencias académicas inventadas: cada fuente debe ser leída y verificada por el equipo antes de citarla.

## 4. Stack real y razones técnicas

| Componente | Versión | Razón técnica (verificable en el proyecto) |
|---|---|---|
| PHP | 8.2+ | Requisito de Laravel 12; enums nativos usados para estados y roles |
| Laravel | 12.69.3 | Soporte de seguridad vigente; autenticación, autorización, validación, colas y tareas programadas integradas |
| MySQL / MariaDB | 8.0 / 10.4 | Transacciones y bloqueo de filas (`lockForUpdate`) para RN-03 |
| Tailwind CSS + Vite | 4 / 7 | Assets compilados sin CDN, compatibles con una Content-Security-Policy estricta |
| PHPUnit / Dusk / k6 | 11.5 / 8.7 / 2.2 | Cubren las Fases 6, 7 y 8 del plan |
| Larastan / Pint | 3.12 / 1.30 | Análisis estático y estilo (Fase 5) |

## 5. Redacción sugerida (sin citar lo que no respalda)

> "La arquitectura del Sistema Web de la Clínica Eirene sigue un modelo en tres capas, consistente con lo reportado por Beltrán et al. (2025) para plataformas de e-salud, y emplea una base de datos relacional MySQL y el hash de contraseñas con bcrypt. El control de acceso se implementa mediante roles (administrador, recepcionista, psicólogo y paciente) y políticas de autorización por pertenencia. Como framework de desarrollo se seleccionó Laravel 12, por contar con soporte de seguridad vigente y con mecanismos integrados de autenticación, autorización, validación y transacciones (Laravel, 2025) y **[justificación y fuente académica a completar]**. Asimismo, siguiendo a Olive et al. (2025), el sistema envía recordatorios automáticos de cita con 24 horas de anticipación como funcionalidad de valor en clínicas de psicoterapia."

## 6. Riesgo tecnológico (actualizado)

La versión anterior de este documento indicaba: *"Laravel 8 ya no recibe correcciones de seguridad (desde enero de 2023)"*. **Ese riesgo se resolvió:** el proyecto se migró a **Laravel 12**, que tiene soporte de seguridad vigente, y `composer audit` no reporta vulnerabilidades (CP-SYS-33). Riesgo residual: mantener el proyecto actualizado dentro de la rama 12 y planificar la migración a la siguiente versión mayor antes de que termine el soporte de Laravel 12.

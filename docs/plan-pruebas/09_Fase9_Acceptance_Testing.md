# Fase 9: Acceptance Testing (UAT) (Laravel 12)

> Adapta el PDF *09 Fase 9 Acceptance Testing*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

> **Estado: instrumentos listos, ejecución pendiente.** Esta fase la ejecutan **usuarios reales** de la clínica. Los resultados no pueden salir de las pruebas automatizadas ni de los PDF. Las tablas de resultados de este documento están vacías a propósito.

## 1. Preparación

1. Sistema con datos de demostración: `php artisan migrate:fresh --seed`. Contraseña de las cuentas de demostración: `contraseña`.
2. Un participante por perfil como mínimo (recomendado: 5 en total). Cada uno usa su propia cuenta:

| Perfil | Cuenta de demostración |
|---|---|
| Administrador | `admin` |
| Recepcionista | `recepcion@eirene.test` |
| Psicóloga | `psicologo1@eirene.test` |
| Psicólogo | `psicologo2@eirene.test` |
| Paciente | `paciente@eirene.test` (o crear una cuenta en `/registro`) |

3. El facilitador **no ayuda** durante la tarea: solo lee el escenario, cronometra y anota.
4. **Datos que el facilitador prepara antes de cada escenario** (con la cuenta de recepción o de administrador):
   - CP-UAT-03 y 04: una cita del paciente con un pago pendiente y su voucher (el paciente puede subirlo desde "Reportar pago").
   - CP-UAT-08 y 09: dos derivaciones a la psicóloga, desde la ficha de dos pacientes distintos ("Derivar paciente").
   - CP-UAT-11: una cita de la psicóloga con fecha **de hoy**. No se pueden registrar sesiones de citas futuras.
   - CP-UAT-13: una cita del paciente en una fecha próxima.

## 2. Casos CP-UAT con guion

Los 15 casos se mantienen, con los roles reales. Los casos 08 y 09 (derivación) ya se pueden ejecutar porque la derivación está implementada.

| ID | Perfil | Escenario (lo que se lee al participante) | Criterio de aceptación | Resultado | Tiempo | Observaciones |
|---|---|---|---|---|---|---|
| CP-UAT-01 | Paciente / Recepción | "Regístrese y solicite una cita de ansiedad para el próximo lunes por la mañana" | Cita creada y visible en "Mis citas" | ☐ Sí ☐ No | ___ min | |
| CP-UAT-02 | Paciente / Recepción | "Registre un paciente con el celular 12345 y corrija el error que le indique el sistema" | Entiende el mensaje y lo corrige sin ayuda | ☐ Sí ☐ No | ___ min | |
| CP-UAT-03 | Recepcionista | "Valide el pago pendiente de la cita y confirme la cita" | Pago validado y cita confirmada | ☐ Sí ☐ No | ___ min | |
| CP-UAT-04 | Recepcionista | "El voucher que subió el paciente no se lee bien: rechácelo" | Pago rechazado; la cita sigue pendiente | ☐ Sí ☐ No | ___ min | |
| CP-UAT-05 | Administrador | "Cree la promoción 'Paquete 6 sesiones' de S/ 420, pagable en 3 cuotas, y luego cambie el precio" | Promoción creada y editada | ☐ Sí ☐ No | ___ min | |
| CP-UAT-06 | Administrador | "Encuentre a la paciente Ana Torres por su DNI" | La encuentra con el buscador | ☐ Sí ☐ No | ___ min | |
| CP-UAT-07 | Administrador | "Diga cuántas citas se atendieron el último mes y exporte el reporte" | Lee el indicador y descarga el CSV | ☐ Sí ☐ No | ___ min | |
| CP-UAT-08 | Psicólogo | "Le derivaron un paciente: revíselo y acéptelo" | Derivación aceptada | ☐ Sí ☐ No | ___ min | |
| CP-UAT-09 | Psicólogo | "Rechace la otra derivación, explicando el motivo" | Rechazada con motivo | ☐ Sí ☐ No | ___ min | |
| CP-UAT-10 | Psicólogo | "Agregue su horario del sábado de 9 a 12 y pause el del viernes por la tarde" | Bloque creado y pausado (gris en la agenda) | ☐ Sí ☐ No | ___ min | |
| CP-UAT-11 | Psicólogo | "Registre las notas de la sesión de hoy" | Historial guardado; cita atendida | ☐ Sí ☐ No | ___ min | |
| CP-UAT-12 | Psicólogo | "Intente abrir la historia clínica de un paciente que no atiende" (el facilitador le da la URL) | Ve "Acceso no permitido" | ☐ Sí ☐ No | ___ min | |
| CP-UAT-13 | Paciente / Recepción | "Reprograme su cita hasta que el sistema ya no lo permita" | Entiende el aviso del límite de 3 | ☐ Sí ☐ No | ___ min | |
| CP-UAT-14 | Paciente | "Revise el correo de recordatorio de su cita de mañana" | Lo recibe un día antes. En pruebas: `php artisan citas:enviar-recordatorios` y revisar `storage/logs/laravel.log` | ☐ Sí ☐ No | — | |
| CP-UAT-15 | Todos | Ciclo completo: registro → derivación → cita → voucher → validación → sesión → paciente finalizado | Todos los pasos se completan | ☐ Sí ☐ No | ___ min | |

## 3. Cuestionario SUS (System Usability Scale)

Cada participante responde al terminar sus tareas, en una escala de 1 (totalmente en desacuerdo) a 5 (totalmente de acuerdo):

| # | Afirmación | 1 | 2 | 3 | 4 | 5 |
|---|---|---|---|---|---|---|
| 1 | Creo que me gustaría usar este sistema con frecuencia. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 2 | Encontré el sistema innecesariamente complejo. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 3 | Pensé que el sistema era fácil de usar. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 4 | Creo que necesitaría el apoyo de un técnico para poder usar este sistema. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 5 | Encontré que las funciones del sistema estaban bien integradas. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 6 | Pensé que había demasiada inconsistencia en el sistema. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 7 | Imagino que la mayoría de las personas aprendería a usar este sistema muy rápido. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 8 | Encontré el sistema muy difícil de usar. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 9 | Me sentí muy seguro(a) usando el sistema. | ☐ | ☐ | ☐ | ☐ | ☐ |
| 10 | Necesité aprender muchas cosas antes de poder usar el sistema. | ☐ | ☐ | ☐ | ☐ | ☐ |

**Cálculo del puntaje:**
- Ítems impares (1, 3, 5, 7, 9): respuesta − 1.
- Ítems pares (2, 4, 6, 8, 10): 5 − respuesta.
- **Puntaje SUS = suma × 2,5** (de 0 a 100).

**Puntaje objetivo: 80 o más** (excelente: por encima del promedio de la industria, que es 68).

La plantilla para registrar las respuestas está en [`evidencias/plantilla-sus.csv`](evidencias/plantilla-sus.csv). En una hoja de cálculo, el puntaje de cada fila se obtiene con:

```
=((B2-1)+(5-C2)+(D2-1)+(5-E2)+(F2-1)+(5-G2)+(H2-1)+(5-I2)+(J2-1)+(5-K2))*2,5
```

## 4. Plantilla de resultados

| Participante | Perfil | Casos aprobados | SUS | Comentarios |
|---|---|---|---|---|
| P1 | | / | | |
| P2 | | / | | |
| P3 | | / | | |
| P4 | | / | | |
| P5 | | / | | |
| **Promedio** | | | | |

## 5. Indicadores de negocio (medir, no suponer)

Los PDF afirman mejoras que deben **medirse** en esta fase:

| Indicador | Afirmación del PDF | Cómo medirlo | AS-IS medido | TO-BE medido |
|---|---|---|---|---|
| Tiempo de inscripción de un paciente | De 15 a menos de 3 minutos | Cronometrar CP-UAT-01 y el mismo proceso por WhatsApp/Excel | ___ min | ___ min |
| Tareas manuales del proceso | De 16 tareas a 3 pasos de supervisión | Contar los pasos de CP-UAT-15 | 16 | ___ |
| Vouchers extraviados | Se eliminan | Pagos sin voucher en un mes de uso | ___ | ___ |
| Reprogramaciones por cita | Máximo 3 | Promedio en el reporte (`reportes`) | ___ | ___ |

## 6. Criterios de aprobación

- [ ] Al menos el 90 % de los casos CP-UAT aprobados por los participantes.
- [ ] Puntaje SUS promedio ≥ 80.
- [ ] Indicadores de negocio medidos (no estimados).
- [ ] Observaciones de los usuarios registradas e incorporadas al backlog.

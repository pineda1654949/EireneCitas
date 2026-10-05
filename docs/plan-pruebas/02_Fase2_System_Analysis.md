# Fase 2: System Analysis y gestión de riesgos (Laravel 12)

> Adapta el PDF *02 Fase 2 System Analysis*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Límites del sistema (TO-BE)

| Componente | Incluido | Excluido |
|---|---|---|
| Captación e inscripción | Registro de pacientes y citas validado | Campañas publicitarias externas |
| Agenda | Horarios por psicólogo, matriz de colores y control RN-01 | Sincronización con agendas personales |
| Validación financiera | Registro de pagos, voucher, cuotas, validar/rechazar | Pasarela de cobro en línea |
| Seguridad | Sesión, roles, Policies, aislamiento y cifrado del historial | Peritajes o diagnóstico legal |
| Atención | Citas, reprogramación, derivación, historial | Videollamada propia (Meet es trabajo futuro) |

## 2. Factibilidad

- **Técnica:** Laravel 12, MySQL y Blade son tecnologías maduras. **Laravel 12 tiene soporte de seguridad vigente**; Laravel 8, que se usaba antes, lo había perdido en enero de 2023.
- **Operativa:** los formularios guiados reducen errores de digitación. El tiempo de aprendizaje debe medirse en la UAT.
- **Económica:** reducción de pérdidas por vouchers extraviados y seguimiento de cuotas. Las cifras del PDF (de 15 a 3 minutos) deben validarse con medición.

## 3. Matriz de riesgos (estado actualizado)

| ID | Riesgo | Nivel | Mitigación en Laravel 12 | Estado |
|---|---|---|---|---|
| RSG-01 | Pérdida de datos del formulario por recarga | Alto | `old()` en todos los componentes de formulario y validación en servidor | ✅ Mitigado (CP-UT-42, CP-SYS-09) |
| RSG-02 | Acceso no autorizado a expedientes | Crítico | Middleware `rol:psicologo` + `PacientePolicy` (solo el psicólogo tratante o asignado) + notas cifradas | ✅ Mitigado (CP-IT-27, CP-SYS-07, CP-SYS-17) |
| RSG-03 | Doble reserva de horario | Alto | `DB::transaction` + `lockForUpdate()` sobre la fila del psicólogo | ✅ Mitigado (CP-IT-21; 30 reservas simultáneas en CP-SYS-25) |
| RSG-04 | Exceso de reprogramaciones | Medio | Contador en `citas` + regla en `CitaService` + autorización del administrador | ✅ Mitigado (CP-UT-01, CP-IT-19) |
| RSG-05 | Vouchers falsos | Alto | Validación manual por la recepción + reglas `mimes`/`max` + archivos en disco privado | ✅ Mitigado (CP-UT-27 a 29, CP-SYS-18, CP-SYS-34) |
| RSG-06 | Caída del servidor | Medio | Páginas de error propias, `APP_DEBUG=false`, logs diarios, respaldos automáticos | ✅ Mitigado (CP-SYS-21) |
| RSG-07 | Framework sin parches de seguridad | Alto | **Migración a Laravel 12** + `composer audit` en la CI | ✅ Resuelto (CP-SYS-33) |
| RSG-08 | Rutas de `citas` solo con `auth` (sin rol) | Alto | `CitaPolicy` limita ver, reprogramar y cancelar a la cita propia o al personal | ✅ Resuelto (CP-SYS-15) |
| RSG-09 | Rendimiento sin OPcache | Medio | Activar OPcache en el servidor de producción (ver [Fase 8](08_Fase8_System_Testing.md)) | ⚠️ Medido: sin OPcache una página tarda unos 690 ms; con OPcache, unos 60 ms |
| RSG-10 | Antivirus que elimina archivos de prueba con firmas maliciosas | Bajo | Los archivos falsos de las pruebas usan contenido inofensivo | ✅ Mitigado (hallazgo de la Fase 8) |

RSG-07 y RSG-08 salieron de la revisión de las rutas; RSG-09 y RSG-10 aparecieron al ejecutar la Fase 8.

## 4. Criterios de aprobación

1. [ ] Límites aprobados por la dirección de la clínica.
2. [x] Factibilidad ratificada (técnica comprobada con las Fases 6 a 8).
3. [x] Matriz de riesgos integrada al backlog y a las pruebas.
4. [ ] Firma de línea base.

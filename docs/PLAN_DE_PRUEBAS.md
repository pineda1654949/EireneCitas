# Plan y reporte de pruebas · Eirene

## 1. Objetivo

Verificar que el Sistema de Gestión de Citas Eirene cumple sus requerimientos funcionales (RF-01 a RF-08) y los requisitos no funcionales de **confiabilidad de reglas de negocio**, **seguridad** y **mantenibilidad**, antes de su paso a producción.

## 2. Alcance

| Incluido | Excluido |
|---|---|
| Reglas de negocio (servicios y modelos) | Pruebas de carga y estrés |
| Controladores, validaciones y autorización (HTTP) | Pruebas end-to-end en navegador real |
| Notificaciones por correo (contenido y destinatarios) | Entrega real de correos por SMTP |
| Comandos de consola y tareas programadas | Ejecución real de respaldos con `mysqldump` |
| Seguridad: cabeceras, cifrado, control de acceso | Pruebas de penetración |
| Migraciones y seeders | |

## 3. Niveles y tipos de prueba

| Nivel | Ubicación | Qué verifica | Casos |
|---|---|---|---|
| **Unitarias** | `tests/Unit` | Lógica aislada: enums de estado y roles, reglas de la cita (límite de reprogramaciones, cancelación) | 21 |
| **Integración / funcionalidad** | `tests/Feature` | Flujos completos vía HTTP con base de datos real (SQLite en memoria): controladores, validación, políticas, servicios, eventos y notificaciones | 203 |
| **Análisis estático** | `phpstan.neon` | Tipos, llamadas inexistentes y errores lógicos detectables sin ejecutar (PHPStan + Larastan, nivel 6) | — |
| **Estilo de código** | `pint.json` | Convenciones PSR-12 / Laravel | — |
| **Regresión** | GitHub Actions | Toda la suite en cada *push* (PHP 8.2, 8.3 y 8.4, SQLite y MySQL 8) | 224 |

**Total: 224 casos de prueba y 666 aserciones.**

## 4. Técnicas de diseño de casos

| Técnica | Dónde se aplica | Ejemplo |
|---|---|---|
| **Análisis de valores límite** | Límite de 3 reprogramaciones | `ReglasDeCitaTest::test_puede_reprogramarse_hasta_el_limite` prueba 0, 1, 2, **3** y 4 |
| | Bloques de horario que se tocan en el borde | `HorarioTest`: un bloque que termina justo cuando empieza otro no se cruza |
| | Anticipación mínima para reservar el mismo día | `AgendaServiceTest::test_hoy_solo_ofrece_horas_con_la_anticipacion_minima` |
| **Partición de equivalencia** | Estados de cita que ocupan o liberan horario | `AgendaServiceTest::test_una_cita_ocupa_el_horario_segun_su_estado` (5 clases) |
| | Datos de entrada inválidos | `RegistrarCitaTest::test_valida_los_datos_de_la_cita` y `RegistroTest::test_valida_los_datos_de_registro` |
| **Tabla de decisión** | Matriz de permisos rol × módulo | `ControlDeAccesoTest::test_cada_rol_solo_accede_a_sus_modulos`: 11 módulos × 4 roles |
| **Transición de estados** | Ciclo de vida de la cita (pendiente → confirmada → atendida / reprogramada / cancelada) | `CancelarYConfirmarCitaTest`, `ReprogramarCitaTest`, `PagoTest` |
| **Pruebas negativas** | Acciones que deben rechazarse | Doble reserva, cita ajena, pago ya procesado, cuenta inactiva, etc. |

Todos los casos con datos múltiples usan *data providers* de PHPUnit, con un nombre descriptivo para cada conjunto.

## 5. Entorno y datos de prueba

- **Base de datos:** SQLite en memoria, recreada en cada prueba (`RefreshDatabase`). En CI también se usa **MySQL 8.0**.
- **Tiempo congelado:** todas las pruebas se ejecutan como si fuera el *lunes 5 de octubre de 2026 a las 08:00 (America/Lima)*. Ver `tests/TestCase.php`. Así los resultados son deterministas.
- **Datos:** *factories* de Laravel (`database/factories`) con estados legibles: `->psicologo()`, `->confirmada()`, `->inactivo()`, etc. El trait `tests/Concerns/CreaEscenarios.php` arma los escenarios repetidos.
- **Servicios externos simulados:** `Notification::fake()` para los correos y cola síncrona. Como `fake()` no serializa las notificaciones, `EnvioRealDeCorreosTest` además envía los correos por la cola real (lección del DEF-006).

## 6. Matriz de trazabilidad

### Requerimientos funcionales

| Requerimiento | Archivos de prueba | Casos clave |
|---|---|---|
| **RF-01** Registro de citas | `Citas/RegistrarCitaTest` (20) | El paciente reserva para sí; el personal, para un paciente; no hay doble reserva; no se reserva fuera de horario; especialidad del psicólogo; 7 validaciones de entrada; promoción inactiva; notificaciones |
| **RF-02** Disponibilidad en tiempo real | `Servicios/AgendaServiceTest` (17), `Api/DisponibilidadApiTest` (5) | Generación de horas; bloques múltiples; estados que ocupan; hora propia al reprogramar; bloque pausado; fecha pasada; anticipación; psicólogo inactivo; parámetros configurables |
| **RF-03** Reprogramación y cancelación | `Citas/ReprogramarCitaTest` (9), `Citas/CancelarYConfirmarCitaTest`, `Unit/Models/ReglasDeCitaTest` (11) | Límite de 3 (valores límite y 3 reprogramaciones seguidas); horario ocupado; historial de cambios; cancelar libera el horario; no se cancela dos veces |
| **RF-04** Confirmación | `Citas/CancelarYConfirmarCitaTest` (9), `Pagos/PagoTest` (9) | Sin pago validado no se confirma; validar el pago confirma; un pago de cita cancelada no la reactiva; pago ya procesado |
| **RF-05** Datos del paciente | `Admin/PacienteTest` (6), `Auth/RegistroTest` (12) | Alta y edición; DNI único; búsqueda; no se elimina un paciente con citas; registro público con política de contraseñas |
| **RF-06** Historia clínica | `Psicologo/HistorialClinicoTest` (8) | Registro y estado atendida; solo el psicólogo asignado; solo el psicólogo tratante consulta la historia; el personal administrativo no ve las notas |
| **RF-07** Control de acceso | `Seguridad/ControlDeAccesoTest` (28), `Auth/InicioDeSesionTest` (10) | Matriz rol × módulo; visibilidad de citas por rol; invitados; usuario por nombre (`admin`); bloqueo por intentos; cuenta inactiva |
| **RF-08** Reportes | `Admin/ReporteYAuditoriaTest` | Indicadores del periodo; validación del rango; exportación CSV |

### Requisitos no funcionales

| Requisito | Archivos de prueba | Evidencia |
|---|---|---|
| Confiabilidad de reglas de negocio | `ReglasDeCitaTest`, `EstadoCitaTest`, `AgendaServiceTest` | Valores límite y particiones de equivalencia |
| Seguridad | `CabecerasYProteccionesTest` (7), `InicioDeSesionTest`, `RecuperarContrasenaTest` (6) | CSP y cabeceras OWASP, HSTS, notas clínicas cifradas, contraseñas con *hash*, regeneración de sesión, respuesta que no revela si un correo existe |
| Trazabilidad / auditoría | `ReporteYAuditoriaTest` | Usuario y valores en cada cambio; la auditoría nunca guarda contraseñas ni notas clínicas; depuración por antigüedad |
| Operación en producción | `ComandosYNotificacionesTest` (7), `SeedersTest` (3) | Recordatorios; creación del administrador; tareas programadas registradas; sin usuarios de prueba en producción; seeders idempotentes |
| Usabilidad / idioma | `RegistroTest`, `CabecerasYProteccionesTest`, `ComandosYNotificacionesTest` | Mensajes de validación, páginas de error y correos en español |

## 7. Criterios de entrada y salida

**Entrada:** el código compila, las dependencias están instaladas y las migraciones se ejecutan.

**Salida (criterio para pasar a producción):**
- El 100 % de las pruebas aprobadas.
- 0 errores de PHPStan (nivel 6).
- 0 diferencias de estilo en Pint.
- 0 vulnerabilidades conocidas en `composer audit` y `npm audit`.
- Todos los RF con al menos una prueba positiva y una negativa (ver la matriz).

## 8. Cómo ejecutar

```bash
composer test                                   # toda la suite
php artisan test --testsuite=Unit               # solo unitarias
php artisan test tests/Feature/Citas            # un módulo
php artisan test --filter=reprogramar           # por nombre
composer analyse                                # análisis estático
composer lint                                   # estilo
```

**Cobertura de código:** requiere la extensión PCOV o Xdebug. XAMPP no la trae; la CI sí la usa y publica el reporte HTML como artefacto (`reporte-cobertura`). Para generarla localmente con Xdebug:

```bash
php -d xdebug.mode=coverage vendor/bin/phpunit --coverage-html build/cobertura
```

## 9. Resultados de la ejecución

| Fecha | Entorno | Pruebas | Aserciones | Resultado |
|---|---|---|---|---|
| 2026-10-05 | Windows 11 · PHP 8.2.12 · SQLite | 224 | 666 | ✅ Todas aprobadas |
| 2026-10-05 | Windows 11 · PHP 8.2.12 · MariaDB 10.4 (XAMPP) | 224 | 666 | ✅ Todas aprobadas |
| 2026-10-05 | Migraciones en MariaDB: aplicar → revertir todas → aplicar | — | — | ✅ 16/16 reversibles |
| 2026-10-05 | Respaldo real con `mysqldump` + `backup:monitor` | — | — | ✅ Respaldo válido y sano |
| 2026-10-05 | Recorrido HTTP de todas las páginas por rol con datos reales | 35 páginas | — | ✅ Todas responden 200 |
| 2026-10-05 | PHPStan nivel 6 | — | — | ✅ 0 errores |
| 2026-10-05 | Pint | — | — | ✅ Sin diferencias |
| 2026-10-05 | `composer audit` / `npm audit` | — | — | ✅ 0 vulnerabilidades |

## 10. Defectos detectados

| ID | Detectado por | Descripción | Severidad | Estado |
|---|---|---|---|---|
| DEF-001 | `HorarioTest`, caso "termina justo al empezar" (valor límite) | Un bloque de 07:00 a 09:00 se rechazaba como "cruzado" con un bloque de 09:00 a 13:00. Las horas se guardaban a veces como `09:00` y otras como `09:00:00`, y la comparación de texto `'09:00' < '09:00:00'` daba verdadero. | Media | ✅ Corregido: las horas se normalizan a `HH:MM:SS` al guardarse (`Horario::normalizarHora`) |
| DEF-002 | Revisión de código previa a la migración | Las citas reprogramadas no ocupaban su horario, lo que permitía reservar dos veces la misma hora. | Alta | ✅ Corregido (`EstadoCita::activos`) y cubierto por `AgendaServiceTest` |
| DEF-003 | Revisión de código | Validar el pago de una cita cancelada la volvía a "confirmada". | Alta | ✅ Corregido y cubierto por `PagoTest` |
| DEF-004 | Revisión de código | La edición de promociones no funcionaba porque el parámetro de la ruta era `{promocione}`. | Media | ✅ Corregido y cubierto por `PsicologoYPromocionTest` |
| DEF-005 | Análisis estático (PHPStan) | Faltaban tipos en los accessors y en las closures de auditoría. | Baja | ✅ Corregido |
| DEF-006 | Prueba manual del usuario al registrar una cita | Error 500 al enviar los correos: la propiedad `readonly` de `NotificacionDeCita` no se podía reconstruir al deserializar la notificación desde la cola. Las pruebas no lo detectaron porque `Notification::fake()` no serializa. | Alta | ✅ Corregido. Regresión cubierta por `EnvioRealDeCorreosTest`, que envía los correos por la cola real |
| DEF-007 | Revisión extensiva: migraciones revertidas en MariaDB | `migrate:rollback` fallaba: MySQL reemplaza el índice automático de una llave foránea por el índice compuesto nuevo, y luego no permite borrarlo. En SQLite no ocurre. | Media | ✅ Corregido. `MigracionesYTraduccionesTest` revierte y reaplica todas las migraciones |
| DEF-008 | Revisión extensiva: comparación de claves de traducción | 37 reglas de validación sin traducir. Los mensajes de la política de contraseñas salían en inglés en el registro. | Media | ✅ Traducción completa, con una prueba que falla si falta alguna regla |
| DEF-009 | Revisión extensiva | El registro rechazaba correos escritos con mayúsculas (regla `lowercase`) en lugar de normalizarlos. | Baja | ✅ Se normalizan a minúsculas en el registro, el login y el alta de psicólogos |
| DEF-010 | Revisión extensiva | Un paciente podía tener dos citas a la misma hora con psicólogos distintos, al registrar o al reprogramar. | Alta | ✅ Nueva regla en `CitaService` |
| DEF-011 | Revisión extensiva | El registro público duplicaba la ficha de un paciente ya creado por recepción con el mismo DNI. | Media | ✅ Se rechaza con un mensaje que indica acercarse a la clínica (vincular la ficha automáticamente permitiría suplantar al paciente) |
| DEF-012 | Revisión extensiva | El psicólogo podía marcar como atendida una cita futura, lo que alteraba los reportes. | Media | ✅ Solo desde el día de la cita; el botón se oculta antes |
| DEF-013 | Revisión extensiva | Reprogramar a la misma fecha y hora consumía una de las 3 reprogramaciones. | Baja | ✅ Se rechaza |
| DEF-014 | Revisión extensiva (seguridad) | Inyección de fórmulas en el CSV exportado (OWASP *CSV Injection*): un nombre como `=HYPERLINK(...)` se ejecutaba al abrir el archivo en Excel. | Alta | ✅ Las celdas que empiezan con `= + - @` se neutralizan |
| DEF-015 | Revisión extensiva | "Tu próxima sesión" mostraba una cita lejana si el paciente tenía más de 10 citas futuras. | Baja | ✅ Consulta propia ordenada por cercanía |
| DEF-016 | Revisión extensiva | El seeder de demostración duplicaba los horarios en cada ejecución (formato `09:00` frente a `09:00:00`). | Baja | ✅ Corregido; la prueba de idempotencia ahora cuenta los horarios |

Las pruebas de regresión de DEF-009 a DEF-015 están en `tests/Feature/RevisionDefectosTest.php`. Cada una falló antes de su corrección (ver la sección 11).

## 11. Revisión extensiva (2026-10-05)

Método aplicado después de la primera versión:

1. **Ejecución contra la infraestructura real**, no solo contra dobles de prueba: la suite completa en MariaDB, las migraciones de ida y vuelta, un respaldo real con `mysqldump` y un recorrido HTTP de todas las pantallas por rol con los datos de la base local.
2. **Comparación automática** de las claves de traducción del framework con las del proyecto.
3. **Lectura del código** buscando reglas de negocio incompletas (solapamientos, límites, estados) y riesgos OWASP.
4. **Confirmación antes de corregir:** cada sospecha se convirtió primero en una prueba. Si fallaba, era un defecto: se registró y se corrigió. Dos sospechas (mensajes de contraseñas, cuya traducción ya se había completado, y el primer día del rango de reportes) resultaron no ser defectos; se mantienen como pruebas de valor límite.

**Observaciones que no son defectos, pero conviene decidir antes de producción:**
- **No existe una pantalla para crear cuentas de recepcionista.** Hoy solo se crean con el seeder de demostración, que no corre en producción, así que la clínica no podría dar de alta a su personal de recepción.
- Reprogramar una cita ya confirmada la deja en estado "reprogramada", y la recepción debe volver a confirmarla aunque el pago ya esté validado.
- Excel elimina los ceros a la izquierda de un DNI al abrir el CSV.

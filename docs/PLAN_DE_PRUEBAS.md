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
| **Integración / funcionalidad** | `tests/Feature` | Flujos completos vía HTTP con base de datos real (SQLite en memoria): controladores, validación, políticas, servicios, eventos y notificaciones | 183 |
| **Análisis estático** | `phpstan.neon` | Tipos, llamadas inexistentes y errores lógicos detectables sin ejecutar (PHPStan + Larastan, nivel 6) | — |
| **Estilo de código** | `pint.json` | Convenciones PSR-12 / Laravel | — |
| **Regresión** | GitHub Actions | Toda la suite en cada *push* (PHP 8.2, 8.3 y 8.4, SQLite y MySQL 8) | 204 |

**Total: 204 casos de prueba y 621 aserciones.**

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
- **Servicios externos simulados:** `Notification::fake()` para los correos y cola síncrona.

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
| 2026-10-05 | Windows 11 · PHP 8.2.12 · SQLite | 204 | 621 | ✅ Todas aprobadas |
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

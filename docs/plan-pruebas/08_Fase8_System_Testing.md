# Fase 8: System Testing (Laravel 12)

> Adapta el PDF *08 Fase 8 System Testing*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

**Herramientas:**
- **Laravel Dusk 8.7** con Google Chrome 154 en modo *headless*, para los flujos E2E y la usabilidad.
- **PHPUnit 11**, para los casos de seguridad que no requieren navegador.
- **k6 2.2**, para carga, concurrencia, estrés y estabilidad.
- `composer audit`.

OWASP ZAP no se ejecutó: los vectores que cubriría (inyección, XSS, CSRF, cabeceras) se verificaron con casos dirigidos (CP-SYS-13 a 23).

| Grupo | Ubicación | Ejecución | Resultado |
|---|---|---|---|
| Flujos E2E y usabilidad | `tests/Browser/` | `php artisan dusk` | ✅ 15/15 (52 aserciones) |
| Seguridad | `tests/Fase8/` | `php artisan test --testsuite=Fase8` | ✅ 21/21 (112 aserciones) |
| Rendimiento | `tests/Carga/` | `k6 run ...` | ⚠️ 4 de 5 escenarios cumplen el umbral |

## 1. Resultados por caso

| ID | Escenario | Resultado esperado | Herramienta | Resultado |
|---|---|---|---|---|
| **Flujos E2E** | | | | |
| CP-SYS-01 | Recepción registra un paciente y agenda una cita con pago | Registro en BD, cita visible, pago con voucher | Dusk | ✅ |
| CP-SYS-02 | Recepción valida el pago y confirma la cita | Cita confirmada | Dusk | ✅ |
| CP-SYS-03 | Reprogramación al límite | Cuarto cambio bloqueado con aviso | Dusk | ✅ |
| CP-SYS-04 | Derivación del paciente al psicólogo | El psicólogo acepta y el paciente queda asignado | Dusk (2 navegadores) | ✅ |
| CP-SYS-05 | Rechazo de la derivación | Rechazada con motivo visible | Dusk | ✅ |
| CP-SYS-06 | Dos usuarios eligen la misma hora | Uno reserva; al otro se le avisa | Dusk (2 navegadores) + k6 | ✅ |
| CP-SYS-07 | El psicólogo registra el historial de una cita | Visible solo para ese psicólogo | Dusk (2 navegadores) | ✅ |
| CP-SYS-08 | La cita pasa por confirmada y cancelada | Estado coherente en el panel, el listado y el detalle de ambos roles | Dusk (2 navegadores) | ✅ |
| CP-SYS-09 | Error de validación en un formulario | No se pierde lo escrito | Dusk | ✅ |
| CP-SYS-10 | Dashboard y reportes | Los totales coinciden con la BD | Dusk | ✅ |
| **Seguridad** | | | | |
| CP-SYS-11 | El psicólogo abre `admin/promociones` | 403 | PHPUnit | ✅ |
| CP-SYS-12 | Sesión expirada | Redirige al login | Dusk (se borra la cookie de sesión) | ✅ |
| CP-SYS-13 | Cookie de sesión alterada | Redirige al login | PHPUnit | ✅ |
| CP-SYS-14 | Algoritmo `none` de JWT | **No aplica** (no hay JWT). Se prueba que un *Bearer* `alg:none` es ignorado | PHPUnit | ✅ |
| CP-SYS-15 | Un usuario ve la cita de otro (`citas/{cita}`) | 403 (y 404 si no existe) | PHPUnit | ✅ |
| CP-SYS-16 | Inyección SQL en el DNI y las búsquedas (4 cargas) | Sin fuga ni error 500; la tabla sigue intacta | PHPUnit | ✅ |
| CP-SYS-17 | XSS en el historial clínico | Se escapa con `{{ }}`; ninguna vista usa `{!! !!}` | PHPUnit | ✅ |
| CP-SYS-18 | Subir `.php`, `.exe`, `.jpg.php` o `.html` como voucher | Rechazado; no se guarda nada | PHPUnit | ✅ |
| CP-SYS-19 | 50 intentos fallidos de login | Bloqueo por usuario (+ 429 por IP) | PHPUnit | ✅ |
| CP-SYS-20 | Formulario sin token CSRF | 419 | PHPUnit (CSRF reactivado) | ✅ |
| CP-SYS-21 | `APP_DEBUG=false` y un error forzado | Sin detalles internos | PHPUnit | ✅ |
| CP-SYS-22 | Contraseñas en la tabla `users` | Solo hashes bcrypt | PHPUnit | ✅ |
| CP-SYS-23 | `.env` fuera del repositorio | Está en `.gitignore` y no está versionado | PHPUnit (`git ls-files`) | ✅ |
| CP-SYS-33 | `composer audit` | Laravel ≥ 12 y 0 vulnerabilidades | PHPUnit | ✅ |
| CP-SYS-34 | Archivos subidos no ejecutables | Disco privado, nombre aleatorio, `nosniff` y CSP aislada | PHPUnit | ✅ |
| **Rendimiento** (detalle en la sección 3) | | | | |
| CP-SYS-24 | Carga: 25 usuarios durante 1 min | Tiempo medio < 300 ms y < 1 % de errores | k6 | ⚠️ 0 % errores, **promedio 379 ms** |
| CP-SYS-25 | Concurrencia: 30 reservas simultáneas de la misma hora | Sin dobles reservas | k6 | ✅ 1 reserva, 29 rechazos |
| CP-SYS-26 | Estrés: de 0 a 100 usuarios | < 5 % de errores | k6 | ✅ 0,11 % de errores |
| CP-SYS-27 | Estabilidad: 10 usuarios durante 5 min | Sin errores ni degradación | k6 | ✅ 0 % errores, p95 216 ms |
| CP-SYS-28 | Tiempo de carga por página | Promedio < 300 ms | k6 | ✅ 60 a 78 ms (con OPcache) |
| **Usabilidad y compatibilidad** | | | | |
| CP-SYS-29 | Vista móvil (390 × 844) | Menú desplegable y sin desplazamiento horizontal | Dusk | ✅ |
| CP-SYS-30 | Navegadores | Funciona en Chrome | Dusk | ✅ Chrome (*) |
| CP-SYS-31 | Mensajes de error | Visibles y asociados al campo (`aria-invalid`) | Dusk | ✅ |
| CP-SYS-32 | Accesibilidad básica | `lang="es"`, etiquetas en todos los campos, enlace de salto, navegación con teclado | Dusk | ✅ |

(*) **CP-SYS-30:** se automatizó en Chrome (motor Chromium, el mismo de Edge y Opera). Firefox y Safari quedan como **verificación manual pendiente**.

Las capturas de pantalla de cada flujo están en [`evidencias/capturas/`](evidencias/capturas/) (18 imágenes). El registro JUnit de Dusk está en `evidencias/fase8-dusk-junit.xml`.

## 2. Entorno de las pruebas E2E (Dusk)

- Base de datos propia **`eirene_dusk`** (MySQL), definida en `.env.dusk.local` (plantilla: `.env.dusk.example`). La base de trabajo no se toca.
- Servidor: `APP_ENV=dusk.local php artisan serve --port=8010`, luego `php artisan dusk`.
- Cada prueba empieza sin sesión y con el navegador en tamaño de escritorio; Dusk reutiliza el navegador entre pruebas.

## 3. Rendimiento (k6)

### Entorno

- **Apache 2.4 de XAMPP** con PHP 8.2 (`mod_php`, varios hilos), en una instancia aparte en el puerto 8020. Se usó Apache porque `php artisan serve` no atiende peticiones en paralelo en Windows y no serviría para medir concurrencia. Se usó una instancia aparte para no modificar los VirtualHost existentes.
- Base de datos propia **`eirene_k6`**, con datos de `CargaSeeder` (3 psicólogos, 120 pacientes, recepción).
- Configuración de tipo producción: `APP_DEBUG=false` y `php artisan optimize` (configuración, rutas y vistas en caché).
- Equipo: laptop con Windows 11; el cliente k6, la aplicación y MySQL corren en la misma máquina.

### Hallazgo: OPcache desactivado en XAMPP

La primera medición dio unos **690 ms por página con un solo usuario**. La causa fue que **XAMPP trae OPcache desactivado** (`;zend_extension=opcache` en `php.ini`), así que PHP recompilaba todo el framework en cada petición. Prueba de ello: la ruta mínima `/up` tardaba unos 800 ms, frente a los 30 ms de un archivo estático.

Se repitió la medición con OPcache activado, que es la configuración estándar de un servidor de producción. Se hizo con un `php.ini` propio de la instancia de prueba, **sin modificar el de XAMPP**. Resultado: `/up` bajó a unos 50 ms. **Recomendación para producción:** activar OPcache (ver `docs/DESPLIEGUE.md`).

### Resultados

| Caso | Escenario | Peticiones | Promedio | Mediana | p95 | Errores | Umbral | Resultado |
|---|---|---|---|---|---|---|---|---|
| CP-SYS-28 sin OPcache | 1 usuario, 30 recorridos | 269 | 686 ms | 675 ms | 891 ms | 0 % | promedio < 300 ms | ❌ |
| **CP-SYS-28** | 1 usuario, 30 recorridos | 269 | **67 ms** | 58 ms | 104 ms | 0 % | promedio < 300 ms por página | ✅ (60 a 78 ms por página) |
| CP-SYS-24 sin OPcache | 25 usuarios, 1 min | 390 | 14,1 s | 13,5 s | 29,3 s | 0 % | promedio < 300 ms | ❌ |
| **CP-SYS-24** | 25 usuarios (4 recursos en paralelo cada uno), 1 min | 5686 | **379 ms** | 207 ms | 1,25 s | **0 %** | promedio < 300 ms; errores < 1 % | ⚠️ Errores ✅, promedio ❌ |
| **CP-SYS-25** | 30 reservas simultáneas de la misma hora | 120 | 6,0 s | 5,6 s | 9,8 s | 0 % | exactamente 1 reserva | ✅ **1 éxito, 29 rechazos**; 1 cita en BD |
| **CP-SYS-26** | De 0 a 100 usuarios en 1 min, 30 s sostenido | 6165 | 618 ms | 78 ms | 1,51 s | **0,11 %** (7) | errores < 5 % | ✅ |
| **CP-SYS-27** | 10 usuarios, 5 min | 4204 | 221 ms | 64 ms | **216 ms** | 0 % | p95 < 500 ms; errores < 1 % | ✅ |

Archivos: `evidencias/k6-cp-sys-*.json` (métricas completas) y `.txt` (salida de consola).

### Interpretación

- **RN-03 (sin colisiones):** ✅ verificada con concurrencia real. Las 30 peticiones llegaron al mismo instante; el bloqueo `lockForUpdate()` las serializó y solo una creó la cita. El tiempo alto de CP-SYS-25 (6 s de promedio) es el costo esperado de esa serialización bajo una ráfaga artificial de 30 reservas idénticas.
- **CP-SYS-24 no cumple el promedio de 300 ms** con 25 usuarios que piden 4 recursos a la vez (hasta 100 peticiones simultáneas) en una laptop que además ejecuta MySQL y el propio k6. La mediana (207 ms) sí está bajo el umbral; el promedio sube por las peticiones más lentas. **Acción propuesta:** repetir la medición en el servidor de producción, con MySQL en un equipo aparte y OPcache, antes de considerar optimizaciones de código.
- **Observación:** en la primera corrida de CP-SYS-25, el script clasificó 29 respuestas como "inesperadas" (código distinto de 302), aunque la base registró correctamente 1 sola cita. No se reprodujo en las tres corridas siguientes, que sí registraban el código HTTP de cada respuesta. Se deja anotado y sin explicación confirmada.

## 4. Hallazgos de esta fase

| ID | Hallazgo | Acción |
|---|---|---|
| F8-01 | OPcache desactivado en XAMPP: unas 10 veces más lento | Documentado en `docs/DESPLIEGUE.md`; medición repetida con OPcache |
| F8-02 | **Windows Defender eliminaba un archivo de pruebas.** Para simular un voucher malicioso, CP-SYS-18 usaba un contenido con la firma de una *webshell* PHP. El antivirus lo detectó y puso el archivo en cuarentena, y la suite de la Fase 8 desapareció sin aviso. | El archivo de prueba ahora usa un contenido PHP inofensivo; la validación rechaza igual por tipo y extensión |
| F8-03 | Laravel Pint renombraba los métodos `test_CP_UT_01` a `test_c_p_u_t_01` (regla `php_unit_method_casing`), lo que rompía la trazabilidad con los ID del plan | Regla desactivada en `pint.json` |

## 5. Criterios de aprobación

- [x] Flujos E2E sin errores (15/15).
- [x] Cero accesos no autorizados (CP-SYS-11, 13 a 15).
- [x] Vulnerabilidades críticas documentadas con su plan de corrección (no se encontraron; `composer audit` limpio).
- [x] Sin dobles reservas bajo concurrencia real (CP-SYS-25).
- [ ] Tiempo medio < 300 ms con 25 usuarios simultáneos: **no cumplido en el equipo de desarrollo** (379 ms). Pendiente de medir en el servidor de producción.

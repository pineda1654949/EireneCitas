# Fase 6: Unit Testing (Laravel 12)

> Adapta el PDF *06 Fase 6 Unit Testing*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

**Herramientas:** PHPUnit 11.5, SQLite en memoria (y MySQL para regresión), factories, tiempo congelado con `travelTo` (equivale a `Carbon::setTestNow`), PCOV para cobertura.

**Ubicación:** `tests/Fase6/`. Cada método se llama `test_CP_UT_xx_...`, con el ID del caso. **Ejecución:** `php artisan test --testsuite=Fase6`.

Configuración en `phpunit.xml` (ya incluida):

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Las extensiones `pdo_sqlite` y `sqlite3` ya vienen activas en el PHP 8.2 de XAMPP.

**Nota sobre el alcance de "unitario":** igual que el ejemplo del PDF, muchos casos ejercitan la regla a través de la ruta HTTP (prueba de componente). Las reglas puras, sin base de datos, tienen además pruebas unitarias aisladas en `tests/Unit` (21 pruebas: enums y reglas del modelo `Cita`).

## Resultados (ejecución del 5 de octubre de 2026)

**54 ejecuciones (47 métodos, algunos con varios conjuntos de datos) · 152 aserciones · ✅ todas aprobadas.**

| ID | Caso | Resultado esperado | Método (archivo) | Resultado |
|---|---|---|---|---|
| **Reglas de negocio** | | | `ReglasDeNegocioTest` | |
| CP-UT-01 | RN-01: reprogramar una cita con 3 cambios | Se rechaza; el contador sigue en 3 | `test_CP_UT_01_rn01_bloquea_el_cuarto_cambio` | ✅ |
| CP-UT-02 | RN-01: reprogramar con 2 cambios | Se acepta; el contador queda en 3 | `test_CP_UT_02_rn01_acepta_el_tercer_cambio` | ✅ |
| CP-UT-03 | RN-01: reprogramar sin motivo | Error de validación | `test_CP_UT_03_rn01_exige_motivo_al_reprogramar` | ✅ |
| CP-UT-04 | RN-02: confirmar con pago pendiente | Se rechaza; la cita no cambia | `test_CP_UT_04_rn02_no_confirma_con_pago_pendiente` | ✅ |
| CP-UT-05 | RN-02: confirmar con pago validado | Cita confirmada | `test_CP_UT_05_rn02_confirma_con_pago_validado` | ✅ |
| CP-UT-06 | RN-02: confirmar con pago rechazado | Se rechaza | `test_CP_UT_06_rn02_no_confirma_con_pago_rechazado` | ✅ |
| CP-UT-07 | RN-03: hora ya ocupada | Se rechaza la reserva | `test_CP_UT_07_rn03_rechaza_una_hora_ocupada` | ✅ |
| CP-UT-08 | RN-03: hora fuera del horario del psicólogo | Se rechaza | `test_CP_UT_08_rn03_rechaza_una_hora_fuera_del_horario` | ✅ |
| CP-UT-09 | Fecha pasada | Error de validación | `test_CP_UT_09_rechaza_una_fecha_pasada` | ✅ |
| **Modelos Eloquent** | | | `ModelosTest` | |
| CP-UT-10 | Paciente con DNI duplicado | Excepción de clave única | `test_CP_UT_10_..._dni_duplicado_...` | ✅ |
| CP-UT-11 | Paciente con correo duplicado | Excepción de clave única | `test_CP_UT_11_..._correo_duplicado_...` | ✅ |
| CP-UT-12 | Paciente sin nombres | Error de campo obligatorio | `test_CP_UT_12_paciente_sin_nombres_...` | ✅ |
| CP-UT-13 | Edad cero o negativa | Error de validación | `test_CP_UT_13_edad_cero_o_negativa_da_error` | ✅ |
| CP-UT-14 | Estados por defecto del paciente | Inscripto y pago pendiente | `test_CP_UT_14_estados_por_defecto_...` | ✅ |
| CP-UT-15 | Contador de reprogramaciones por defecto | 0 | `test_CP_UT_15_..._por_defecto_es_cero` | ✅ |
| CP-UT-16 | Monto de pago con decimales | Se guarda con 2 decimales (`80.50`) | `test_CP_UT_16_..._dos_decimales` | ✅ |
| CP-UT-17 | Promoción con precio menor o igual a 0 | Error de validación | `test_CP_UT_17_..._precio_menor_o_igual_a_cero_...` | ✅ |
| CP-UT-18 | Usuario con usuario o correo duplicado | Excepción de clave única | `test_CP_UT_18_..._duplicado_lanza_excepcion` | ✅ |
| CP-UT-19 | Usuario con un rol fuera de los permitidos | Error (`ValueError` del enum `Rol`) | `test_CP_UT_19_usuario_con_rol_no_permitido_da_error` | ✅ (*) |
| **Validaciones (FormRequest)** | | | `ValidacionesTest` | |
| CP-UT-20 | DNI de 7 dígitos | Rechazado | `test_CP_UT_20_21_22_validacion_del_dni` | ✅ |
| CP-UT-21 | DNI de 8 dígitos | Aceptado | (mismo método, otro conjunto de datos) | ✅ |
| CP-UT-22 | DNI con letras | Rechazado | (mismo método) | ✅ |
| CP-UT-23 | Correo mal formado | Rechazado | `test_CP_UT_23_correo_mal_formado_se_rechaza` | ✅ |
| CP-UT-24 | Correo en mayúsculas | Se guarda en minúsculas | `test_CP_UT_24_..._minusculas` | ✅ |
| CP-UT-25 | Celular que no empieza con 9 (y variantes) | Rechazado | `test_CP_UT_25_26_validacion_del_celular` (3 conjuntos) | ✅ |
| CP-UT-26 | Celular válido | Aceptado | (mismo método) | ✅ |
| CP-UT-27 | Voucher `.exe` | Rechazado | `test_CP_UT_27_28_29_validacion_del_voucher` | ✅ |
| CP-UT-28 | Voucher `.jpg` de más de 5 MB | Rechazado | (mismo método) | ✅ |
| CP-UT-29 | Voucher `.png` de 1 MB | Aceptado | (mismo método) | ✅ |
| CP-UT-30 | Nota de historial muy corta | Rechazada (mínimo 10 caracteres) | `test_CP_UT_30_nota_de_historial_muy_corta_se_rechaza` | ✅ |
| **Seguridad** | | | `SeguridadTest` | |
| CP-UT-31 | Middleware `rol` con un rol no permitido | Bloquea (403) y deja pasar el permitido | `test_CP_UT_31_middleware_rol_...` (2 métodos) | ✅ |
| CP-UT-32 | Middleware `auth` sin sesión | Redirige al login | `test_CP_UT_32_..._redirige_al_login` | ✅ |
| CP-UT-33 | Usuario de otro rol que cambia de ruta | Bloqueado (403) | `test_CP_UT_33_..._es_bloqueado` | ✅ |
| CP-UT-34 | Sesión expirada | Redirige al login | `test_CP_UT_34_sesion_expirada_redirige_al_login` | ✅ (**) |
| CP-UT-35 | Login válido | Sesión iniciada y regenerada | `test_CP_UT_35_..._regenera_la_sesion` | ✅ |
| CP-UT-36 | `Hash::make` | Hash distinto del texto, bcrypt y `Hash::check` verdadero | `test_CP_UT_36_...` | ✅ |
| CP-UT-37 | Contraseña incorrecta | `Hash::check` falso | `test_CP_UT_37_...` | ✅ |
| CP-UT-38 | Excepción con `APP_DEBUG=false` | Sin detalles internos ni stack trace | `test_CP_UT_38_...` | ✅ |
| **Vistas Blade** (en lugar de los componentes React) | | | `VistasYDerivacionTest` | |
| CP-UT-39 | Vista `citas/crear` | Muestra el formulario de cita | `test_CP_UT_39_...` | ✅ |
| CP-UT-40 | Formulario con datos válidos | Se procesa sin errores | `test_CP_UT_40_...` | ✅ |
| CP-UT-41 | Formulario vacío | Muestra los mensajes de error | `test_CP_UT_41_...` | ✅ |
| CP-UT-42 | Error de validación | Conserva lo escrito (`old()`) | `test_CP_UT_42_...` | ✅ |
| CP-UT-43 | Vista de horarios | Libres, ocupados y bloqueados diferenciados | `test_CP_UT_43_la_agenda_diferencia_...` | ✅ |
| CP-UT-44 | Hora ocupada | No se puede seleccionar | `test_CP_UT_44_...` | ✅ |
| CP-UT-45 | Hora disponible | Se puede seleccionar | `test_CP_UT_45_...` | ✅ |
| CP-UT-46 | Reprogramar con el contador en 3 | Aviso y botón oculto | `test_CP_UT_46_...` | ✅ |
| CP-UT-47 | Dashboard (`home`) | Muestra métricas reales | `test_CP_UT_47_dashboard_muestra_metricas` | ✅ |
| CP-UT-48 | Logout | Cierra la sesión y redirige al login | `test_CP_UT_48_...` | ✅ |
| **Derivación (RF-05)** | | | `VistasYDerivacionTest` | |
| CP-UT-49 | Rechazo de derivación sin motivo | Error de validación | `test_CP_UT_49_...` | ✅ |
| CP-UT-50 | Aceptación de la derivación | El paciente queda asignado | `test_CP_UT_50_...` | ✅ |
| CP-UT-51 | Psicólogo equivocado | 403 al aceptar o rechazar | `test_CP_UT_51_...` | ✅ |

(*) **CP-UT-19:** el PDF habla de "3 roles permitidos". El sistema tiene **4**: administrador, recepcionista, psicólogo y **paciente** (para el autoregistro). La prueba verifica que cualquier otro valor (por ejemplo `superusuario`) se rechaza.

(**) **CP-UT-34:** en PHPUnit la expiración se simula vaciando la sesión. La expiración real del navegador (cuando deja de enviar la cookie) se verifica en la Fase 8 con Dusk (CP-SYS-12). El tiempo configurado es `SESSION_LIFETIME = 120` minutos.

**Ejemplo real de CP-UT-01** (adaptado al esquema verdadero: `numero_reprogramaciones` en lugar de `num_reprogramaciones`, y fecha y hora separadas):

```php
public function test_CP_UT_01_rn01_bloquea_el_cuarto_cambio(): void
{
    [$psicologo] = $this->psicologoConAgenda();
    $cita = $this->citaPara($psicologo, atributos: ['numero_reprogramaciones' => 3]);

    $this->actingAs($this->recepcion)
        ->put(route('citas.reprogramar', $cita), ['fecha' => $this->proximoLunes(), 'hora' => '12:00', 'motivo' => 'Prueba del limite'])
        ->assertSessionHasErrors('fecha');

    $this->assertSame(3, $cita->fresh()?->numero_reprogramaciones);
}
```

## Cobertura

Comando (PCOV se carga solo para la medición, sin modificar el `php.ini` de XAMPP):

```bash
php -d extension=pcov -d pcov.enabled=1 -d pcov.directory=app vendor/bin/phpunit --coverage-html build/cobertura
```

| Métrica (suite completa, 347 pruebas) | Resultado |
|---|---|
| Líneas | **97,45 %** (1299 de 1333) |
| Métodos | 91,61 % (251 de 274) |
| Clases | 74,65 % (53 de 71) |

Evidencias: `evidencias/phpunit-cobertura.txt` y `evidencias/cobertura-clover.xml`.

**Pruebas de mutación (Infection PHP):** no se ejecutaron. Quedan como trabajo futuro para medir la calidad de las aserciones, además de la cobertura.

## Criterios de aprobación

- [x] 100 % de las suites en verde (54/54 en la Fase 6; 347/347 en la regresión completa, en SQLite y en MySQL).
- [x] RN-01, RN-02 y RN-03 verificadas.
- [x] Cobertura de líneas ≥ 90 %: **97,45 %**.

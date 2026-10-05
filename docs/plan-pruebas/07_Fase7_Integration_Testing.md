# Fase 7: Integration Testing (Laravel 12)

> Adapta el PDF *07 Fase 7 Integration Testing*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

**Herramientas:** pruebas Feature de PHPUnit 11 con `RefreshDatabase`, `actingAs()`, `Notification::fake()` (equivale a `Mail::fake()` para las notificaciones), `Storage::fake()` y Mockery. Base de datos SQLite en memoria y **también MySQL** (`eirene_pruebas`), con los mismos resultados.

**Ubicación:** `tests/Fase7/`. **Ejecución:** `php artisan test --testsuite=Fase7`.

## Resultados (5 de octubre de 2026)

**39 casos (CP-IT-01 a 38 + CP-IT-19b) · 130 aserciones · ✅ todos aprobados.**

| ID | Flujo | Ruta real | Resultado esperado | Resultado |
|---|---|---|---|---|
| **Autenticación** | | | | |
| CP-IT-01 | Login válido | `POST login` | Sesión iniciada; redirige a `home` | ✅ |
| CP-IT-02 | Clave incorrecta | `POST login` | Vuelve con error | ✅ |
| CP-IT-03 | Usuario inexistente | `POST login` | El mismo mensaje que CP-IT-02 (no revela si el usuario existe) | ✅ |
| CP-IT-04 | Usuario inactivo | `POST login` | Acceso denegado | ✅ |
| **Pacientes y citas (inscripción)** | | | | |
| CP-IT-05 | Crear un paciente válido | `POST admin/pacientes` | Registro en BD con estado Inscripto; redirige | ✅ |
| CP-IT-06 | Fallo a mitad del proceso | `POST registro` con un error forzado al crear la ficha | Sin registros parciales: ni usuario ni ficha (*rollback*) | ✅ |
| CP-IT-07 | DNI duplicado | `POST admin/pacientes` | Error de validación; un solo registro | ✅ |
| CP-IT-08 | Datos inválidos | `POST admin/pacientes` | Errores por campo (6 campos) | ✅ |
| CP-IT-09 | Cuotas según la promoción | `POST citas/{cita}/pagos` | Cuotas guardadas como 1/3 y 2/3 | ✅ |
| CP-IT-10 | Cuotas sobre el máximo | `POST citas/{cita}/pagos` | Rechazado (también en promociones al contado) | ✅ |
| CP-IT-11 | Promoción inactiva | `POST citas` | Rechazado | ✅ |
| **Pagos (RN-02)** | | | | |
| CP-IT-12 | Validar el pago | `PUT pagos/{pago}/validar` | Pago validado; cita confirmada | ✅ |
| CP-IT-13 | Rechazar el pago | `PUT pagos/{pago}/rechazar` | Pago rechazado; cita sin confirmar | ✅ |
| CP-IT-14 | Pago inexistente | `PUT pagos/9999/validar` | 404 | ✅ |
| CP-IT-15 | El psicólogo valida un pago | `PUT pagos/{pago}/validar` | 403 | ✅ |
| CP-IT-16 | Confirmar sin pago validado | `PUT citas/{cita}/confirmar` | Rechazado (RN-02) | ✅ |
| **Agenda y reprogramación (RN-01, RN-03)** | | | | |
| CP-IT-17 | Consultar horas | `GET api/horas-disponibles` | Lista de horas libres | ✅ |
| CP-IT-18 | Reprogramar válido | `PUT citas/{cita}/reprogramar` | El contador sube, la fecha cambia y el motivo queda registrado | ✅ |
| CP-IT-19 | Cuarto cambio (RN-01) | `PUT citas/{cita}/reprogramar` | Rechazado; el contador sigue en 3 | ✅ |
| CP-IT-19b | Cuarto cambio autorizado por el administrador | `PUT citas/{cita}/reprogramar` | Aceptado (contador en 4), según RN-01 | ✅ |
| CP-IT-20 | Reprogramar a una hora ocupada | `PUT citas/{cita}/reprogramar` | Rechazado | ✅ |
| CP-IT-21 | Doble reserva (RN-03) | Dos `POST citas` a la misma hora | Solo se guarda una | ✅ |
| CP-IT-22 | Psicólogo inexistente | `POST citas` | Error de validación (302), no 500 | ✅ |
| **Derivación y expediente** | | | | |
| CP-IT-23 | Derivar un paciente | `POST admin/pacientes/{paciente}/derivaciones` | Derivación pendiente; correo al psicólogo | ✅ |
| CP-IT-24 | Aceptar la derivación | `PUT psicologo/derivaciones/{d}/aceptar` | Paciente asignado; aviso a la clínica | ✅ |
| CP-IT-25 | Rechazar la derivación | `PUT psicologo/derivaciones/{d}/rechazar` | Motivo guardado; aviso a la clínica; no se puede responder dos veces | ✅ |
| CP-IT-26 | Registrar el historial | `POST psicologo/citas/{cita}/historial` | Historial guardado; cita atendida; paciente "En proceso" | ✅ |
| CP-IT-27 | El psicólogo ve un historial ajeno | `GET psicologo/pacientes/{id}/historial` | 403 (autorización por pertenencia) | ✅ |
| CP-IT-28 | Cambiar el estado del paciente | `PUT admin/pacientes/{id}` | Estado actualizado (Finalizado) | ✅ |
| CP-IT-29 | Estado inválido | `PUT admin/pacientes/{id}` | Error de validación | ✅ |
| **Seguridad** | | | | |
| CP-IT-30 | Ruta protegida sin sesión | `GET admin/pacientes` | 302 al login | ✅ |
| CP-IT-31 | Sesión manipulada | Cookie alterada | 302 al login | ✅ |
| CP-IT-32 | Sesión expirada | Sesión vaciada | 302 al login | ✅ |
| CP-IT-33 | El psicólogo crea una promoción | `POST admin/promociones` | 403 | ✅ |
| CP-IT-34 | El psicólogo lista pacientes | `GET admin/pacientes` | 403 | ✅ |
| CP-IT-35 | Inyección SQL en el DNI | `POST admin/pacientes` y búsqueda | Error de validación, sin fuga de datos | ✅ |
| **Notificaciones** | | | | |
| CP-IT-36 | Correo al confirmar la cita | `Notification::fake()` | Correo al paciente **y al psicólogo** | ✅ |
| CP-IT-37 | Recordatorio de 24 h | Comando `citas:enviar-recordatorios` | Enviado y programado (`schedule:list`) | ✅ |
| CP-IT-38 | Fallo del correo | Mock que lanza un error SMTP | No se revierte la confirmación (302 normal) | ✅ |

**Ejemplo real de CP-IT-33:**

```php
public function test_CP_IT_33_psicologo_no_puede_crear_promociones(): void
{
    $this->actingAs(User::factory()->psicologo()->create())
        ->post(route('admin.promociones.store'), ['nombre' => 'X', 'numero_sesiones' => 1, 'precio' => 10])
        ->assertForbidden();

    $this->assertDatabaseCount('promociones', 0);
}
```

**Sobre CP-IT-21:** PHPUnit no ejecuta peticiones en paralelo. Aquí se verifica la lógica (la segunda reserva ve la primera). La concurrencia real se validó en la Fase 8 con k6: 30 reservas simultáneas, de las que solo una se guardó (CP-SYS-25).

**Cambios del sistema que surgieron de esta fase:**
- **CP-IT-36:** el correo de confirmación ahora llega también al psicólogo (antes solo al paciente).
- **CP-IT-38:** el envío de correos se aisló con manejo de errores, para que una caída del servidor SMTP no interrumpa la operación.

## Criterios de aprobación

- [x] Transacciones consistentes (CP-IT-06).
- [x] 100 % de las rutas protegidas rechazan los accesos no autorizados.
- [x] Cero errores 500 en los flujos probados (CP-IT-22).

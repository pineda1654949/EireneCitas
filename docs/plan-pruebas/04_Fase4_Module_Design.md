# Fase 4: Module Design (Laravel 12)

> Adapta el PDF *04 Fase 4 Module Design*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Propósito

Descomponer la arquitectura en módulos, modelos y contratos de ruta. Cierra la Rama de Verificación de V-Bounce.

## 2. Módulos y controladores reales

| Módulo | Controlador | Lógica / regla |
|---|---|---|
| Autenticación | `Auth\SesionController`, `RegistroController`, `RecuperarContrasenaController`, `RestablecerContrasenaController` | Login con límite de intentos, registro de pacientes, recuperación de contraseña |
| Pacientes | `Admin\PacienteController` + `PacienteRequest` | CRUD, DNI y correo únicos, edad 1 a 120, celular `9XXXXXXXX`, estado de atención |
| Derivación | `Admin\DerivacionController`, `Psicologo\DerivacionController`, `DerivacionService` | Derivar, aceptar, rechazar con motivo |
| Promociones | `Admin\PromocionController` + `PromocionRequest` | CRUD, precio mayor a 0, cuotas de 2 a 12 |
| Psicólogos | `Admin\PsicologoController` + `PsicologoRequest` | CRUD, especialidades; con citas se desactiva en lugar de borrarse |
| Citas | `CitaController` + `CitaService` + `CitaPolicy` | Crear, ver, reprogramar (RN-01), cancelar, confirmar (RN-02), sin colisiones (RN-03) |
| Pagos | `Admin\PagoController` + `CitaService` | Registrar con voucher y cuotas, validar, rechazar (RN-02) |
| Disponibilidad | `Api\DisponibilidadController` + `AgendaService` | Matriz de colores, horas libres, psicólogos por especialidad |
| Horarios | `Psicologo\HorarioController` + `HorarioRequest` | Alta, pausa y baja de bloques sin cruces |
| Historial | `Psicologo\HistorialController` + `PacientePolicy` | Notas por cita (cifradas) y por paciente |
| Reportes | `Admin\ReporteController` | Indicadores y exportación CSV |
| Auditoría | `Admin\AuditoriaController` | Bitácora de acciones |

## 3. Modelos de datos

Ver [04 Diccionario de datos](04_Diccionario_de_Datos_BD.md). Modelos: `User` (incluye a los psicólogos), `Paciente`, `Cita`, `Pago`, `Promocion`, `Horario`, `HistorialClinico`, `Derivacion`, `Reprogramacion`, `Especialidad` y `Auditoria`. No existe un modelo `Psicologo` separado ni uno llamado `Historial`.

## 4. Contratos de ruta (reemplazan los endpoints `/api/v1`)

| Método | Ruta | Rol | Respuesta esperada | Verificado en |
|---|---|---|---|---|
| POST | `login` | guest | 302 a `home` o de vuelta con error; 429 tras exceder el límite | CP-IT-01 a 04, CP-SYS-19 |
| GET/POST | `admin/pacientes` | administrador, recepcionista | Vista o 302 tras guardar | CP-IT-05, CP-IT-34 |
| POST | `admin/pacientes/{paciente}/derivaciones` | administrador, recepcionista | 302 con la derivación creada | CP-IT-23 |
| PUT | `psicologo/derivaciones/{derivacion}/aceptar` / `rechazar` | psicólogo destinatario | 302; 403 si es otro psicólogo | CP-UT-50, CP-UT-51 |
| GET | `api/agenda-del-dia` | autenticado | JSON `[{hora, estado}]` | CP-UT-43 |
| GET | `api/horas-disponibles` | autenticado | JSON con las horas libres | CP-IT-17 |
| POST | `citas` | autenticado (`CitaPolicy::create`) | 302 a la cita creada | CP-UT-40, CP-IT-21 |
| PUT | `citas/{cita}/reprogramar` | autenticado (`CitaPolicy`) | 302; bloqueo en el 4.º cambio salvo autorización del administrador | CP-IT-18, CP-IT-19 |
| PUT | `citas/{cita}/confirmar` | recepcionista, administrador | 302; bloqueada si el pago no está validado | CP-IT-16 |
| POST | `citas/{cita}/pagos` | personal o el propio paciente | 302 con el pago (o la cuota) registrado | CP-IT-09, CP-UT-29 |
| PUT | `pagos/{pago}/validar` / `rechazar` | recepcionista, administrador | 302 con el pago validado o rechazado | CP-IT-12, CP-IT-13 |
| POST | `psicologo/citas/{cita}/historial` | psicólogo de la cita | 302 con el historial guardado | CP-IT-26 |

Respuestas de error comunes: 302 a `/login` sin sesión, 403 con rol no permitido, 404 si no existe el recurso, 419 sin token CSRF, 422 con validación en peticiones JSON y 429 por exceso de peticiones.

## 5. Criterios de aprobación

- [x] Controladores, modelos y rutas aprobados.
- [x] Contratos verificados por las pruebas de las Fases 6 a 8 (columna "Verificado en").

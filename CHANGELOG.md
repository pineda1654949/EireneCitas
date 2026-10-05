# Registro de cambios

## [2.0.0] - 2026-10-05

Versión preparada para producción.

### Plataforma
- Migración de Laravel 8 (sin soporte de seguridad) a **Laravel 12** y PHP 8.2+.
- Migraciones incrementales que conservan los datos existentes: tablas de sesiones, caché, colas, tokens de recuperación, auditoría e índices de consulta.

### Nuevas funciones
- Recuperación de contraseña por correo.
- Notificaciones por correo, encoladas: registro, confirmación, reprogramación y cancelación de citas, y recordatorio del día anterior.
- Bitácora de auditoría consultable por el administrador, que también registra inicios de sesión e intentos fallidos.
- Respaldos automáticos diarios de la base de datos, con monitoreo y aviso por correo.
- Exportación de reportes a CSV.
- Pausar o reactivar bloques de horario.
- Comando `eirene:crear-admin` para crear el primer administrador en producción.
- Reglas de negocio configurables (`config/eirene.php`).

### Seguridad
- Límite de intentos de inicio de sesión y de formularios públicos.
- Cabeceras HTTP de seguridad (CSP, X-Frame-Options, HSTS, entre otras).
- Notas clínicas cifradas en la base de datos.
- Historia clínica visible solo para los psicólogos tratantes.
- Política de contraseñas (mínimo 8 caracteres, con letras y números).
- Las sesiones abiertas se cierran si se desactiva la cuenta.
- Sin usuarios de demostración en producción.

### Interfaz
- Nuevo diseño con Tailwind CSS 4 y Vite, adaptable a dispositivos móviles, con componentes reutilizables.
- Reserva de citas con horas disponibles en forma de botones.
- Páginas de error en español.

### Calidad
- 210 pruebas automatizadas (unitarias y de funcionalidad), con matriz de trazabilidad.
- Análisis estático con PHPStan/Larastan (nivel 6) y estilo de código con Pint.
- Integración continua con GitHub Actions.

### Corregido
- Las citas reprogramadas no ocupaban su horario.
- Validar el pago de una cita cancelada la volvía a confirmar.
- La edición de promociones no funcionaba.
- El cruce de bloques de horario se detectaba mal en el borde (DEF-001).
- Error 500 al enviar los correos de cita desde la cola (DEF-006).

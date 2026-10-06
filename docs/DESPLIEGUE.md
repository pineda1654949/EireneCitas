# Guía de despliegue a producción · Eirene

Esta guía cubre dos escenarios: **hosting compartido con cPanel** (lo más económico) y **VPS con Nginx** (más control). Las secciones 1, 4, 5 y 6 aplican a ambos.

---

## 1. Requisitos del servidor

| Componente | Versión mínima |
|---|---|
| PHP | **8.2** (recomendado 8.3) |
| Extensiones PHP | `bcmath`, `ctype`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` |
| Base de datos | MySQL 8.0 o MariaDB 10.4+ (`utf8mb4_unicode_ci`) |
| Herramientas | Composer 2, acceso a **cron**, `mysqldump` (para los respaldos) |
| **OPcache** | **Activado** (`zend_extension=opcache`, `opcache.enable=1`). Sin OPcache cada página tarda unas 10 veces más: en las pruebas de la Fase 8, unos 690 ms frente a unos 60 ms. XAMPP lo trae desactivado |
| Certificado | **HTTPS obligatorio** (Let's Encrypt gratuito) |

Node.js **no** es necesario en el servidor: los assets se compilan antes de subir el código (`npm run build`).

---

## 2. Hosting compartido (cPanel)

1. **Compilar en tu PC** antes de subir:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
2. **Subir el proyecto** a una carpeta **fuera** de `public_html`, por ejemplo `/home/usuario/eirene`. Incluye `vendor/` y `public/build/`. No subas `.env`, `node_modules/` ni `tests/`.
3. **Apuntar el dominio a `public/`.** En cPanel → *Dominios*, cambia la raíz del documento a `/home/usuario/eirene/public`.
   Si el hosting no lo permite, copia el contenido de `public/` en `public_html/` y edita `public_html/index.php` para que las rutas apunten a `../eirene/vendor/autoload.php` y `../eirene/bootstrap/app.php`.
4. **Crear la base de datos** y un usuario con permisos **solo** sobre ella (cPanel → *Bases de datos MySQL*).
5. **Configurar `.env`** a partir de `.env.production.example` (ver la sección 4).
6. **Terminal de cPanel** (o SSH):
   ```bash
   cd ~/eirene
   php artisan key:generate              # SOLO la primera vez
   php artisan migrate --force
   php artisan db:seed --force           # carga especialidades y promociones (sin usuarios de prueba)
   php artisan eirene:crear-admin        # crea el primer administrador
   php artisan storage:link
   php artisan optimize                  # cachea configuración, rutas, vistas y eventos
   ```
7. **Cron** (cPanel → *Trabajos de Cron*), cada minuto:
   ```
   * * * * * cd /home/usuario/eirene && php artisan schedule:run >> /dev/null 2>&1
   ```
   Esta única tarea procesa la cola de correos, envía los recordatorios, hace los respaldos y depura la auditoría.

---

## 3. VPS (Ubuntu + Nginx + PHP-FPM)

```bash
sudo apt install nginx mysql-server php8.3-fpm php8.3-{mysql,mbstring,xml,zip,bcmath,curl} unzip
cd /var/www && sudo git clone <repositorio> eirene && cd eirene
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # o compila en tu PC y sube public/build
cp .env.production.example .env  # y completa los valores
php artisan key:generate && php artisan migrate --force && php artisan db:seed --force
php artisan eirene:crear-admin && php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
```

Bloque del servidor de Nginx (`/etc/nginx/sites-available/eirene`):

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/eirene/public;
    index index.php;

    client_max_body_size 10M;
    server_tokens off;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

Luego: `sudo certbot --nginx -d tu-dominio.com` (HTTPS) y el mismo cron de la sección 2 con `crontab -e -u www-data`.

> En un VPS puedes reemplazar el procesamiento de la cola por cron con un *worker* permanente (`supervisor` ejecutando `php artisan queue:work`). Si lo haces, quita la tarea `queue:work` de `routes/console.php`.

---

## 4. Variables de entorno críticas

| Variable | Valor en producción | Por qué |
|---|---|---|
| `APP_ENV` | `production` | Activa HTTPS forzado y oculta los usuarios de prueba |
| `APP_DEBUG` | **`false`** | Con `true` se exponen el código y las credenciales en los errores |
| `APP_KEY` | Generada **una sola vez** | Cifra sesiones y **notas clínicas**: si se pierde o cambia, las notas no se pueden recuperar. Guárdala en un gestor de contraseñas. |
| `APP_URL` | `https://...` | Enlaces correctos en los correos |
| `SESSION_SECURE_COOKIE` | `true` | La cookie de sesión solo viaja por HTTPS |
| `QUEUE_CONNECTION` | `database` | Los correos no retrasan las respuestas |
| `MAIL_*` | Datos SMTP reales | Confirmaciones, recordatorios y recuperación de contraseña |
| `BACKUP_ARCHIVE_PASSWORD` | Contraseña robusta | Cifra los ZIP de respaldo (contienen datos de salud) |
| `LOG_LEVEL` | `warning` | Evita registrar información de más |

---

## 5. Respaldos

- **Automáticos:** todos los días a la 01:30 se respalda la base de datos en `storage/app/private/respaldos/` (`backup:run --only-db`). La retención está en `config/backup.php` (7 días completos, luego diarios, semanales y mensuales).
- **Monitoreo:** a las 03:00 `backup:monitor` verifica que exista un respaldo reciente y avisa por correo a `BACKUP_NOTIFICATION_EMAIL` si falla.
- **Copia fuera del servidor (recomendado):** configura un disco S3 o FTP en `config/filesystems.php` y agrégalo a `BACKUP_DISKS` (por ejemplo `respaldos,s3`).
- **Respaldo manual:** `php artisan backup:run --only-db`. Para ver el estado: `php artisan backup:list`.
- **Restauración:** descomprime el ZIP (con la contraseña) y ejecuta `mysql -u USUARIO -p BASE < db-dumps/mysql-BASE.sql`. **Prueba la restauración** en un entorno aparte al menos una vez al mes.

---

## 6. Actualizar una versión ya publicada

```bash
php artisan down --retry=60           # modo mantenimiento (muestra la página 503)
git pull                              # o sube los archivos nuevos
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize                  # vuelve a generar las cachés
php artisan up
```

**Reversión:** restaura el código de la versión anterior y ejecuta `php artisan migrate:rollback --step=N` solo si la versión agregó migraciones. Antes de actualizar, toma siempre un respaldo manual.

---

## 7. Lista de verificación antes de salir a producción

- [ ] `APP_ENV=production` y `APP_DEBUG=false`
- [ ] OPcache activado (`php -i | grep opcache.enable` debe mostrar `On`)
- [ ] HTTPS activo y redirección de HTTP a HTTPS
- [ ] `APP_KEY` generada y guardada en un lugar seguro
- [ ] El dominio apunta a la carpeta `public/` (el `.env` no es accesible desde el navegador)
- [ ] Usuario de base de datos con permisos solo sobre su base
- [ ] Primer administrador creado con `eirene:crear-admin` (no existen cuentas de demostración)
- [ ] Cron de `schedule:run` configurado y verificado con `php artisan schedule:list`
- [ ] SMTP probado: solicitar "¿Olvidaste tu contraseña?" y recibir el correo
- [ ] Primer respaldo generado (`php artisan backup:run --only-db`) y restauración probada
- [ ] `php artisan optimize` ejecutado
- [ ] Permisos: solo `storage/` y `bootstrap/cache/` con escritura
- [ ] La CI está en verde en la versión desplegada
- [ ] `/up` responde 200 (sirve para monitorear la disponibilidad con UptimeRobot u otro servicio similar)

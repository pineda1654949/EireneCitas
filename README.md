# Eirene - Sistema de Gestion de Citas

Sistema web para la gestion de citas de la clinica psicologica **Eirene**, desarrollado en
**PHP con el framework Laravel 8**, pensado para ejecutarse en un entorno **XAMPP** (Apache + MySQL).

Cubre los requerimientos funcionales de tu documento de proyecto:

| Requerimiento | Descripcion | Donde esta en el codigo |
|---|---|---|
| RF-01 | Registro de citas | `CitaController@store`, vista `citas/create.blade.php` |
| RF-02 | Consulta de disponibilidad de horarios en tiempo real | `Api\DisponibilidadController`, tabla `horarios` |
| RF-03 | Reprogramacion y cancelacion de citas (maximo 3 reprogramaciones) | `CitaController@reprogramar` / `@cancelar`, `Cita::MAX_REPROGRAMACIONES` |
| RF-04 | Confirmacion de citas | `CitaController@confirm`, `PagoController@validar` |
| RF-05 | Registro y actualizacion de datos del paciente | `Admin\PacienteController` |
| RF-06 | Consulta de historia clinica | `Psicologo\HistorialController` |
| RF-07 | Control de acceso por rol (administrador, recepcionista, psicologo, paciente) | `RoleMiddleware`, `routes/web.php` |
| RF-08 | Generacion de reportes / indicadores | `Admin\ReporteController` |

---

## 1. Requisitos previos

1. **XAMPP** instalado (incluye PHP 7.4+/8.x y MySQL) — https://www.apachefriends.org/
2. **Composer** instalado (gestor de dependencias de PHP) — https://getcomposer.org/download/
   - Este proyecto **no trae la carpeta `vendor/`** (son las librerias de Laravel); Composer las
     descarga automaticamente en el paso 3.
3. No necesitas Node.js ni `npm`: las vistas usan Bootstrap 5 vía CDN, no hay que compilar assets.

---

## 2. Copiar el proyecto a XAMPP

1. Descomprime este `.zip`.
2. Copia toda la carpeta `EireneCitas` dentro de `C:\xampp\htdocs\` (Windows) o
   `/Applications/XAMPP/htdocs/` (Mac) / `/opt/lampp/htdocs/` (Linux).

---

## 3. Instalar las dependencias de Laravel

Abre una terminal (cmd, PowerShell o terminal) dentro de la carpeta del proyecto:

```bash
cd C:\xampp\htdocs\EireneCitas
composer install
```

Esto creara la carpeta `vendor/` con el framework Laravel y sus librerias.

---

## 4. Configurar el archivo de entorno (`.env`)

1. Duplica el archivo `.env.example` y renombralo a `.env`.
2. Genera la clave de la aplicacion:

```bash
php artisan key:generate
```

3. El archivo `.env` ya viene preconfigurado para XAMPP por defecto:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eirene_citas
DB_USERNAME=root
DB_PASSWORD=
```

Si tu XAMPP usa otro usuario/contrasena de MySQL, ajusta estas lineas.

---

## 5. Crear la base de datos

1. Inicia **Apache** y **MySQL** desde el Panel de Control de XAMPP.
2. Abre **phpMyAdmin**: http://localhost/phpmyadmin
3. Crea una nueva base de datos llamada exactamente: `eirene_citas`
   (cotejamiento sugerido: `utf8mb4_unicode_ci`).

---

## 6. Ejecutar las migraciones y cargar datos de prueba

Desde la terminal, en la carpeta del proyecto:

```bash
php artisan migrate --seed
```

Esto crea todas las tablas (usuarios, pacientes, citas, pagos, historiales clinicos, etc.) y
carga usuarios de prueba, especialidades, horarios y promociones.

---

## 7. Levantar el sistema

Tienes dos opciones:

**Opcion A — servidor propio de Laravel (recomendado para desarrollo):**
```bash
php artisan serve
```
Luego abre: http://localhost:8000

**Opcion B — usando Apache de XAMPP directamente:**
Abre: `http://localhost/EireneCitas/public/`
(Para una URL mas limpia puedes configurar un Virtual Host apuntando a la carpeta `public/`).

---

## 8. Usuarios de prueba (contrasena para todos: `password`)

| Rol | Correo |
|---|---|
| Administrador | admin@eirene.test |
| Recepcionista | recepcion@eirene.test |
| Psicologo 1 (Ansiedad, Depresion, Autoestima) | psicologo1@eirene.test |
| Psicologo 2 (Pareja, Infantil) | psicologo2@eirene.test |
| Paciente | paciente@eirene.test |

Cualquier persona nueva tambien puede registrarse libremente como paciente desde
`http://localhost:8000/registro`.

---

## 9. Estructura del proyecto (resumen)

```
app/
  Http/Controllers/         Controladores (Auth, Citas, Admin, Psicologo, Api)
  Http/Middleware/          RoleMiddleware.php -> control de acceso por rol (RF-07)
  Models/                   User, Paciente, Cita, Pago, Horario, HistorialClinico, etc.
database/
  migrations/                Estructura de la base de datos
  seeders/                    Datos iniciales (usuarios, especialidades, horarios, promociones)
resources/views/
  layouts/                    Plantillas base (login/registro y panel con sidebar)
  auth/                        Login y registro de pacientes
  dashboard/                   Un panel distinto por cada rol
  citas/                       Solicitar, ver, reprogramar y cancelar citas
  admin/                        Pacientes, psicologos, promociones, pagos, reportes
  psicologo/                    Horarios de disponibilidad e historial clinico
routes/web.php                Todas las rutas del sistema, agrupadas por rol
```

---

## 10. Notas importantes

- **Seguridad**: cambia las contrasenas de los usuarios de prueba antes de usar el sistema en
  un entorno real (produccion).
- **Reglas de negocio ya implementadas**:
  - Maximo **3 reprogramaciones** por cita (`Cita::MAX_REPROGRAMACIONES`), tal como especifica
    tu documento en los requisitos no funcionales de confiabilidad.
  - El sistema no permite dos citas para el mismo psicologo en el mismo horario (validacion en
    `CitaController@store`).
  - Al confirmar un pago, la cita pasa automaticamente a estado "confirmada" (RF-04),
    eliminando la verificacion manual del proceso anterior descrito en tu documento (AS-IS).
- Este proyecto tomo como referencia la estructura tecnica (Laravel + roles) del proyecto
  "CitaMe" que adjuntaste, pero **todo el modelo de datos, reglas de negocio, roles y vistas
  fueron creados desde cero** siguiendo los requerimientos especificos de tu documento
  "Sistema de Gestion de Citas Eirene".

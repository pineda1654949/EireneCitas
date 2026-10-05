# Fase 3: Software Design (Laravel 12)

> Adapta el PDF *03 Fase 3 Software Design*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Propósito

Traducir los requerimientos en una arquitectura modular. El PDF usa SPA + API REST + RDBMS; EireneCitas usa el **MVC de Laravel 12**, con vistas Blade renderizadas en el servidor y llamadas AJAX puntuales para la agenda.

## 2. Arquitectura en 3 capas

| Capa | Tecnología | Responsabilidad | Componentes |
|---|---|---|---|
| Presentación | Blade + **Tailwind CSS 4** + JavaScript propio (Vite 7). Sin React | Formularios, paneles por rol, matriz de horas | Layouts (`x-layouts.app`), componentes (`x-card`, `x-form.input`...) y vistas por módulo |
| Negocio | Laravel 12 (PHP 8.2+) | Rutas, controladores, reglas RN-01, RN-02 y RN-03 | Controllers, Middleware, FormRequest, Policies, **Services** |
| Persistencia | MySQL + Eloquent | Modelos, relaciones, transacciones | Modelos con enums, migraciones, seeders y factories |

## 3. Patrón de la capa de negocio

Los PDF usan Controller-Service-Repository. En EireneCitas:

- **Controllers** (`app/Http/Controllers/{Admin,Api,Auth,Psicologo}`): reciben la petición y devuelven una vista o una redirección. Son delgados.
- **FormRequest** (`app/Http/Requests`): validación y, en algunos casos, autorización.
- **Policies** (`app/Policies`): autorización por pertenencia (cita propia, paciente tratado, derivación recibida).
- **Services** (`app/Services`): ✅ **existen**. `AgendaService` (disponibilidad y matriz de colores), `CitaService` (RN-01, RN-02, RN-03, pagos y sesiones) y `DerivacionService` (RF-05).
- **Models** (`app/Models`): Eloquent. No se usa una capa Repository separada: Eloquent cumple ese papel.

Las reglas de negocio incumplidas lanzan `ReglaDeNegocioException`, que `bootstrap/app.php` convierte en un error de formulario (redirección con mensaje) o en un 422 si la petición es JSON.

## 4. Diseño de interfaz por módulo

| Módulo | Vistas / rutas | Criterio ISO 25010 |
|---|---|---|
| Inscripción y citas | `citas/crear`, `admin/pacientes/create` | Operabilidad y protección contra errores |
| Panel administrativo | `home`, `admin/*`, `reportes` | Claridad |
| Panel del psicólogo | `psicologo/horarios`, `psicologo/derivaciones`, `psicologo/.../historial` | Eficiencia |
| Agenda | `psicologo/horarios`, `api/agenda-del-dia` | Inteligibilidad (Verde = libre, Rojo = ocupado, Gris = no disponible) |

La interfaz se adapta a móviles (CP-SYS-29) y cumple pautas básicas de accesibilidad: etiquetas en todos los campos, enlace "Saltar al contenido", `aria-current` y `aria-invalid` (CP-SYS-32).

## 5. Seguridad

- Contraseñas con bcrypt (cast `hashed` de Eloquent / `Hash::make`).
- Sesión web cifrada, regenerada al iniciar sesión (CP-UT-35).
- Protección CSRF con `@csrf` en los formularios (CP-SYS-20: 419 sin token).
- Middleware `auth` y `rol:`, más Policies.
- Escape automático de Blade con `{{ }}`. No hay ninguna vista con `{!! !!}` (CP-SYS-17).
- Cabeceras OWASP: Content-Security-Policy estricta, X-Frame-Options, HSTS con HTTPS, etc.
- El JWT HS256 de 12 h del PDF **no aplica**: la expiración se configura con `SESSION_LIFETIME` (CP-SYS-14).

## 6. Criterios de aprobación

- [x] Modelos y controladores validados contra RN-01, RN-02 y RN-03 (Fases 6 y 7).
- [x] Formularios y paneles probados en navegador real (Fase 8, Dusk).
- [x] Middlewares probados con denegación explícita de accesos no autorizados (403 en la matriz rol × módulo).

# Fase 5: Code Generation (El Bounce) (Laravel 12)

> Adapta el PDF *05 Fase 5 Code Generation*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Concepto

Es el núcleo de la V: la IA genera el código a partir de las especificaciones de la Fase 4 y el desarrollador actúa como **verificador** (auditor técnico). En este proyecto se usó un asistente de IA (Claude Code) para generar la migración a Laravel 12, las funciones nuevas y las pruebas. Cada cambio se verificó con las herramientas de la sección 3 y con las pruebas de las Fases 6 a 8.

## 2. Artefactos generados en Laravel 12

| Capa | Artefactos |
|---|---|
| Presentación | Vistas Blade, componentes reutilizables (`resources/views/components`), Tailwind CSS 4 y JavaScript sin dependencias (`resources/js`) |
| Negocio | Controladores, FormRequests, Policies, Services (`AgendaService`, `CitaService`, `DerivacionService`), middleware `VerificarRol`, notificaciones |
| Datos | Modelos Eloquent con enums, 19 migraciones, factories y seeders (`UsuariosDemoSeeder`, `CargaSeeder`) |
| Pruebas | 347 pruebas PHPUnit, 15 pruebas Dusk y 5 escenarios k6 |

## 3. Revisión estática y seguridad (SAST)

| Vector | Control en Laravel 12 | Resultado |
|---|---|---|
| Inyección SQL | Eloquent y Query Builder con parámetros; sin SQL concatenado | ✅ CP-IT-35, CP-SYS-16 |
| Payloads | FormRequest y escape de Blade (`{{ }}`), sin `{!! !!}` | ✅ CP-SYS-17 |
| Secretos | `.env` y `APP_KEY` fuera del repositorio | ✅ CP-SYS-23 |
| Errores | `APP_DEBUG=false` y páginas de error propias | ✅ CP-SYS-21, CP-UT-38 |
| Estilo | **Laravel Pint** (`vendor/bin/pint --test`) | ✅ Sin diferencias |
| Análisis estático | **Larastan 3** nivel 6 (`vendor/bin/phpstan analyse`) | ✅ 0 errores |
| Dependencias | `composer audit` y `npm audit` | ✅ 0 vulnerabilidades |

La integración continua (`.github/workflows/ci.yml`) repite estos controles en cada *push*.

## 4. Métricas de eficiencia

El PDF reporta una reducción de 40 h a 6 h (85 %) y un 100 % de cobertura de especificaciones. Esos números son **del PDF original**. Las métricas medibles de este proyecto son:

| Métrica | Valor medido |
|---|---|
| Casos del plan automatizados (Fases 6 a 8) | 123 de 123 casos definidos (51 CP-UT, 38 CP-IT y 34 CP-SYS) |
| Cobertura de líneas del código de `app/` | **97,45 %** (1299 de 1333 líneas) |
| Defectos encontrados por las pruebas y corregidos | 16 registrados (DEF-001 a DEF-016) + hallazgos de la Fase 8 |
| Correcciones aplicadas al código generado tras la revisión humana | Ver la tabla de defectos en `docs/PLAN_DE_PRUEBAS.md` |
| Horas de generación asistida | ______ (completar por el equipo) |
| Horas de revisión humana | ______ (completar por el equipo) |

## 5. Criterios de aprobación

- [x] La aplicación inicia sin errores (`php artisan serve`).
- [x] Las migraciones crean todas las tablas (`php artisan migrate:fresh --seed`) y se pueden revertir.
- [x] Pint y Larastan sin errores.
- [x] Prototipo listo para las pruebas de la rama de validación.

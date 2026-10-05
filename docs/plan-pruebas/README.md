# Plan de pruebas V-Bounce · EireneCitas (Laravel 12)

Documentación del proyecto **Sistema de Gestión de Citas de la Clínica Psicológica Eirene**, adaptada de los PDF originales (escritos para React, Node.js y Express) al sistema real.

> **Versión del framework:** **Laravel 12** (v12.69.3) sobre PHP 8.2+. Una versión anterior de estos documentos hablaba de *Laravel 8.83*. El proyecto se migró a Laravel 12 el 5 de octubre de 2026, porque Laravel 8 dejó de recibir correcciones de seguridad en enero de 2023. Todas las marcas **[VERIFICAR]** de la versión anterior se resolvieron contra el código real; donde había una brecha, la función se implementó y se probó.

## Resultado de la ejecución del plan (5 de octubre de 2026)

| Fase | Herramienta | Casos del plan | Resultado |
|---|---|---|---|
| 5 · Code Generation | Pint, Larastan (PHPStan nivel 6), `composer audit` | — | ✅ 0 observaciones |
| 6 · Unit Testing | PHPUnit 11 | CP-UT-01 a CP-UT-51 (47 métodos, 54 ejecuciones) | ✅ 54/54 |
| 7 · Integration Testing | PHPUnit 11 (Feature) | CP-IT-01 a CP-IT-38 (+ CP-IT-19b) | ✅ 39/39 |
| 8 · System Testing (seguridad) | PHPUnit 11 | CP-SYS-11, 13 a 23, 33, 34 | ✅ 21/21 |
| 8 · System Testing (E2E) | Laravel Dusk 8 + Chrome 154 | CP-SYS-01 a 10, 12, 29 a 32 | ✅ 15/15 |
| 8 · System Testing (rendimiento) | k6 2.2 + Apache | CP-SYS-24 a 28 (+ CP-SYS-06 a escala) | ⚠️ 4 de 5 cumplen. Ver la Fase 8 |
| 9 · Acceptance Testing | Usuarios reales + SUS | CP-UAT-01 a 15 | 📋 Guion e instrumentos listos; **pendiente de ejecución con usuarios** |
| Regresión | PHPUnit (SQLite y MySQL) | Suite completa | ✅ 347 pruebas y 1089 aserciones en ambos motores |
| Cobertura | PCOV | Criterio ≥ 90 % de líneas | ✅ **97,45 %** de líneas (1299/1333) |

## Documentos

| # | Documento | Contenido |
|---|---|---|
| 00 | [Equivalencias y hallazgos](00_Equivalencias_Laravel.md) | Equivalencias Node → Laravel 12, mapa de rutas reales y brechas resueltas (leer primero) |
| 01 | [Análisis del MVP](01_Analisis_del_MVP.md) | Alcance por rol y estado real de cada función |
| 01 | [Fase 1 · Requirements Gathering](01_Fase1_Requirements_Gathering.md) | Catálogo RF-01 a RF-08, reglas RN-01 a RN-03 y trazabilidad |
| 02 | [Análisis funcional](02_Analisis_Funcional.md) | Módulos, agenda por colores, derivación |
| 02 | [Fase 2 · System Analysis](02_Fase2_System_Analysis.md) | Límites, factibilidad y riesgos (estado actualizado) |
| 03 | [Backlog técnico](03_Backlog_Tecnico.md) | Épicas, historias y tareas en Laravel 12 |
| 03 | [Fase 3 · Software Design](03_Fase3_Software_Design.md) | Arquitectura en 3 capas y patrón de la capa de negocio |
| 04 | [Diccionario de datos (BD)](04_Diccionario_de_Datos_BD.md) | Tablas y columnas reales (MySQL + Eloquent) |
| 04 | [Fase 4 · Module Design](04_Fase4_Module_Design.md) | Módulos, controladores y contratos de ruta |
| 05 | [Diccionario funcional](05_Diccionario_Funcional.md) | Campos, controles y reglas de validación |
| 05 | [Fase 5 · Code Generation](05_Fase5_Code_Generation.md) | Revisión estática y métricas |
| 06 | [Diseño técnico](06_Diseno_Tecnico.md) | Estructura, seguridad, transacciones e integraciones |
| 06 | [Fase 6 · Unit Testing](06_Fase6_Unit_Testing.md) | CP-UT-01 a 51 con resultados |
| 07 | [Fase 7 · Integration Testing](07_Fase7_Integration_Testing.md) | CP-IT-01 a 38 con resultados |
| 08 | [Fase 8 · System Testing](08_Fase8_System_Testing.md) | E2E, seguridad y rendimiento con resultados |
| 09 | [Fase 9 · Acceptance Testing](09_Fase9_Acceptance_Testing.md) | Guion UAT, cuestionario SUS y plantillas |
| 10 | [Sustento metodológico](10_Sustento_Metodologico.md) | Modelo V-Bounce aplicado |
| 11 | [Sustento tecnológico](11_Sustento_Tecnologico.md) | Justificación del stack Laravel 12 |

Las evidencias (registros JUnit, salidas de k6, cobertura y 18 capturas de pantalla de Dusk) están en [`evidencias/`](evidencias/).

## Cómo volver a ejecutar el plan

```bash
# Fases 6, 7 y 8 (PHPUnit): una fase o todas
php artisan test --testsuite=Fase6
php artisan test --testsuite=Fase7
php artisan test --testsuite=Fase8
composer test                        # suite completa (regresión)

# Cobertura (requiere PCOV o Xdebug)
php -d extension=pcov -d pcov.enabled=1 vendor/bin/phpunit --coverage-html build/cobertura

# Fase 8 E2E (Dusk): servidor con la base eirene_dusk y luego las pruebas
APP_ENV=dusk.local php artisan serve --port=8010
php artisan dusk

# Fase 8 rendimiento (k6): ver la sección "Entorno de rendimiento" de 08_Fase8_System_Testing.md
k6 run -e BASE=http://127.0.0.1:8020 -e PSICOLOGO_ID=1 -e ESPECIALIDAD_ID=1 tests/Carga/cp_sys_25_concurrencia.js
```

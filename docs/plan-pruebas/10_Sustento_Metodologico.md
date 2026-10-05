# Documento de Sustento Metodológico (V-Bounce) (Laravel 12)

> Adapta el PDF *Doc Sustento Metodológico VBounce Eirene*. Proyecto EireneCitas sobre **Laravel 12** (antes Laravel 8.83; ver [00](00_Equivalencias_Laravel.md)).

## 1. Qué cambia

**Casi nada.** La metodología V-Bounce no depende del lenguaje. Solo cambian el campo "Sistema objetivo", de *Node.js/MySQL* a **Laravel 12 / MySQL**, y las menciones a "backend en Node.js, Express y Sequelize", que pasan a ser "Laravel 12 y Eloquent".

## 2. Fundamentación

Fuente metodológica: Cory Hymel (Crowdbotics), modelo V-Bounce (AI-Native SDLC). Tres premisas:

1. Generación de código casi instantánea con modelos de lenguaje.
2. Lenguaje natural como interfaz primaria.
3. El humano pasa de programador a **verificador** estratégico.

"Bounce" (rebote): el equipo pasa poco tiempo en la base de la V (codificación) y concentra el esfuerzo en verificar (rama izquierda) y validar (rama derecha).

## 3. Fases (Ramas I, II y III) aplicadas a EireneCitas

| Rama | Fase | Rol del humano en Eirene (Laravel 12) | Evidencia |
|---|---|---|---|
| I · Verificación | 1 Requirements | Validar la completitud del flujo de inscripción y de las reglas | [Fase 1](01_Fase1_Requirements_Gathering.md): catálogo y trazabilidad |
| I | 2 System Analysis | Verificar RN-01, RN-02, RN-03 y los riesgos | [Fase 2](02_Fase2_System_Analysis.md): matriz de 10 riesgos |
| I | 3 Software Design | Revisar la arquitectura Laravel 12, Eloquent y los paneles | [Fase 3](03_Fase3_Software_Design.md) |
| I | 4 Module Design | Aprobar modelos y rutas | [Fase 4](04_Fase4_Module_Design.md): contratos de ruta |
| II · Implementación | 5 Code Generation | Revisión estática (Pint, Larastan nivel 6, `composer audit`) | [Fase 5](05_Fase5_Code_Generation.md): 0 observaciones |
| III · Validación | 6 Unit Testing | Ejecutar PHPUnit sobre modelos y reglas | [Fase 6](06_Fase6_Unit_Testing.md): 54/54, cobertura del 97,45 % |
| III | 7 Integration | Pruebas Feature de rutas, BD y middleware | [Fase 7](07_Fase7_Integration_Testing.md): 39/39 |
| III | 8 System Testing | E2E con Dusk, seguridad y carga con k6 | [Fase 8](08_Fase8_System_Testing.md): 36/36 funcionales, rendimiento 4/5 |
| III | 9 Acceptance | UAT con usuarios y escala SUS | [Fase 9](09_Fase9_Acceptance_Testing.md): instrumentos listos, pendiente |

## 4. Ciclo de 6 pasos por fase

Input, AI Generation, Human Review, Refinement, Approval y Knowledge Capture. Se aplica igual en cada fase. En este proyecto, el paso **Human Review / Refinement** detectó y corrigió 16 defectos en el código generado (DEF-001 a DEF-016, registrados en `docs/PLAN_DE_PRUEBAS.md`) y 3 hallazgos de entorno en la Fase 8 (F8-01 a F8-03). Es la evidencia de que el rol de verificador agrega valor sobre la generación automática.

## 5. Justificación en Eirene

1. **Prototipo no persistente:** la IA acelera el backend mientras el equipo verifica la seguridad de los datos clínicos (notas cifradas, acceso por pertenencia).
2. **Reglas críticas:** RN-01 (3 reprogramaciones), RN-02 (pago verificado) y RN-03 (sin colisiones) se verifican con casos de prueba escritos desde la Fase 1 y ejecutados en las Fases 6, 7 y 8.
3. **Calidad ISO/IEC 25010 y seguridad ISO/IEC 27001:** el esfuerzo humano se concentra en la verificación.

## 6. Consistencia

El documento original cita a Hymel con el título "ARTICULO - MODELO METODOLOGIA". Para la entrega, incluye la referencia bibliográfica completa en el formato que pida la universidad: **[REFERENCIA COMPLETA A COMPLETAR]**.

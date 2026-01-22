# Sistema de Evaluaciones y Jurados - CAS

## Índice
1. [Resumen del Sistema](#resumen-del-sistema)
2. [Fases de Evaluación](#fases-de-evaluación)
3. [Evaluación Curricular (CV)](#evaluación-curricular-cv)
4. [Evaluación de Entrevista](#evaluación-de-entrevista)
5. [Gestión de Jurados](#gestión-de-jurados)
6. [Cálculo de Puntajes](#cálculo-de-puntajes)

---

## Resumen del Sistema

El sistema de evaluación CAS consta de **dos fases evaluativas** con un puntaje total de **100 puntos**:

| Fase | Puntaje Máximo | Puntaje Mínimo Aprobatorio |
|------|----------------|---------------------------|
| Evaluación Curricular (CV) | 50 pts | 35 pts |
| Entrevista Personal | 50 pts | - |
| **TOTAL** | **100 pts** | - |

**Flujo:** El postulante debe obtener mínimo **35 puntos en CV** para pasar a la fase de Entrevista.

---

## Fases de Evaluación

| Fase | Código | Descripción | Requiere Jurado |
|------|--------|-------------|-----------------|
| 2 | CV_EVALUATION | Evaluación Curricular | Sí |
| 3 | INTERVIEW | Entrevista Personal | Sí |

---

## Evaluación Curricular (CV)

**Puntaje:** Máximo 50 puntos | Mínimo aprobatorio: 35 puntos

Los criterios están asociados a un `position_code_id` específico según el tipo de puesto.

### Estructura de Criterios CV

| Criterio | Mín | Máx | Composición |
|----------|-----|-----|-------------|
| Formación Académica | 17 | 25 | Base (17) + Adicionales (hasta 8) |
| Experiencia Específica | 18 | 25 | Base (18) + Adicionales (hasta 7) |
| **TOTAL CV** | **35** | **50** | |

### Sistema de Puntos Adicionales

Los puntos adicionales son **acumulables** (se suman todos los que el postulante cumple).

**Formato en base de datos:**
```json
{
  "is_cumulative": true,
  "additional_points": [
    { "value": 4, "description": "Egresado de Maestría" },
    { "value": 4, "description": "Grado de Magister" }
  ]
}
```

### Criterios por Tipo de Puesto

#### ESP_I - Profesional Especialista

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Egresado Maestría (+4), Grado Magíster (+4) |
| Experiencia Específica | 18 | >1 año <2 años gestión pública (+3), >2 años gestión pública (+4) |

#### PP_I, PP_II - Profesional de Planta I/II

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Estudio Maestría (+3), Egresado Maestría (+5) |
| Experiencia Específica | 18 | >1 año <2 años gestión pública (+3), >2-3 años gestión pública (+4) |

#### PP_III - Profesional de Planta III

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Estudio Maestría (+3), Egresado Maestría (+5) |
| Experiencia Específica | 18 | >6 meses <1 año gestión pública (+3), >1 año gestión pública (+4) |

#### AA_I - Asistente Administrativo

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Título Profesional (+4), Estudio Maestría (+4) |
| Experiencia Específica | 18 | >6 meses <1 año gestión pública (+3), >1 año gestión pública (+4) |

#### TEC_I - Técnico Administrativo

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Bachiller/Título Técnico (+4), Título Universitario (+4) |
| Experiencia Específica | 18 | >1 año <2 años exp. general (+3), >2 años exp. general (+4) |

#### TEC_II - Técnico de Soporte

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Bachiller/Título Técnico (+8) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+3), >1 año exp. general (+4) |

#### AAI - Auxiliar I

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Egresado Universitario/Técnico (+4), Bachiller (+4) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+7) |

*Nota: Experiencia general desde culminación secundaria.*

#### AAII - Auxiliar II

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Egresado Universitario/Técnico (+8) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+4), >1 año exp. general (+3) |

*Nota: Experiencia general desde culminación secundaria.*

#### AAIII - Auxiliar III

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Estudios Universitarios/Técnicos (+7) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+8) |

---

## Evaluación de Entrevista

**Puntaje:** Máximo 50 puntos

Los criterios de entrevista son **genéricos** (sin `position_code_id`), aplicables a todos los puestos.

### Criterios de Entrevista

| Criterio | Mín | Máx | Descripción |
|----------|-----|-----|-------------|
| Dominio y conocimiento de las funciones del puesto | 0 | 12.50 | Comprensión de funciones y responsabilidades del cargo |
| Grado de Análisis | 0 | 12.50 | Capacidad de analizar situaciones, resolver problemas y tomar decisiones |
| Ética y Actitud | 0 | 12.50 | Valores, principios y disposición hacia el trabajo |
| Comunicación | 0 | 12.50 | Capacidad de expresarse de manera clara, coherente y profesional |
| **TOTAL ENTREVISTA** | **0** | **50** | |

---

## Gestión de Jurados

### Asignación de Jurados a Convocatoria

| Campo | Tipo | Descripción |
|-------|------|-------------|
| job_posting_id | uuid | Convocatoria asignada |
| jury_id | uuid | Usuario con rol JURY |
| role | enum | TITULAR / SUPLENTE |
| designation_document | string | Documento de designación |
| designation_number | string | Número de resolución |

### Roles

| Rol | Función |
|-----|---------|
| TITULAR | Realiza evaluaciones activamente |
| SUPLENTE | Reemplazo en caso de impedimento del titular |

### Conflictos de Interés

| Tipo | Descripción |
|------|-------------|
| FAMILIAR | Relación de parentesco |
| LABORAL | Relación laboral previa |
| ECONOMICO | Vínculo económico |
| AMISTAD | Relación personal cercana |

**Acciones:** NONE (sin acción), RECUSAL (se excusa), REASSIGNMENT (reasignación)

---

## Cálculo de Puntajes

### Fórmula General

```
PUNTAJE_CV = Formación_Académica + Experiencia_Específica
           = (base_FA + adicionales_FA) + (base_EE + adicionales_EE)
           = (17 + [0-8]) + (18 + [0-7])
           = 35 a 50 puntos

PUNTAJE_ENTREVISTA = Dominio + Análisis + Ética + Comunicación
                   = 12.50 + 12.50 + 12.50 + 12.50
                   = 0 a 50 puntos

PUNTAJE_FINAL = PUNTAJE_CV + PUNTAJE_ENTREVISTA
              = 0 a 100 puntos
```

### Ejemplo de Cálculo

**Postulante a ESP_I:**

| Criterio | Base | Adicional | Total |
|----------|------|-----------|-------|
| Formación Académica | 17 | +4 (Egresado Maestría) | 21 |
| Experiencia Específica | 18 | +4 (>2 años gestión pública) | 22 |
| **TOTAL CV** | | | **43** |

| Criterio Entrevista | Puntaje |
|---------------------|---------|
| Dominio y conocimiento | 10.00 |
| Grado de Análisis | 11.50 |
| Ética y Actitud | 12.00 |
| Comunicación | 11.00 |
| **TOTAL ENTREVISTA** | **44.50** |

**PUNTAJE FINAL: 43 + 44.50 = 87.50 puntos**

### Bonificaciones Especiales

Se aplican sobre el puntaje final:

| Condición | Bonificación |
|-----------|--------------|
| Discapacidad | +15% |
| Licenciado FFAA | +10% |
| Deportista Nacional | +10% |
| Deportista Internacional | +15% |
| Víctima de Terrorismo | +10% |

```
puntaje_con_bonificacion = min(puntaje_final × (1 + bonificacion), 100)
```

---

## Estructura de Base de Datos

### Tabla: evaluation_criteria

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | int | PK |
| position_code_id | int/null | FK a position_codes (null = entrevista) |
| phase | int | 2 = CV, 3 = Entrevista |
| name | string | Nombre del criterio |
| description | json/text | Configuración adicional o descripción |
| max_score | decimal(5,2) | Puntaje máximo |
| min_score | decimal(5,2) | Puntaje mínimo/base |

### Tabla: evaluations

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | uuid | PK |
| application_id | uuid | FK postulación |
| process_phase_id | uuid | FK fase (CV o Entrevista) |
| evaluator_id | uuid | FK jurado evaluador |
| status | enum | PENDIENTE, EN_PROGRESO, COMPLETADA |
| raw_score | decimal(5,2) | Puntaje sin bonificación |
| final_score | decimal(5,2) | Puntaje con bonificación |

### Tabla: evaluation_details

| Campo | Tipo | Descripción |
|-------|------|-------------|
| evaluation_id | uuid | FK evaluación |
| evaluation_criterion_id | int | FK criterio |
| score | decimal(5,2) | Puntaje otorgado |
| comments | text | Observaciones del jurado |

---

**Extraído de:** cas-info.md + evaluation_criteria.json
**Versión:** 1.0
**Fecha:** 2025

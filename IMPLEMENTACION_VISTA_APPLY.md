# Instrucciones para actualizar apply.blade.php

## Ubicación
`Modules/ApplicantPortal/resources/views/job-postings/apply.blade.php`

## Cambios necesarios

### 1. Mostrar requisito de carrera (Agregar después del título del perfil)

```blade
{{-- Información del requisito de carrera --}}
@if(!empty($acceptedCareerNames))
    <div class="alert alert-info mb-4">
        <h5><i class="fas fa-graduation-cap"></i> Requisito de Carrera Profesional</h5>
        <p class="mb-0">
            Este puesto requiere título profesional en:
            <strong>{{ implode(' o ', $acceptedCareerNames) }}</strong>
        </p>
        @if(count($acceptedCareerNames) > 1)
            <small class="text-muted">
                Se aceptan carreras equivalentes según normativa vigente.
            </small>
        @endif
    </div>
@endif
```

### 2. Reemplazar el campo de texto libre de carrera por SELECT

Buscar el input de carrera y reemplazarlo con:

```blade
<div class="mb-3">
    <label>Carrera Profesional *</label>
    <select
        :name="`academics[${index}][careerId]`"
        x-model="academics[index].careerId"
        @change="checkCareerMatch(index)"
        class="form-control"
        required
    >
        <option value="">Seleccione una carrera</option>
        @foreach($academicCareers as $categoryGroup => $careers)
            <optgroup label="{{ $categoryGroup }}">
                @foreach($careers as $career)
                    <option
                        value="{{ $career->id }}"
                        @if(in_array($career->id, $acceptedCareerIds)) data-accepted="true" @endif
                    >
                        {{ $career->name }}
                        @if(in_array($career->id, $acceptedCareerIds))
                            ✓
                        @endif
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>

    {{-- Advertencia si no coincide con requisito --}}
    <div
        x-show="academics[index].careerId && !isCareerAccepted(academics[index].careerId)"
        class="mt-2 p-2 bg-yellow-50 border border-yellow-300 rounded"
        style="display: none;"
    >
        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
        <span class="text-yellow-800">
            La carrera seleccionada no coincide con el requisito del perfil.
            Puedes postular, pero es probable que seas declarado NO APTO.
        </span>
    </div>

    {{-- Indicador de match --}}
    <div
        x-show="academics[index].careerId && isCareerAccepted(academics[index].careerId)"
        class="mt-2 p-2 bg-green-50 border border-green-300 rounded"
        style="display: none;"
    >
        <i class="fas fa-check-circle text-green-600"></i>
        <span class="text-green-800">
            ✓ Cumple con el requisito de carrera profesional
        </span>
    </div>
</div>
```

### 3. Actualizar Alpine.js data section

Agregar en el objeto de datos de Alpine.js:

```javascript
{
    academics: [
        {
            degreeType: '',
            careerId: '', // 💎 NUEVO: ID de carrera
            careerField: '', // Mantener por compatibilidad
            institution: '',
            degreeTitle: '',
            issueDate: ''
        }
    ],
    acceptedCareerIds: @json($acceptedCareerIds),

    isCareerAccepted(careerId) {
        return this.acceptedCareerIds.includes(careerId);
    },

    checkCareerMatch(index) {
        const careerId = this.academics[index].careerId;
        if (careerId && !this.isCareerAccepted(careerId)) {
            console.warn('Carrera seleccionada no cumple requisito');
        }
    },

    addAcademic() {
        this.academics.push({
            degreeType: '',
            careerId: '',
            careerField: '',
            institution: '',
            degreeTitle: '',
            issueDate: ''
        });
    },

    removeAcademic(index) {
        if (this.academics.length > 1) {
            this.academics.splice(index, 1);
        }
    }
}
```

### 4. IMPORTANTE: Actualizar el envío del formulario

En el método que serializa el formulario, asegurarse de incluir `careerId`:

```javascript
formData.academics = this.academics.map(academic => ({
    degreeType: academic.degreeType,
    institution: academic.institution,
    careerId: academic.careerId, // 💎 NUEVO
    careerField: academic.careerField, // Mantener por compatibilidad
    degreeTitle: academic.degreeTitle,
    issueDate: academic.issueDate
}));
```

## Notas importantes

1. **Compatibilidad**: El campo `careerField` se mantiene para registros históricos pero ahora se prioriza `careerId`
2. **Validación en tiempo real**: Las advertencias visuales ayudan al postulante a saber si cumple con el requisito
3. **Equivalencias**: Los IDs aceptados ya incluyen equivalencias (calculados en el backend)
4. **Fallback**: Si un perfil no tiene carreras mapeadas, el sistema usará validación legacy

## Testing

Después de implementar, probar:
1. Ver mensaje de requisito de carrera en la vista
2. Verificar que el SELECT muestre carreras agrupadas por categoría
3. Validar que las advertencias amarillas/verdes funcionen
4. Comprobar que el formulario envíe correctamente `careerId`
5. Verificar que AutoGrader valide correctamente usando la tabla pivote

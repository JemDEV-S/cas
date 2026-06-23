<div x-show="currentStep === 7" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Condiciones Especiales</h2>
    <p class="text-gray-600 mb-6">Marca si cumples con alguna de estas condiciones. Los documentos sustentatorios serán solicitados en la Fase 5.</p>

    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded">
        <p class="text-sm text-yellow-700">
            <strong>Nota:</strong> Las bonificaciones se aplicarán según el Decreto Legislativo vigente. Solo marca las condiciones que puedas sustentar con documentos oficiales.
        </p>
    </div>

    <div class="space-y-4">
        <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
            <input type="checkbox"
                   x-model="formData.specialConditions.disability"
                   @change="autoSave"
                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <div class="ml-3">
                <span class="font-semibold text-gray-900">Persona con Discapacidad</span>
                <p class="text-sm text-gray-600 mt-1">Bonificación del 15% sobre el puntaje final (Ley N° 29973)</p>
            </div>
        </label>

        <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
            <input type="checkbox"
                   x-model="formData.specialConditions.military"
                   @change="autoSave"
                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <div class="ml-3">
                <span class="font-semibold text-gray-900">Licenciado de las Fuerzas Armadas</span>
                <p class="text-sm text-gray-600 mt-1">Bonificación del 10% sobre el puntaje final (Ley N° 29248)</p>
            </div>
        </label>

        <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
            <input type="checkbox"
                   x-model="formData.specialConditions.athleteNational"
                   @change="autoSave"
                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <div class="ml-3">
                <span class="font-semibold text-gray-900">Deportista Calificado Nacional</span>
                <p class="text-sm text-gray-600 mt-1">Bonificación del 10% sobre el puntaje final (Ley N° 27674)</p>
            </div>
        </label>

        <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
            <input type="checkbox"
                   x-model="formData.specialConditions.athleteIntl"
                   @change="autoSave"
                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <div class="ml-3">
                <span class="font-semibold text-gray-900">Deportista Calificado Internacional</span>
                <p class="text-sm text-gray-600 mt-1">Bonificación del 15% sobre el puntaje final (Ley N° 27674)</p>
            </div>
        </label>
    </div>
</div>

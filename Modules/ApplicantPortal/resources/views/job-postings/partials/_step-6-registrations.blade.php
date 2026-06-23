<div x-show="currentStep === 6" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Registros Profesionales</h2>
    <p class="text-gray-600 mb-6">Completa según aplique a tu profesión y al perfil solicitado.</p>

    <div class="space-y-6">
        @if($jobProfile->colegiatura_required)
            <div class="border border-gray-200 rounded-xl p-6">
                <h3 class="font-bold text-gray-900 mb-4">Colegiatura y Habilitación Profesional *</h3>

                <div class="mb-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox"
                               x-model="formData.registrations.colegiatura.habilitado"
                               @change="autoSave"
                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div class="ml-3">
                            <span class="font-semibold text-gray-900">Cuento con habilitación profesional vigente</span>
                            <p class="text-sm text-gray-600 mt-1">Declaro que me encuentro colegiado(a) y habilitado(a) para ejercer la profesión</p>
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
                     :class="{ 'opacity-50': !formData.registrations.colegiatura.habilitado }">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Colegio Profesional</label>
                        <input type="text"
                               x-model="formData.registrations.colegiatura.college"
                               @input="autoSave"
                               :disabled="!formData.registrations.colegiatura.habilitado"
                               :required="formData.registrations.colegiatura.habilitado"
                               placeholder="Ej: Colegio de Ingenieros del Perú"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Colegiatura</label>
                        <input type="text"
                               x-model="formData.registrations.colegiatura.number"
                               @input="autoSave"
                               :disabled="!formData.registrations.colegiatura.habilitado"
                               :required="formData.registrations.colegiatura.habilitado"
                               placeholder="Ej: 123456"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                    </div>
                </div>

                <div x-show="!formData.registrations.colegiatura.habilitado" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Este perfil requiere contar con colegiatura y habilitación profesional vigente. Si no la tienes, tu postulación podría no ser considerada.
                    </p>
                </div>
            </div>
        @endif

        <div class="border border-gray-200 rounded-xl p-6">
            <h3 class="font-bold text-gray-900 mb-4">Registro OSCE <span class="text-sm font-normal text-gray-500">(Si aplica)</span></h3>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Registro OSCE</label>
                <input type="text"
                       x-model="formData.registrations.osce"
                       @input="autoSave"
                       placeholder="Si aplica"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="border border-gray-200 rounded-xl p-6">
            <h3 class="font-bold text-gray-900 mb-4">Licencia de Conducir</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Licencia</label>
                    <input type="text"
                           x-model="formData.registrations.license.number"
                           @input="autoSave"
                           placeholder="Si posees"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Categoría</label>
                    <select x-model="formData.registrations.license.category"
                            @change="autoSave"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione...</option>
                        <option value="A-I">A-I (Moto menor 150cc)</option>
                        <option value="A-IIa">A-IIa (Moto 150-250cc)</option>
                        <option value="A-IIb">A-IIb (Moto mayor 250cc)</option>
                        <option value="A-IIIa">A-IIIa (Auto menor 2000kg)</option>
                        <option value="A-IIIb">A-IIIb (Auto mayor 2000kg)</option>
                        <option value="A-IIIc">A-IIIc (Transporte personal)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Vigencia</label>
                    <input type="date"
                           x-model="formData.registrations.license.expiryDate"
                           @input="autoSave"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>
</div>

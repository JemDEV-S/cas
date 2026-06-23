<div x-show="currentStep === 3" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Experiencia Laboral</h2>
    <p class="text-gray-600 mb-6">Declara tu experiencia laboral. El sistema calculará automáticamente la duración exacta en años, meses y días.</p>

    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-amber-800">
                    <strong>¡Importante!</strong> Marca correctamente cada experiencia:
                </p>
                <ul class="mt-2 text-sm text-amber-700 list-disc list-inside space-y-1">
                    <li><strong>Experiencia General:</strong> Toda tu experiencia laboral cuenta automáticamente</li>
                    <li><strong>Experiencia Específica:</strong> Solo marca el checkbox si las funciones están <strong>directamente relacionadas</strong> con el puesto al que postulas</li>
                    <li><strong>Sector Público:</strong> Marca si trabajaste en instituciones del Estado</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-5 mb-6 shadow-sm">
        <h3 class="font-bold text-blue-900 mb-3 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
            </svg>
            Requisitos del Perfil
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="flex items-start">
                <span class="text-2xl mr-2">📋</span>
                <div>
                    <p class="font-semibold text-blue-900">Experiencia General Requerida:</p>
                    <p class="text-lg font-bold text-blue-700">
                        @if($jobProfile->general_experience_years)
                            {{ is_object($jobProfile->general_experience_years) ? $jobProfile->general_experience_years->toHuman() : $jobProfile->general_experience_years . ' años' }}
                        @else
                            No requerida
                        @endif
                        (mínimo)
                    </p>
                </div>
            </div>
            <div class="flex items-start">
                <span class="text-2xl mr-2">🎯</span>
                <div>
                    <p class="font-semibold text-purple-900">Experiencia Específica Requerida:</p>
                    <p class="text-lg font-bold text-purple-700">
                        @if($jobProfile->specific_experience_years)
                            {{ is_object($jobProfile->specific_experience_years) ? $jobProfile->specific_experience_years->toHuman() : $jobProfile->specific_experience_years . ' años' }}
                        @else
                            No requerida
                        @endif
                        (mínimo)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="relative overflow-hidden bg-gradient-to-br from-green-50 to-green-100 border-2 rounded-xl p-5 shadow-sm"
             :class="{
                 'border-green-500': checkExperienceRequirement('general'),
                 'border-yellow-500': !checkExperienceRequirement('general')
             }">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm text-green-700 font-semibold mb-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        Experiencia General Total
                    </p>
                    <p class="text-3xl font-bold text-green-900" x-text="calculateTotalExperience('general')"></p>
                </div>
                <div class="ml-3">
                    <span x-show="checkExperienceRequirement('general')" class="text-3xl">✅</span>
                    <span x-show="!checkExperienceRequirement('general')" class="text-3xl">⚠️</span>
                </div>
            </div>
            <p class="text-xs mt-2"
               :class="checkExperienceRequirement('general') ? 'text-green-600' : 'text-yellow-600'">
                <span x-show="checkExperienceRequirement('general')">✓ Cumples el requisito</span>
                <span x-show="!checkExperienceRequirement('general')">⚠ No cumples el requisito mínimo</span>
            </p>
        </div>

        <div class="relative overflow-hidden bg-gradient-to-br from-purple-50 to-purple-100 border-2 rounded-xl p-5 shadow-sm"
             :class="{
                 'border-purple-500': checkExperienceRequirement('specific'),
                 'border-yellow-500': !checkExperienceRequirement('specific')
             }">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm text-purple-700 font-semibold mb-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Experiencia Específica Total
                    </p>
                    <p class="text-3xl font-bold text-purple-900" x-text="calculateTotalExperience('specific')"></p>
                </div>
                <div class="ml-3">
                    <span x-show="checkExperienceRequirement('specific')" class="text-3xl">✅</span>
                    <span x-show="!checkExperienceRequirement('specific')" class="text-3xl">⚠️</span>
                </div>
            </div>
            <p class="text-xs mt-2"
               :class="checkExperienceRequirement('specific') ? 'text-purple-600' : 'text-yellow-600'">
                <span x-show="checkExperienceRequirement('specific')">✓ Cumples el requisito</span>
                <span x-show="!checkExperienceRequirement('specific')">⚠ No cumples el requisito mínimo</span>
            </p>
        </div>
    </div>

    <template x-for="(experience, index) in formData.experiences" :key="index">
        <div class="border border-gray-200 rounded-xl p-6 mb-4">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-bold text-gray-900">Experiencia <span x-text="index + 1"></span></h3>
                <button type="button"
                        @click="removeExperience(index)"
                        x-show="formData.experiences.length > 1"
                        class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Empresa/Organización *</label>
                    <input type="text"
                           x-model="experience.organization"
                           @input="autoSave"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cargo/Puesto *</label>
                    <input type="text"
                           x-model="experience.position"
                           @input="autoSave"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Inicio *</label>
                    <input type="date"
                           x-model="experience.startDate"
                           @input="autoSave"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Fin</label>
                    <input type="date"
                           x-model="experience.endDate"
                           @input="autoSave"
                           :disabled="experience.isCurrent"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                    <label class="flex items-center mt-2">
                        <input type="checkbox"
                               x-model="experience.isCurrent"
                               @change="autoSave"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">Trabajo actual</span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Tipo de Experiencia (marca lo que corresponda):</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                               :class="experience.isPublicSector ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'">
                            <input type="checkbox"
                                   x-model="experience.isPublicSector"
                                   @change="autoSave"
                                   class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-semibold text-gray-900 flex items-center">
                                    🏛️ Sector Público
                                </span>
                                <span class="text-xs text-gray-600">Experiencia en instituciones del Estado</span>
                            </div>
                        </label>

                        <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                               :class="experience.isSpecific ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                            <input type="checkbox"
                                   x-model="experience.isSpecific"
                                   @change="autoSave"
                                   class="mt-0.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3">
                                <span class="text-sm font-semibold text-gray-900 flex items-center">
                                    🎯 Experiencia Específica
                                </span>
                                <span class="text-xs text-gray-600">Funciones relacionadas al puesto</span>
                            </div>
                        </label>
                    </div>
                    <p class="text-xs text-amber-600 mt-2" x-show="experience.isSpecific">
                        ⚠️ Solo marca como específica si las funciones que realizaste están <strong>directamente relacionadas</strong> con el puesto al que postulas
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción de Funciones</label>
                    <textarea x-model="experience.description"
                              @input="autoSave"
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="md:col-span-2 bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <strong>Duración calculada:</strong>
                        <span x-text="calculateDuration(experience.startDate, experience.endDate || (experience.isCurrent ? new Date().toISOString().slice(0,7) : ''))"></span>
                    </p>
                </div>
            </div>
        </div>
    </template>

    <button type="button"
            @click="addExperience"
            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-colors">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Agregar otra experiencia
    </button>
</div>

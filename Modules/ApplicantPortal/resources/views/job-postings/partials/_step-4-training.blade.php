<div x-show="currentStep === 4" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Capacitaciones y Cursos</h2>
    <p class="text-gray-600 mb-6">Marca las capacitaciones que cumples y declara información adicional. Los certificados serán solicitados en la Fase 5.</p>

    @if(!empty($jobProfile->required_courses) && count($jobProfile->required_courses) > 0)
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 p-5 mb-6 rounded-xl shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-blue-900 flex items-center">
                    <i class="fas fa-certificate mr-2"></i> Capacitaciones Requeridas
                </h5>
                <div class="text-sm font-semibold text-blue-700 bg-white px-3 py-1 rounded-full">
                    <span x-text="getRequiredCoursesCompliance().met"></span> de {{ count($jobProfile->required_courses) }} cumplidas
                </div>
            </div>

            <p class="text-sm text-blue-800 mb-4">
                <i class="fas fa-info-circle"></i>
                Indica si posees cada capacitación requerida. Si tienes una similar con otro nombre, márcala como "afín".
            </p>

            <div class="space-y-4">
                @foreach($jobProfile->required_courses as $index => $course)
                    <div class="bg-white p-5 rounded-xl border-2 border-gray-200 shadow-sm">
                        <div class="mb-3">
                            <span class="font-semibold text-gray-900 text-base">📋 {{ $course }}</span>
                        </div>

                        <div class="space-y-3">
                            {{-- Opción 1: Tengo esta capacitación exacta --}}
                            <label class="flex items-start cursor-pointer p-3 rounded-lg hover:bg-green-50 transition-colors"
                                :class="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact' ? 'bg-green-50 border-2 border-green-300' : 'border-2 border-transparent'">
                                <input
                                    type="radio"
                                    name="course_status_{{ $index }}"
                                    value="exact"
                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].status"
                                    @change="autoSave"
                                    class="mt-0.5 text-green-600 focus:ring-green-500"
                                >
                                <div class="ml-3 flex-1">
                                    <span class="font-medium text-gray-800">✅ Tengo esta capacitación exacta</span>

                                    <div x-show="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        class="mt-3 space-y-3 pl-2 border-l-2 border-green-300">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Institución *</label>
                                                <input
                                                    type="text"
                                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].institution"
                                                    @input="autoSave"
                                                    :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                                    :disabled="formData.requiredCoursesCompliance[{{ $index }}].status !== 'exact'"
                                                    placeholder="Nombre de la institución"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Año *</label>
                                                <input
                                                    type="number"
                                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].year"
                                                    @input="autoSave"
                                                    :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                                    :disabled="formData.requiredCoursesCompliance[{{ $index }}].status !== 'exact'"
                                                    placeholder="2020"
                                                    min="1990"
                                                    :max="new Date().getFullYear()"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Horas *</label>
                                                <input
                                                    type="number"
                                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].hours"
                                                    @input="autoSave"
                                                    :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                                    :disabled="formData.requiredCoursesCompliance[{{ $index }}].status !== 'exact'"
                                                    placeholder="40"
                                                    min="1"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            {{-- Opción 2: Tengo una capacitación afín --}}
                            <label class="flex items-start cursor-pointer p-3 rounded-lg hover:bg-amber-50 transition-colors"
                                :class="formData.requiredCoursesCompliance[{{ $index }}].status === 'related' ? 'bg-amber-50 border-2 border-amber-300' : 'border-2 border-transparent'">
                                <input
                                    type="radio"
                                    name="course_status_{{ $index }}"
                                    value="related"
                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].status"
                                    @change="autoSave"
                                    class="mt-0.5 text-amber-600 focus:ring-amber-500"
                                >
                                <div class="ml-3 flex-1">
                                    <div>
                                        <span class="font-medium text-gray-800">⚠️ Tengo una capacitación similar con otro nombre</span>
                                        <button type="button"
                                            class="ml-1 text-amber-600 hover:text-amber-800"
                                            title="Una capacitación es afín cuando el contenido es similar aunque el nombre sea diferente. Ejemplo: Si requieren 'Excel Avanzado' y tienes 'Hojas de Cálculo Avanzadas'"
                                            @click="$event.stopPropagation()">
                                            <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div x-show="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        class="mt-3 space-y-3 pl-2 border-l-2 border-amber-300">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre de tu capacitación *</label>
                                            <input
                                                type="text"
                                                x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedCourseName"
                                                @input="autoSave"
                                                :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                :disabled="formData.requiredCoursesCompliance[{{ $index }}].status !== 'related'"
                                                placeholder="Ej: Hojas de Cálculo Avanzadas"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                            >
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Institución *</label>
                                                <input
                                                    type="text"
                                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedInstitution"
                                                    @input="autoSave"
                                                    :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                    :disabled="formData.requiredCoursesCompliance[{{ $index }}].status !== 'related'"
                                                    placeholder="Nombre de la institución"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Año *</label>
                                                <input
                                                    type="number"
                                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedYear"
                                                    @input="autoSave"
                                                    :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                    :disabled="formData.requiredCoursesCompliance[{{ $index }}].status !== 'related'"
                                                    placeholder="2020"
                                                    min="1990"
                                                    :max="new Date().getFullYear()"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Horas *</label>
                                                <input
                                                    type="number"
                                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedHours"
                                                    @input="autoSave"
                                                    :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                    :disabled="formData.requiredCoursesCompliance[{{ $index }}].status !== 'related'"
                                                    placeholder="40"
                                                    min="1"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                                >
                                            </div>
                                        </div>
                                        <div class="bg-amber-100 border border-amber-300 rounded-lg p-2">
                                            <p class="text-xs text-amber-800">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Nota:</strong> El comité evaluará si tu capacitación es afín a la requerida. Deberás presentar el certificado en la Fase 5.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            {{-- Opción 3: No tengo esta capacitación --}}
                            <label class="flex items-start cursor-pointer p-3 rounded-lg hover:bg-gray-50 transition-colors"
                                :class="formData.requiredCoursesCompliance[{{ $index }}].status === 'none' ? 'bg-gray-50 border-2 border-gray-300' : 'border-2 border-transparent'">
                                <input
                                    type="radio"
                                    name="course_status_{{ $index }}"
                                    value="none"
                                    x-model="formData.requiredCoursesCompliance[{{ $index }}].status"
                                    @change="autoSave"
                                    class="mt-0.5 text-gray-600 focus:ring-gray-500"
                                >
                                <div class="ml-3">
                                    <span class="font-medium text-gray-700">❌ No tengo esta capacitación</span>
                                    <p class="text-xs text-gray-500 mt-1">Puedes postular igual, pero es probable que seas declarado NO APTO en esta evaluación.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="relative my-8">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t-2 border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white text-gray-500 font-medium">Capacitaciones Adicionales</span>
        </div>
    </div>

    <div class="mb-6 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-5">
        <div class="flex items-start mb-3">
            <div class="flex-shrink-0 mr-3 mt-0.5">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <div class="flex-1">
                <h5 class="font-semibold text-gray-900 mb-1">
                    Capacitaciones Adicionales <span class="text-sm font-normal text-gray-500">(Opcional)</span>
                </h5>
                <p class="text-sm text-gray-600">
                    Si tienes otras capacitaciones que consideras relevantes para el puesto y que no están listadas arriba, agrégalas aquí.
                </p>
            </div>
        </div>
    </div>

    <template x-for="(training, index) in formData.additionalTrainings" :key="index">
        <div class="border border-gray-300 rounded-xl p-6 mb-4 bg-white">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-bold text-gray-900">Capacitación <span x-text="index + 1"></span></h3>
                <button type="button"
                        @click="removeAdditionalTraining(index)"
                        class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nombre del Curso/Capacitación
                        <span class="text-red-500" x-show="training.institution || training.hours || training.certificationDate">*</span>
                    </label>
                    <input type="text"
                           x-model="training.courseName"
                           @input="autoSave"
                           placeholder="Ej: Gestión de Proyectos con MS Project"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Institución que dictó
                        <span class="text-red-500" x-show="training.courseName || training.hours || training.certificationDate">*</span>
                    </label>
                    <input type="text"
                           x-model="training.institution"
                           @input="autoSave"
                           placeholder="Ej: Universidad Nacional, SENATI, etc."
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Número de Horas
                        <span class="text-red-500" x-show="training.courseName || training.institution || training.certificationDate">*</span>
                    </label>
                    <input type="number"
                           x-model="training.hours"
                           @input="autoSave"
                           placeholder="Ej: 40"
                           min="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mes/Año de Certificación
                        <span class="text-red-500" x-show="training.courseName || training.institution || training.hours">*</span>
                    </label>
                    <input type="month"
                           x-model="training.certificationDate"
                           @input="autoSave"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div x-show="(training.courseName || training.institution || training.hours || training.certificationDate) &&
                          (!training.courseName || !training.institution || !training.hours || !training.certificationDate)"
                 class="mt-3 p-2 bg-yellow-50 border border-yellow-300 rounded text-xs text-yellow-800">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atención:</strong> Si completas algún campo, debes completar todos los campos de esta capacitación.
            </div>
        </div>
    </template>

    <button type="button"
            @click="addAdditionalTraining"
            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-colors">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Agregar otra capacitación adicional
    </button>
</div>

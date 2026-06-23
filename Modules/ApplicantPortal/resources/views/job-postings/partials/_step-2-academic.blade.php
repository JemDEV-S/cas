<div x-show="currentStep === 2" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Formación Académica</h2>
    <p class="text-gray-600 mb-6">Declara tu formación académica. Los documentos sustentatorios serán solicitados en la Fase 5.</p>

    <div class="alert bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
        <h5 class="font-bold text-blue-900 mb-3"><i class="fas fa-graduation-cap"></i> Requisitos Académicos del Puesto</h5>

        @if(!empty($jobProfile->education_levels))
            <div class="mb-3">
                <p class="text-sm text-blue-800 font-semibold mb-1">Nivel Educativo Requerido:</p>
                <p class="text-sm text-blue-700">
                    <strong>{{ \Modules\JobProfile\Enums\EducationLevelEnum::formatMultiple($jobProfile->education_levels) }}</strong>
                </p>
            </div>
        @elseif($jobProfile->education_level)
            <div class="mb-3">
                <p class="text-sm text-blue-800 font-semibold mb-1">Nivel Educativo Requerido:</p>
                <p class="text-sm text-blue-700">
                    <strong>{{ $jobProfile->education_level }}</strong>
                </p>
            </div>
        @endif

        @if(!empty($acceptedCareerNames))
            <div>
                <p class="text-sm text-blue-800 font-semibold mb-1">Carreras Profesionales Requeridas:</p>
                <p class="text-sm text-blue-700">
                    <strong>{{ implode(' • ', $acceptedCareerNames) }}</strong>
                </p>
                @if(count($acceptedCareerNames) > 1)
                    <small class="text-xs text-blue-600 mt-1 block">
                        ✓ Se aceptan carreras equivalentes según normativa vigente
                    </small>
                @endif
                <small class="text-xs text-blue-600 mt-1 block">
                    ℹ️ Si tu carrera es afín pero no aparece en la lista, puedes indicarlo marcando la opción correspondiente
                </small>
            </div>
        @elseif($jobProfile->career_field)
            <div>
                <p class="text-sm text-blue-800 font-semibold mb-1">Campo de Carrera:</p>
                <p class="text-sm text-blue-700">
                    <strong>{{ $jobProfile->career_field }}</strong>
                </p>
            </div>
        @endif
    </div>

    <template x-for="(academic, index) in formData.academics" :key="index">
        <div class="border border-gray-200 rounded-xl p-6 mb-4">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-bold text-gray-900">Título/Grado <span x-text="index + 1"></span></h3>
                <button type="button"
                        @click="removeAcademic(index)"
                        x-show="formData.academics.length > 1"
                        class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Grado Académico *
                        <button type="button"
                            class="ml-1 text-blue-500 hover:text-blue-700"
                            title="Información sobre niveles educativos"
                            @click="showEducationHelp = !showEducationHelp">
                            <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </label>

                    <div x-show="showEducationHelp"
                        x-cloak
                        class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                        <p class="font-semibold mb-1">💡 Guía de Niveles Educativos:</p>
                        <ul class="space-y-1 ml-3">
                            <li><strong>Egresado:</strong> Has completado los estudios pero no tienes título</li>
                            <li><strong>Bachiller:</strong> Grado académico universitario previo al título</li>
                            <li><strong>Título:</strong> Has obtenido el título profesional o técnico</li>
                            <li><strong>Postgrado:</strong> Maestría, Doctorado o Especialización</li>
                        </ul>
                        <p class="mt-2 text-blue-700">
                            <i class="fas fa-file-alt"></i> Los documentos sustentatorios serán solicitados en la Fase 5
                        </p>
                    </div>

                    <select x-model="academic.degreeType"
                            @change="checkEducationLevel(index); autoSave()"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione...</option>
                        @foreach($educationLevels as $level)
                            <option value="{{ $level['value'] }}"
                                data-level="{{ $level['level'] }}"
                                title="{{ $level['description'] }}">
                                {{ $level['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Institución Educativa *</label>
                    <input type="text"
                           x-model="academic.institution"
                           @input="autoSave"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Carrera Profesional
                        <span x-show="!academic.isRelatedCareer" class="text-red-500">*</span>
                    </label>
                    <select
                        :name="`academics[${index}][careerId]`"
                        x-model="academic.careerId"
                        @change="if (academic.careerId) { academic.isRelatedCareer = false; academic.relatedCareerName = ''; } checkCareerMatch(index); autoSave()"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                        :required="!academic.isRelatedCareer"
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
                                        @if(in_array($career->id, $acceptedCareerIds)) ✓ @endif
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    <div class="mt-3 p-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 transition-colors">
                        <label class="flex items-start cursor-pointer group">
                            <input
                                type="checkbox"
                                x-model="academic.isRelatedCareer"
                                @change="if (academic.isRelatedCareer) { academic.careerId = ''; } autoSave()"
                                class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="ml-3 flex-1">
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-600">
                                    ¿Tu carrera no está en la lista?
                                </span>
                                <span class="block text-xs text-gray-600 mt-1">
                                    Si tu carrera profesional es afín al puesto pero no aparece en el catálogo, márcala aquí e indica su nombre.
                                </span>
                            </span>
                        </label>

                        <div x-show="academic.isRelatedCareer"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            class="mt-3 pl-7">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre de tu Carrera *
                            </label>
                            <input
                                type="text"
                                x-model="academic.relatedCareerName"
                                @input="autoSave"
                                :required="academic.isRelatedCareer"
                                placeholder="Ej: Ingeniería de Software, Administración de Empresas, etc."
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                            >
                            <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded text-xs text-amber-700">
                                <i class="fas fa-info-circle"></i>
                                <strong>Nota:</strong> El comité evaluará si tu carrera es afín al puesto. Los documentos sustentatorios serán solicitados en la Fase 5.
                            </div>
                        </div>
                    </div>

                    <input type="hidden" x-model="academic.careerField">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Año de Graduación *</label>
                    <input type="number"
                           x-model="academic.year"
                           @input="autoSave"
                           required
                           min="1950"
                           :max="new Date().getFullYear()"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </template>

    <button type="button"
            @click="addAcademic"
            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-colors">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Agregar otro título/grado
    </button>
</div>

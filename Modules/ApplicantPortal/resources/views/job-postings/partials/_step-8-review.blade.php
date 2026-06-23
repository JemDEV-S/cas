<div x-show="currentStep === 8" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Revisión y Confirmación</h2>
    <p class="text-gray-600 mb-6">Revisa cuidadosamente toda la información antes de enviar tu postulación.</p>

    {{-- Semáforo de cumplimiento --}}
    <div class="mb-6 p-6 rounded-xl"
         :class="{
             'bg-green-50 border-2 border-green-500': complianceStatus === 'full',
             'bg-yellow-50 border-2 border-yellow-500': complianceStatus === 'partial',
             'bg-red-50 border-2 border-red-500': complianceStatus === 'none'
         }">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <svg x-show="complianceStatus === 'full'" class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <svg x-show="complianceStatus === 'partial'" class="w-12 h-12 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <svg x-show="complianceStatus === 'none'" class="w-12 h-12 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-2"
                    :class="{
                        'text-green-900': complianceStatus === 'full',
                        'text-yellow-900': complianceStatus === 'partial',
                        'text-red-900': complianceStatus === 'none'
                    }">
                    <span x-show="complianceStatus === 'full'">✓ Cumples todos los requisitos del perfil</span>
                    <span x-show="complianceStatus === 'partial'">⚠ Cumples parcialmente los requisitos</span>
                    <span x-show="complianceStatus === 'none'">✗ No cumples los requisitos mínimos</span>
                </h3>
                <p class="text-sm"
                   :class="{
                       'text-green-700': complianceStatus === 'full',
                       'text-yellow-700': complianceStatus === 'partial',
                       'text-red-700': complianceStatus === 'none'
                   }">
                    <span x-show="complianceStatus === 'full'">Excelente. Tu perfil se ajusta a los requisitos solicitados.</span>
                    <span x-show="complianceStatus === 'partial'">Puedes postular, pero hay requisitos que podrían no cumplirse completamente.</span>
                    <span x-show="complianceStatus === 'none'">Puedes postular de todos modos, pero es probable que seas declarado NO APTO en la evaluación automática.</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Resumen de errores de validación --}}
    <div x-show="getTotalErrorCount() > 0" class="mb-6">
        <div class="bg-red-50 border-2 border-red-300 rounded-xl p-6">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-900 mb-1">⚠️ Hay campos con errores o incompletos</h3>
                    <p class="text-sm text-red-700">
                        Se encontraron <strong x-text="getTotalErrorCount()"></strong> campo(s) que necesitan ser corregidos antes de enviar tu postulación.
                        Haz clic en "Ir al paso" para corregir cada error.
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                @foreach([1 => 'Datos Personales', 2 => 'Formación Académica', 3 => 'Experiencia Laboral', 4 => 'Capacitaciones', 6 => 'Registros Profesionales', 8 => 'Declaraciones'] as $stepNum => $stepName)
                    <template x-if="stepHasErrors({{ $stepNum }})">
                        <div class="bg-white border border-red-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-red-800 flex items-center">
                                    <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 text-xs flex items-center justify-center mr-2">{{ $stepNum }}</span>
                                    {{ $stepName }}
                                </h4>
                                @if($stepNum !== 8)
                                    <button type="button" @click="currentStep = {{ $stepNum }}" class="text-red-600 text-sm font-medium hover:underline">
                                        Ir al paso →
                                    </button>
                                @endif
                            </div>
                            <ul class="space-y-1">
                                <template x-for="error in validateStep({{ $stepNum }})" :key="error.field">
                                    <li class="flex items-start text-sm text-red-700">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><strong x-text="error.field"></strong>: <span x-text="error.message"></span></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                @endforeach
            </div>
        </div>
    </div>

    <div x-show="getTotalErrorCount() === 0" class="mb-6">
        <div class="bg-green-50 border-2 border-green-300 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">✓ Todos los campos obligatorios están completos y con formato válido</p>
            </div>
        </div>
    </div>

    {{-- Resumen de datos --}}
    <div class="space-y-4 mb-6">
        <div class="border-2 rounded-xl p-5 bg-white hover:shadow-md transition-shadow"
             :class="stepHasErrors(1) ? 'border-red-300' : 'border-gray-200'">
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" :class="stepHasErrors(1) ? 'text-red-600' : 'text-blue-600'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Datos Personales
                    <span x-show="stepHasErrors(1)" class="ml-2 text-xs text-red-600 font-normal">(<span x-text="validateStep(1).length"></span> error(es))</span>
                </h4>
                <button type="button" @click="currentStep = 1" class="text-blue-600 text-sm hover:underline">Editar</button>
            </div>
            <p class="text-sm text-gray-600" x-text="`${formData.personal.fullName} - DNI: ${formData.personal.dni}`"></p>
            <p class="text-xs text-gray-500 mt-1" x-text="`Email: ${formData.personal.email} | Tel: ${formData.personal.phone}`"></p>
        </div>

        <div class="border-2 rounded-xl p-5 bg-white hover:shadow-md transition-shadow"
             :class="stepHasErrors(2) ? 'border-red-300' : 'border-gray-200'">
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" :class="stepHasErrors(2) ? 'text-red-600' : 'text-blue-600'" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                    Formación Académica
                    <span x-show="stepHasErrors(2)" class="ml-2 text-xs text-red-600 font-normal">(<span x-text="validateStep(2).length"></span> error(es))</span>
                </h4>
                <button type="button" @click="currentStep = 2" class="text-blue-600 text-sm hover:underline">Editar</button>
            </div>
            <p class="text-sm text-gray-600" x-text="`${formData.academics.length} título(s)/grado(s) declarado(s)`"></p>
            <template x-if="formData.academics.some(a => a.isRelatedCareer)">
                <p class="text-xs text-amber-600 mt-1"><i class="fas fa-exclamation-triangle"></i> Incluye carrera(s) afín(es) sujeta(s) a evaluación</p>
            </template>
        </div>

        <div class="border-2 rounded-xl p-5 bg-white hover:shadow-md transition-shadow"
             :class="stepHasErrors(3) ? 'border-red-300' : 'border-gray-200'">
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" :class="stepHasErrors(3) ? 'text-red-600' : 'text-blue-600'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                    </svg>
                    Experiencia Laboral
                    <span x-show="stepHasErrors(3)" class="ml-2 text-xs text-red-600 font-normal">(<span x-text="validateStep(3).length"></span> error(es))</span>
                </h4>
                <button type="button" @click="currentStep = 3" class="text-blue-600 text-sm hover:underline">Editar</button>
            </div>
            <p class="text-sm text-gray-600" x-text="`${formData.experiences.length} experiencia(s) declarada(s)`"></p>
            <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                <div class="bg-gray-50 p-2 rounded">
                    <span class="font-semibold">General:</span>
                    <span x-text="calculateTotalExperience('general')"></span>
                </div>
                <div class="bg-gray-50 p-2 rounded">
                    <span class="font-semibold">Específica:</span>
                    <span x-text="calculateTotalExperience('specific')"></span>
                </div>
            </div>
        </div>

        <div class="border-2 rounded-xl p-5 bg-white hover:shadow-md transition-shadow"
             :class="stepHasErrors(4) ? 'border-red-300' : 'border-gray-200'">
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" :class="stepHasErrors(4) ? 'text-red-600' : 'text-blue-600'" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Capacitaciones
                    <span x-show="stepHasErrors(4)" class="ml-2 text-xs text-red-600 font-normal">(<span x-text="validateStep(4).length"></span> error(es))</span>
                </h4>
                <button type="button" @click="currentStep = 4" class="text-blue-600 text-sm hover:underline">Editar</button>
            </div>
            <div class="text-sm text-gray-600">
                <div class="flex items-center gap-2 mb-1">
                    <span>Requeridas:</span>
                    <span class="font-semibold"
                          :class="{
                              'text-green-600': formData.requiredCoursesCompliance.filter(c => c.status === 'exact').length === formData.requiredCoursesCompliance.length,
                              'text-amber-600': formData.requiredCoursesCompliance.filter(c => c.status === 'exact').length < formData.requiredCoursesCompliance.length && formData.requiredCoursesCompliance.filter(c => c.status === 'exact' || c.status === 'related').length > 0,
                              'text-red-600': formData.requiredCoursesCompliance.filter(c => c.status === 'exact' || c.status === 'related').length === 0
                          }"
                          x-text="`${formData.requiredCoursesCompliance.filter(c => c.status === 'exact' || c.status === 'related').length} de ${formData.requiredCoursesCompliance.length}`">
                    </span>
                </div>
                <p class="text-xs" x-text="`Adicionales: ${formData.additionalTrainings.length}`"></p>
                <template x-if="formData.requiredCoursesCompliance.filter(c => c.status === 'related').length > 0">
                    <p class="text-xs text-amber-600 mt-1">
                        <i class="fas fa-info-circle"></i> Incluye capacitación(es) afín(es)
                    </p>
                </template>
            </div>
        </div>
    </div>

    {{-- Declaración jurada --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
        <h4 class="font-bold text-gray-900 mb-4">Declaración Jurada</h4>

        <label class="flex items-start mb-4">
            <input type="checkbox"
                   x-model="formData.declarationAccepted"
                   required
                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="ml-3 text-sm text-gray-700">
                Declaro bajo juramento que toda la información proporcionada es verdadera y puede ser verificada mediante documentos sustentatorios en la siguiente fase del proceso. Soy consciente de que cualquier información falsa puede resultar en la descalificación inmediata del proceso de selección.

                Asimismo, si lo declarado no se ajusta a la verdad, me sujeto a lo establecido en el artículo 438 del Código Penal, así como a las responsabilidades administrativas, civiles y/o penales que correspondan, de conformidad con el marco legal vigente.
            </span>
        </label>

        <label class="flex items-start">
            <input type="checkbox"
                   x-model="formData.termsAccepted"
                   required
                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="ml-3 text-sm text-gray-700">
                Acepto los términos y condiciones del proceso de selección CAS y autorizo el tratamiento de mis datos personales conforme a la Ley N° 29733 - Ley de Protección de Datos Personales.
            </span>
        </label>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-sm text-blue-700">
            <strong>Recuerda:</strong> Esta ficha es solo un comprobante de inscripción. Los documentos sustentatorios serán solicitados en la Fase 5 (Presentación de CV documentado) únicamente a los postulantes declarados APTOS.
        </p>
    </div>
</div>

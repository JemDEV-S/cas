<div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
    <button type="button"
            @click="previousStep"
            x-show="currentStep > 1"
            class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Anterior
    </button>

    <div class="flex gap-3 ml-auto">
<button type="button"
                @click="nextStep"
                x-show="currentStep < 8"
                class="px-6 py-3 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all">
            Siguiente
            <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <button type="submit"
                x-show="currentStep === 8"
                :disabled="!formData.termsAccepted || !formData.declarationAccepted || getTotalErrorCount() > 0 || isSubmitting"
                class="px-6 py-3 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <template x-if="isSubmitting">
                <span class="flex items-center">
                    <svg class="animate-spin w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Enviando...
                </span>
            </template>
            <template x-if="!isSubmitting">
                <span>
                    <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span x-show="getTotalErrorCount() === 0">Enviar Postulación</span>
                    <span x-show="getTotalErrorCount() > 0">Corregir Errores (<span x-text="getTotalErrorCount()"></span>)</span>
                </span>
            </template>
        </button>
    </div>
</div>

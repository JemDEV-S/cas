<div x-show="showAutocompleteModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50" @click="rejectAutocomplete()"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 z-10"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-center justify-center w-14 h-14 bg-blue-100 rounded-full mx-auto mb-5">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">
            Tienes una postulación anterior
        </h3>

        <p class="text-gray-600 text-center text-sm mb-2">
            Encontramos datos de tu postulación
            <span class="font-semibold text-gray-800" x-text="'#' + (window.wizardConfig.previousApplicationData?.applicationCode ?? '')"></span>.
        </p>
        <p class="text-gray-600 text-center text-sm mb-6">
            ¿Deseas autocompletar este formulario con esa información?
            Podrás revisar y editar cada sección antes de enviar.
        </p>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-6 text-xs text-yellow-800">
            <strong>Nota:</strong> Los datos específicos del perfil (cursos requeridos y áreas de conocimiento)
            no se autocompletarán, ya que son distintos por convocatoria.
        </div>

        <div class="flex flex-col gap-3">
            <button type="button"
                    @click="acceptAutocomplete()"
                    class="w-full px-6 py-3 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Sí, autocompletar con mis datos anteriores
            </button>

            <button type="button"
                    @click="rejectAutocomplete()"
                    class="w-full px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all">
                No, empezar desde cero
            </button>
        </div>
    </div>
</div>

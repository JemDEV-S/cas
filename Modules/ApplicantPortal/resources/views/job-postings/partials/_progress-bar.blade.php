<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Paso <span x-text="currentStep"></span> de 8</h3>
            <p class="text-sm text-gray-600" x-text="stepTitle"></p>
        </div>
        <div class="text-sm text-gray-500" x-show="lastSaved">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Guardado automático: <span x-text="lastSaved"></span>
            </span>
        </div>
    </div>

    <div class="relative">
        <div class="overflow-hidden h-2 text-xs flex rounded-full bg-gray-200">
            <div class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center gradient-municipal transition-all duration-500"
                 :style="`width: ${(currentStep / 8) * 100}%`"></div>
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <template x-for="step in 8" :key="step">
            <div class="flex flex-col items-center cursor-pointer" @click="currentStep = step">
                <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all relative"
                     :class="{
                         'bg-gradient-to-r from-blue-500 to-blue-600 text-white active': currentStep === step && !stepHasErrors(step),
                         'bg-gradient-to-r from-red-500 to-red-600 text-white': currentStep === step && stepHasErrors(step),
                         'completed text-white': currentStep > step && !stepHasErrors(step),
                         'bg-red-500 text-white': currentStep > step && stepHasErrors(step),
                         'bg-gray-200 text-gray-500': currentStep < step && !stepHasErrors(step),
                         'bg-red-200 text-red-700': currentStep < step && stepHasErrors(step)
                     }">
                    <span x-show="!stepHasErrors(step)" x-text="step"></span>
                    <span x-show="stepHasErrors(step)" class="text-xs">!</span>
                    <span x-show="stepHasErrors(step)"
                          class="absolute -top-1 -right-1 w-4 h-4 bg-red-600 rounded-full text-white text-xs flex items-center justify-center border-2 border-white">
                        <span x-text="validateStep(step).length"></span>
                    </span>
                </div>
                <div class="text-xs mt-1 hidden md:block"
                     :class="stepHasErrors(step) ? 'text-red-600 font-semibold' : 'text-gray-500'"
                     x-text="getStepName(step)"></div>
            </div>
        </template>
    </div>
</div>

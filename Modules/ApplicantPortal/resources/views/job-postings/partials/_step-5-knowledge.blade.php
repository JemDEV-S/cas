<div x-show="currentStep === 5" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Conocimientos Técnicos</h2>
    <p class="text-gray-600 mb-6">Marca los conocimientos técnicos que posees.</p>

    @if($jobProfile->knowledge_areas && count($jobProfile->knowledge_areas) > 0)
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
            <h5 class="font-bold text-blue-900 mb-3"><i class="fas fa-laptop-code"></i> Conocimientos Técnicos Requeridos</h5>
            <div class="space-y-3">
                @foreach($jobProfile->knowledge_areas as $index => $area)
                    <div class="bg-white p-4 rounded-lg border border-blue-200">
                        <label class="flex items-start cursor-pointer">
                            <input
                                type="checkbox"
                                x-model="formData.knowledgeCompliance[{{ $index }}].hasIt"
                                @change="autoSave"
                                class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="ml-3 font-semibold text-gray-900">{{ $area }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
            <p class="text-gray-600">No se especificaron conocimientos técnicos para este perfil.</p>
        </div>
    @endif

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Otros Conocimientos (opcional)</label>
        <textarea x-model="formData.otherKnowledge"
                  @input="autoSave"
                  rows="4"
                  maxlength="255"
                  placeholder="Describe otros conocimientos relevantes que posees..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
    </div>
</div>

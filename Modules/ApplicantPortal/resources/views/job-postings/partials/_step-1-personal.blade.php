<div x-show="currentStep === 1" class="fade-in">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Datos Personales</h2>

    @if(empty($user->birth_date) || empty($user->address) || empty($user->phone))
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>¡Atención!</strong> Algunos de tus datos personales están incompletos.
                    Debes actualizar tu perfil de usuario antes de poder postular. Campos faltantes:
                    @if(empty($user->birth_date)) Fecha de Nacimiento @endif
                    @if(empty($user->address)){{ empty($user->birth_date) ? ', ' : '' }}Dirección @endif
                    @if(empty($user->phone)){{ (empty($user->birth_date) || empty($user->address)) ? ', ' : '' }}Teléfono @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
        <p class="text-sm text-blue-700">
            <strong>Información:</strong> Los datos personales se cargan automáticamente de tu perfil de usuario. Los campos bloqueados no pueden ser modificados en este formulario.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo *</label>
            <input type="text"
                   x-model="formData.personal.fullName"
                   readonly
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
            <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">DNI *</label>
            <input type="text"
                   x-model="formData.personal.dni"
                   required
                   maxlength="8"
                   pattern="[0-9]{8}"
                   readonly
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
            <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Nacimiento *</label>
            <input type="date"
                   x-model="formData.personal.birthDate"
                   readonly
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
            <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección Completa *</label>
            <input type="text"
                   x-model="formData.personal.address"
                   readonly
                   required
                   placeholder="Ej: Av. Los Incas 123, San Jerónimo, Cusco"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
            <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono / Celular *</label>
            <input type="tel"
                   x-model="formData.personal.phone"
                   readonly
                   required
                   placeholder="Ej: 987654321 o 084-123456"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
            <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
            <input type="email"
                   x-model="formData.personal.email"
                   required
                   readonly
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
            <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
        </div>
    </div>
</div>

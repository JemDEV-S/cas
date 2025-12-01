@extends('layouts.app')

@section('title', 'Nueva Asignación')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Nueva Asignación Organizacional</h1>
                    <p class="mt-1 text-sm text-gray-600">Asignar usuario a una unidad organizacional</p>
                </div>
                <a href="{{ route('assignments.index') }}">
                    <x-button variant="secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </x-button>
                </a>
            </div>
        </div>

        <!-- Alertas de errores -->
        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-red-800">Por favor corrija los siguientes errores:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('assignments.store') }}">
            @csrf

            <x-card>
                <div class="p-6">
                    
                    <!-- COMPONENTE DE BÚSQUEDA DE USUARIO -->
                    <div class="mb-6" x-data="userSearch()">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Usuario <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Input oculto para enviar el ID real -->
                        <input type="hidden" name="user_id" x-model="selectedUserId" required>

                        <div class="relative">
                            <!-- Input visual de búsqueda -->
                            <div class="relative">
                                <input type="text" 
                                       x-model="searchQuery"
                                       @input.debounce.300ms="searchUsers()"
                                       @focus="open = true"
                                       @click.away="open = false"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10"
                                       placeholder="Buscar por nombre o DNI..."
                                       :class="{'border-red-300': {{ $errors->has('user_id') ? 'true' : 'false' }}}">
                                
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>

                                <!-- Botón para limpiar selección -->
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" 
                                     x-show="selectedUserId" 
                                     @click="clearSelection()">
                                    <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Dropdown de resultados -->
                            <div x-show="open && (users.length > 0 || isLoading)" 
                                 class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                 style="display: none;">
                                
                                <!-- Loading state -->
                                <div x-show="isLoading" class="px-4 py-2 text-sm text-gray-500">
                                    Buscando...
                                </div>

                                <!-- Lista de usuarios -->
                                <template x-for="user in users" :key="user.id">
                                    <div @click="selectUser(user)" 
                                         class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 transition-colors">
                                        <div class="flex items-center">
                                            <span class="font-medium block truncate" x-text="user.first_name + ' ' + user.last_name"></span>
                                            <span class="ml-2 text-gray-500 text-xs" x-text="'DNI: ' + user.dni"></span>
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- No results -->
                                <div x-show="!isLoading && users.length === 0 && searchQuery.length > 2" class="px-4 py-2 text-sm text-gray-500">
                                    No se encontraron usuarios.
                                </div>
                            </div>
                        </div>

                        <!-- Feedback visual de usuario seleccionado -->
                        <div x-show="selectedUserId" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Usuario seleccionado: <span class="font-bold ml-1" x-text="selectedUserName"></span>
                        </div>

                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Busque y seleccione el usuario que será asignado</p>
                    </div>

                    <!-- Unidad Organizacional (Se mantiene select normal si son pocas, o aplica la misma lógica) -->
                    <div class="mb-6">
                        <label for="organization_unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Unidad Organizacional <span class="text-red-500">*</span>
                        </label>
                        <select name="organization_unit_id" id="organization_unit_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('organization_unit_id') border-red-300 @enderror"
                                required>
                            <option value="">Seleccione una unidad</option>
                            @foreach($organizationalUnits as $unit)
                                <option value="{{ $unit->id }}" {{ old('organization_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('organization_unit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('start_date') border-red-300 @enderror"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Fin (Opcional)
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('end_date') border-red-300 @enderror"
                                   value="{{ old('end_date') }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tipo de asignación -->
                    <div class="mb-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="is_primary" id="is_primary"
                                       value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       {{ old('is_primary') ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3">
                                <label for="is_primary" class="font-medium text-gray-700">Asignación Principal</label>
                                <p class="text-sm text-gray-500">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marque esta opción si esta será la unidad principal del usuario.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-blue-800 mb-2">Información</h3>
                                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                    <li>El usuario será notificado por correo electrónico</li>
                                    <li>Las fechas pueden modificarse posteriormente</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between rounded-b-lg">
                    <a href="{{ route('assignments.index') }}">
                        <x-button type="button" variant="secondary">Cancelar</x-button>
                    </a>
                    <x-button type="submit" variant="success">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardar Asignación
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Lógica para la búsqueda de usuarios con Alpine.js
    function userSearch() {
        return {
            searchQuery: '',
            users: [],
            selectedUserId: '{{ old('user_id') }}', // Recuperar valor old si existe
            selectedUserName: '',
            open: false,
            isLoading: false,

            // Inicialización (si hay un error de validación, intentamos mostrar el nombre del usuario seleccionado previamente)
            init() {
                if (this.selectedUserId) {
                    // Aquí podrías hacer un fetch para obtener el nombre del usuario si solo tienes el ID
                    // O pasarlo desde el controlador si es posible. Por ahora simulamos visualmente.
                    this.selectedUserName = 'Usuario seleccionado previamente'; 
                }
            },

            searchUsers() {
                if (this.searchQuery.length < 2) {
                    this.users = [];
                    return;
                }

                this.isLoading = true;
                this.open = true;

                fetch(`{{ route('users.search') }}?query=${this.searchQuery}`)
                    .then(response => response.json())
                    .then(data => {
                        this.users = data;
                        this.isLoading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.isLoading = false;
                    });
            },

            selectUser(user) {
                this.selectedUserId = user.id;
                this.selectedUserName = `${user.first_name} ${user.last_name} (${user.dni})`;
                this.searchQuery = this.selectedUserName; // Mostrar nombre en el input
                this.open = false;
            },

            clearSelection() {
                this.selectedUserId = '';
                this.selectedUserName = '';
                this.searchQuery = '';
                this.users = [];
            }
        }
    }

    // Lógica existente de fechas
    document.addEventListener('DOMContentLoaded', function() {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        endDate.addEventListener('change', function() {
            if (this.value && startDate.value) {
                if (new Date(this.value) <= new Date(startDate.value)) {
                    alert('La fecha de fin debe ser posterior a la fecha de inicio');
                    this.value = '';
                }
            }
        });

        startDate.addEventListener('change', function() {
            if (this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                endDate.min = nextDay.toISOString().split('T')[0];
                if (endDate.value && new Date(endDate.value) <= new Date(this.value)) {
                    endDate.value = '';
                }
            }
        });
    });
</script>
@endpush
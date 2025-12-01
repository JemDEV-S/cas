@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="min-h-screen bg-gray-50/50 pb-8">
    <!-- Header con degradado sutil o fondo limpio -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Usuarios</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Administra el acceso y los roles de los miembros del sistema CAS.
                </p>
            </div>
            @can('user.create.user')
            <a href="{{ route('users.create') }}" 
               class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuevo Usuario
            </a>
            @endcan
        </div>
    </div>

    <!-- Panel de Filtros -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Búsqueda con Icono -->
                <div class="md:col-span-5">
                    <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        Búsqueda General
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar por DNI, nombre o correo..."
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                        >
                    </div>
                </div>

                <!-- Filtro de Rol -->
                <div class="md:col-span-3">
                    <label for="role" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        Rol
                    </label>
                    <select name="role" id="role" class="block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg bg-gray-50">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro de Estado -->
                <div class="md:col-span-2">
                    <label for="status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                        Estado
                    </label>
                    <select name="status" id="status" class="block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg bg-gray-50">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>

                <!-- Botones de Acción -->
                <div class="md:col-span-2 flex space-x-2">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        Filtrar
                    </button>
                    <a href="{{ route('users.index') }}" class="inline-flex justify-center items-center px-3 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" title="Limpiar filtros">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de Resultados -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            DNI
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Roles
                        </th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <!-- Columna Usuario (Foto + Nombre + Email) -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($user->photo_url)
                                        <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="{{ $user->photo_url }}" alt="{{ $user->first_name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                            {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Columna DNI -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded">
                                {{ $user->dni }}
                            </span>
                        </td>

                        <!-- Columna Roles -->
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <!-- Columna Estado -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                    <span class="w-1.5 h-1.5 mr-1.5 bg-green-600 rounded-full"></span>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">
                                    <span class="w-1.5 h-1.5 mr-1.5 bg-red-600 rounded-full"></span>
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        <!-- Columna Acciones -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                @can('user.view.user')
                                <a href="{{ route('users.show', $user) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Ver detalle">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @endcan

                                @can('user.update.user')
                                <a href="{{ route('users.edit', $user) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan

                                @can('user.delete.user')
                                    @if(auth()->id() != $user->id)
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">No se encontraron usuarios</h3>
                                <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros de búsqueda.</p>
                                <a href="{{ route('users.index') }}" class="mt-4 text-blue-600 hover:text-blue-500 font-medium">
                                    Limpiar filtros
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        @if($users->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
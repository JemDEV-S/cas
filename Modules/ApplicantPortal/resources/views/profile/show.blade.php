@extends('applicantportal::components.layouts.master')

@section('title', 'Mi Perfil')

@section('content')

{{-- Encabezado --}}
<div class="mb-8">
    <div class="gradient-municipal rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-6 py-8 sm:px-12 sm:py-10">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <div class="w-24 h-24 gradient-municipal rounded-2xl flex items-center justify-center text-white text-4xl font-bold shadow-lg ring-4 ring-white/30 flex-shrink-0">
                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                </div>
                <div class="text-white text-center sm:text-left">
                    <h1 class="text-3xl font-bold mb-1">{{ $user->getFullNameAttribute() }}</h1>
                    <p class="text-white/80 text-sm mb-1">{{ $user->email }}</p>
                    @if($user->dni)
                        <p class="text-white/70 text-sm">DNI: {{ $user->dni }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Columna principal: datos personales --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Información personal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 gradient-municipal-soft flex items-center gap-3">
                <div class="w-10 h-10 gradient-municipal rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Información Personal</h2>
            </div>

            <form action="{{ route('applicant.profile.update') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombres <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition @error('first_name') border-red-400 @enderror">
                        @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Apellidos <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition @error('last_name') border-red-400 @enderror">
                        @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition @error('phone') border-red-400 @enderror"
                            placeholder="Ej: 987654321">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Fecha de nacimiento</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '') }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition @error('birth_date') border-red-400 @enderror">
                        @error('birth_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Género</label>
                        <select name="gender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition @error('gender') border-red-400 @enderror">
                            <option value="">Seleccionar</option>
                            <option value="MASCULINO" @selected(old('gender', $user->gender) === 'MASCULINO')>Masculino</option>
                            <option value="FEMENINO"  @selected(old('gender', $user->gender) === 'FEMENINO')>Femenino</option>
                        </select>
                        @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dirección</label>
                        <input type="text" name="address" value="{{ old('address', $user->address) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition @error('address') border-red-400 @enderror"
                            placeholder="Av. / Calle / Jr.">
                        @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Distrito</label>
                        <input type="text" name="district" value="{{ old('district', $user->district) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Provincia</label>
                        <input type="text" name="province" value="{{ old('province', $user->province) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Departamento</label>
                        <input type="text" name="department" value="{{ old('department', $user->department) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-municipal-blue focus:border-transparent transition">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 gradient-municipal text-white text-sm font-bold rounded-xl hover:shadow-lg transition-all duration-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Columna derecha: datos no editables + cambio de contraseña --}}
    <div class="space-y-6">

        {{-- Datos de cuenta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 gradient-municipal-soft flex items-center gap-3">
                <div class="w-10 h-10 gradient-municipal rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Datos de Cuenta</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">DNI</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $user->dni ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Correo electrónico</p>
                    <p class="text-sm font-semibold text-gray-800 break-all">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Miembro desde</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Cambiar contraseña --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 gradient-municipal-soft flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Cambiar Contraseña</h2>
            </div>

            <form action="{{ route('applicant.profile.update-password') }}" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Contraseña actual</label>
                    <input type="password" name="current_password"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition @error('current_password') border-red-400 @enderror">
                    @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva contraseña</label>
                    <input type="password" name="password"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition @error('password') border-red-400 @enderror">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition">
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-sm font-bold rounded-xl hover:shadow-lg transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Actualizar contraseña
                </button>
            </form>
        </div>

    </div>
</div>

@endsection

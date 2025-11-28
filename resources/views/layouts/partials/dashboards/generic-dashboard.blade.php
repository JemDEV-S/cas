{{-- Dashboard Genérico para roles no específicos --}}
<div class="space-y-6">

    {{-- Mensaje de Bienvenida --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">¡Bienvenido al Sistema CAS!</h2>
                <p class="text-indigo-100">
                    Sistema de Gestión de Convocatorias y Contrataciones CAS
                </p>
            </div>
            <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Información de Usuario --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </span>
            Mi Información
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                        <span class="text-white font-bold text-2xl">
                            {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                        </span>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Nombre Completo</p>
                    <p class="text-lg font-bold text-gray-900">{{ auth()->user()->full_name }}</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Correo Electrónico</p>
                    <p class="text-lg font-bold text-gray-900">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Rol Asignado</p>
                    <p class="text-lg font-bold text-gray-900">{{ auth()->user()->roles->first()?->name ?? 'Sin rol' }}</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl border border-amber-200">
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Último Acceso</p>
                    <p class="text-lg font-bold text-gray-900">
                        {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Primera vez' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Accesos Disponibles --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            Acciones Disponibles
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('profile.show') }}"
               class="group flex items-center p-5 border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Ver Mi Perfil</h4>
                    <p class="text-sm text-gray-500">Información personal</p>
                </div>
            </a>

            <a href="{{ route('profile.edit') }}"
               class="group flex items-center p-5 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-gray-900 group-hover:text-green-600 transition-colors">Editar Perfil</h4>
                    <p class="text-sm text-gray-500">Actualizar datos</p>
                </div>
            </a>

            <a href="{{ route('profile.preferences') }}"
               class="group flex items-center p-5 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Preferencias</h4>
                    <p class="text-sm text-gray-500">Configuración</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Mensaje Informativo --}}
    <div class="bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl p-6 border-2 border-blue-300">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-blue-900 mb-2">Sistema de Gestión CAS</h3>
                <p class="text-sm text-blue-800">
                    Bienvenido al sistema de gestión de convocatorias. Este panel se adaptará según los permisos asignados a tu cuenta.
                    Para acceder a funcionalidades específicas, contacta al administrador del sistema.
                </p>
            </div>
        </div>
    </div>

    {{-- Guía Rápida --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </span>
            Guía de Uso
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                <div class="flex items-start">
                    <span class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-lg font-bold mr-3">1</span>
                    <div>
                        <h4 class="font-bold text-blue-900 mb-1">Completa tu perfil</h4>
                        <p class="text-sm text-blue-800">Asegúrate de tener toda tu información actualizada</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-xl border border-green-200">
                <div class="flex items-start">
                    <span class="flex items-center justify-center w-8 h-8 bg-green-600 text-white rounded-lg font-bold mr-3">2</span>
                    <div>
                        <h4 class="font-bold text-green-900 mb-1">Explora el sistema</h4>
                        <p class="text-sm text-green-800">Utiliza el menú de navegación para acceder a las diferentes secciones</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                <div class="flex items-start">
                    <span class="flex items-center justify-center w-8 h-8 bg-purple-600 text-white rounded-lg font-bold mr-3">3</span>
                    <div>
                        <h4 class="font-bold text-purple-900 mb-1">Configura preferencias</h4>
                        <p class="text-sm text-purple-800">Personaliza tu experiencia en la sección de preferencias</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gradient-to-r from-amber-50 to-amber-100 rounded-xl border border-amber-200">
                <div class="flex items-start">
                    <span class="flex items-center justify-center w-8 h-8 bg-amber-600 text-white rounded-lg font-bold mr-3">4</span>
                    <div>
                        <h4 class="font-bold text-amber-900 mb-1">Solicita ayuda</h4>
                        <p class="text-sm text-amber-800">Si tienes dudas, contacta al administrador del sistema</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

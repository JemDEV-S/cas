<!-- NAVIGATION COMPONENT (navigation.blade.php) -->
<nav class="bg-white/80 backdrop-blur-md border-b border-gray-200/50 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="#" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-700 bg-clip-text text-transparent">
                            Portal CAS
                        </span>
                    </a>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:ml-10 md:flex md:space-x-2">
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 transition-all duration-200">
                        Inicio
                    </a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                        Mis Postulaciones
                    </a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                        Convocatorias
                    </a>
                    <a href="#" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200">
                        Mi Perfil
                    </a>
                </div>
            </div>
            
            <!-- Right Side -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <button class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                
                <!-- User Menu -->
                <div class="relative flex items-center space-x-3 bg-gray-50 rounded-lg px-3 py-2 hover:bg-gray-100 transition-all duration-200 cursor-pointer">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-gray-700">
                        {{ auth()->user()->name ?? 'Usuario' }}
                    </span>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</nav>
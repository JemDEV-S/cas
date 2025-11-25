@props(['class' => ''])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="logo-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#1D4ED8;stop-opacity:1" />
        </linearGradient>
    </defs>

    <!-- Círculo exterior -->
    <circle cx="158" cy="158" r="150" fill="url(#logo-gradient)" opacity="0.1"/>

    <!-- Ícono de documento/convocatoria -->
    <g transform="translate(58, 58)">
        <!-- Documento principal -->
        <rect x="40" y="20" width="120" height="160" rx="8" fill="url(#logo-gradient)"/>
        <rect x="40" y="20" width="120" height="160" rx="8" fill="none" stroke="#1E40AF" stroke-width="2"/>

        <!-- Líneas de texto -->
        <line x1="60" y1="50" x2="140" y2="50" stroke="white" stroke-width="3" stroke-linecap="round"/>
        <line x1="60" y1="70" x2="140" y2="70" stroke="white" stroke-width="3" stroke-linecap="round"/>
        <line x1="60" y1="90" x2="120" y2="90" stroke="white" stroke-width="3" stroke-linecap="round"/>

        <!-- Ícono de usuario/persona -->
        <circle cx="100" cy="120" r="12" fill="white"/>
        <path d="M 85 150 Q 85 135, 100 135 Q 115 135, 115 150 L 115 155 L 85 155 Z" fill="white"/>

        <!-- Marca de verificación -->
        <circle cx="140" cy="35" r="18" fill="#10B981"/>
        <path d="M 133 35 L 138 40 L 147 31" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
</svg>

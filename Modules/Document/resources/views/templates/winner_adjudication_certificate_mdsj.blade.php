<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Constancia de Adjudicación' }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica Neue', sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #333;
            margin: 8mm 6mm 10mm 6mm;
        }

        /* Header institucional */
        .header {
            text-align: center;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid #1e3a5f;
        }

        .institution-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 4px;
        }

        .institution-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .institution-subtitle {
            font-size: 7pt;
            color: #4a5568;
        }

        /* Titulo del documento */
        .document-header {
            background-color: #1e3a5f;
            color: white;
            padding: 6px 12px;
            margin: 8px 0;
            text-align: center;
        }

        .document-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .document-subtitle {
            font-size: 7pt;
            opacity: 0.9;
            margin-top: 2px;
        }

        /* Informacion del proceso */
        .process-info {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #1e3a5f;
            padding: 5px 8px;
            margin: 6px 0;
            font-size: 6pt;
        }

        .info-row {
            display: inline-block;
            margin-right: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #4a5568;
        }

        .info-value {
            color: #2d3748;
        }

        /* Estadisticas resumidas */
        .stats-summary {
            text-align: center;
            background-color: #edf2f7;
            padding: 6px;
            margin: 6px 0;
            border-radius: 2px;
        }

        .stats-summary .stat-item {
            display: inline-block;
            margin: 0 15px;
            font-size: 7pt;
        }

        .stats-summary .stat-number {
            font-size: 14pt;
            font-weight: bold;
            color: #1e3a5f;
        }

        .stats-summary .stat-label {
            font-size: 6pt;
            color: #4a5568;
            text-transform: uppercase;
        }

        /* Unidad Organizacional */
        .unit-section {
            margin: 5px 0;
            page-break-inside: avoid;
        }

        .unit-header {
            background-color: #1e3a5f;
            color: white;
            padding: 4px 8px;
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        /* Perfil de Puesto */
        .profile-section {
            margin: 3px 0 5px 0;
            page-break-inside: avoid;
        }

        .profile-header {
            background-color: #e2e8f0;
            padding: 3px 6px;
            font-size: 7pt;
            margin-bottom: 2px;
            border-left: 3px solid #2b6cb0;
        }

        .profile-code {
            font-weight: bold;
            color: #2b6cb0;
        }

        .profile-title {
            color: #2d3748;
        }

        .profile-meta {
            font-size: 6pt;
            color: #718096;
            margin-top: 1px;
        }

        /* Tabla de postulantes */
        table.applicants {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 7pt;
        }

        table.applicants thead {
            background-color: #2d3748;
            color: white;
        }

        table.applicants th {
            padding: 4px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            border: 1px solid #1a202c;
        }

        table.applicants td {
            padding: 6px 3px;
            border: 1px solid #cbd5e0;
            vertical-align: middle;
        }

        table.applicants tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }

        /* Resultado badges */
        .result-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 6pt;
            text-transform: uppercase;
        }

        .result-badge.ganador {
            background-color: #faf089;
            color: #744210;
            border: 1px solid #d69e2e;
        }

        .result-badge.accesitario {
            background-color: #fbd38d;
            color: #7b341e;
            border: 1px solid #ed8936;
        }

        /* Area de firma */
        .signature-area {
            border-top: 1px solid #cbd5e0;
            padding-top: 5px;
            min-height: 40px;
            position: relative;
        }

        .signature-label {
            font-size: 5pt;
            color: #718096;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 100%;
            margin-top: 25px;
        }

        /* Contador de fila */
        .row-number {
            font-weight: bold;
            color: #2d3748;
            font-size: 7pt;
        }

        /* Notas al pie */
        .notes-section {
            background-color: #fffbeb;
            border: 1px solid #f6e05e;
            border-left: 3px solid #d69e2e;
            padding: 5px 8px;
            margin: 8px 0;
            font-size: 6pt;
        }

        .notes-title {
            font-weight: bold;
            color: #744210;
            margin-bottom: 3px;
            font-size: 7pt;
        }

        .notes-content {
            color: #744210;
        }

        .notes-content ul {
            margin-left: 10px;
            margin-top: 2px;
        }

        .notes-content li {
            margin-bottom: 2px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 3mm;
            left: 6mm;
            right: 6mm;
            text-align: center;
            font-size: 5pt;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
            background-color: white;
        }

        .page-break {
            page-break-after: always;
        }

        /* Resaltar ganadores */
        tr.ganador-row {
            background-color: #fefcbf !important;
            font-weight: 500;
        }

        tr.accesitario-row {
            background-color: #fef3c7 !important;
        }
    </style>
</head>
<body>
    {{-- Header institucional --}}
    <div class="header">
        <div class="institution-name">MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO</div>
        <div class="institution-subtitle">Provincia de Cusco - Región Cusco | Oficina de Recursos Humanos</div>
    </div>

    {{-- Titulo del documento --}}
    <div class="document-header">
        <div class="document-title">{{ $title ?? 'CONSTANCIA DE ADJUDICACIÓN' }}</div>
        <div class="document-subtitle">{{ $subtitle ?? 'Proceso de Selección CAS' }}</div>
    </div>

    {{-- Contenido por Unidades Organizacionales --}}
    @foreach($units as $unitIndex => $unit)
    <div class="unit-section">
        <div class="unit-header">
            {{ $unit['name'] }}
        </div>

        @foreach($unit['profiles'] as $profile)
        <div class="profile-section">
            <div class="profile-header">
                <div>
                    <span class="profile-code">{{ $profile['code'] }}</span> -
                    <span class="profile-title">{{ $profile['title'] }}</span>
                </div>
                <div class="profile-meta">
                    Cargo: {{ $profile['position_code'] }} - {{ $profile['position_name'] }} |
                    Vacantes: {{ $profile['vacancies'] }} |
                    Ganadores: {{ $profile['stats']['winners'] }} |
                    Accesitarios: {{ $profile['stats']['accesitarios'] }}
                </div>
            </div>

            @if(count($profile['applications']) > 0)
            <table class="applicants">
                <thead>
                    <tr>
                        <th style="width: 4%;">N°</th>
                        <th style="width: 8%;">DNI</th>
                        <th style="width: 40%;" class="text-left">Apellidos y Nombres</th>
                        <th style="width: 12%;">Resultado</th>
                        <th style="width: 36%;">Firma del Postulante</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profile['applications'] as $index => $application)
                    @php
                        $isGanador = $application->selection_result === 'GANADOR';
                        $isAccesitario = $application->selection_result === 'ACCESITARIO';
                    @endphp
                    <tr class="{{ $isGanador ? 'ganador-row' : ($isAccesitario ? 'accesitario-row' : '') }}">
                        <td class="text-center">
                            <span class="row-number">{{ $index + 1 }}</span>
                        </td>
                        <td class="text-center">{{ $application->dni }}</td>
                        <td class="text-left" style="font-weight: 500;">
                            {{ strtoupper($application->full_name) }}
                        </td>
                        <td class="text-center">
                            @if($isGanador)
                                <span class="result-badge ganador">GANADOR</span>
                            @elseif($isAccesitario)
                                <span class="result-badge accesitario">
                                    ACCESITARIO
                                    @if($application->accesitario_order)
                                        #{{ $application->accesitario_order }}
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="signature-area">
                                <div class="signature-line"></div>
                                <div class="signature-label">Firma</div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach

    {{-- Notas importantes --}}
    <div class="notes-section">
        <div class="notes-title">NOTAS IMPORTANTES:</div>
        <div class="notes-content">
            <ul>
                <li><strong>GANADOR:</strong> Postulante adjudicado a una vacante del proceso CAS {{ $posting->code ?? '' }}.</li>
                <li><strong>ACCESITARIO:</strong> Postulante en lista de espera ordenado por mérito, con posibilidad de cubrir una vacante en caso de renuncia o desistimiento de un ganador.</li>
                <li>La presente constancia es válida para efectos del proceso de contratación y tiene carácter de declaración jurada.</li>
                <li>Los postulantes adjudicados deberán presentarse en la Oficina de Recursos Humanos para iniciar el proceso de contratación según cronograma establecido.</li>
                <li>La firma en el presente documento confirma la aceptación del resultado y el compromiso de asumir las funciones del cargo adjudicado.</li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Municipalidad Distrital de San Jerónimo - Sistema CAS | Constancia de Adjudicación Oficial | Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>

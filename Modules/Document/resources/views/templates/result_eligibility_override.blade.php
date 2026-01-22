<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Resultado de Reevaluacion de Elegibilidad' }}</title>
    <style>
        @page {
            margin: 10mm 8mm 12mm 8mm;
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
            line-height: 1.2;
            color: #333;
        }

        /* Header institucional */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1e3a5f;
        }

        .institution-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
        }

        .institution-subtitle {
            font-size: 8pt;
            color: #4a5568;
        }

        /* Titulo del documento */
        .document-header {
            background-color: #92400e;
            color: white;
            padding: 8px 15px;
            margin: 8px 0;
            text-align: center;
        }

        .document-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .document-subtitle {
            font-size: 8pt;
            opacity: 0.9;
            margin-top: 2px;
        }

        /* Informacion del proceso */
        .process-info {
            background-color: #fffbeb;
            border: 1px solid #fcd34d;
            border-left: 3px solid #92400e;
            padding: 6px 10px;
            margin: 8px 0;
            font-size: 7pt;
        }

        .info-row {
            display: inline-block;
            margin-right: 25px;
        }

        .info-label {
            font-weight: bold;
            color: #4a5568;
        }

        .info-value {
            color: #2d3748;
        }

        /* Estadisticas en linea */
        .statistics {
            margin: 10px 0;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-table td {
            padding: 8px 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .stat-number {
            font-size: 18pt;
            font-weight: bold;
            line-height: 1;
        }

        .stat-total .stat-number { color: #92400e; }
        .stat-approved .stat-number { color: #166534; }
        .stat-rejected .stat-number { color: #991b1b; }

        .stat-label {
            font-size: 7pt;
            color: #4a5568;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .stat-percentage {
            font-size: 6pt;
            color: #718096;
        }

        /* Unidad Organizacional */
        .unit-section {
            margin: 10px 0;
            page-break-inside: avoid;
        }

        .unit-header {
            background-color: #78350f;
            color: white;
            padding: 6px 10px;
            font-size: 9pt;
            font-weight: bold;
        }

        .unit-stats {
            float: right;
            font-size: 7pt;
            font-weight: normal;
        }

        /* Perfil de Puesto */
        .profile-section {
            margin: 6px 0 6px 8px;
            border-left: 2px solid #fcd34d;
            padding-left: 8px;
        }

        .profile-header {
            background-color: #fef3c7;
            padding: 5px 8px;
            font-size: 8pt;
            margin-bottom: 4px;
        }

        .profile-code {
            font-weight: bold;
            color: #92400e;
        }

        .profile-title {
            color: #2d3748;
        }

        .profile-meta {
            float: right;
            font-size: 7pt;
            color: #718096;
        }

        /* Tablas */
        table.applications {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 6px;
        }

        table.applications thead {
            background-color: #78350f;
            color: white;
        }

        table.applications th {
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 6pt;
            text-transform: uppercase;
            border: 1px solid #451a03;
        }

        table.applications td {
            padding: 3px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        table.applications tbody tr:nth-child(even) {
            background-color: #fffbeb;
        }

        .text-center { text-align: center; }

        /* Badge de decision */
        .decision-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6pt;
            text-transform: uppercase;
        }

        .decision-badge.procede {
            background-color: #d1fae5;
            color: #065f46;
        }

        .decision-badge.no-procede {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Badge de nuevo estado */
        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6pt;
            text-transform: uppercase;
        }

        .status-badge.apto {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-badge.no-apto {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Resolucion */
        .resolution-text {
            font-size: 6pt;
            color: #374151;
            line-height: 1.1;
        }

        .resolution-summary {
            font-weight: bold;
            color: #1f2937;
        }

        /* Subtotales */
        .profile-subtotal {
            text-align: right;
            font-size: 6pt;
            color: #718096;
            font-style: italic;
            padding: 2px 0;
        }

        /* Notas */
        .notes-section {
            background-color: #fef3c7;
            border: 1px solid #fcd34d;
            border-left: 3px solid #92400e;
            padding: 6px 10px;
            margin: 10px 0;
            font-size: 7pt;
        }

        .notes-title {
            font-weight: bold;
            color: #78350f;
            margin-bottom: 4px;
        }

        .notes-content {
            color: #78350f;
        }

        .notes-content ul {
            margin-left: 12px;
            margin-top: 2px;
        }

        .notes-content li {
            margin-bottom: 1px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6pt;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding: 4px 8mm;
            background-color: white;
        }

        .page-break {
            page-break-after: always;
        }

        /* Tipo de resolucion */
        .type-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 5pt;
            text-transform: uppercase;
            background-color: #e5e7eb;
            color: #374151;
        }
    </style>
</head>
<body>
    {{-- Header institucional --}}
    <div class="header">
        <div class="institution-name">MUNICIPALIDAD DISTRITAL DE SAN JERONIMO</div>
        <div class="institution-subtitle">Provincia de Cusco - Region Cusco | Oficina de Recursos Humanos</div>
    </div>

    {{-- Titulo del documento --}}
    <div class="document-header">
        <div class="document-title">{{ $title ?? 'RESULTADO DE REEVALUACION DE ELEGIBILIDAD' }}</div>
        <div class="document-subtitle">{{ $subtitle ?? 'Resolucion de Reclamos - Proceso CAS' }}</div>
    </div>

    {{-- Informacion del proceso y estadisticas en una fila --}}
    <table style="width: 100%; border-collapse: collapse; margin: 8px 0;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="process-info" style="margin: 0;">
                    <span class="info-row">
                        <span class="info-label">Convocatoria:</span>
                        <span class="info-value">{{ $posting->code ?? 'N/A' }}</span>
                    </span>
                    <span class="info-row">
                        <span class="info-label">Fecha:</span>
                        <span class="info-value">{{ $date ?? now()->format('d/m/Y') }}</span>
                    </span>
                    <span class="info-row">
                        <span class="info-label">Hora:</span>
                        <span class="info-value">{{ $time ?? now()->format('H:i') }} hrs.</span>
                    </span>
                    <br>
                    <span class="info-row">
                        <span class="info-label">Fase:</span>
                        <span class="info-value">{{ $phase ?? 'Reevaluacion de Elegibilidad (Reclamos)' }}</span>
                    </span>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="stats-table">
                    <tr>
                        <td class="stat-total" style="background-color: #fef3c7; width: 33%;">
                            <div class="stat-number">{{ $stats['total'] ?? 0 }}</div>
                            <div class="stat-label">Total Resueltos</div>
                        </td>
                        <td class="stat-approved" style="background-color: #d1fae5; width: 33%;">
                            <div class="stat-number">{{ $stats['approved'] ?? 0 }}</div>
                            <div class="stat-label">Procede</div>
                            <div class="stat-percentage">{{ ($stats['total'] ?? 0) > 0 ? number_format((($stats['approved'] ?? 0) / $stats['total']) * 100, 1) : 0 }}%</div>
                        </td>
                        <td class="stat-rejected" style="background-color: #fee2e2; width: 33%;">
                            <div class="stat-number">{{ $stats['rejected'] ?? 0 }}</div>
                            <div class="stat-label">No Procede</div>
                            <div class="stat-percentage">{{ ($stats['total'] ?? 0) > 0 ? number_format((($stats['rejected'] ?? 0) / $stats['total']) * 100, 1) : 0 }}%</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Contenido por Unidades Organizacionales --}}
    @foreach($units as $unitIndex => $unit)
    <div class="unit-section">
        <div class="unit-header">
            {{ $unit['name'] }}
            <span class="unit-stats">
                Total: {{ $unit['stats']['total'] }} | Procede: {{ $unit['stats']['approved'] }} | No Procede: {{ $unit['stats']['rejected'] }}
            </span>
        </div>

        @foreach($unit['profiles'] as $profile)
        <div class="profile-section">
            <div class="profile-header">
                <span class="profile-code">{{ $profile['code'] }}</span> -
                <span class="profile-title">{{ $profile['title'] }}</span>
                <span class="profile-meta">
                    Cargo: {{ $profile['position_code'] }} | Vacantes: {{ $profile['vacancies'] }}
                </span>
            </div>

            <table class="applications">
                <thead>
                    <tr>
                        <th style="width: 3%;" class="text-center">N</th>
                        <th style="width: 8%;">Codigo</th>
                        <th style="width: 7%;" class="text-center">DNI</th>
                        <th style="width: 18%;">Apellidos y Nombres</th>
                        <th style="width: 6%;" class="text-center">Estado Orig.</th>
                        <th style="width: 7%;" class="text-center">Decision</th>
                        <th style="width: 6%;" class="text-center">Nuevo Estado</th>
                        <th style="width: 5%;" class="text-center">Tipo</th>
                        <th style="width: 25%;">Resolucion</th>
                        <th style="width: 5%;" class="text-center">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @foreach($profile['applications'] as $application)
                    @php $override = $application->eligibilityOverride; @endphp
                    <tr>
                        <td class="text-center">{{ $counter++ }}</td>
                        <td>{{ $application->code }}</td>
                        <td class="text-center">{{ $application->dni }}</td>
                        <td>{{ strtoupper($application->full_name) }}</td>
                        <td class="text-center">
                            <span class="status-badge {{ $override->original_status === 'NO_APTO' ? 'no-apto' : '' }}">
                                {{ $override->original_status_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="decision-badge {{ $override->decision->value === 'APPROVED' ? 'procede' : 'no-procede' }}">
                                {{ $override->decision->label() }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="status-badge {{ $override->new_status === 'APTO' ? 'apto' : 'no-apto' }}">
                                {{ $override->new_status_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $types = [
                                    'CLAIM' => 'Reclamo',
                                    'CORRECTION' => 'Corrección de Oficio',
                                    'OTHER' => 'Otro'
                                ];
                            @endphp
                            <span class="type-badge">{{ $types[$override->resolution_type] ?? $override->resolution_type }}</span>
                        </td>
                        <td class="resolution-text">
                            <span class="resolution-summary">{{ $override->resolution_summary }}</span>
                            @if($override->resolution_detail)
                                <br><span style="color: #25272b;">{{ $override->resolution_detail }}</span>
                            @endif
                        </td>
                        <td class="text-center" style="font-size: 6pt;">{{ $override->resolved_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="profile-subtotal">
                Subtotal {{ $profile['code'] }}: {{ $profile['stats']['total'] }} reclamos
                ({{ $profile['stats']['approved'] }} procede, {{ $profile['stats']['rejected'] }} no procede)
            </div>
        </div>
        @endforeach
    </div>

    {{-- Salto de pagina entre unidades si hay muchos registros --}}
    @if($unitIndex < count($units) - 1 && $unit['stats']['total'] > 10)
    <div class="page-break"></div>
    @endif
    @endforeach

    {{-- Notas importantes --}}
    <div class="notes-section">
        <div class="notes-title">NOTA IMPORTANTE:</div>
        <div class="notes-content">
            <ul>
                <li>Los reclamos con decision <strong>PROCEDE</strong> han cambiado el estado del postulante a <strong>APTO</strong>.</li>
                <li>Los reclamos con decision <strong>NO PROCEDE</strong> mantienen al postulante en estado <strong>NO APTO</strong>.</li>
                <li>Los postulantes declarados APTOS pasaran a la siguiente etapa del proceso de seleccion segun cronograma.</li>
                <li>Documento oficial generado por el Sistema CAS - Municipalidad Distrital de San Jeronimo.</li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Municipalidad Distrital de San Jeronimo - Sistema CAS | Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>

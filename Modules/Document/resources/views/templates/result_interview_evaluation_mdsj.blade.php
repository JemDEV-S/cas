<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Resultado de Evaluacion de Entrevista' }}</title>
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
            background-color: #2d5a87;
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
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #2d5a87;
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

        .stat-total .stat-number { color: #2b6cb0; }
        .stat-pass .stat-number { color: #276749; }
        .stat-fail .stat-number { color: #c53030; }
        .stat-avg .stat-number { color: #744210; }

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
            background-color: #2d5a87;
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
            border-left: 2px solid #cbd5e0;
            padding-left: 8px;
        }

        .profile-header {
            background-color: #e2e8f0;
            padding: 5px 8px;
            font-size: 8pt;
            margin-bottom: 4px;
        }

        .profile-code {
            font-weight: bold;
            color: #2b6cb0;
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
            background-color: #4a5568;
            color: white;
        }

        table.applications th {
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 6pt;
            text-transform: uppercase;
            border: 1px solid #2d3748;
        }

        table.applications td {
            padding: 3px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        table.applications tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .text-center { text-align: center; }

        /* Badge de resultado */
        .result-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6pt;
            text-transform: uppercase;
        }

        .result-badge.apto {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .result-badge.no-apto {
            background-color: #fed7d7;
            color: #742a2a;
        }

        /* Score badge */
        .score-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7pt;
        }

        .score-badge.pass {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .score-badge.fail {
            background-color: #fed7d7;
            color: #742a2a;
        }

        /* Bonus badge */
        .bonus-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
        }

        .bonus-badge.age {
            background-color: #d4f0d4;
            color: #22543d;
        }

        .bonus-badge.military {
            background-color: #e9d8fd;
            color: #553c9a;
        }

        /* Comentarios */
        .comment-text {
            font-size: 6pt;
            color: #4a5568;
            line-height: 1.1;
            font-style: italic;
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
            background-color: #ebf8ff;
            border: 1px solid #90cdf4;
            border-left: 3px solid #3182ce;
            padding: 6px 10px;
            margin: 10px 0;
            font-size: 7pt;
        }

        .notes-title {
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 4px;
        }

        .notes-content {
            color: #2c5282;
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
        <div class="document-title">{{ $title ?? 'RESULTADO DE EVALUACION DE ENTREVISTA PERSONAL' }}</div>
        <div class="document-subtitle">{{ $subtitle ?? 'Proceso de Seleccion CAS - FASE 8' }}</div>
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
                        <span class="info-value">{{ $phase ?? 'Entrevista Personal' }}</span>
                    </span>
                    <span class="info-row">
                        <span class="info-label">Puntaje Minimo:</span>
                        <span class="info-value">{{ $min_score ?? 35 }} / {{ $max_score ?? 50 }} puntos</span>
                    </span>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="stats-table">
                    <tr>
                        <td class="stat-total" style="background-color: #ebf4ff; width: 25%;">
                            <div class="stat-number">{{ $stats['total'] ?? 0 }}</div>
                            <div class="stat-label">Evaluados</div>
                        </td>
                        <td class="stat-pass" style="background-color: #f0fff4; width: 25%;">
                            <div class="stat-number">{{ $stats['pass'] ?? 0 }}</div>
                            <div class="stat-label">Aprobados</div>
                            <div class="stat-percentage">{{ $stats['total'] > 0 ? number_format(($stats['pass'] / $stats['total']) * 100, 1) : 0 }}%</div>
                        </td>
                        <td class="stat-fail" style="background-color: #fff5f5; width: 25%;">
                            <div class="stat-number">{{ $stats['fail'] ?? 0 }}</div>
                            <div class="stat-label">Reprobados</div>
                            <div class="stat-percentage">{{ $stats['total'] > 0 ? number_format(($stats['fail'] / $stats['total']) * 100, 1) : 0 }}%</div>
                        </td>
                        <td class="stat-avg" style="background-color: #fffbeb; width: 25%;">
                            <div class="stat-number">{{ number_format($stats['avg_score'] ?? 0, 1) }}</div>
                            <div class="stat-label">Promedio</div>
                            <div class="stat-percentage">de {{ $max_score ?? 50 }} pts</div>
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
                Evaluados: {{ $unit['stats']['total'] }} | Aprobados: {{ $unit['stats']['pass'] }} | Reprobados: {{ $unit['stats']['fail'] }} | Promedio: {{ number_format($unit['stats']['avg_score'] ?? 0, 1) }}
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
                        <th style="width: 20%;">Apellidos y Nombres</th>
                        <th style="width: 8%;" class="text-center">P. RAW</th>
                        <th style="width: 7%;" class="text-center">B. Joven</th>
                        <th style="width: 7%;" class="text-center">B. FF.AA.</th>
                        <th style="width: 9%;" class="text-center">Total c/Bonus</th>
                        <th style="width: 7%;" class="text-center">Resultado</th>
                        <th style="width: 10%;">Evaluador</th>
                        <th style="width: 14%;">Comentarios</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @foreach($profile['applications'] as $application)
                    @php
                        $interviewScore = $application->interview_score ?? 0;
                        $ageBonus = $application->age_bonus ?? 0;
                        $militaryBonus = $application->military_bonus ?? 0;
                        $totalWithBonus = $application->interview_score_with_bonus ?? ($interviewScore + $ageBonus + $militaryBonus);
                        $isPassing = $interviewScore >= ($min_score ?? 35);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $counter++ }}</td>
                        <td>{{ $application->code }}</td>
                        <td class="text-center">{{ $application->dni }}</td>
                        <td>{{ strtoupper($application->full_name) }}</td>
                        <td class="text-center">
                            <span class="score-badge {{ $isPassing ? 'pass' : 'fail' }}">
                                {{ number_format($interviewScore, 2) }} / {{ $max_score ?? 50 }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($ageBonus > 0)
                                <span class="bonus-badge age">+{{ number_format($ageBonus, 2) }}</span>
                            @else
                                <span style="color: #a0aec0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($militaryBonus > 0)
                                <span class="bonus-badge military">+{{ number_format($militaryBonus, 2) }}</span>
                            @else
                                <span style="color: #a0aec0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="score-badge {{ $isPassing ? 'pass' : 'fail' }}" style="font-size: 8pt;">
                                {{ number_format($totalWithBonus, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="result-badge {{ $isPassing ? 'apto' : 'no-apto' }}">
                                {{ $isPassing ? 'APTO' : 'NO APTO' }}
                            </span>
                        </td>
                        <td style="font-size: 6pt;">{{ $application->evaluator_name }}</td>
                        <td class="comment-text">
                            {{ $application->evaluation_comments ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="profile-subtotal">
                Subtotal {{ $profile['code'] }}: {{ $profile['stats']['total'] }} evaluados
                ({{ $profile['stats']['pass'] }} aprobados, {{ $profile['stats']['fail'] }} reprobados, promedio: {{ number_format($profile['stats']['avg_score'] ?? 0, 1) }} pts)
            </div>
        </div>
        @endforeach
    </div>

    {{-- Salto de pagina entre unidades si hay muchos registros --}}
    @if($unitIndex < count($units) - 1 && $unit['stats']['total'] > 12)
    <div class="page-break"></div>
    @endif
    @endforeach

    {{-- Notas importantes --}}
    <div class="notes-section">
        <div class="notes-title">NOTAS IMPORTANTES:</div>
        <div class="notes-content">
            <ul>
                <li>Los postulantes con puntaje RAW <strong>mayor o igual a {{ $min_score ?? 35 }} puntos</strong> son declarados <strong>APTOS</strong> y continuaran a la siguiente etapa.</li>
                <li>Los postulantes con puntaje RAW <strong>menor a {{ $min_score ?? 35 }} puntos</strong> son declarados <strong>NO APTOS</strong> y quedan descalificados.</li>
                <li>El puntaje maximo de la entrevista es <strong>{{ $max_score ?? 50 }} puntos</strong>.</li>
                <li><strong>B. Joven (10%):</strong> Bonificacion para postulantes menores de 29 anos sobre el puntaje RAW (Ley 31533 Art. 3.1)</li>
                <li><strong>B. FF.AA. (10%):</strong> Bonificacion para licenciados de las Fuerzas Armadas sobre el puntaje RAW (RPE 61-2010-SERVIR/PE Art. 4)</li>
                <li>Las bonificaciones son <strong>acumulables</strong> y pueden hacer que el puntaje total supere los {{ $max_score ?? 50 }} puntos.</li>
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

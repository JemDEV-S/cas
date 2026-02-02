<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Cuadro de Meritos - Resultados Finales' }}</title>
    <style>
        @page {
            margin: 8mm 6mm 10mm 6mm;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica Neue', sans-serif;
            font-size: 7pt;
            line-height: 1.2;
            color: #333;
        }

        /* Header institucional */
        .header {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #1e3a5f;
        }

        .institution-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
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
            margin: 6px 0;
            text-align: center;
        }

        .document-title {
            font-size: 9pt;
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
            margin-right: 20px;
        }

        .info-label {
            font-weight: bold;
            color: #4a5568;
        }

        .info-value {
            color: #2d3748;
        }

        /* Estadisticas */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-table td {
            padding: 6px 10px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .stat-number {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1;
        }

        .stat-winners .stat-number { color: #d69e2e; }
        .stat-accesit .stat-number { color: #ed8936; }
        .stat-nosel .stat-number { color: #718096; }
        .stat-vacancies .stat-number { color: #3182ce; }

        .stat-label {
            font-size: 6pt;
            color: #4a5568;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* Unidad Organizacional */
        .unit-section {
            margin: 8px 0;
            page-break-inside: avoid;
        }

        .unit-header {
            background-color: #1e3a5f;
            color: white;
            padding: 5px 8px;
            font-size: 8pt;
            font-weight: bold;
        }

        .unit-stats {
            float: right;
            font-size: 6pt;
            font-weight: normal;
        }

        /* Perfil de Puesto */
        .profile-section {
            margin: 5px 0 5px 6px;
            border-left: 2px solid #cbd5e0;
            padding-left: 6px;
        }

        .profile-header {
            background-color: #e2e8f0;
            padding: 4px 6px;
            font-size: 7pt;
            margin-bottom: 3px;
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
            font-size: 6pt;
            color: #718096;
        }

        /* Tablas */
        table.applications {
            width: 100%;
            border-collapse: collapse;
            font-size: 6pt;
            margin-bottom: 4px;
        }

        table.applications thead {
            background-color: #2d3748;
            color: white;
        }

        table.applications th {
            padding: 3px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 5pt;
            text-transform: uppercase;
            border: 1px solid #1a202c;
        }

        table.applications td {
            padding: 2px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        table.applications tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }

        /* Resultado badges */
        .result-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 5pt;
            text-transform: uppercase;
        }

        .result-badge.ganador {
            background-color: #faf089;
            color: #744210;
        }

        .result-badge.accesitario {
            background-color: #fbd38d;
            color: #7b341e;
        }

        .result-badge.no-seleccionado {
            background-color: #e2e8f0;
            color: #4a5568;
        }

        /* Ranking badge */
        .ranking-badge {
            display: inline-block;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 7pt;
        }

        .ranking-badge.gold {
            background-color: #faf089;
            color: #744210;
        }

        .ranking-badge.silver {
            background-color: #e2e8f0;
            color: #4a5568;
        }

        .ranking-badge.bronze {
            background-color: #fbd38d;
            color: #7b341e;
        }

        .ranking-badge.normal {
            background-color: #edf2f7;
            color: #4a5568;
        }

        /* Score badge */
        .score-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 6pt;
        }

        .score-badge.high {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .score-badge.medium {
            background-color: #fefcbf;
            color: #744210;
        }

        .score-badge.low {
            background-color: #fed7d7;
            color: #742a2a;
        }

        /* Bonus badge */
        .bonus-badge {
            display: inline-block;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 5pt;
            font-weight: bold;
        }

        .bonus-badge.age { background-color: #d4f0d4; color: #22543d; }
        .bonus-badge.military { background-color: #e9d8fd; color: #553c9a; }
        .bonus-badge.public { background-color: #bee3f8; color: #2c5282; }
        .bonus-badge.disability { background-color: #fed7d7; color: #742a2a; }
        .bonus-badge.athlete { background-color: #fbd38d; color: #7b341e; }
        .bonus-badge.terrorism { background-color: #feebc8; color: #744210; }

        /* Subtotales */
        .profile-subtotal {
            text-align: right;
            font-size: 5pt;
            color: #718096;
            font-style: italic;
            padding: 2px 0;
        }

        /* Notas */
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
        }

        .notes-content {
            color: #744210;
        }

        .notes-content ul {
            margin-left: 10px;
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
            font-size: 5pt;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding: 3px 6mm;
            background-color: white;
        }

        .page-break {
            page-break-after: always;
        }

        /* Filas especiales */
        tr.ganador-row {
            background-color: #fefcbf !important;
        }

        tr.accesitario-row {
            background-color: #feebc8 !important;
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
        <div class="document-title">{{ $title ?? 'CUADRO DE MERITOS - RESULTADOS FINALES' }}</div>
        <div class="document-subtitle">{{ $subtitle ?? 'Proceso de Seleccion CAS' }}</div>
    </div>

    {{-- Informacion del proceso y estadisticas --}}
    <table style="width: 100%; border-collapse: collapse; margin: 6px 0;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
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
                        <span class="info-value">{{ $phase ?? 'Resultados Finales' }}</span>
                    </span>
                    <span class="info-row">
                        <span class="info-label">Puntaje Minimo:</span>
                        <span class="info-value">{{ $min_score ?? 70 }} / 100+ puntos</span>
                    </span>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table class="stats-table">
                    <tr>
                        <td class="stat-vacancies" style="background-color: #ebf8ff; width: 25%;">
                            <div class="stat-number">{{ $stats['vacancies'] ?? 0 }}</div>
                            <div class="stat-label">Vacantes</div>
                        </td>
                        <td class="stat-winners" style="background-color: #fefcbf; width: 25%;">
                            <div class="stat-number">{{ $stats['winners'] ?? 0 }}</div>
                            <div class="stat-label">Ganadores</div>
                        </td>
                        <td class="stat-accesit" style="background-color: #feebc8; width: 25%;">
                            <div class="stat-number">{{ $stats['accesitarios'] ?? 0 }}</div>
                            <div class="stat-label">Accesitarios</div>
                        </td>
                        <td class="stat-nosel" style="background-color: #edf2f7; width: 25%;">
                            <div class="stat-number">{{ $stats['not_selected'] ?? 0 }}</div>
                            <div class="stat-label">No Selec.</div>
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
                Vacantes: {{ $unit['stats']['vacancies'] }} |
                Ganadores: {{ $unit['stats']['winners'] }} |
                Accesitarios: {{ $unit['stats']['accesitarios'] }} |
                No Selec.: {{ $unit['stats']['not_selected'] }}
            </span>
        </div>

        @foreach($unit['profiles'] as $profile)
        <div class="profile-section">
            <div class="profile-header">
                <span class="profile-code">{{ $profile['code'] }}</span> -
                <span class="profile-title">{{ $profile['title'] }}</span>
                <span class="profile-meta">
                    Cargo: {{ $profile['position_code'] }} |
                    Vacantes: {{ $profile['vacancies'] }} |
                    Postulantes: {{ $profile['stats']['total'] }}
                </span>
            </div>

            <table class="applications">
                <thead>
                    <tr>
                        <th style="width: 2%;">Rank</th>
                        <th style="width: 6%;">Codigo</th>
                        <th style="width: 5%;">DNI</th>
                        <th style="width: 14%;" class="text-left">Apellidos y Nombres</th>
                        <th style="width: 4%;">P.CV</th>
                        <th style="width: 4%;">P.Entr</th>
                        <th style="width: 4%;">B.Joven</th>
                        <th style="width: 4%;">B.FFAA</th>
                        <th style="width: 4%;">B.Pub</th>
                        <th style="width: 4%;">B.Disc</th>
                        <th style="width: 4%;">B.Depo</th>
                        <th style="width: 4%;">B.Terror</th>
                        <th style="width: 5%;">Subtot</th>
                        <th style="width: 5%;">Tot.Bon</th>
                        <th style="width: 5%;" style="background-color: #2d3748;">FINAL</th>
                        <th style="width: 8%;">Resultado</th>
                        <th style="width: 3%;">Ord</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profile['applications'] as $application)
                    @php
                        $isGanador = $application->selection_result === 'GANADOR';
                        $isAccesitario = $application->selection_result === 'ACCESITARIO';

                        $curriculumScore = $application->curriculum_score ?? 0;
                        $interviewScore = $application->interview_score ?? 0;
                        $ageBonus = $application->age_bonus ?? 0;
                        $militaryBonus = $application->military_bonus ?? 0;
                        $publicSectorBonus = $application->public_sector_bonus ?? 0;
                        $specialBonusTotal = $application->special_bonus_total ?? 0;

                        // Calcular bonificaciones especiales individuales
                        $disabilityBonus = 0;
                        $athleteBonus = 0;
                        $terrorismBonus = 0;

                        if ($application->relationLoaded('specialConditions')) {
                            foreach ($application->specialConditions as $condition) {
                                $subtotal = $curriculumScore + $interviewScore + $ageBonus + $militaryBonus + $publicSectorBonus;
                                $bonus = $subtotal * ($condition->bonus_percentage / 100);
                                switch ($condition->condition_type) {
                                    case 'DISABILITY':
                                        $disabilityBonus = $bonus;
                                        break;
                                    case 'ATHLETE_NATIONAL':
                                    case 'ATHLETE_INTL':
                                        $athleteBonus += $bonus;
                                        break;
                                    case 'TERRORISM':
                                        $terrorismBonus = $bonus;
                                        break;
                                }
                            }
                        }

                        $subtotal = $curriculumScore + $interviewScore + $ageBonus + $militaryBonus + $publicSectorBonus;
                        $totalBonus = $ageBonus + $militaryBonus + $publicSectorBonus + $specialBonusTotal;
                        $finalScore = $application->final_score ?? 0;

                        // Ranking badge class
                        $rankingClass = 'normal';
                        if ($application->final_ranking == 1) $rankingClass = 'gold';
                        elseif ($application->final_ranking == 2) $rankingClass = 'silver';
                        elseif ($application->final_ranking == 3) $rankingClass = 'bronze';
                    @endphp
                    <tr class="{{ $isGanador ? 'ganador-row' : ($isAccesitario ? 'accesitario-row' : '') }}">
                        <td class="text-center">
                            <span class="ranking-badge {{ $rankingClass }}">{{ $application->final_ranking }}</span>
                        </td>
                        <td style="font-size: 5pt;">{{ $application->code }}</td>
                        <td class="text-center">{{ $application->dni }}</td>
                        <td class="text-left" style="font-size: 5pt;">{{ strtoupper($application->full_name) }}</td>
                        <td class="text-center">
                            <span class="score-badge {{ $curriculumScore >= 35 ? 'high' : 'low' }}">
                                {{ number_format($curriculumScore, 1) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="score-badge {{ $interviewScore >= 35 ? 'high' : 'low' }}">
                                {{ number_format($interviewScore, 1) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($ageBonus > 0)
                                <span class="bonus-badge age">+{{ number_format($ageBonus, 1) }}</span>
                            @else
                                <span style="color: #cbd5e0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($militaryBonus > 0)
                                <span class="bonus-badge military">+{{ number_format($militaryBonus, 1) }}</span>
                            @else
                                <span style="color: #cbd5e0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($publicSectorBonus > 0)
                                <span class="bonus-badge public">+{{ number_format($publicSectorBonus, 1) }}</span>
                            @else
                                <span style="color: #cbd5e0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($disabilityBonus > 0)
                                <span class="bonus-badge disability">+{{ number_format($disabilityBonus, 1) }}</span>
                            @else
                                <span style="color: #cbd5e0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($athleteBonus > 0)
                                <span class="bonus-badge athlete">+{{ number_format($athleteBonus, 1) }}</span>
                            @else
                                <span style="color: #cbd5e0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($terrorismBonus > 0)
                                <span class="bonus-badge terrorism">+{{ number_format($terrorismBonus, 1) }}</span>
                            @else
                                <span style="color: #cbd5e0;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ number_format($subtotal, 1) }}
                        </td>
                        <td class="text-center">
                            @if($totalBonus > 0)
                                <span style="color: #276749; font-weight: bold;">+{{ number_format($totalBonus, 1) }}</span>
                            @else
                                <span style="color: #cbd5e0;">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="score-badge {{ $finalScore >= 70 ? 'high' : ($finalScore >= 50 ? 'medium' : 'low') }}" style="font-size: 7pt;">
                                {{ number_format($finalScore, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($isGanador)
                                <span class="result-badge ganador">GANADOR</span>
                            @elseif($isAccesitario)
                                <span class="result-badge accesitario">ACCESITARIO</span>
                            @else
                                <span class="result-badge no-seleccionado">NO SELEC.</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isAccesitario && $application->accesitario_order)
                                <span style="font-weight: bold; color: #c05621;">#{{ $application->accesitario_order }}</span>
                            @elseif($isGanador)
                                <span style="font-weight: bold; color: #744210;">V{{ $application->final_ranking }}</span>
                            @else
                                <span style="color: #cbd5e0;">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="profile-subtotal">
                {{ $profile['code'] }}: {{ $profile['stats']['total'] }} postulantes |
                Ganadores: {{ $profile['stats']['winners'] }} |
                Accesitarios: {{ $profile['stats']['accesitarios'] }} |
                No Seleccionados: {{ $profile['stats']['not_selected'] }}
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
        <div class="notes-title">LEYENDA DE BONIFICACIONES Y NOTAS:</div>
        <div class="notes-content">
            <ul>
                <li><strong>P.CV:</strong> Puntaje Evaluacion Curricular (max 50 pts)</li>
                <li><strong>P.Entr:</strong> Puntaje Entrevista Personal RAW (max 50 pts)</li>
                <li><strong>B.Joven:</strong> Bonificacion Joven 10% sobre entrevista (Ley 31533 Art. 3.1) - Menores de 29 anos</li>
                <li><strong>B.FFAA:</strong> Bonificacion Licenciado FF.AA. 10% sobre entrevista (RPE 61-2010-SERVIR/PE Art. 4)</li>
                <li><strong>B.Pub:</strong> Bonificacion Experiencia Sector Publico (1 pt/ano, max 3 pts) - Ley 31533 Art. 3.2</li>
                <li><strong>B.Disc:</strong> Bonificacion Discapacidad 15% sobre subtotal (Ley 29973 Art. 48)</li>
                <li><strong>B.Depo:</strong> Bonificacion Deportista Nacional/Internacional 10-15% sobre subtotal (Ley 27674)</li>
                <li><strong>B.Terror:</strong> Bonificacion Victima Terrorismo 10% sobre subtotal (Ley 27277)</li>
                <li><strong>Puntaje Minimo:</strong> {{ $min_score ?? 70 }} puntos para ser considerado elegible</li>
                <li><strong>GANADOR:</strong> Postulante asignado a una vacante | <strong>ACCESITARIO:</strong> En lista de espera por orden de merito</li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Municipalidad Distrital de San Jeronimo - Sistema CAS | Cuadro de Meritos Oficial | Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>

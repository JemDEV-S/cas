<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma de Entrevistas - {{ $jobPosting->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4F46E5;
        }

        .header h1 {
            font-size: 16pt;
            color: #1F2937;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .header h2 {
            font-size: 12pt;
            color: #4F46E5;
            margin-bottom: 8px;
        }

        .header-info {
            font-size: 8pt;
            color: #6B7280;
            margin-top: 5px;
        }

        .metadata {
            background: #F3F4F6;
            padding: 8px 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 8pt;
        }

        .stats-container {
            margin-bottom: 15px;
            display: table;
            width: 100%;
        }

        .stat-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 8px;
            background: #EEF2FF;
            border-radius: 4px;
        }

        .stat-box:not(:last-child) {
            border-right: 3px solid white;
        }

        .stat-number {
            font-size: 18pt;
            font-weight: bold;
            color: #4F46E5;
            display: block;
        }

        .stat-label {
            font-size: 8pt;
            color: #6B7280;
            text-transform: uppercase;
            display: block;
            margin-top: 2px;
        }

        .date-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .date-header {
            background: #4F46E5;
            color: white;
            padding: 6px 10px;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 12px;
            border-radius: 3px;
        }

        .time-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .time-header {
            background: #8B5CF6;
            color: white;
            padding: 5px 8px;
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 6px;
            border-radius: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        th {
            background: #6366F1;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 8pt;
            vertical-align: top;
            word-wrap: break-word;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-scheduled {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-completed {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .no-interviews {
            text-align: center;
            padding: 30px;
            color: #9CA3AF;
            font-style: italic;
            background: #F9FAFB;
            border-radius: 4px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #9CA3AF;
            padding: 8px 0;
            border-top: 1px solid #E5E7EB;
        }

        .page-number:after {
            content: counter(page);
        }

        .location {
            font-size: 7pt;
            color: #4B5563;
            display: block;
            margin-top: 2px;
        }

        .warnings-section {
            margin: 25px 0 60px 0;
            page-break-inside: avoid;
            border: 2px solid #DC2626;
            border-radius: 6px;
            overflow: hidden;
        }

        .warnings-header {
            background: #DC2626;
            color: white;
            padding: 8px 12px;
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
        }

        .warnings-content {
            background: #FEF2F2;
            padding: 12px 15px;
        }

        .warning-item {
            display: flex;
            margin-bottom: 8px;
            padding: 6px 8px;
            background: white;
            border-left: 3px solid #DC2626;
            border-radius: 3px;
            align-items: flex-start;
        }

        .warning-item:last-child {
            margin-bottom: 0;
        }

        .warning-number {
            color: #DC2626;
            font-weight: bold;
            font-size: 9pt;
            min-width: 20px;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .warning-text {
            font-size: 8pt;
            color: #1F2937;
            line-height: 1.5;
        }

        .warning-text strong {
            color: #DC2626;
            font-weight: bold;
        }

        @media print {
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>CRONOGRAMA DE ENTREVISTAS</h1>
        <h2>{{ $jobPosting->title }}</h2>
        <div class="header-info">
            <strong>Convocatoria:</strong> {{ $jobPosting->code }} |
            <strong>Fase:</strong> {{ $phase->name }} |
            <strong>Generado:</strong> {{ $generatedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Interviews by Date and Time -->
    @if($interviews->isNotEmpty())
        @php
            // Agrupar por fecha
            $interviewsByDate = $interviews->groupBy(function($interview) {
                return $interview->interview_scheduled_at
                    ? \Carbon\Carbon::parse($interview->interview_scheduled_at)->format('Y-m-d')
                    : 'sin_programar';
            });
        @endphp

        @foreach($interviewsByDate as $date => $dayInterviews)
            <div class="date-section">
                @if($date === 'sin_programar')
                    <div class="date-header">
                        📋 ENTREVISTAS SIN PROGRAMAR ({{ $dayInterviews->count() }})
                    </div>
                @else
                    @php
                        $dateCarbon = \Carbon\Carbon::parse($date);
                        $dayName = ucfirst($dateCarbon->locale('es')->isoFormat('dddd'));
                    @endphp
                    <div class="date-header">
                        {{ $dayName }}, {{ $dateCarbon->format('d/m/Y') }} ({{ $dayInterviews->count() }} entrevistas)
                    </div>
                @endif

                @if($date === 'sin_programar')
                    {{-- Para entrevistas sin programar, mostrar sin agrupar por hora --}}
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 10%;">N°</th>
                                <th style="width: 30%;">Código</th>
                                <th style="width: 60%;">Postulante</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dayInterviews as $index => $interview)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $interview->application->code ?? 'N/A' }}</strong></td>
                                <td>
                                    <strong>{{ $interview->application->full_name ?? 'N/A' }}</strong>
                                    <br>
                                    <span style="font-size: 6pt; color: #6B7280;">
                                        DNI: {{ $interview->application->dni ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- Para entrevistas programadas, agrupar por hora --}}
                    @php
                        // Agrupar por hora
                        $interviewsByHour = $dayInterviews->groupBy(function($interview) {
                            return \Carbon\Carbon::parse($interview->interview_scheduled_at)->format('H:i');
                        });
                    @endphp

                    @foreach($interviewsByHour as $hour => $hourInterviews)
                        <div class="time-section">
                            <div class="time-header">
                                {{ $hour }} ({{ $hourInterviews->count() }} postulantes)
                                @if($hourInterviews->first()->interview_location)
                                    | {{ $hourInterviews->first()->interview_location }}
                                @endif
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">N°</th>
                                        <th style="width: 25%;">Código</th>
                                        <th style="width: 45%;">Postulante</th>
                                        <th style="width: 20%;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hourInterviews as $index => $interview)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $interview->application->code ?? 'N/A' }}</strong></td>
                                        <td>
                                            <strong>{{ $interview->application->full_name ?? 'N/A' }}</strong>
                                            <br>
                                            <span style="font-size: 6pt; color: #6B7280;">
                                                DNI: {{ $interview->application->dni ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($interview->status->value === 'COMPLETED')
                                                <span class="badge badge-completed">Completada</span>
                                            @elseif($interview->interview_scheduled_at)
                                                <span class="badge badge-scheduled">Citado</span>
                                            @else
                                                <span class="badge badge-pending">Pendiente</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    @else
        <div class="no-interviews">
            <p>No hay entrevistas programadas para mostrar.</p>
        </div>
    @endif

    <!-- Advertencias -->
    <div class="warnings-section">
        <div class="warnings-header">
            ⚠️ ADVERTENCIAS IMPORTANTES
        </div>
        <div class="warnings-content">
            <div class="warning-item">
                <span class="warning-number">1.</span>
                <span class="warning-text">Los postulantes deben presentarse <strong>10 MINUTOS ANTES</strong> de la hora citada. Se llamará a lista a la hora exacta programada.</span>
            </div>
            <div class="warning-item">
                <span class="warning-number">2.</span>
                <span class="warning-text">La ausencia o llegada tardía (después de la hora citada) resultará en la <strong>DESCALIFICACIÓN AUTOMÁTICA</strong> del proceso de selección.</span>
            </div>
            <div class="warning-item">
                <span class="warning-number">3.</span>
                <span class="warning-text">Es <strong>OBLIGATORIO</strong> presentar el DNI original. No se aceptarán copias ni documentos vencidos.</span>
            </div>
            <div class="warning-item">
                <span class="warning-number">4.</span>
                <span class="warning-text">Los postulantes deben presentarse con vestimenta <strong>FORMAL</strong> (no se permite jeans, zapatillas deportivas, ni ropa casual).</span>
            </div>
            <div class="warning-item">
                <span class="warning-number">5.</span>
                <span class="warning-text">No está permitido el uso de dispositivos electrónicos durante la entrevista. Los celulares deben permanecer <strong>APAGADOS</strong>.</span>
            </div>
            <div class="warning-item">
                <span class="warning-number">6.</span>
                <span class="warning-text">No se permiten acompañantes en la sala de espera. Solo ingresará el postulante.</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Cronograma de Entrevistas - {{ $jobPosting->title }} | Página <span class="page-number"></span> |
        Generado: {{ $generatedAt->format('d/m/Y H:i') }}
    </div>
</body>
</html>
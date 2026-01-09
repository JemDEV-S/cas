<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
        }

        .institution-name {
            font-size: 16pt;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .document-subtitle {
            font-size: 11pt;
            color: #666;
        }

        .info-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #0066cc;
        }

        .info-row {
            margin: 5px 0;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 9pt;
        }

        table thead {
            background-color: #0066cc;
            color: white;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        table th {
            font-weight: bold;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .rank-1 {
            background-color: #ffd700 !important;
            font-weight: bold;
        }

        .rank-2 {
            background-color: #c0c0c0 !important;
        }

        .rank-3 {
            background-color: #cd7f32 !important;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .winner-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }

        .signatures {
            margin-top: 60px;
            page-break-inside: avoid;
        }

        .signature-block {
            display: inline-block;
            width: 30%;
            text-align: center;
            margin: 20px 1.5%;
            vertical-align: top;
        }

        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            margin-bottom: 5px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 10pt;
        }

        .signature-role {
            font-size: 9pt;
            color: #666;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="institution-name">
            MINISTERIO DE DESARROLLO E INCLUSIÓN SOCIAL
        </div>
        <div class="institution-name">
            OFICINA DE RECURSOS HUMANOS
        </div>
        <div class="document-title">{{ $title }}</div>
        <div class="document-subtitle">{{ $subtitle }}</div>
    </div>

    {{-- Información del proceso --}}
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Convocatoria:</span>
            <span>{{ $posting->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de publicación:</span>
            <span>{{ $date }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fase:</span>
            <span>{{ $phase }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total de postulantes:</span>
            <span>{{ count($applications) }}</span>
        </div>
    </div>

    {{-- Ganador destacado --}}
    @if(isset($applications[0]) && $applications[0]->final_ranking === 1)
        <div style="margin: 20px 0; padding: 20px; background-color: #d4edda; border-left: 4px solid #28a745; border-radius: 5px;">
            <h3 style="color: #155724; margin-bottom: 10px;">
                🏆 GANADOR DEL PROCESO DE SELECCIÓN
            </h3>
            <div style="font-size: 14pt;">
                <strong>{{ $applications[0]->full_name }}</strong>
            </div>
            <div style="color: #666; margin-top: 5px;">
                DNI: {{ $applications[0]->dni }} | Código: {{ $applications[0]->code }}
            </div>
            <div style="margin-top: 10px; font-size: 18pt; font-weight: bold; color: #155724;">
                Puntaje Final: {{ number_format($applications[0]->final_score, 2) }}
            </div>
        </div>
    @endif

    {{-- Ranking Final Completo --}}
    <h3 style="margin-top: 30px; color: #0066cc;">RANKING FINAL</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 7%;" class="text-center">Rank</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 28%;">Apellidos y Nombres</th>
                <th style="width: 8%;">DNI</th>
                <th style="width: 12%;" class="text-center">P. Curr.</th>
                <th style="width: 12%;" class="text-center">P. Entrev.</th>
                <th style="width: 10%;" class="text-center">Bonif.</th>
                <th style="width: 13%;" class="text-center">P. Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $application)
            <tr class="{{ $application->final_ranking <= 3 ? 'rank-' . $application->final_ranking : '' }}">
                <td class="text-center">
                    <strong>{{ $application->final_ranking }}</strong>
                    @if($application->final_ranking === 1)
                        🥇
                    @elseif($application->final_ranking === 2)
                        🥈
                    @elseif($application->final_ranking === 3)
                        🥉
                    @endif
                </td>
                <td>{{ $application->code }}</td>
                <td>
                    {{ $application->full_name }}
                    @if($application->final_ranking === 1)
                        <span class="winner-badge">GANADOR</span>
                    @endif
                </td>
                <td>{{ $application->dni }}</td>
                <td class="text-center">{{ number_format($application->curriculum_score ?? 0, 2) }}</td>
                <td class="text-center">{{ number_format($application->interview_score ?? 0, 2) }}</td>
                <td class="text-center">
                    {{ $application->special_condition_bonus > 0 ? number_format($application->special_condition_bonus, 2) : '-' }}
                </td>
                <td class="text-center">
                    <strong style="font-size: 11pt;">{{ number_format($application->final_score, 2) }}</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Leyenda de puntajes --}}
    <div style="margin-top: 20px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #0066cc; font-size: 9pt;">
        <p style="margin: 5px 0;"><strong>Leyenda:</strong></p>
        <p style="margin: 5px 0;">• <strong>P. Curr.:</strong> Puntaje de Evaluación Curricular</p>
        <p style="margin: 5px 0;">• <strong>P. Entrev.:</strong> Puntaje de Entrevista Personal</p>
        <p style="margin: 5px 0;">• <strong>Bonif.:</strong> Bonificación por condiciones especiales</p>
        <p style="margin: 5px 0;">• <strong>P. Final:</strong> Puntaje Total Final (suma de todos los anteriores)</p>
    </div>

    {{-- Nota informativa --}}
    <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107;">
        <p style="margin: 0; font-size: 10pt;">
            <strong>NOTA IMPORTANTE:</strong> Estos son los resultados finales del proceso de selección.
            El postulante que figura en el primer lugar del ranking es el ganador del concurso público.
            Los siguientes postulantes en el orden de mérito quedan como cuadro de méritos para eventuales
            reemplazos de acuerdo a la normativa vigente.
        </p>
    </div>

    {{-- Firmas (placeholder - se añadirán digitalmente) --}}
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-name">[FIRMA DIGITAL]</div>
            <div class="signature-role">Presidente del Jurado</div>
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-name">[FIRMA DIGITAL]</div>
            <div class="signature-role">Jurado Titular 1</div>
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-name">[FIRMA DIGITAL]</div>
            <div class="signature-role">Jurado Titular 2</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Documento generado electrónicamente. Verificar firmas digitales en el portal institucional.
    </div>
</body>
</html>

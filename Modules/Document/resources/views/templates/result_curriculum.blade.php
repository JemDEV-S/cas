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
            font-size: 10pt;
        }

        table thead {
            background-color: #0066cc;
            color: white;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            font-weight: bold;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .rank-1 { background-color: #ffd700 !important; font-weight: bold; }
        .rank-2 { background-color: #c0c0c0 !important; }
        .rank-3 { background-color: #cd7f32 !important; }

        .text-center { text-align: center; }

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

    {{-- Ranking de Evaluación Curricular --}}
    <h3 style="margin-top: 30px; color: #0066cc;">RANKING DE EVALUACIÓN CURRICULAR</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">Ranking</th>
                <th style="width: 12%;">Código</th>
                <th style="width: 35%;">Apellidos y Nombres</th>
                <th style="width: 10%;">DNI</th>
                <th style="width: 20%;">Vacante</th>
                <th style="width: 15%;" class="text-center">Puntaje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $application)
            <tr class="{{ $application->ranking <= 3 ? 'rank-' . $application->ranking : '' }}">
                <td class="text-center">
                    <strong>{{ $application->ranking }}</strong>
                    @if($application->ranking === 1)
                        🥇
                    @elseif($application->ranking === 2)
                        🥈
                    @elseif($application->ranking === 3)
                        🥉
                    @endif
                </td>
                <td>{{ $application->code }}</td>
                <td>{{ $application->full_name }}</td>
                <td>{{ $application->dni }}</td>
                <td>{{ $application->assignedVacancy?->code ?? $application->jobProfile->code }}</td>
                <td class="text-center">
                    <strong>{{ number_format($application->curriculum_score, 2) }}</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Nota informativa --}}
    <div style="margin-top: 30px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107;">
        <p style="margin: 0; font-size: 10pt;">
            <strong>NOTA:</strong> Los postulantes que figuran en este ranking están habilitados para continuar
            a la siguiente fase del proceso de selección. Los puntajes corresponden a la evaluación curricular
            realizada por el comité de selección.
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

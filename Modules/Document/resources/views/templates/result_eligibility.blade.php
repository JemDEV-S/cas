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

        .logo {
            margin-bottom: 15px;
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

        .statistics {
            margin: 20px 0;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .stat-box {
            flex: 1;
            padding: 15px;
            margin: 0 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        .stat-number {
            font-size: 24pt;
            font-weight: bold;
            color: #0066cc;
        }

        .stat-label {
            font-size: 10pt;
            color: #666;
            margin-top: 5px;
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

        .result-apto {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            text-align: center;
        }

        .result-no-apto {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            text-align: center;
        }

        .signatures {
            margin-top: 60px;
            page-break-inside: avoid;
        }

        .signature-block {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin: 20px 2%;
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

        @media print {
            body {
                padding: 20px;
            }
            .page-break {
                page-break-after: always;
            }
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
    </div>

    {{-- Estadísticas --}}
    <div class="statistics">
        <div class="stat-box">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Postulantes</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" style="color: #28a745;">{{ $stats['eligible'] }}</div>
            <div class="stat-label">APTOS</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" style="color: #dc3545;">{{ $stats['not_eligible'] }}</div>
            <div class="stat-label">NO APTOS</div>
        </div>
    </div>

    {{-- Lista de APTOS --}}
    <h3 style="margin-top: 30px; color: #28a745;">POSTULANTES APTOS</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 35%;">Apellidos y Nombres</th>
                <th style="width: 10%;">DNI</th>
                <th style="width: 25%;">Vacante</th>
                <th style="width: 15%;">Resultado</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($aptos as $application)
            <tr>
                <td style="text-align: center;">{{ $counter++ }}</td>
                <td>{{ $application->code }}</td>
                <td>{{ $application->full_name }}</td>
                <td>{{ $application->dni }}</td>
                <td>{{ $application->vacancy->code }}</td>
                <td><span class="result-apto">APTO</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Lista de NO APTOS --}}
    <div class="page-break"></div>
    <h3 style="margin-top: 30px; color: #dc3545;">POSTULANTES NO APTOS</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 25%;">Apellidos y Nombres</th>
                <th style="width: 10%;">DNI</th>
                <th style="width: 20%;">Vacante</th>
                <th style="width: 10%;">Resultado</th>
                <th style="width: 20%;">Motivo</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($no_aptos as $application)
            <tr>
                <td style="text-align: center;">{{ $counter++ }}</td>
                <td>{{ $application->code }}</td>
                <td>{{ $application->full_name }}</td>
                <td>{{ $application->dni }}</td>
                <td>{{ $application->vacancy->code }}</td>
                <td><span class="result-no-apto">NO APTO</span></td>
                <td style="font-size: 8pt;">{{ Str::limit($application->ineligibility_reason, 50) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

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
            <div class="signature-role">Jurado Titular</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Documento generado electrónicamente. Verificar firmas digitales en el portal institucional.
    </div>
</body>
</html>

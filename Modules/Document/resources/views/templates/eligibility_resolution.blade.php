<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resolucion de Reclamo - {{ $application->code }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica Neue', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
        }

        /* Header institucional */
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .institution-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
        }

        .institution-subtitle {
            font-size: 9pt;
            color: #666;
        }

        /* Titulo del documento */
        .document-title {
            background-color: #1e3a5f;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
        }

        /* Secciones de informacion */
        .info-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #1e3a5f;
        }

        .info-section-title {
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 8px;
            font-size: 10pt;
            text-transform: uppercase;
        }

        .info-row {
            margin: 5px 0;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
            color: #4a5568;
        }

        .info-value {
            color: #2d3748;
        }

        /* Caja de resultado */
        .result-box {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid;
            text-align: center;
        }

        .result-approved {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .result-rejected {
            border-color: #dc3545;
            background-color: #f8d7da;
        }

        .result-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .result-approved .result-title {
            color: #155724;
        }

        .result-rejected .result-title {
            color: #721c24;
        }

        .result-subtitle {
            margin-top: 5px;
            font-size: 10pt;
        }

        .result-approved .result-subtitle {
            color: #155724;
        }

        .result-rejected .result-subtitle {
            color: #721c24;
        }

        /* Seccion de resolucion */
        .resolution-section {
            margin: 20px 0;
        }

        .resolution-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 8px;
            color: #1e3a5f;
        }

        .resolution-content {
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            min-height: 100px;
        }

        .resolution-summary {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
            color: #2d3748;
        }

        .resolution-detail {
            font-size: 10pt;
            line-height: 1.6;
            color: #4a5568;
            text-align: justify;
        }

        /* Motivo original */
        .original-reason {
            margin: 15px 0;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
        }

        .original-reason-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }

        .original-reason-text {
            color: #856404;
            font-size: 10pt;
        }

        /* Firmas */
        .signatures {
            margin-top: 60px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 250px;
            margin: 50px auto 5px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 10pt;
        }

        .signature-role {
            font-size: 9pt;
            color: #666;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }

        /* Tabla de tipo de resolucion */
        .resolution-type-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .type-claim {
            background-color: #e2e8f0;
            color: #2d3748;
        }

        .type-correction {
            background-color: #bee3f8;
            color: #2a4365;
        }

        .type-other {
            background-color: #faf5ff;
            color: #553c9a;
        }
    </style>
</head>
<body>
    {{-- Header institucional --}}
    <div class="header">
        <div class="institution-name">MUNICIPALIDAD DISTRITAL DE SAN JERONIMO</div>
        <div class="institution-subtitle">Provincia de Cusco - Region Cusco | Oficina de Recursos Humanos</div>
    </div>

    {{-- Titulo --}}
    <div class="document-title">
        Resolucion de Reevaluacion de Elegibilidad
    </div>

    {{-- Informacion de la convocatoria --}}
    <div class="info-section">
        <div class="info-section-title">Datos de la Convocatoria</div>
        <div class="info-row">
            <span class="info-label">Convocatoria:</span>
            <span class="info-value">{{ $posting->code ?? 'N/A' }} - {{ $posting->title ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Perfil de Puesto:</span>
            <span class="info-value">{{ $application->jobProfile->code ?? 'N/A' }} - {{ $application->jobProfile->profile_name ?? '' }}</span>
        </div>
    </div>

    {{-- Informacion del postulante --}}
    <div class="info-section">
        <div class="info-section-title">Datos del Postulante</div>
        <div class="info-row">
            <span class="info-label">Codigo de Postulacion:</span>
            <span class="info-value">{{ $application->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">DNI:</span>
            <span class="info-value">{{ $application->dni }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Postulante:</span>
            <span class="info-value">{{ strtoupper($application->full_name) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado Original:</span>
            <span class="info-value">{{ $override->original_status_label }}</span>
        </div>
    </div>

    {{-- Motivo original de NO APTO (si aplica) --}}
    @if($override->original_reason)
    <div class="original-reason">
        <div class="original-reason-title">Motivo original de inelegibilidad:</div>
        <div class="original-reason-text">{{ $override->original_reason }}</div>
    </div>
    @endif

    {{-- Resultado de la reevaluacion --}}
    <div class="result-box {{ $override->decision->value === 'APPROVED' ? 'result-approved' : 'result-rejected' }}">
        <div class="result-title">
            @if($override->decision->value === 'APPROVED')
                EL RECLAMO PROCEDE
            @else
                EL RECLAMO NO PROCEDE
            @endif
        </div>
        <div class="result-subtitle">
            Nuevo Estado: <strong>{{ $override->new_status_label }}</strong>
        </div>
    </div>

    {{-- Tipo de resolucion --}}
    <div style="text-align: center; margin: 10px 0;">
        <span class="resolution-type-badge type-{{ strtolower($override->resolution_type) }}">
            {{ $override->resolution_type_label }}
        </span>
    </div>

    {{-- Fundamento de la resolucion --}}
    <div class="resolution-section">
        <div class="resolution-title">FUNDAMENTO DE LA RESOLUCION:</div>
        <div class="resolution-content">
            <div class="resolution-summary">{{ $override->resolution_summary }}</div>
            <div class="resolution-detail">{{ $override->resolution_detail }}</div>
        </div>
    </div>

    {{-- Datos de la resolucion --}}
    <div class="info-section">
        <div class="info-section-title">Datos de la Resolucion</div>
        <div class="info-row">
            <span class="info-label">Fecha de Resolucion:</span>
            <span class="info-value">{{ $override->resolved_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo de Resolucion:</span>
            <span class="info-value">{{ $override->resolution_type_label }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Resuelto por:</span>
            <span class="info-value">{{ $override->resolver->name ?? 'No especificado' }}</span>
        </div>
    </div>

    {{-- Firma --}}
    <div class="signatures">
        <div class="signature-line"></div>
        <div class="signature-name">{{ $override->resolver->name ?? 'Responsable' }}</div>
        <div class="signature-role">Comision de Seleccion CAS</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Documento generado por Sistema CAS - MDSJ | {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>

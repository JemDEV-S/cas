<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Convocatoria Completa' }}</title>
    <style>
        @page { margin: 2cm 1.5cm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #222;
        }

        /* ENCABEZADO */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2c3e50;
        }

        .header h1 {
            font-size: 13pt;
            color: #1a1a1a;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 10pt;
            color: #444;
            margin-bottom: 3px;
        }

        .header .code {
            font-size: 9pt;
            color: #666;
            font-weight: bold;
        }

        /* RESUMEN */
        .summary {
            background: #ecf0f1;
            padding: 8px 10px;
            margin-bottom: 12px;
            border-left: 4px solid #3498db;
            font-size: 8.5pt;
        }

        .summary-item {
            display: inline-block;
            margin-right: 15px;
            font-weight: bold;
        }

        .summary-item span {
            color: #2c3e50;
            font-size: 9.5pt;
        }

        /* TÍTULOS DE SECCIÓN */
        .section-title {
            background: #2c3e50;
            color: white;
            padding: 6px 10px;
            margin: 12px 0 8px 0;
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* TABLA DE PERFILES */
        .profiles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7.5pt;
        }

        .profiles-table thead {
            background: #34495e;
            color: white;
        }

        .profiles-table th {
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
            font-size: 7.5pt;
            vertical-align: middle;
        }

        .profiles-table td {
            padding: 5px 4px;
            border: 1px solid #bdc3c7;
            vertical-align: top;
        }

        .profiles-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .profiles-table tbody tr:hover {
            background: #e8f4f8;
        }

        .profile-code {
            font-weight: bold;
            color: #2c3e50;
            font-size: 8pt;
        }

        .vacancies {
            text-align: center;
            font-weight: bold;
            color: #27ae60;
            font-size: 9pt;
        }

        .salary {
            font-weight: bold;
            color: #c0392b;
            white-space: nowrap;
            font-size: 8pt;
        }

        .list-compact {
            margin: 0;
            padding-left: 12px;
            font-size: 7pt;
            line-height: 1.2;
        }

        .list-compact li {
            margin-bottom: 2px;
        }

        /* PIE DE PÁGINA */
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 7.5pt;
            color: #777;
        }

        .page-break {
            page-break-after: always;
        }

        .detail-box {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 8px;
            page-break-inside: avoid;
        }

        .detail-box h3 {
            color: #2c3e50;
            font-size: 9pt;
            margin-bottom: 6px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }

        .detail-label {
            color: #34495e;
            font-weight: bold;
            font-size: 8pt;
            margin-top: 5px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO PRINCIPAL -->
    <div class="header">
        <h1>MUNICIPALIDAD DISTRITAL DE SAN JUAN DE MIRAFLORES</h1>
        <div class="subtitle">{{ $convocatoria_nombre }}</div>
        <div class="code">{{ $convocatoria_codigo }} | {{ $proceso_nombre }} - {{ $año }}</div>
    </div>

    <!-- RESUMEN EJECUTIVO -->
    <div class="summary">
        <div class="summary-item">
            TOTAL PERFILES: <span>{{ $total_perfiles }}</span>
        </div>
        <div class="summary-item">
            TOTAL VACANTES: <span>{{ $total_vacantes }}</span>
        </div>
        <div class="summary-item">
            FECHA: <span>{{ $fecha_generacion }}</span>
        </div>
    </div>

    <!-- TABLA DE PERFILES CONVOCADOS -->
    <div class="section-title">I. Perfiles Convocados</div>

    @if(count($perfiles) > 0)
        <table class="profiles-table">
            <thead>
                <tr>
                    <th style="width: 7%;">CÓD.</th>
                    <th style="width: 18%;">CARGO</th>
                    <th style="width: 15%;">UNIDAD ORGÁNICA</th>
                    <th style="width: 5%;">VAC.</th>
                    <th style="width: 12%;">CONTRATO</th>
                    <th style="width: 13%;">EDUCACIÓN</th>
                    <th style="width: 10%;">EXPERIENCIA</th>
                    <th style="width: 10%;">REMUN.</th>
                    <th style="width: 10%;">UBICACIÓN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perfiles as $perfil)
                <tr>
                    <td class="profile-code">{{ $perfil['codigo'] }}</td>
                    <td>
                        <strong>{{ $perfil['titulo'] }}</strong><br>
                        <small style="font-size: 6.5pt;">{{ $perfil['nombre_perfil'] }}</small>
                    </td>
                    <td style="font-size: 7pt;">{{ $perfil['unidad_organica'] }}</td>
                    <td class="vacancies">{{ $perfil['vacantes'] }}</td>
                    <td style="font-size: 7pt;">
                        {{ $perfil['tipo_contrato'] }}<br>
                        <small style="font-size: 6.5pt;">{{ $perfil['regimen_laboral'] }}</small>
                    </td>
                    <td style="font-size: 7pt;">{{ $perfil['nivel_educativo'] }}</td>
                    <td style="font-size: 7pt;">
                        <strong>Gral:</strong> {{ $perfil['experiencia_general'] }} años<br>
                        <strong>Esp:</strong> {{ $perfil['experiencia_especifica'] }} años
                    </td>
                    <td class="salary">{{ $perfil['remuneracion'] }}</td>
                    <td style="font-size: 7pt;">{{ $perfil['ubicacion'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- DETALLE DE FUNCIONES Y COMPETENCIAS -->
        <div class="page-break"></div>

        <div class="section-title">II. Funciones y Competencias por Perfil</div>

        @foreach($perfiles as $index => $perfil)
            <div class="detail-box">
                <h3>{{ $perfil['codigo'] }} - {{ $perfil['titulo'] }}</h3>

                @if(!empty($perfil['funciones_principales']))
                    <div class="detail-label">FUNCIONES PRINCIPALES:</div>
                    <ul class="list-compact">
                        @foreach($perfil['funciones_principales'] as $funcion)
                            <li>{{ $funcion }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($perfil['competencias_requeridas']))
                    <div class="detail-label">COMPETENCIAS REQUERIDAS:</div>
                    <ul class="list-compact">
                        @foreach($perfil['competencias_requeridas'] as $competencia)
                            <li>{{ $competencia }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($perfil['conocimientos']))
                    <div class="detail-label">CONOCIMIENTOS:</div>
                    <ul class="list-compact">
                        @foreach($perfil['conocimientos'] as $conocimiento)
                            <li>{{ $conocimiento }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if(($index + 1) % 2 === 0 && ($index + 1) < count($perfiles))
                <div class="page-break"></div>
            @endif
        @endforeach
    @else
        <p style="text-align: center; padding: 30px; color: #999;">
            No hay perfiles aprobados para mostrar
        </p>
    @endif

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p><strong>Documento Oficial</strong> | Generado: {{ $fecha_generacion }}</p>
        <p>Municipalidad Distrital de San Juan de Miraflores | Sistema CAS</p>
    </div>
</body>
</html>

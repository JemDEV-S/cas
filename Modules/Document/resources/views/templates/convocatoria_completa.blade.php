<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Convocatoria Completa' }}</title>
    <style>
        @page {
            size: A4 portrait;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #222;
            margin: 20mm 15mm;
            padding: 0;
        }

        /* ENCABEZADO */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2c3e50;
        }

        .header h1 {
            font-size: 11pt;
            color: #1a1a1a;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 9pt;
            color: #444;
            margin-bottom: 3px;
        }

        .header .code {
            font-size: 8pt;
            color: #666;
            font-weight: bold;
        }

        /* CONTENEDOR DE PERFIL */
        .profile-page {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .profile-header {
            background: #2c3e50;
            color: white;
            padding: 10px;
            margin-bottom: 12px;
            text-align: center;
        }

        .profile-header h2 {
            font-size: 10pt;
            margin-bottom: 3px;
        }

        .profile-header .profile-code {
            font-size: 8pt;
            font-weight: normal;
        }

        /* TABLA DE DATOS DEL PERFIL */
        .profile-data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .profile-data-table th {
            background: #34495e;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 7.5pt;
            border: 1px solid #2c3e50;
            width: 30%;
            vertical-align: top;
        }

        .profile-data-table td {
            padding: 6px 8px;
            border: 1px solid #bdc3c7;
            font-size: 7.5pt;
            background: white;
            vertical-align: top;
        }

        .profile-data-table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        /* LISTAS DENTRO DE TABLA */
        .profile-list {
            margin: 3px 0 0 0;
            padding-left: 16px;
            line-height: 1.4;
        }

        .profile-list li {
            margin-bottom: 3px;
            font-size: 7pt;
        }

        /* VALORES DESTACADOS */
        .highlight-value {
            font-weight: bold;
            color: #2c3e50;
            font-size: 8pt;
        }

        .vacancies-badge {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8.5pt;
        }

        .salary-badge {
            display: inline-block;
            background: #c0392b;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8pt;
        }

        /* PIE DE PÁGINA */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 0;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 7pt;
            color: #777;
            background: white;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @if(count($perfiles) > 0)
        @foreach($perfiles as $index => $perfil)
            <!-- PÁGINA POR PERFIL -->
            <div class="profile-page">
                <!-- ENCABEZADO -->
                <div class="header">
                    <h1>MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO</h1>
                    <div class="subtitle">{{ $convocatoria_nombre }}</div>
                    <div class="code">{{ $convocatoria_codigo }} | {{ $proceso_nombre }} - {{ $año }}</div>
                </div>

                <!-- ENCABEZADO DEL PERFIL -->
                <div class="profile-header">
                    <h2>{{ $perfil['titulo'] }}</h2>
                    <div class="profile-code">CÓDIGO: {{ $perfil['codigo'] }}</div>
                </div>

                <!-- TABLA DE DATOS DEL PERFIL -->
                <table class="profile-data-table">
                    <tr>
                        <th>NOMBRE DEL PERFIL</th>
                        <td>{{ $perfil['nombre_perfil'] }}</td>
                    </tr>
                    @if(!empty($perfil['codigo_cargo']) || !empty($perfil['nombre_cargo']))
                    <tr>
                        <th>CÓDIGO DE CARGO</th>
                        <td><span class="highlight-value">{{ $perfil['codigo_cargo'] }}</span> - {{ $perfil['nombre_cargo'] }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>UNIDAD ORGÁNICA</th>
                        <td>{{ $perfil['unidad_organica'] }}</td>
                    </tr>
                    <tr>
                        <th>NÚMERO DE VACANTES</th>
                        <td><span class="vacancies-badge">{{ $perfil['vacantes'] }} {{ $perfil['vacantes'] == 1 ? 'VACANTE' : 'VACANTES' }}</span></td>
                    </tr>
                    <tr>
                        <th>RÉGIMEN LABORAL</th>
                        <td>{{ $perfil['regimen_laboral'] }}</td>
                    </tr>
                    <tr>
                        <th>REMUNERACIÓN MENSUAL</th>
                        <td><span class="salary-badge">{{ $perfil['remuneracion'] }}</span></td>
                    </tr>
                    <tr>
                        <th>UBICACIÓN</th>
                        <td>{{ $perfil['ubicacion'] }}</td>
                    </tr>
                    <tr>
                        <th>NIVEL EDUCATIVO</th>
                        <td>{{ $perfil['nivel_educativo'] }}</td>
                    </tr>
                    @if(!empty($perfil['area_estudios']))
                    <tr>
                        <th>ÁREA DE ESTUDIOS</th>
                        <td>{{ $perfil['area_estudios'] }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>EXPERIENCIA GENERAL</th>
                        <td><span class="highlight-value">{{ $perfil['experiencia_general'] }}</span></td>
                    </tr>
                    <tr>
                        <th>EXPERIENCIA ESPECÍFICA</th>
                        <td><span class="highlight-value">{{ $perfil['experiencia_especifica'] }}</span></td>
                    </tr>
                    @if(!empty($perfil['experiencia_especifica_descripcion']))
                    <tr>
                        <th>DESCRIPCIÓN DE EXPERIENCIA ESPECÍFICA</th>
                        <td>{{ $perfil['experiencia_especifica_descripcion'] }}</td>
                    </tr>
                    @endif

                    @if(!empty($perfil['funciones_principales']))
                    <tr>
                        <th>FUNCIONES PRINCIPALES</th>
                        <td>
                            <ul class="profile-list">
                                @foreach($perfil['funciones_principales'] as $funcion)
                                    <li>{{ $funcion }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @endif

                    @if(!empty($perfil['competencias_requeridas']))
                    <tr>
                        <th>COMPETENCIAS REQUERIDAS</th>
                        <td>
                            <ul class="profile-list">
                                @foreach($perfil['competencias_requeridas'] as $competencia)
                                    <li>{{ $competencia }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @endif

                    @if(!empty($perfil['conocimientos']))
                    <tr>
                        <th>CONOCIMIENTOS</th>
                        <td>
                            <ul class="profile-list">
                                @foreach($perfil['conocimientos'] as $conocimiento)
                                    <li>{{ $conocimiento }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @endif

                    @if(!empty($perfil['capacitaciones']))
                    <tr>
                        <th>CAPACITACIONES</th>
                        <td>
                            <ul class="profile-list">
                                @foreach($perfil['capacitaciones'] as $capacitacion)
                                    <li>{{ $capacitacion }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @endif
                </table>

                <!-- PIE DE PÁGINA -->
                <div style="margin-top: 20px; padding-top: 8px; border-top: 1px solid #bdc3c7; text-align: center; font-size: 7pt; color: #777;">
                    <p><strong>Documento Oficial</strong> | Generado: {{ $fecha_generacion }}</p>
                    <p>MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO | Sistema CAS</p>
                    <p>Perfil {{ $index + 1 }} de {{ $total_perfiles }}</p>
                </div>
            </div>
        @endforeach
    @else
        <div class="header">
            <h1>MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO</h1>
            <div class="subtitle">{{ $convocatoria_nombre }}</div>
            <div class="code">{{ $convocatoria_codigo }} | {{ $proceso_nombre }} - {{ $año }}</div>
        </div>
        <p style="text-align: center; padding: 30px; color: #999;">
            No hay perfiles aprobados para mostrar
        </p>
    @endif
</body>
</html>

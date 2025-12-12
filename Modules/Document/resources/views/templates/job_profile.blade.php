<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 2cm 1.5cm; }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header h2 {
            font-size: 16pt;
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-weight: bold;
        }
        .header .subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .header .code {
            font-size: 10pt;
            color: #666;
            margin-top: 8px;
        }
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            color: #fff;
            background-color: #2c3e50;
            padding: 6px 10px;
            margin: 12px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 9.5pt;
        }
        table, th, td {
            border: 1px solid #bdc3c7;
        }
        th, td {
            padding: 6px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #ecf0f1;
            font-weight: bold;
            color: #2c3e50;
            width: 35%;
        }
        td {
            background-color: #fff;
        }
        .footer {
            margin-top: 40px;
            font-size: 8.5pt;
            text-align: center;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
            color: #7f8c8d;
        }
        ul {
            margin: 3px 0;
            padding-left: 25px;
            line-height: 1.6;
        }
        ul li {
            margin-bottom: 4px;
        }
        .content-text {
            padding: 5px 10px;
            line-height: 1.6;
        }
        .two-column {
            display: flex;
            gap: 15px;
        }
        .two-column .column {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h2>ANEXO N° 2</h2>
        <div class="subtitle">Formato para Perfil del Puesto Solicitado</div>
        <div class="code">Código del Documento: {{ $code }}</div>
    </div>

    <!-- Datos Generales -->
    <div class="section">
        <div class="section-title">I. IDENTIFICACIÓN DEL PUESTO</div>
        <table>
            @if(isset($parent_organizational_unit) && $parent_organizational_unit)
            <tr>
                <th>Unidad Organizacional</th>
                <td>{{ $parent_organizational_unit }}</td>
            </tr>
            @endif
            <tr>
                <th>Unidad Solicitante</th>
                <td>{{ $requesting_unit }}</td>
            </tr>
            <tr>
                <th>Denominación del Puesto</th>
                <td>{{ $profile_name }}</td>
            </tr>
            <tr>
                <th>Cargo Requerido</th>
                <td><strong>{{ $required_position }}</strong></td>
            </tr>
            <tr>
                <th>Código del Puesto</th>
                <td>{{ $position_code }}</td>
            </tr>
            <tr>
                <th>Régimen Laboral</th>
                <td>{{ $work_regime }}</td>
            </tr>
            <tr>
                <th>Número de Vacantes</th>
                <td>{{ $total_vacancies }}</td>
            </tr>
            <tr>
                <th>Vigencia del Contrato</th>
                <td>{{ $contract_duration ?? '3 MESES' }}</td>
            </tr>
            @if(isset($work_location) && $work_location)
            <tr>
                <th>Lugar de Prestación</th>
                <td>{{ $work_location }}</td>
            </tr>
            @endif
            @if(isset($formatted_salary) && $formatted_salary !== 'NO ESPECIFICADO')
            <tr>
                <th>Remuneración Mensual</th>
                <td>{{ $formatted_salary }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Funciones Principales -->
    @if(!empty($main_functions))
    <div class="section">
        <div class="section-title">II. FUNCIONES ESPECÍFICAS A REALIZAR</div>
        <div class="content-text">
            <ul>
                @foreach($main_functions as $function)
                <li>{{ $function }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Requisitos Académicos -->
    <div class="section">
        <div class="section-title">III. REQUISITOS ACADÉMICOS</div>
        <table>
            <tr>
                <th>Nivel Educativo</th>
                <td>{{ $education_level }}</td>
            </tr>
            <tr>
                <th>Carrera Profesional</th>
                <td>{{ $career_field }}</td>
            </tr>
            <tr>
                <th>Título Requerido</th>
                <td>{{ $title_required }}</td>
            </tr>
            <tr>
                <th>Colegiatura</th>
                <td>{{ $colegiatura_required }}</td>
            </tr>
        </table>
    </div>

    <!-- Requisitos Generales del Cargo (Anexo 11) -->
    @if(isset($requisitos_generales) && $requisitos_generales !== 'No especificado')
    <div class="section">
        <div class="section-title">IV. REQUISITOS GENERALES DEL CARGO</div>
        <div class="content-text">
            <p style="margin: 0;">{{ $requisitos_generales }}</p>
        </div>
    </div>
    @endif

    <!-- Experiencia -->
    <div class="section">
        <div class="section-title">V. EXPERIENCIA</div>
        <table>
            <tr>
                <th>Experiencia General</th>
                <td>{{ $general_experience_years }}</td>
            </tr>
            <tr>
                <th>Experiencia Específica</th>
                <td>{{ $specific_experience_years }}</td>
            </tr>
            @if(isset($specific_experience_description) && $specific_experience_description)
            <tr>
                <th>Descripción de la Experiencia</th>
                <td>{{ $specific_experience_description }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Capacitaciones -->
    @if(!empty($required_courses))
    <div class="section">
        <div class="section-title">VI. CAPACITACIONES REQUERIDAS</div>
        <div class="content-text">
            <p style="margin: 0 0 5px 0;"><em>Es necesario acreditar con documentos como constancias, certificados o diplomas</em></p>
            <ul>
                @foreach($required_courses as $course)
                <li>{{ is_array($course) ? $course['name'] : $course }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Conocimientos y Competencias -->
    @if(!empty($knowledge_areas) || !empty($required_competencies))
    <div class="section">
        <div class="section-title">VII. CONOCIMIENTOS Y COMPETENCIAS</div>
        <div class="content-text">
            @if(!empty($knowledge_areas))
            <p style="margin: 8px 0 3px 0;"><strong>Conocimientos Requeridos:</strong></p>
            <ul>
                @foreach($knowledge_areas as $knowledge)
                <li>{{ $knowledge }}</li>
                @endforeach
            </ul>
            @endif

            @if(!empty($required_competencies))
            <p style="margin: 12px 0 3px 0;"><strong>Competencias Requeridas:</strong></p>
            <ul>
                @foreach($required_competencies as $competency)
                <li>{{ $competency }}</li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
    @endif

    <!-- Condiciones de Trabajo -->
    @if(isset($working_conditions) && $working_conditions)
    <div class="section">
        <div class="section-title">VIII. CONDICIONES DE TRABAJO</div>
        <div class="content-text">
            <p style="margin: 0;">{{ $working_conditions }}</p>
        </div>
    </div>
    @endif

    <!-- Justificación -->
    @if(isset($justification) && $justification)
    <div class="section">
        <div class="section-title">IX. JUSTIFICACIÓN</div>
        <div class="content-text">
            <p style="margin: 0;">{{ $justification }}</p>
        </div>
    </div>
    @endif

    <!-- Aprobaciones -->
    <div class="section">
        <div class="section-title">X. APROBACIONES Y VALIDACIONES</div>
        <table>
            <tr>
                <th width="25%">Solicitado por</th>
                <th width="35%">Nombre</th>
                <th width="40%">Fecha</th>
            </tr>
            <tr>
                <td><strong>Jefe de Área</strong></td>
                <td>{{ $requested_by ?? '___________________________' }}</td>
                <td>{{ $requested_at ?? '_______________' }}</td>
            </tr>
            <tr>
                <td><strong>Recursos Humanos</strong></td>
                <td>ISIS CUSI QOYLLOR QUISPE QUISPE </td>
                <td>{{ $reviewed_at ?? '_______________' }}</td>
            </tr>
        </table>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p>Documento generado automáticamente el {{ $generation_date }} a las {{ $generation_time }}</p>
        <p>Código de Documento: <strong>{{ $code }}</strong></p>
    </div>
</body>
</html>

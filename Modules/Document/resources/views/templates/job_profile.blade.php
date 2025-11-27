<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 12pt; color: #333; margin-top: 15px; margin-bottom: 10px; border-bottom: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .footer { margin-top: 50px; font-size: 9pt; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
        ul { margin: 5px 0; padding-left: 20px; }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h2>PERFIL DEL PUESTO</h2>
        <p><strong>{{ $profile_title }}</strong></p>
        <p>Código: {{ $code }}</p>
    </div>

    <!-- Datos Generales -->
    <div class="section">
        <div class="section-title">I. DATOS GENERALES</div>
        <table>
            <tr>
                <th width="30%">Código del Puesto</th>
                <td>{{ $position_code }}</td>
            </tr>
            <tr>
                <th>Denominación del Puesto</th>
                <td>{{ $profile_name }}</td>
            </tr>
            <tr>
                <th>Unidad Organizacional</th>
                <td>{{ $organizational_unit }}</td>
            </tr>
            <tr>
                <th>Unidad Solicitante</th>
                <td>{{ $requesting_unit }}</td>
            </tr>
            <tr>
                <th>Nivel del Puesto</th>
                <td>{{ $job_level }}</td>
            </tr>
            <tr>
                <th>Tipo de Contrato</th>
                <td>{{ $contract_type }}</td>
            </tr>
            <tr>
                <th>Rango Salarial</th>
                <td>{{ $salary_range }}</td>
            </tr>
            <tr>
                <th>Régimen Laboral</th>
                <td>{{ $work_regime }}</td>
            </tr>
            <tr>
                <th>Número de Vacantes</th>
                <td>{{ $total_vacancies }}</td>
            </tr>
        </table>
    </div>

    <!-- Misión del Puesto -->
    @if($mission)
    <div class="section">
        <div class="section-title">II. MISIÓN DEL PUESTO</div>
        <p>{{ $mission }}</p>
    </div>
    @endif

    <!-- Funciones Principales -->
    @if(!empty($main_functions))
    <div class="section">
        <div class="section-title">III. FUNCIONES PRINCIPALES</div>
        <ul>
            @foreach($main_functions as $function)
            <li>{{ $function }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Requisitos Académicos -->
    <div class="section">
        <div class="section-title">IV. REQUISITOS ACADÉMICOS</div>
        <table>
            <tr>
                <th width="30%">Nivel Educativo</th>
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

    <!-- Experiencia -->
    <div class="section">
        <div class="section-title">V. EXPERIENCIA</div>
        <table>
            <tr>
                <th width="30%">Experiencia General</th>
                <td>{{ $general_experience_years }} años</td>
            </tr>
            <tr>
                <th>Experiencia Específica</th>
                <td>{{ $specific_experience_years }} años</td>
            </tr>
            @if($specific_experience_description)
            <tr>
                <th>Descripción</th>
                <td>{{ $specific_experience_description }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Conocimientos y Competencias -->
    @if(!empty($knowledge_areas) || !empty($required_competencies))
    <div class="section">
        <div class="section-title">VI. CONOCIMIENTOS Y COMPETENCIAS</div>

        @if(!empty($knowledge_areas))
        <p><strong>Conocimientos:</strong></p>
        <ul>
            @foreach($knowledge_areas as $knowledge)
            <li>{{ $knowledge }}</li>
            @endforeach
        </ul>
        @endif

        @if(!empty($required_competencies))
        <p><strong>Competencias Requeridas:</strong></p>
        <ul>
            @foreach($required_competencies as $competency)
            <li>{{ $competency }}</li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif

    <!-- Condiciones de Trabajo -->
    @if($working_conditions)
    <div class="section">
        <div class="section-title">VII. CONDICIONES DE TRABAJO</div>
        <p>{{ $working_conditions }}</p>
    </div>
    @endif

    <!-- Justificación -->
    @if($justification)
    <div class="section">
        <div class="section-title">VIII. JUSTIFICACIÓN</div>
        <p>{{ $justification }}</p>
    </div>
    @endif

    <!-- Aprobaciones -->
    <div class="section">
        <div class="section-title">IX. APROBACIONES</div>
        <table>
            <tr>
                <th width="30%">Solicitado por</th>
                <td>{{ $requested_by }}</td>
                <td>{{ $requested_at }}</td>
            </tr>
            <tr>
                <th>Revisado por</th>
                <td>{{ $reviewed_by }}</td>
                <td>{{ $reviewed_at }}</td>
            </tr>
            <tr>
                <th>Aprobado por</th>
                <td>{{ $approved_by }}</td>
                <td>{{ $approved_at }}</td>
            </tr>
        </table>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p>Documento generado el {{ $generation_date }} a las {{ $generation_time }}</p>
        <p>Código de Documento: {{ $code }}</p>
    </div>
</body>
</html>

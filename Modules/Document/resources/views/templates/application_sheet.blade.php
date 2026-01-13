<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Postulación - {{ $application_code ?? '' }}</title>
    <style>
        @page {
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.3;
            color: #000;
            margin: 20mm 15mm;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #2c3e50;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 10pt;
            color: #34495e;
            margin-bottom: 3px;
        }

        .header .codes {
            font-size: 8pt;
            color: #7f8c8d;
            margin-top: 6px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            padding: 5px 8px;
            background-color: #ecf0f1;
            font-weight: bold;
            border: 1px solid #bdc3c7;
            font-size: 8pt;
        }

        .info-value {
            display: table-cell;
            width: 65%;
            padding: 5px 8px;
            border: 1px solid #bdc3c7;
            font-size: 8pt;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #2c3e50;
            color: white;
            padding: 6px 10px;
            font-size: 9.5pt;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .section-subtitle {
            background-color: #34495e;
            color: white;
            padding: 5px 8px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table th {
            background-color: #34495e;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
            font-size: 8pt;
        }

        table td {
            padding: 5px 6px;
            border: 1px solid #bdc3c7;
            font-size: 8pt;
            vertical-align: top;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .item-card {
            border: 1px solid #bdc3c7;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #ffffff;
        }

        .item-card-header {
            background-color: #ecf0f1;
            padding: 6px 8px;
            font-weight: bold;
            margin: -10px -10px 8px -10px;
            border-bottom: 2px solid #bdc3c7;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            margin-right: 4px;
        }

        .badge-specific {
            background-color: #3498db;
            color: white;
        }

        .badge-general {
            background-color: #95a5a6;
            color: white;
        }

        .badge-public {
            background-color: #27ae60;
            color: white;
        }

        .badge-private {
            background-color: #e67e22;
            color: white;
        }

        .badge-related {
            background-color: #9b59b6;
            color: white;
        }

        .badge-bonus {
            background-color: #e74c3c;
            color: white;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7pt;
            color: #7f8c8d;
            padding-top: 10px;
            border-top: 1px solid #bdc3c7;
        }

        .disclaimer {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            color: #856404;
        }

        .no-data {
            color: #7f8c8d;
            font-style: italic;
            text-align: center;
            padding: 12px;
            font-size: 8pt;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        ul {
            margin-left: 20px;
            margin-top: 5px;
        }

        li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ficha de Postulación</h1>
        <h2>{{ $job_posting_title ?? 'Proceso CAS' }}</h2>
        <div class="codes">
            <strong>Código de Postulación:</strong> {{ $application_code ?? 'N/A' }} |
            <strong>Fecha:</strong> {{ $application_date ?? date('d/m/Y') }} |
            <strong>Convocatoria:</strong> {{ $job_posting_code ?? 'N/A' }}
        </div>
    </div>

    <!-- SECCIÓN 1: DATOS PERSONALES -->
    <div class="section">
        <div class="section-title">1. Datos Personales</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombres y Apellidos Completos:</div>
                <div class="info-value">{{ strtoupper($full_name ?? 'N/A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">DNI:</div>
                <div class="info-value">{{ $dni ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Nacimiento:</div>
                <div class="info-value">{{ $birth_date ?? 'N/A' }} ({{ $age ?? 'N/A' }} años)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Correo Electrónico:</div>
                <div class="info-value">{{ $email ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Teléfono / Celular:</div>
                <div class="info-value">{{ $phone ?? $mobile_phone ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Dirección:</div>
                <div class="info-value">{{ $address ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: PERFIL AL QUE POSTULA -->
    <div class="section">
        <div class="section-title">2. Perfil al que Postula</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre del Perfil:</div>
                <div class="info-value">{{ strtoupper($job_profile_name ?? 'N/A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Código de Perfil:</div>
                <div class="info-value">{{ $profile_code ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: FORMACIÓN ACADÉMICA -->
    <div class="section">
        <div class="section-title">3. Formación Académica</div>
        @if(!empty($academics) && count($academics) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Institución</th>
                        <th style="width: 22%;">Nivel / Grado</th>
                        <th style="width: 38%;">Carrera / Especialidad</th>
                        <th style="width: 10%;">Año</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($academics as $academic)
                    <tr>
                        <td>{{ strtoupper($academic['institution_name'] ?? $academic['institutionName'] ?? 'N/A') }}</td>
                        <td>{{ strtoupper($academic['degree_type_label'] ?? $academic['degreeTypeLabel'] ?? $academic['degree_type'] ?? $academic['degreeType'] ?? 'N/A') }}</td>
                        <td>
                            @if(!empty($academic['is_related_career']) || !empty($academic['isRelatedCareer']))
                                <span class="badge badge-related">AFÍN</span>
                                {{ strtoupper($academic['related_career_name'] ?? $academic['relatedCareerName'] ?? $academic['career_field'] ?? $academic['careerField'] ?? 'N/A') }}
                            @else
                                {{ strtoupper($academic['career_field'] ?? $academic['careerField'] ?? $academic['degree_title'] ?? $academic['degreeTitle'] ?? 'N/A') }}
                            @endif
                        </td>
                        <td class="text-center">{{ $academic['issue_date'] ?? $academic['issueDate'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No se registró información académica</div>
        @endif
    </div>

    <!-- SECCIÓN 4: EXPERIENCIA LABORAL -->
    <div class="section">
        <div class="section-title">4. Experiencia Laboral</div>

        @if(!empty($experiences) && count($experiences) > 0)
            <!-- Experiencia General -->
            @php
                $generalExps = array_filter($experiences, fn($exp) => empty($exp['is_specific']) || $exp['is_specific'] === false);
                $specificExps = array_filter($experiences, fn($exp) => !empty($exp['is_specific']) && $exp['is_specific'] === true);
            @endphp

            @if(count($generalExps) > 0)
                <div class="section-subtitle">4.1 Experiencia General</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Organización</th>
                            <th style="width: 25%;">Cargo</th>
                            <th style="width: 18%;">Fecha Inicio</th>
                            <th style="width: 18%;">Fecha Fin</th>
                            <th style="width: 14%;">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($generalExps as $exp)
                        <tr>
                            <td>{{ strtoupper($exp['organization'] ?? 'N/A') }}</td>
                            <td>{{ strtoupper($exp['position'] ?? 'N/A') }}</td>
                            <td class="text-center">{{ $exp['start_date'] ?? $exp['startDate'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $exp['end_date'] ?? $exp['endDate'] ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if(!empty($exp['is_public_sector']) || !empty($exp['isPublicSector']))
                                    <span class="badge badge-public">PÚBLICO</span>
                                @else
                                    <span class="badge badge-private">PRIVADO</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if(!empty($total_general_experience) && !empty($total_general_experience['formatted']))
                        <tr style="background-color: #ecf0f1; font-weight: bold;">
                            <td colspan="4" class="text-right">TOTAL EXPERIENCIA GENERAL:</td>
                            <td class="text-center">{{ strtoupper($total_general_experience['formatted']) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            @endif

            <!-- Experiencia Específica -->
            @if(count($specificExps) > 0)
                <div class="section-subtitle">4.2 Experiencia Específica</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Organización</th>
                            <th style="width: 25%;">Cargo</th>
                            <th style="width: 18%;">Fecha Inicio</th>
                            <th style="width: 18%;">Fecha Fin</th>
                            <th style="width: 14%;">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($specificExps as $exp)
                        <tr>
                            <td>{{ strtoupper($exp['organization'] ?? 'N/A') }}</td>
                            <td>{{ strtoupper($exp['position'] ?? 'N/A') }}</td>
                            <td class="text-center">{{ $exp['start_date'] ?? $exp['startDate'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $exp['end_date'] ?? $exp['endDate'] ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if(!empty($exp['is_public_sector']) || !empty($exp['isPublicSector']))
                                    <span class="badge badge-public">PÚBLICO</span>
                                @else
                                    <span class="badge badge-private">PRIVADO</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @if(!empty($total_specific_experience) && !empty($total_specific_experience['formatted']))
                        <tr style="background-color: #ecf0f1; font-weight: bold;">
                            <td colspan="4" class="text-right">TOTAL EXPERIENCIA ESPECÍFICA:</td>
                            <td class="text-center">{{ strtoupper($total_specific_experience['formatted']) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            @endif
        @else
            <div class="no-data">No se registró experiencia laboral</div>
        @endif
    </div>

    <!-- SECCIÓN 5: CAPACITACIONES Y CURSOS -->
    <div class="section">
        <div class="section-title">5. Capacitaciones y Cursos</div>
        @if(!empty($trainings) && count($trainings) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Institución</th>
                        <th style="width: 40%;">Nombre del Curso</th>
                        <th style="width: 15%;">Horas</th>
                        <th style="width: 15%;">Año</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trainings as $training)
                    <tr>
                        <td>{{ strtoupper($training['institution'] ?? $training['institution_name'] ?? 'N/A') }}</td>
                        <td>{{ strtoupper($training['course_name'] ?? $training['courseName'] ?? 'N/A') }}</td>
                        <td class="text-center">{{ $training['academic_hours'] ?? $training['academicHours'] ?? $training['hours'] ?? 'N/A' }}</td>
                        <td class="text-center">
                            @php
                                $year = $training['start_date'] ?? $training['startDate'] ?? '';
                                echo $year ? date('Y', strtotime($year)) : 'N/A';
                            @endphp
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No se registraron capacitaciones</div>
        @endif
    </div>

    <!-- SECCIÓN 6: CONOCIMIENTOS TÉCNICOS -->
    <div class="section">
        <div class="section-title">6. Conocimientos Técnicos</div>
        @if(!empty($knowledge) && count($knowledge) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Conocimiento / Área</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($knowledge as $k)
                    <tr>
                        <td>{{ strtoupper($k['knowledge_name'] ?? $k['knowledgeName'] ?? 'N/A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No se registraron conocimientos técnicos</div>
        @endif
    </div>

    <!-- SECCIÓN 7: REGISTROS PROFESIONALES (solo si existen) -->
    @if(!empty($professional_registrations) && count($professional_registrations) > 0)
    <div class="section">
        <div class="section-title">7. Registros Profesionales</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Tipo de Registro</th>
                    <th style="width: 30%;">Número</th>
                    <th style="width: 30%;">Institución</th>
                </tr>
            </thead>
            <tbody>
                @foreach($professional_registrations as $reg)
                <tr>
                    <td>{{ strtoupper($reg['type'] ?? 'N/A') }}</td>
                    <td>{{ $reg['number'] ?? 'N/A' }}</td>
                    <td>{{ strtoupper($reg['institution'] ?? 'N/A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- SECCIÓN 8: CONDICIONES ESPECIALES (solo si existen) -->
    @if(!empty($special_conditions) && count($special_conditions) > 0)
    <div class="section">
        <div class="section-title">8. Condiciones Especiales / Bonificaciones</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">Tipo de Condición Especial</th>
                    <th style="width: 30%;">Bonificación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($special_conditions as $condition)
                <tr>
                    <td>{{ strtoupper($condition['type'] ?? 'N/A') }}</td>
                    <td class="text-center">
                        <span class="badge badge-bonus">{{ $condition['bonus_percentage'] ?? $condition['bonusPercentage'] ?? '0' }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- DISCLAIMER IMPORTANTE -->
    <div class="disclaimer">
        LA VALORACIÓN DE LOS MÉRITOS Y ANTECEDENTES, SE REALIZARÁN CONTRA LA INFORMACIÓN CONTENIDA EN LOS CERTIFICADOS, CONSTANCIAS Y/O TODA LA DOCUMENTACIÓN PRESENTADA DE ACUERDO A LO ESTABLECIDO EN LAS BASES.
    </div>

    <div class="footer">
        <p><strong>Municipalidad Distrital de San Jerónimo</strong></p>
        <p>Documento generado automáticamente por el Sistema de Gestión CAS</p>
        <p>{{ $generation_date ?? date('d/m/Y') }} - {{ $generation_time ?? date('H:i:s') }}</p>
    </div>
</body>
</html>

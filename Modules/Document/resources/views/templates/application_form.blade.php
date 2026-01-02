<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Postulación - CAS {{ date('Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #3484A5;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 16pt;
            color: #3484A5;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 12pt;
            color: #666;
            font-weight: normal;
        }

        .code-box {
            background: #f0f0f0;
            border: 2px solid #3484A5;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
        }

        .code-box strong {
            font-size: 14pt;
            color: #3484A5;
        }

        .section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 12px;
        }

        .section-title {
            background: #3484A5;
            color: white;
            padding: 8px 12px;
            margin: -12px -12px 12px -12px;
            font-size: 11pt;
            font-weight: bold;
        }

        .field-group {
            margin-bottom: 8px;
        }

        .field-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            min-width: 150px;
        }

        .field-value {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
        }

        table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 9pt;
        }

        .declaration-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px;
            margin-top: 20px;
            font-size: 9pt;
            font-style: italic;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 10px;
        }

        hr {
            border: none;
            border-top: 2px dashed #ddd;
            margin: 15px 0;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>FICHA DE POSTULACIÓN - CAS {{ date('Y') }}</h1>
        <h2>Municipalidad Distrital de San Jerónimo</h2>
        <h2>Provincia de Cusco - Departamento de Cusco</h2>
    </div>

    <!-- Código de Postulación -->
    <div class="code-box">
        <strong>CÓDIGO DE POSTULACIÓN:</strong> {{ $application->code }}<br>
        <span style="font-size: 9pt;">Fecha: {{ $application->application_date->format('d/m/Y H:i') }}</span>
    </div>

    <!-- I. DATOS DE LA CONVOCATORIA -->
    <div class="section">
        <div class="section-title">I. DATOS DE LA CONVOCATORIA</div>

        <div class="field-group">
            <span class="field-label">Convocatoria:</span>
            <span class="field-value">{{ $posting->code }} - {{ $posting->title }}</span>
        </div>

        <div class="field-group">
            <span class="field-label">Perfil:</span>
            <span class="field-value">{{ $profile->profile_name }}</span>
        </div>

        <div class="field-group">
            <span class="field-label">Código de Perfil:</span>
            <span class="field-value">{{ $profile->code }}</span>
        </div>

        <div class="field-group">
            <span class="field-label">Unidad Solicitante:</span>
            <span class="field-value">{{ $profile->requestingUnit->name ?? 'N/A' }}</span>
        </div>

        <div class="field-group">
            <span class="field-label">Unidad Orgánica:</span>
            <span class="field-value">{{ $profile->organizationalUnit->name ?? 'N/A' }}</span>
        </div>

        <div class="field-group">
            <span class="field-label">Remuneración Mensual:</span>
            <span class="field-value">S/ {{ number_format($profile->positionCode->base_salary ?? 0, 2) }}</span>
        </div>

        <div class="field-group">
            <span class="field-label">Tipo de Contrato:</span>
            <span class="field-value">{{ $profile->contract_type ?? 'CAS' }}</span>
        </div>
    </div>

    <!-- II. DATOS PERSONALES -->
    <div class="section">
        <div class="section-title">II. DATOS PERSONALES DEL POSTULANTE</div>

        <div class="field-group">
            <span class="field-label">Nombres y Apellidos:</span>
            <span class="field-value">{{ $application->full_name }}</span>
        </div>

        <div class="two-column">
            <div class="column">
                <div class="field-group">
                    <span class="field-label">DNI:</span>
                    <span class="field-value">{{ $application->dni }}</span>
                </div>
            </div>
            <div class="column">
                <div class="field-group">
                    <span class="field-label">Fecha de Nacimiento:</span>
                    <span class="field-value">{{ $application->birth_date ? \Carbon\Carbon::parse($application->birth_date)->format('d/m/Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="field-group">
            <span class="field-label">Dirección:</span>
            <span class="field-value">{{ $application->address }}</span>
        </div>

        <div class="two-column">
            <div class="column">
                <div class="field-group">
                    <span class="field-label">Email:</span>
                    <span class="field-value">{{ $application->email }}</span>
                </div>
            </div>
            <div class="column">
                <div class="field-group">
                    <span class="field-label">Celular:</span>
                    <span class="field-value">{{ $application->mobile_phone }}</span>
                </div>
            </div>
        </div>

        @if($application->phone)
        <div class="field-group">
            <span class="field-label">Teléfono Fijo:</span>
            <span class="field-value">{{ $application->phone }}</span>
        </div>
        @endif
    </div>

    <!-- III. FORMACIÓN ACADÉMICA DECLARADA -->
    <div class="section">
        <div class="section-title">III. FORMACIÓN ACADÉMICA DECLARADA</div>

        @if($application->academics && $application->academics->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Grado/Título</th>
                        <th width="35%">Institución</th>
                        <th width="30%">Carrera/Especialidad</th>
                        <th width="10%">Año</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($application->academics as $index => $academic)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $academic->degree_type }}</td>
                        <td>{{ $academic->institution }}</td>
                        <td>{{ $academic->career_field }}</td>
                        <td>{{ $academic->year }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="font-style: italic; color: #666;">No se declaró formación académica.</p>
        @endif
    </div>

    <!-- IV. EXPERIENCIA LABORAL DECLARADA -->
    <div class="section">
        <div class="section-title">IV. EXPERIENCIA LABORAL DECLARADA</div>

        @php
            $totalGeneralMonths = 0;
            $totalSpecificMonths = 0;

            foreach($application->experiences as $exp) {
                if ($exp->start_date) {
                    $start = \Carbon\Carbon::parse($exp->start_date);
                    $end = $exp->end_date ? \Carbon\Carbon::parse($exp->end_date) : \Carbon\Carbon::now();
                    $months = $start->diffInMonths($end);

                    $totalGeneralMonths += $months;
                    if ($exp->is_specific) {
                        $totalSpecificMonths += $months;
                    }
                }
            }

            $generalYears = floor($totalGeneralMonths / 12);
            $generalMonths = $totalGeneralMonths % 12;

            $specificYears = floor($totalSpecificMonths / 12);
            $specificMonths = $totalSpecificMonths % 12;
        @endphp

        <div style="background: #f5f5f5; padding: 10px; margin-bottom: 10px; border-radius: 4px;">
            <strong>Experiencia General Total:</strong> {{ $generalYears }} año(s), {{ $generalMonths }} mes(es)<br>
            <strong>Experiencia Específica Total:</strong> {{ $specificYears }} año(s), {{ $specificMonths }} mes(es)
        </div>

        @if($application->experiences && $application->experiences->count() > 0)
            @foreach($application->experiences as $index => $exp)
            <div style="border: 1px solid #e0e0e0; padding: 8px; margin-bottom: 8px; border-radius: 4px;">
                <div style="margin-bottom: 4px;">
                    <strong>{{ $index + 1 }}. {{ $exp->organization }} - {{ $exp->position }}</strong>
                    @if($exp->is_public_sector)
                        <span class="badge badge-info">Sector Público</span>
                    @endif
                    @if($exp->is_specific)
                        <span class="badge badge-success">Experiencia Específica</span>
                    @endif
                </div>

                <div class="two-column" style="font-size: 9pt;">
                    <div class="column">
                        <strong>Periodo:</strong> {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('m/Y') : 'N/A' }} -
                        {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('m/Y') : 'Actualidad' }}
                    </div>
                    <div class="column">
                        @php
                            if ($exp->start_date) {
                                $start = \Carbon\Carbon::parse($exp->start_date);
                                $end = $exp->end_date ? \Carbon\Carbon::parse($exp->end_date) : \Carbon\Carbon::now();
                                $months = $start->diffInMonths($end);
                                $years = floor($months / 12);
                                $remainingMonths = $months % 12;
                                $duration = "{$years} año(s), {$remainingMonths} mes(es)";
                            } else {
                                $duration = 'N/A';
                            }
                        @endphp
                        <strong>Duración:</strong> {{ $duration }}
                    </div>
                </div>

                @if($exp->description)
                <div style="margin-top: 4px; font-size: 9pt; color: #555;">
                    <strong>Funciones:</strong> {{ $exp->description }}
                </div>
                @endif
            </div>
            @endforeach
        @else
            <p style="font-style: italic; color: #666;">No se declaró experiencia laboral.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- V. CAPACITACIONES Y CURSOS DECLARADOS -->
    <div class="section">
        <div class="section-title">V. CAPACITACIONES Y CURSOS DECLARADOS</div>

        @if($application->trainings && $application->trainings->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="40%">Nombre del Curso</th>
                        <th width="30%">Institución</th>
                        <th width="15%">Horas</th>
                        <th width="10%">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($application->trainings as $index => $training)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $training->course_name }}</td>
                        <td>{{ $training->institution }}</td>
                        <td>{{ $training->hours }}h</td>
                        <td>{{ $training->certification_date ? \Carbon\Carbon::parse($training->certification_date)->format('m/Y') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @php
                $totalHours = $application->trainings->sum('hours');
            @endphp
            <div style="text-align: right; margin-top: 8px; font-weight: bold;">
                Total de horas de capacitación: {{ $totalHours }} horas
            </div>
        @else
            <p style="font-style: italic; color: #666;">No se declararon capacitaciones.</p>
        @endif
    </div>

    <!-- VI. CONOCIMIENTOS TÉCNICOS -->
    @if($application->knowledge && $application->knowledge->count() > 0)
    <div class="section">
        <div class="section-title">VI. CONOCIMIENTOS TÉCNICOS DECLARADOS</div>

        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="60%">Área de Conocimiento</th>
                    <th width="35%">Nivel Declarado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($application->knowledge as $index => $know)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $know->area }}</td>
                    <td>{{ $know->level }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- VII. REGISTROS PROFESIONALES -->
    @if($application->professionalRegistrations && $application->professionalRegistrations->count() > 0)
    <div class="section">
        <div class="section-title">VII. REGISTROS PROFESIONALES DECLARADOS</div>

        @foreach($application->professionalRegistrations as $reg)
        <div class="field-group">
            <span class="field-label">{{ $reg->type }}:</span>
            <span class="field-value">
                {{ $reg->number }}
                @if($reg->institution)
                    - {{ $reg->institution }}
                @endif
                @if($reg->category)
                    ({{ $reg->category }})
                @endif
                @if($reg->expiry_date)
                    - Vigencia: {{ \Carbon\Carbon::parse($reg->expiry_date)->format('d/m/Y') }}
                @endif
            </span>
        </div>
        @endforeach
    </div>
    @endif

    <!-- VIII. CONDICIONES ESPECIALES (BONIFICACIONES) -->
    @if($application->specialConditions && $application->specialConditions->count() > 0)
    <div class="section">
        <div class="section-title">VIII. CONDICIONES ESPECIALES DECLARADAS</div>

        <p style="margin-bottom: 8px; font-size: 9pt; color: #666;">
            <strong>Nota:</strong> Las bonificaciones se aplicarán según la normativa vigente, previa verificación de documentos sustentatorios en la Fase 5.
        </p>

        <table>
            <thead>
                <tr>
                    <th width="60%">Condición Especial</th>
                    <th width="20%">Bonificación</th>
                    <th width="20%">Base Legal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($application->specialConditions as $condition)
                <tr>
                    <td>{{ $condition->type }}</td>
                    <td>{{ $condition->bonus_percentage }}%</td>
                    <td style="font-size: 8pt;">
                        @switch($condition->type)
                            @case('DISCAPACIDAD')
                                Ley N° 29973
                                @break
                            @case('LICENCIADO_FFAA')
                                Ley N° 29248
                                @break
                            @case('DEPORTISTA_DESTACADO')
                            @case('DEPORTISTA_CALIFICADO')
                                Ley N° 28036
                                @break
                        @endswitch
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $totalBonus = $application->specialConditions->sum('bonus_percentage');
        @endphp

        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 8px; margin-top: 8px; border-radius: 4px;">
            <strong>Bonificación Total Declarada:</strong> {{ $totalBonus }}%
        </div>
    </div>
    @endif

    <hr>

    <!-- DECLARACIÓN JURADA -->
    <div class="declaration-box">
        <h3 style="text-align: center; margin-bottom: 10px; font-size: 11pt;">DECLARACIÓN JURADA</h3>

        <p style="text-align: justify; margin-bottom: 8px;">
            Yo, <strong>{{ $application->full_name }}</strong>, identificado(a) con DNI N° <strong>{{ $application->dni }}</strong>,
            declaro bajo juramento que toda la información proporcionada en la presente ficha de postulación es verdadera,
            completa y puede ser verificada mediante documentos sustentatorios en la siguiente fase del proceso de selección.
        </p>

        <p style="text-align: justify; margin-bottom: 8px;">
            Asimismo, declaro conocer que cualquier información falsa, inexacta o documentación fraudulenta presentada
            dará lugar a mi descalificación automática del proceso de selección, sin perjuicio de las acciones legales
            que correspondan conforme a la normativa vigente.
        </p>

        <p style="text-align: justify; margin-bottom: 8px;">
            <strong>IMPORTANTE:</strong> Esta ficha de postulación es solo un comprobante de inscripción en la
            <strong>Fase 3 (Registro Virtual de Postulantes)</strong>. Los documentos sustentatorios (títulos, certificados,
            constancias de trabajo, etc.) serán solicitados únicamente a los postulantes que resulten
            <strong>APTOS</strong> en el proceso de evaluación preliminar, durante la
            <strong>Fase 5 (Presentación de CV Documentado)</strong>.
        </p>

        <div style="margin-top: 20px; text-align: center;">
            <p>_________________________________</p>
            <p><strong>{{ $application->full_name }}</strong></p>
            <p>DNI: {{ $application->dni }}</p>
        </div>
    </div>

    <!-- INFORMACIÓN DE VERIFICACIÓN -->
    <div class="section" style="margin-top: 20px;">
        <div class="section-title">INFORMACIÓN DE VERIFICACIÓN</div>

        <div class="two-column">
            <div class="column">
                <div class="field-group">
                    <span class="field-label">Hash de Verificación:</span><br>
                    <code style="font-size: 8pt;">{{ md5($application->id . $application->code . $application->created_at) }}</code>
                </div>
            </div>
            <div class="column">
                <div class="field-group">
                    <span class="field-label">IP de Registro:</span>
                    <span class="field-value">{{ $application->ip_address ?? 'N/A' }}</span>
                </div>
                <div class="field-group">
                    <span class="field-label">Fecha de Envío:</span>
                    <span class="field-value">{{ $application->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Documento generado electrónicamente el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Municipalidad Distrital de San Jerónimo - Proceso CAS {{ date('Y') }}</p>
        <p>Este documento es válido únicamente para el proceso de selección al que postula</p>
    </div>

</body>
</html>

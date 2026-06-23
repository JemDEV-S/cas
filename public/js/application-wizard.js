function applicationWizard() {
    return {
        currentStep: 1,
        lastSaved: null,
        showEducationHelp: false,
        isSubmitting: false,
        showAutocompleteModal: false,

        acceptedCareerIds: window.wizardConfig.acceptedCareerIds,
        educationLevels: window.wizardConfig.educationLevels,
        minimumEducationLevel: window.wizardConfig.minimumEducationLevel,
        minimumEducationLevelValue: window.wizardConfig.minimumEducationLevelValue,

        // ============================================
        // SISTEMA DE VALIDACIÓN
        // ============================================
        validationErrors: [],
        touchedFields: {},

        validationRules: {
            'personal.fullName': { required: true, label: 'Nombre Completo' },
            'personal.dni': { required: true, pattern: /^[0-9]{8}$/, label: 'DNI', message: 'El DNI debe tener exactamente 8 dígitos' },
            'personal.birthDate': { required: true, type: 'date', label: 'Fecha de Nacimiento' },
            'personal.address': { required: true, minLength: 4, label: 'Dirección', message: 'La dirección debe tener al menos 10 caracteres' },
            'personal.phone': { required: true, pattern: /^[0-9\-\s]{7,15}$/, label: 'Teléfono', message: 'Ingrese un número de teléfono válido' },
            'personal.email': { required: true, type: 'email', label: 'Email', message: 'Ingrese un email válido' },
            'academic.degreeType': { required: true, label: 'Grado Académico' },
            'academic.institution': { required: true, minLength: 3, label: 'Institución Educativa', message: 'El nombre de la institución es muy corto' },
            'academic.careerId': { required: false, label: 'Carrera Profesional' },
            'academic.relatedCareerName': { required: false, minLength: 5, label: 'Nombre de Carrera Afín', message: 'El nombre de la carrera debe tener al menos 5 caracteres' },
            'academic.year': { required: true, type: 'year', min: 1950, label: 'Año de Graduación', message: 'Ingrese un año válido' },
            'experience.organization': { required: true, minLength: 3, label: 'Empresa/Organización', message: 'El nombre de la organización es muy corto' },
            'experience.position': { required: true, minLength: 3, label: 'Cargo/Puesto', message: 'El nombre del cargo es muy corto' },
            'experience.startDate': { required: true, type: 'date', label: 'Fecha de Inicio' },
            'experience.endDate': { required: false, type: 'date', label: 'Fecha de Fin' },
            'training.courseName': { required: true, minLength: 5, label: 'Nombre del Curso', message: 'El nombre del curso es muy corto' },
            'training.institution': { required: true, minLength: 3, label: 'Institución', message: 'El nombre de la institución es muy corto' },
            'training.hours': { required: true, type: 'number', min: 1, label: 'Horas', message: 'Las horas deben ser mayor a 0' },
            'training.certificationDate': { required: true, type: 'month', label: 'Fecha de Certificación' },
            'registration.colegiatura.college': { required: false, minLength: 5, label: 'Colegio Profesional' },
            'registration.colegiatura.number': { required: false, minLength: 3, label: 'Número de Colegiatura' },
            'requiredCourse.institution': { required: true, minLength: 3, label: 'Institución', message: 'El nombre de la institución es muy corto' },
            'requiredCourse.year': { required: true, type: 'year', min: 1990, label: 'Año', message: 'Ingrese un año válido' },
            'requiredCourse.hours': { required: true, type: 'number', min: 1, label: 'Horas', message: 'Las horas deben ser mayor a 0' },
            'requiredCourse.relatedCourseName': { required: true, minLength: 5, label: 'Nombre de Capacitación Afín', message: 'El nombre debe tener al menos 5 caracteres' },
        },

        formData: {
            personal: {
                fullName: window.wizardConfig.user.fullName,
                dni: window.wizardConfig.user.dni,
                birthDate: window.wizardConfig.user.birthDate,
                address: window.wizardConfig.user.address,
                phone: window.wizardConfig.user.phone,
                email: window.wizardConfig.user.email,
            },
            academics: [{
                degreeType: '',
                institution: '',
                careerId: '',
                careerField: '',
                isRelatedCareer: false,
                relatedCareerName: '',
                year: ''
            }],
            experiences: [{
                organization: '',
                position: '',
                startDate: '',
                endDate: '',
                isCurrent: false,
                isPublicSector: false,
                isSpecific: false,
                description: ''
            }],
            requiredCoursesCompliance: window.wizardConfig.requiredCoursesComplianceInitial,
            additionalTrainings: [],
            knowledgeCompliance: window.wizardConfig.knowledgeComplianceInitial,
            otherKnowledge: '',
            registrations: {
                colegiatura: {
                    habilitado: false,
                    college: '',
                    number: ''
                },
                osce: '',
                license: {
                    number: '',
                    category: '',
                    expiryDate: ''
                }
            },
            specialConditions: {
                disability: false,
                military: false,
                athleteNational: false,
                athleteIntl: false
            },
            declarationAccepted: false,
            termsAccepted: false
        },

        init() {
            const draftKey = 'applicationDraft_' + window.wizardConfig.userId + '_' + window.wizardConfig.jobProfileId;

            if (window.wizardConfig.draftApplicationData) {
                // Edición de borrador guardado en BD: tiene prioridad sobre todo
                this.applyPreviousData(window.wizardConfig.draftApplicationData);
                // Sincronizar localStorage con los datos del borrador
                this.autoSave();
            } else if (localStorage.getItem(draftKey)) {
                this.loadFromLocalStorage();
            } else if (window.wizardConfig.previousApplicationData) {
                this.showAutocompleteModal = true;
            }

            window.clearApplicationDraft = () => {
                localStorage.removeItem(draftKey);
                console.log('✅ Borrador eliminado. Recarga la página para ver datos frescos.');
            };

            setInterval(() => { this.autoSave(); }, 30000);
        },

        applyPreviousData(data) {
            if (!data) return;
            if (data.academics && data.academics.length > 0)
                this.formData.academics = data.academics;
            if (data.experiences && data.experiences.length > 0)
                this.formData.experiences = data.experiences;
            if (data.registrations)
                this.formData.registrations = data.registrations;
            if (data.specialConditions)
                this.formData.specialConditions = data.specialConditions;
        },

        acceptAutocomplete() {
            this.applyPreviousData(window.wizardConfig.previousApplicationData);
            this.showAutocompleteModal = false;
            this.autoSave();
        },

        rejectAutocomplete() {
            this.showAutocompleteModal = false;
        },

        getStepName(step) {
            const names = {
                1: 'Personal',
                2: 'Académica',
                3: 'Experiencia',
                4: 'Capacitación',
                5: 'Conocimientos',
                6: 'Registros',
                7: 'Bonificaciones',
                8: 'Confirmación'
            };
            return names[step] || '';
        },

        // ============================================
        // FUNCIONES DE VALIDACIÓN
        // ============================================

        markTouched(fieldKey) {
            this.touchedFields[fieldKey] = true;
        },

        validateValue(value, rules) {
            const errors = [];

            if (rules.required && (!value || (typeof value === 'string' && value.trim() === ''))) {
                errors.push(rules.message || `${rules.label} es requerido`);
                return errors;
            }

            if (!value || (typeof value === 'string' && value.trim() === '')) {
                return errors;
            }

            if (rules.pattern && !rules.pattern.test(value)) {
                errors.push(rules.message || `${rules.label} tiene un formato inválido`);
            }

            if (rules.type === 'email') {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(value)) {
                    errors.push(rules.message || 'Ingrese un email válido');
                }
            }

            if (rules.type === 'date') {
                const date = new Date(value);
                if (isNaN(date.getTime())) {
                    errors.push(rules.message || 'Ingrese una fecha válida');
                }
            }

            if (rules.type === 'month') {
                const monthPattern = /^\d{4}-(0[1-9]|1[0-2])$/;
                if (!monthPattern.test(value)) {
                    errors.push(rules.message || 'Ingrese un mes válido (YYYY-MM)');
                }
            }

            if (rules.type === 'year') {
                const year = parseInt(value);
                const currentYear = new Date().getFullYear();
                if (isNaN(year) || year < (rules.min || 1900) || year > currentYear) {
                    errors.push(rules.message || `Ingrese un año válido entre ${rules.min || 1900} y ${currentYear}`);
                }
            }

            if (rules.type === 'number') {
                const num = parseFloat(value);
                if (isNaN(num)) {
                    errors.push(rules.message || 'Ingrese un número válido');
                } else if (rules.min !== undefined && num < rules.min) {
                    errors.push(rules.message || `El valor debe ser al menos ${rules.min}`);
                } else if (rules.max !== undefined && num > rules.max) {
                    errors.push(rules.message || `El valor debe ser máximo ${rules.max}`);
                }
            }

            if (rules.minLength && value.length < rules.minLength) {
                errors.push(rules.message || `${rules.label} debe tener al menos ${rules.minLength} caracteres`);
            }

            if (rules.maxLength && value.length > rules.maxLength) {
                errors.push(rules.message || `${rules.label} debe tener máximo ${rules.maxLength} caracteres`);
            }

            return errors;
        },

        getFieldError(fieldKey, value, customRules = null) {
            const rules = customRules || this.validationRules[fieldKey];
            if (!rules) return '';
            const errors = this.validateValue(value, rules);
            return errors.length > 0 ? errors[0] : '';
        },

        hasFieldError(fieldKey, value, customRules = null) {
            return this.getFieldError(fieldKey, value, customRules) !== '';
        },

        showFieldError(fieldKey, value, customRules = null) {
            return this.touchedFields[fieldKey] && this.hasFieldError(fieldKey, value, customRules);
        },

        getInputClass(fieldKey, value, customRules = null, baseClass = '') {
            if (!this.touchedFields[fieldKey]) {
                return baseClass + ' border-gray-300 focus:ring-blue-500 focus:border-blue-500';
            }
            if (this.hasFieldError(fieldKey, value, customRules)) {
                return baseClass + ' border-red-500 focus:ring-red-500 focus:border-red-500 bg-red-50';
            }
            return baseClass + ' border-green-500 focus:ring-green-500 focus:border-green-500 bg-green-50';
        },

        validateStep(stepNumber) {
            const errors = [];
            switch (stepNumber) {
                case 1: errors.push(...this.validatePersonalData()); break;
                case 2: errors.push(...this.validateAcademics()); break;
                case 3: errors.push(...this.validateExperiences()); break;
                case 4: errors.push(...this.validateTrainings()); break;
                case 6: errors.push(...this.validateRegistrations()); break;
                case 8: errors.push(...this.validateDeclarations()); break;
            }
            return errors;
        },

        validatePersonalData() {
            const errors = [];
            const p = this.formData.personal;

            if (!p.fullName || p.fullName.trim() === '') {
                errors.push({ step: 1, field: 'Nombre Completo', message: 'El nombre completo es requerido' });
            }
            if (!p.dni || !/^[0-9]{8}$/.test(p.dni)) {
                errors.push({ step: 1, field: 'DNI', message: 'El DNI debe tener exactamente 8 dígitos' });
            }
            if (!p.birthDate) {
                errors.push({ step: 1, field: 'Fecha de Nacimiento', message: 'La fecha de nacimiento es requerida' });
            }
            if (!p.address || p.address.length < 10) {
                errors.push({ step: 1, field: 'Dirección', message: 'La dirección debe tener al menos 10 caracteres' });
            }
            if (!p.phone || !/^[0-9\-\s]{7,15}$/.test(p.phone)) {
                errors.push({ step: 1, field: 'Teléfono', message: 'Ingrese un número de teléfono válido (7-15 dígitos)' });
            }
            if (!p.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(p.email)) {
                errors.push({ step: 1, field: 'Email', message: 'Ingrese un email válido' });
            }

            return errors;
        },

        validateAcademics() {
            const errors = [];
            const currentYear = new Date().getFullYear();

            this.formData.academics.forEach((academic, index) => {
                const prefix = `Título/Grado ${index + 1}`;

                if (!academic.institution || academic.institution.length < 3) {
                    errors.push({ step: 2, field: `${prefix} - Institución`, message: 'Ingrese el nombre de la institución (mín. 3 caracteres)' });
                }
                if (academic.isRelatedCareer && (!academic.relatedCareerName || academic.relatedCareerName.length < 5)) {
                    errors.push({ step: 2, field: `${prefix} - Nombre de Carrera Afín`, message: 'Ingrese el nombre de su carrera (mín. 5 caracteres)' });
                }
                if (!academic.year || academic.year < 1950 || academic.year > currentYear) {
                    errors.push({ step: 2, field: `${prefix} - Año de Graduación`, message: `Ingrese un año válido (1950-${currentYear})` });
                }
            });

            return errors;
        },

        validateExperiences() {
            const errors = [];

            this.formData.experiences.forEach((exp, index) => {
                const prefix = `Experiencia ${index + 1}`;

                if (!exp.organization || exp.organization.length < 3) {
                    errors.push({ step: 3, field: `${prefix} - Empresa/Organización`, message: 'Ingrese el nombre de la organización (mín. 3 caracteres)' });
                }
                if (!exp.position || exp.position.length < 3) {
                    errors.push({ step: 3, field: `${prefix} - Cargo/Puesto`, message: 'Ingrese el nombre del cargo (mín. 3 caracteres)' });
                }
                if (!exp.startDate) {
                    errors.push({ step: 3, field: `${prefix} - Fecha de Inicio`, message: 'Seleccione la fecha de inicio' });
                }
                if (!exp.isCurrent && !exp.endDate) {
                    errors.push({ step: 3, field: `${prefix} - Fecha de Fin`, message: 'Seleccione la fecha de fin o marque "Trabajo actual"' });
                }
                if (exp.startDate && exp.endDate && new Date(exp.endDate) < new Date(exp.startDate)) {
                    errors.push({ step: 3, field: `${prefix} - Fechas`, message: 'La fecha de fin debe ser posterior a la fecha de inicio' });
                }
            });

            return errors;
        },

        validateTrainings() {
            const errors = [];
            const currentYear = new Date().getFullYear();
            const requiredCourses = window.wizardConfig.requiredCourses;

            this.formData.requiredCoursesCompliance.forEach((course, index) => {
                const courseName = requiredCourses[index] || `Capacitación requerida ${index + 1}`;

                if (course.status === 'exact') {
                    if (!course.institution || course.institution.length < 3) {
                        errors.push({ step: 4, field: `${courseName} - Institución`, message: 'Ingrese la institución (mín. 3 caracteres)' });
                    }
                    if (!course.year || course.year < 1990 || course.year > currentYear) {
                        errors.push({ step: 4, field: `${courseName} - Año`, message: `Ingrese un año válido (1990-${currentYear})` });
                    }
                    if (!course.hours || course.hours < 1) {
                        errors.push({ step: 4, field: `${courseName} - Horas`, message: 'Las horas deben ser mayor a 0' });
                    }
                } else if (course.status === 'related') {
                    if (!course.relatedCourseName || course.relatedCourseName.length < 5) {
                        errors.push({ step: 4, field: `${courseName} - Nombre de capacitación afín`, message: 'Ingrese el nombre de su capacitación (mín. 5 caracteres)' });
                    }
                    if (!course.relatedInstitution || course.relatedInstitution.length < 3) {
                        errors.push({ step: 4, field: `${courseName} - Institución (afín)`, message: 'Ingrese la institución (mín. 3 caracteres)' });
                    }
                    if (!course.relatedYear || course.relatedYear < 1990 || course.relatedYear > currentYear) {
                        errors.push({ step: 4, field: `${courseName} - Año (afín)`, message: `Ingrese un año válido (1990-${currentYear})` });
                    }
                    if (!course.relatedHours || course.relatedHours < 1) {
                        errors.push({ step: 4, field: `${courseName} - Horas (afín)`, message: 'Las horas deben ser mayor a 0' });
                    }
                }
            });

            this.formData.additionalTrainings.forEach((training, index) => {
                const prefix = `Capacitación adicional ${index + 1}`;
                const hasAnyField = training.courseName || training.institution || training.hours || training.certificationDate;

                if (hasAnyField) {
                    if (!training.courseName || training.courseName.length < 5) {
                        errors.push({ step: 4, field: `${prefix} - Nombre del Curso`, message: 'Ingrese el nombre del curso (mín. 5 caracteres)' });
                    }
                    if (!training.institution || training.institution.length < 3) {
                        errors.push({ step: 4, field: `${prefix} - Institución`, message: 'Ingrese la institución (mín. 3 caracteres)' });
                    }
                    if (!training.hours || training.hours < 1) {
                        errors.push({ step: 4, field: `${prefix} - Horas`, message: 'Las horas deben ser mayor a 0' });
                    }
                    if (!training.certificationDate) {
                        errors.push({ step: 4, field: `${prefix} - Fecha de Certificación`, message: 'Seleccione el mes/año de certificación' });
                    }
                }
            });

            return errors;
        },

        validateRegistrations() {
            const errors = [];
            const colegiaturaRequired = window.wizardConfig.colegiaturaRequired;

            if (colegiaturaRequired && this.formData.registrations.colegiatura.habilitado) {
                if (!this.formData.registrations.colegiatura.college || this.formData.registrations.colegiatura.college.length < 5) {
                    errors.push({ step: 6, field: 'Colegio Profesional', message: 'Ingrese el nombre del colegio profesional (mín. 5 caracteres)' });
                }
                if (!this.formData.registrations.colegiatura.number || this.formData.registrations.colegiatura.number.length < 3) {
                    errors.push({ step: 6, field: 'Número de Colegiatura', message: 'Ingrese el número de colegiatura (mín. 3 caracteres)' });
                }
            }

            return errors;
        },

        validateDeclarations() {
            const errors = [];

            if (!this.formData.declarationAccepted) {
                errors.push({ step: 8, field: 'Declaración Jurada', message: 'Debe aceptar la declaración jurada' });
            }
            if (!this.formData.termsAccepted) {
                errors.push({ step: 8, field: 'Términos y Condiciones', message: 'Debe aceptar los términos y condiciones' });
            }

            return errors;
        },

        getAllValidationErrors() {
            const allErrors = [];
            for (let step = 1; step <= 8; step++) {
                allErrors.push(...this.validateStep(step));
            }
            return allErrors;
        },

        getErrorsByStep() {
            const allErrors = this.getAllValidationErrors();
            const grouped = {};
            allErrors.forEach(error => {
                if (!grouped[error.step]) grouped[error.step] = [];
                grouped[error.step].push(error);
            });
            return grouped;
        },

        stepHasErrors(stepNumber) {
            return this.validateStep(stepNumber).length > 0;
        },

        getTotalErrorCount() {
            return this.getAllValidationErrors().length;
        },

        get stepTitle() {
            const titles = {
                1: 'Información personal básica',
                2: 'Títulos y grados académicos',
                3: 'Historial laboral',
                4: 'Cursos y certificaciones',
                5: 'Conocimientos técnicos',
                6: 'Colegiatura, OSCE y licencias',
                7: 'Condiciones especiales',
                8: 'Revisión final'
            };
            return titles[this.currentStep] || '';
        },

        get complianceStatus() {
            let criticalIssues = 0;
            let warnings = 0;

            if (this.minimumEducationLevel) {
                const hasValidDegree = this.formData.academics.some(academic => {
                    if (!academic.degreeType) return false;
                    return this.meetsEducationRequirement(academic.degreeType);
                });
                if (!hasValidDegree) criticalIssues++;
            }

            if (this.acceptedCareerIds && this.acceptedCareerIds.length > 0) {
                const hasAcceptedCareer = this.formData.academics.some(academic => {
                    return academic.careerId && this.isCareerAccepted(academic.careerId);
                });
                const hasRelatedCareer = this.formData.academics.some(academic => {
                    return academic.isRelatedCareer && academic.relatedCareerName;
                });

                if (!hasAcceptedCareer && !hasRelatedCareer) {
                    criticalIssues++;
                } else if (!hasAcceptedCareer && hasRelatedCareer) {
                    warnings++;
                }
            }

            if (this.formData.requiredCoursesCompliance && this.formData.requiredCoursesCompliance.length > 0) {
                const coursesCompliance = this.getRequiredCoursesCompliance();
                if (coursesCompliance.met === 0 && coursesCompliance.partial === 0) {
                    criticalIssues++;
                } else if (coursesCompliance.met < coursesCompliance.total) {
                    warnings++;
                }
            }

            if (this.formData.knowledgeCompliance && this.formData.knowledgeCompliance.length > 0) {
                const metKnowledge = this.formData.knowledgeCompliance.filter(k => k.hasIt).length;
                const totalKnowledge = this.formData.knowledgeCompliance.length;
                if (metKnowledge === 0) {
                    criticalIssues++;
                } else if (metKnowledge < totalKnowledge) {
                    warnings++;
                }
            }

            if (criticalIssues > 0) return 'none';
            if (warnings > 0) return 'partial';
            return 'full';
        },

        nextStep() {
            if (this.currentStep < 8) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        isCareerAccepted(careerId) {
            if (!careerId) return false;
            return this.acceptedCareerIds.includes(String(careerId));
        },

        checkCareerMatch(index) {
            // Validación visual manejada automáticamente con x-show
        },

        meetsEducationRequirement(degreeType) {
            if (!this.minimumEducationLevel || !degreeType) return true;
            const selectedLevel = this.educationLevels.find(level => level.value === degreeType);
            if (!selectedLevel) return true;
            return selectedLevel.level >= this.minimumEducationLevelValue;
        },

        checkEducationLevel(index) {
            // La validación visual se maneja automáticamente con x-show
        },

        addAcademic() {
            this.formData.academics.push({
                degreeType: '', institution: '', careerId: '', careerField: '',
                isRelatedCareer: false, relatedCareerName: '', year: ''
            });
        },

        removeAcademic(index) {
            this.formData.academics.splice(index, 1);
        },

        addExperience() {
            this.formData.experiences.push({
                organization: '', position: '', startDate: '', endDate: '',
                isCurrent: false, isPublicSector: false, isSpecific: false, description: ''
            });
        },

        removeExperience(index) {
            this.formData.experiences.splice(index, 1);
        },

        addAdditionalTraining() {
            this.formData.additionalTrainings.push({
                courseName: '', institution: '', hours: '', certificationDate: ''
            });
        },

        removeAdditionalTraining(index) {
            this.formData.additionalTrainings.splice(index, 1);
        },

        getRequiredCoursesCompliance() {
            if (!this.formData.requiredCoursesCompliance) return { met: 0, total: 0, partial: 0 };

            const total = this.formData.requiredCoursesCompliance.length;
            let met = 0;
            let partial = 0;

            this.formData.requiredCoursesCompliance.forEach(course => {
                if (course.status === 'exact') met++;
                else if (course.status === 'related') partial++;
            });

            return { met, total, partial };
        },

        calculateDuration(start, end) {
            if (!start) return '0 años, 0 meses, 0 días';

            const startDate = new Date(start);
            const endDate = end ? new Date(end) : new Date();

            let years = endDate.getFullYear() - startDate.getFullYear();
            let months = endDate.getMonth() - startDate.getMonth();
            let days = endDate.getDate() - startDate.getDate();

            if (days < 0) {
                months--;
                const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
                days += prevMonth.getDate();
            }
            if (months < 0) {
                years--;
                months += 12;
            }

            return `${years} año(s), ${months} mes(es), ${days} día(s)`;
        },

        calculateTotalExperience(type) {
            let totalDays = 0;

            this.formData.experiences.forEach(exp => {
                if (type === 'specific' && !exp.isSpecific) return;
                if (exp.startDate) {
                    const startDate = new Date(exp.startDate);
                    const endDate = exp.endDate ? new Date(exp.endDate) : (exp.isCurrent ? new Date() : null);
                    if (endDate) {
                        const diffTime = Math.abs(endDate - startDate);
                        totalDays += Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    }
                }
            });

            const years = Math.floor(totalDays / 365);
            const remainingDays = totalDays % 365;
            const months = Math.floor(remainingDays / 30);
            const days = remainingDays % 30;

            return `${years} año(s), ${months} mes(es), ${days} día(s)`;
        },

        checkExperienceRequirement(type) {
            const config = window.wizardConfig;
            const requiredYears = type === 'general'
                ? parseFloat(config.generalExperienceYears)
                : parseFloat(config.specificExperienceYears);

            if (!requiredYears || requiredYears === 0) return true;

            let totalDays = 0;

            this.formData.experiences.forEach(exp => {
                if (type === 'specific' && !exp.isSpecific) return;
                if (exp.startDate) {
                    const startDate = new Date(exp.startDate);
                    const endDate = exp.endDate ? new Date(exp.endDate) : (exp.isCurrent ? new Date() : null);
                    if (endDate) {
                        const diffTime = Math.abs(endDate - startDate);
                        totalDays += Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    }
                }
            });

            return (totalDays / 365) >= requiredYears;
        },

        autoSave() {
            localStorage.setItem('applicationDraft_' + window.wizardConfig.userId + '_' + window.wizardConfig.jobProfileId, JSON.stringify(this.formData));
            this.lastSaved = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
        },

        loadFromLocalStorage() {
            const saved = localStorage.getItem('applicationDraft_' + window.wizardConfig.userId + '_' + window.wizardConfig.jobProfileId);
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    const personalDataBackup = { ...this.formData.personal };
                    this.formData = { ...this.formData, ...data };
                    this.formData.personal = personalDataBackup;
                    console.log('Borrador cargado desde localStorage (datos personales preservados)');
                } catch (e) {
                    console.error('Error loading draft:', e);
                }
            }
        },

        saveDraft() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.wizardConfig.storeUrl;

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = window.wizardConfig.csrfToken;
            form.appendChild(csrfField);

            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'draft';
            form.appendChild(actionField);

            const dataField = document.createElement('input');
            dataField.type = 'hidden';
            dataField.name = 'formData';
            dataField.value = JSON.stringify(this.formData);
            form.appendChild(dataField);

            document.body.appendChild(form);
            form.submit();
        },

        submitApplication() {
            const allErrors = this.getAllValidationErrors();

            if (allErrors.length > 0) {
                window.scrollTo({ top: 0, behavior: 'smooth' });

                const errorsByStep = this.getErrorsByStep();
                const stepNames = {
                    1: 'Datos Personales', 2: 'Formación Académica', 3: 'Experiencia Laboral',
                    4: 'Capacitaciones', 6: 'Registros Profesionales', 8: 'Declaraciones'
                };

                let errorMessage = `Se encontraron ${allErrors.length} error(es) en el formulario:\n\n`;
                for (const [step, errors] of Object.entries(errorsByStep)) {
                    errorMessage += `📋 ${stepNames[step] || `Paso ${step}`}:\n`;
                    errors.forEach(err => { errorMessage += `   • ${err.field}: ${err.message}\n`; });
                    errorMessage += '\n';
                }
                errorMessage += 'Por favor, corrige los errores antes de enviar la postulación.';
                alert(errorMessage);
                return;
            }

            if (!this.formData.termsAccepted || !this.formData.declarationAccepted) {
                alert('Debes aceptar la declaración jurada y los términos y condiciones');
                return;
            }

            this.formData.additionalTrainings = this.formData.additionalTrainings.filter(training => {
                return training.courseName && training.institution && training.hours && training.certificationDate;
            });

            const form = document.getElementById('application-form');

            const oldActionField = form.querySelector('input[name="action"]');
            const oldDataField = form.querySelector('input[name="formData"]');
            if (oldActionField) oldActionField.remove();
            if (oldDataField) oldDataField.remove();

            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'submit';
            form.appendChild(actionField);

            const dataField = document.createElement('input');
            dataField.type = 'hidden';
            dataField.name = 'formData';
            dataField.value = JSON.stringify(this.formData);
            form.appendChild(dataField);

            localStorage.removeItem('applicationDraft_' + window.wizardConfig.userId + '_' + window.wizardConfig.jobProfileId);

            this.isSubmitting = true;
            form.submit();
        }
    }
}

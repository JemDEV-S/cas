{{-- Formulario de subida de CV documentado --}}
<form action="{{ route('applicant.applications.upload-cv', $application->id) }}"
      method="POST"
      enctype="multipart/form-data"
      id="cv-upload-form"
      class="space-y-4">
    @csrf

    {{-- Área de drag & drop --}}
    <div class="relative">
        <input type="file"
               name="cv_file"
               id="cv_file"
               accept=".pdf,application/pdf"
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
               onchange="handleFileSelect(this)">

        <div id="drop-zone"
             class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all duration-300 hover:border-emerald-500 hover:bg-emerald-50">

            {{-- Estado inicial --}}
            <div id="upload-initial" class="space-y-4">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-700">Arrastra tu archivo PDF aquí</p>
                    <p class="text-sm text-gray-500 mt-1">o haz clic para seleccionar</p>
                </div>
                <div class="flex items-center justify-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        Solo PDF
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Max 15 MB
                    </span>
                </div>
            </div>

            {{-- Estado con archivo seleccionado --}}
            <div id="upload-selected" class="hidden space-y-4">
                <div class="w-16 h-16 mx-auto bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-emerald-700" id="file-name">archivo.pdf</p>
                    <p class="text-sm text-gray-500 mt-1" id="file-size">0 MB</p>
                </div>
                <button type="button"
                        onclick="clearFile()"
                        class="text-sm text-red-600 hover:text-red-800 underline">
                    Cambiar archivo
                </button>
            </div>
        </div>
    </div>

    {{-- Mensaje de error --}}
    @error('cv_file')
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </div>
    @enderror

    {{-- Botón de envío --}}
    <button type="submit"
            id="submit-btn"
            disabled
            class="w-full py-4 px-6 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-lg rounded-xl hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none flex items-center justify-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <span id="submit-text">Subir CV Documentado</span>
    </button>
</form>

<script>
    const maxSize = 15 * 1024 * 1024; // 15 MB en bytes

    function handleFileSelect(input) {
        const file = input.files[0];

        if (!file) {
            clearFile();
            return;
        }

        // Validar tipo de archivo
        if (file.type !== 'application/pdf') {
            alert('Solo se permiten archivos PDF.');
            clearFile();
            return;
        }

        // Validar tamaño
        if (file.size > maxSize) {
            alert('El archivo supera el tamaño máximo de 15 MB.');
            clearFile();
            return;
        }

        // Mostrar información del archivo
        document.getElementById('upload-initial').classList.add('hidden');
        document.getElementById('upload-selected').classList.remove('hidden');
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-size').textContent = formatFileSize(file.size);
        document.getElementById('submit-btn').disabled = false;
        document.getElementById('drop-zone').classList.add('border-emerald-500', 'bg-emerald-50');
        document.getElementById('drop-zone').classList.remove('border-gray-300');
    }

    function clearFile() {
        document.getElementById('cv_file').value = '';
        document.getElementById('upload-initial').classList.remove('hidden');
        document.getElementById('upload-selected').classList.add('hidden');
        document.getElementById('submit-btn').disabled = true;
        document.getElementById('drop-zone').classList.remove('border-emerald-500', 'bg-emerald-50');
        document.getElementById('drop-zone').classList.add('border-gray-300');
    }

    function formatFileSize(bytes) {
        if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        }
        return bytes + ' bytes';
    }

    // Drag and drop visual feedback
    const dropZone = document.getElementById('drop-zone');

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('border-emerald-500', 'bg-emerald-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            if (!document.getElementById('cv_file').files.length) {
                dropZone.classList.remove('border-emerald-500', 'bg-emerald-50');
            }
        });
    });

    // Loading state on submit
    document.getElementById('cv-upload-form').addEventListener('submit', function() {
        const btn = document.getElementById('submit-btn');
        const text = document.getElementById('submit-text');
        btn.disabled = true;
        text.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Subiendo...';
    });
</script>

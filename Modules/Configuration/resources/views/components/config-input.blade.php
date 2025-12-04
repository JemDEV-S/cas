{{-- Componente dinámico de input según el tipo de configuración --}}

@php
    $inputName = "configs[{$config->id}]";
    $currentValue = old($inputName, $config->value ?? $config->default_value);
    $isDisabled = !$config->is_editable;
@endphp

@switch($config->input_type->value)
    @case('checkbox')
        <div class="flex items-center">
            <input type="hidden" name="{{ $inputName }}" value="0">
            <input type="checkbox"
                   id="{{ $config->key }}"
                   name="{{ $inputName }}"
                   value="1"
                   {{ $currentValue == '1' || $currentValue === true ? 'checked' : '' }}
                   {{ $isDisabled ? 'disabled' : '' }}
                   class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">
            <label for="{{ $config->key }}" class="ml-3 text-sm text-gray-700">
                Activado
            </label>
        </div>
        @break

    @case('select')
        <select name="{{ $inputName }}"
                id="{{ $config->key }}"
                {{ $isDisabled ? 'disabled' : '' }}
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
            @if($config->options && is_array($config->options))
                @foreach($config->options as $option)
                    @php
                        $optionValue = is_array($option) ? ($option['value'] ?? $option) : $option;
                        $optionLabel = is_array($option) ? ($option['label'] ?? $option['value'] ?? $option) : $option;
                    @endphp
                    <option value="{{ $optionValue }}"
                            {{ $currentValue == $optionValue ? 'selected' : '' }}>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            @endif
        </select>
        @break

    @case('radio')
        <div class="space-y-2">
            @if($config->options && is_array($config->options))
                @foreach($config->options as $option)
                    @php
                        $optionValue = is_array($option) ? ($option['value'] ?? $option) : $option;
                        $optionLabel = is_array($option) ? ($option['label'] ?? $option['value'] ?? $option) : $option;
                        $radioId = $config->key . '_' . $optionValue;
                    @endphp
                    <div class="flex items-center">
                        <input type="radio"
                               id="{{ $radioId }}"
                               name="{{ $inputName }}"
                               value="{{ $optionValue }}"
                               {{ $currentValue == $optionValue ? 'checked' : '' }}
                               {{ $isDisabled ? 'disabled' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <label for="{{ $radioId }}" class="ml-3 text-sm text-gray-700">
                            {{ $optionLabel }}
                        </label>
                    </div>
                @endforeach
            @endif
        </div>
        @break

    @case('textarea')
        <textarea name="{{ $inputName }}"
                  id="{{ $config->key }}"
                  rows="4"
                  {{ $isDisabled ? 'disabled' : '' }}
                  placeholder="{{ $config->help_text ?? '' }}"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">{{ $currentValue }}</textarea>
        @break

    @case('number')
        <input type="number"
               name="{{ $inputName }}"
               id="{{ $config->key }}"
               value="{{ $currentValue }}"
               {{ $config->min_value !== null ? "min={$config->min_value}" : '' }}
               {{ $config->max_value !== null ? "max={$config->max_value}" : '' }}
               step="{{ $config->value_type->value === 'float' ? '0.01' : '1' }}"
               {{ $isDisabled ? 'disabled' : '' }}
               placeholder="{{ $config->help_text ?? '' }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
        @break

    @case('date')
        <input type="date"
               name="{{ $inputName }}"
               id="{{ $config->key }}"
               value="{{ $currentValue }}"
               {{ $isDisabled ? 'disabled' : '' }}
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
        @break

    @case('datetime')
        <input type="datetime-local"
               name="{{ $inputName }}"
               id="{{ $config->key }}"
               value="{{ $currentValue ? \Carbon\Carbon::parse($currentValue)->format('Y-m-d\TH:i') : '' }}"
               {{ $isDisabled ? 'disabled' : '' }}
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
        @break

    @case('email')
        <input type="email"
               name="{{ $inputName }}"
               id="{{ $config->key }}"
               value="{{ $currentValue }}"
               {{ $isDisabled ? 'disabled' : '' }}
               placeholder="{{ $config->help_text ?? 'ejemplo@correo.com' }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
        @break

    @case('url')
        <input type="url"
               name="{{ $inputName }}"
               id="{{ $config->key }}"
               value="{{ $currentValue }}"
               {{ $isDisabled ? 'disabled' : '' }}
               placeholder="{{ $config->help_text ?? 'https://ejemplo.com' }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
        @break

    @case('color')
        <div class="flex items-center space-x-3">
            <input type="color"
                   name="{{ $inputName }}"
                   id="{{ $config->key }}"
                   value="{{ $currentValue }}"
                   {{ $isDisabled ? 'disabled' : '' }}
                   class="h-12 w-20 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">
            <input type="text"
                   value="{{ $currentValue }}"
                   readonly
                   class="flex-1 px-4 py-3 border border-gray-300 rounded-xl bg-gray-50">
        </div>
        @break

    @case('json_editor')
        <textarea name="{{ $inputName }}"
                  id="{{ $config->key }}"
                  rows="8"
                  {{ $isDisabled ? 'disabled' : '' }}
                  placeholder="{{ $config->help_text ?? '{"key": "value"}' }}"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">{{ is_array($currentValue) ? json_encode($currentValue, JSON_PRETTY_PRINT) : $currentValue }}</textarea>
        @break

    @default
        {{-- Campo de texto por defecto --}}
        <input type="text"
               name="{{ $inputName }}"
               id="{{ $config->key }}"
               value="{{ $currentValue }}"
               {{ $isDisabled ? 'disabled' : '' }}
               placeholder="{{ $config->help_text ?? '' }}"
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $isDisabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
@endswitch

@error($inputName)
    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror

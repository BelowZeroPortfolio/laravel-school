@props([
    'name' => null,
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'options' => [],
    'placeholder' => 'Select an option...'
])

@php
    $hasError = $error || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    
    $selectClasses = 'block w-full rounded-lg border px-3 py-2 text-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-0 ' .
        ($hasError 
            ? 'border-red-300 dark:border-red-600 text-red-900 dark:text-red-200 focus:border-red-500 focus:ring-red-500 bg-red-50 dark:bg-red-900/20' 
            : 'border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700');
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select @if($name) name="{{ $name }}" id="{{ $name }}" @endif
            {{ $attributes->except('class')->merge(['class' => $selectClasses]) }}
            @if($required) required @endif>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @if(is_array($options) && count($options) > 0)
            @foreach($options as $value => $text)
                <option value="{{ $value }}" {{ old($name) == $value ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
    
    @if($hint && !$hasError)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    
    @if($hasError)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
    @endif
</div>

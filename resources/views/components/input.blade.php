@props([
    'type' => 'text',
    'name' => null,
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false
])

@php
    $hasError = $error || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    
    $inputClasses = 'block w-full rounded-lg border px-3 py-2 text-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-0 ' .
        ($hasError 
            ? 'border-red-300 dark:border-red-600 text-red-900 dark:text-red-200 placeholder-red-300 dark:placeholder-red-500 focus:border-red-500 focus:ring-red-500 bg-red-50 dark:bg-red-900/20' 
            : 'border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700');
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
    
    <input type="{{ $type }}"
           @if($name) name="{{ $name }}" id="{{ $name }}" @endif
           {{ $attributes->except('class')->merge(['class' => $inputClasses]) }}
           @if($required) required @endif>
    
    @if($hint && !$hasError)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif
    
    @if($hasError)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
    @endif
</div>

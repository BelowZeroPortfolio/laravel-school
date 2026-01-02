@props(['href', 'active' => false])

<a href="{{ $href }}" 
   {{ $attributes->merge([
       'class' => ($active 
           ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 border-l-4 border-indigo-600 dark:border-indigo-400' 
           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent') 
           . ' flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors duration-150'
   ]) }}>
    @if(isset($icon))
        <span class="mr-3 flex-shrink-0">
            {{ $icon }}
        </span>
    @endif
    {{ $slot }}
</a>

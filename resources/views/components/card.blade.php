@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'padding' => true
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden']) }}>
    @if($title || isset($header))
        <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            @if(isset($header))
                {{ $header }}
            @else
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ $title }}
                        </h3>
                        @if($subtitle)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>
                    @if(isset($actions))
                        <div class="flex items-center space-x-2">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding ? 'p-4 sm:p-6' : '' }}">
        {{ $slot }}
    </div>

    @if($footer || isset($footerSlot))
        <div class="px-4 py-3 sm:px-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
            @if(isset($footerSlot))
                {{ $footerSlot }}
            @else
                {{ $footer }}
            @endif
        </div>
    @endif
</div>

@php
    $schoolYears = \App\Models\SchoolYear::orderBy('start_date', 'desc')->get();
    $activeSchoolYear = $schoolYears->firstWhere('is_active', true);
    $selectedSchoolYearId = session('selected_school_year_id', $activeSchoolYear?->id);
    $selectedSchoolYear = $schoolYears->firstWhere('id', $selectedSchoolYearId) ?? $activeSchoolYear;
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" 
            @click.away="open = false"
            type="button"
            class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="hidden sm:inline">{{ $selectedSchoolYear?->name ?? 'No School Year' }}</span>
        @if($selectedSchoolYear?->is_active)
            <x-badge type="success" size="sm">Active</x-badge>
        @endif
        <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
        <div class="py-1" role="menu">
            @forelse($schoolYears as $schoolYear)
                @if(Route::has('school-year.select'))
                    <form action="{{ route('school-year.select', $schoolYear) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full text-left px-4 py-2 text-sm {{ $schoolYear->id === $selectedSchoolYearId ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center justify-between"
                                role="menuitem">
                            <span>{{ $schoolYear->name }}</span>
                            <div class="flex items-center space-x-1">
                                @if($schoolYear->is_active)
                                    <x-badge type="success" size="sm">Active</x-badge>
                                @endif
                                @if($schoolYear->is_locked)
                                    <x-badge type="warning" size="sm">Locked</x-badge>
                                @endif
                            </div>
                        </button>
                    </form>
                @else
                    <div class="w-full text-left px-4 py-2 text-sm {{ $schoolYear->id === $selectedSchoolYearId ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300' }} flex items-center justify-between">
                        <span>{{ $schoolYear->name }}</span>
                        <div class="flex items-center space-x-1">
                            @if($schoolYear->is_active)
                                <x-badge type="success" size="sm">Active</x-badge>
                            @endif
                            @if($schoolYear->is_locked)
                                <x-badge type="warning" size="sm">Locked</x-badge>
                            @endif
                        </div>
                    </div>
                @endif
            @empty
                <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                    No school years available
                </div>
            @endforelse
        </div>
    </div>
</div>

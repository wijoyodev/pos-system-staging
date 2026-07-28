<!-- Report Header Section -->
<div class="flex flex-col md:flex-row md:items-end justify-between mb-6 lg:mb-8 gap-4">
    <div>
        <nav class="flex items-center text-[10px] lg:text-xs font-semibold text-primary mb-1 lg:mb-2 tracking-wide uppercase">
            <span>{{ $module ?? 'Application' }}</span>
            <span class="material-symbols-outlined text-[12px] lg:text-[14px] mx-1">chevron_right</span>
            <span class="text-slate-400">{{ $submodule ?? 'Management' }}</span>
        </nav>
        <h2 class="text-xl lg:text-3xl font-extrabold text-on-surface tracking-tight leading-none mb-1 lg:mb-2">
            {{ $title ?? 'Page Title' }}
        </h2>
        @if(isset($description))
        <p class="text-xs lg:text-sm text-on-surface-variant max-w-xl font-medium">
            {{ $description }}
        </p>
        @endif
    </div>
    @if(isset($actions) && trim($actions) !== '')
    <div class="flex flex-wrap gap-2 lg:gap-3">
        {{ $actions }}
    </div>
    @endif
</div>

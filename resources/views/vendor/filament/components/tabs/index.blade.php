@props([
    'contained' => false,
    'label' => null,
])

<nav
    {{
        $attributes
            ->merge([
                'aria-label' => $label,
                'role' => 'tablist',
            ])
            ->class([
                'fi-tabs flex max-w-full gap-x-1 overflow-x-auto',
                'fi-contained border-b border-gray-200 px-3 py-2.5 dark:border-white/10' => $contained,
                '  dark:bg-gray-900 ' => ! $contained,
            ])
    }}
>
    {{ $slot }}
</nav>

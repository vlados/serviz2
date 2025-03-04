<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Search Debounce Time
    |--------------------------------------------------------------------------
    |
    | How many milliseconds to wait after typing stops before searching.
    |
    */
    'search_debounce' => 300,

    /*
    |--------------------------------------------------------------------------
    | Global Search Shortcut
    |--------------------------------------------------------------------------
    |
    | The keyboard combination for showing the global search dialog as a string.
    |
    */
    'global_search_shortcut' => 'ctrl+k',

    /*
    |--------------------------------------------------------------------------
    | Global Search Paginator
    |--------------------------------------------------------------------------
    |
    | Maximum number of results to show per search.
    |
    */
    'results_per_group' => 5,
    
    /*
    |--------------------------------------------------------------------------
    | Resources to Include In Global Search
    |--------------------------------------------------------------------------
    |
    | Define which Filament Resources should be searchable.
    | If empty, all Filament Resources will be searchable.
    |
    */
    'resources' => [
        // \App\Filament\Resources\UserResource::class,
    ],
];
import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/customer/**/*.blade.php',
        './resources/views/infolist/**/*.blade.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/vendor/filament-panels/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/ralphjsmit/laravel-filament-activitylog/resources/**/*.blade.php',
        // './vendor/awcodes/filament-quick-create/resources/**/*.blade.php',
    ],
    theme: {
    },

}

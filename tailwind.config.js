import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                grcblue: {
                    50: '#eaf3f7',
                    100: '#d4e7ef',
                    200: '#a9cfe0',
                    300: '#7eb7d1',
                    400: '#1375a0',
                    500: '#106689',
                    600: '#0d5773',
                    700: '#0a485d',
                    800: '#374151',
                    900: '#212a3a',
                },
            },
        },
    },
}
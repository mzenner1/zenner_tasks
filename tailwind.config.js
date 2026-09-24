import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Logo color (#6F68FE) is 600.
                brand: {
                    50:  '#f3f2ff',
                    100: '#e9e8ff',
                    200: '#d5d3ff',
                    300: '#b7b3ff',
                    400: '#9892fe',
                    500: '#817bfe',
                    600: '#6f68fe',
                    700: '#5a52e8',
                    800: '#4841c0',
                    900: '#3b3698',
                    950: '#232057',
                },
            },
        },
    },

    plugins: [forms, typography],
};

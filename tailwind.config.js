import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Public Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#ec5b13',
                'background-light': '#f8f6f6',
                'background-dark': '#221610',
            },
        },
    },

    plugins: [forms],
};

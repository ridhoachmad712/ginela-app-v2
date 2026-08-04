import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const withVar = (v) => `rgb(var(${v}) / <alpha-value>)`;

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
                sans: ['"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(2 6 23 / 0.04), 0 12px 28px -14px rgb(2 6 23 / 0.16)',
                soft: '0 1px 2px 0 rgb(2 6 23 / 0.05)',
                pop: '0 10px 40px -12px rgb(2 6 23 / 0.28)',
            },
            colors: {
                page: withVar('--c-base'),
                surface: {
                    DEFAULT: withVar('--c-surface'),
                    2: withVar('--c-surface-2'),
                    3: withVar('--c-surface-3'),
                },
                ink: {
                    DEFAULT: withVar('--c-ink'),
                    soft: withVar('--c-ink-soft'),
                    faint: withVar('--c-ink-faint'),
                },
                line: {
                    DEFAULT: withVar('--c-line'),
                    soft: withVar('--c-line-soft'),
                },
                accent: {
                    50: withVar('--c-accent-50'),
                    100: withVar('--c-accent-100'),
                    300: withVar('--c-accent-300'),
                    500: withVar('--c-accent-500'),
                    600: withVar('--c-accent-600'),
                    700: withVar('--c-accent-700'),
                },
            },
            keyframes: {
                'fade-in': { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                'slide-up': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'pop-in': {
                    '0%': { opacity: '0', transform: 'scale(.92)' },
                    '60%': { opacity: '1', transform: 'scale(1.03)' },
                    '100%': { transform: 'scale(1)' },
                },
                'toast-in': {
                    '0%': { opacity: '0', transform: 'translateY(12px) scale(.96)' },
                    '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                },
                'check-pop': {
                    '0%': { transform: 'scale(0)', opacity: '0' },
                    '60%': { transform: 'scale(1.15)', opacity: '1' },
                    '100%': { transform: 'scale(1)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                },
            },
            animation: {
                'fade-in': 'fade-in .25s ease-out both',
                'slide-up': 'slide-up .3s cubic-bezier(.16,1,.3,1) both',
                'pop-in': 'pop-in .28s cubic-bezier(.16,1,.3,1) both',
                'toast-in': 'toast-in .3s cubic-bezier(.16,1,.3,1) both',
                'check-pop': 'check-pop .5s cubic-bezier(.16,1,.3,1) both',
            },
        },
    },

    plugins: [forms],
};

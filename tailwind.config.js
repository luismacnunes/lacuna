import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                paper: 'var(--paper)',
                ink: 'var(--ink)',
                sub: 'var(--sub)',
                faint: 'var(--faint)',
                line: 'var(--line)',
                coral: 'var(--coral)',
            },

            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Bricolage Grotesque', ...defaultTheme.fontFamily.sans],
            },

            maxWidth: {
                gap: '660px',
                curate: '680px',
                answer: '720px',
                queue: '760px',
                review: '780px',
                coverage: '880px',
            },

            borderColor: {
                DEFAULT: 'var(--line)',
            },
        },
    },

    plugins: [],
};
/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './*.php',
        './includes/**/*.php',
        './admin/**/*.php',
        './services/**/*.php',
        './portfolio/**/*.php',
        './blog/**/*.php',
        './assets/js/**/*.js',
    ],
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                brand: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif']
            },
            keyframes: {
                zoomOut: {
                    '0%':   { transform: 'scale(1.25)', opacity: '0' },
                    '10%':  { opacity: '1' },
                    '33%':  { transform: 'scale(1.0)',  opacity: '1' },
                    '43%':  { opacity: '0' },
                    '100%': { transform: 'scale(1.0)',  opacity: '0' },
                },
                fadeUp: {
                    '0%':   { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideRight: {
                    '0%':   { opacity: '0', transform: 'translateX(-40px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                pulseGlow: {
                    '0%, 100%': { transform: 'scale(1)',    opacity: '1' },
                    '50%':      { transform: 'scale(1.04)', opacity: '0.95' },
                },
                floatY: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%':      { transform: 'translateY(-12px)' },
                },
                dotPulse: {
                    '0%, 100%': { opacity: '0.35', transform: 'scale(1)' },
                    '50%':      { opacity: '1',    transform: 'scale(1.4)' },
                },
            },
            animation: {
                'zoom-out':    'zoomOut 21s ease-in-out infinite',
                'fade-up':     'fadeUp 0.8s ease-out forwards',
                'slide-right': 'slideRight 0.9s cubic-bezier(0.22, 1, 0.36, 1) forwards',
                'pulse-glow':  'pulseGlow 3s ease-in-out 2s infinite',
                'float-y':     'floatY 6s ease-in-out infinite',
                'dot-pulse':   'dotPulse 7s ease-in-out infinite',
            },
        }
    },
    plugins: [],
};
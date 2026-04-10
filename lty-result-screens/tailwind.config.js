/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.php',
  ],
  theme: {
    extend: {
      keyframes: {
        'rs-enter': {
          from: { opacity: '0', transform: 'translateY(32px) scale(0.97)' },
          to:   { opacity: '1', transform: 'translateY(0) scale(1)' },
        },
        'rs-bounce-icon': {
          '0%, 100%': { transform: 'translateY(0) scale(1)' },
          '30%':       { transform: 'translateY(-18px) scale(1.15)' },
          '50%':       { transform: 'translateY(-8px) scale(1.05)' },
          '70%':       { transform: 'translateY(-14px) scale(1.1)' },
        },
        'rs-confetti-fall': {
          '0%':   { transform: 'translateY(0) rotate(0deg)',       opacity: '1' },
          '80%':  { opacity: '1' },
          '100%': { transform: 'translateY(520px) rotate(680deg)', opacity: '0' },
        },
      },
      animation: {
        'rs-enter':        'rs-enter 0.4s cubic-bezier(0.16,1,0.3,1) both',
        'rs-bounce-icon':  'rs-bounce-icon 0.7s cubic-bezier(0.36,0.07,0.19,0.97) both 0.3s',
        'rs-confetti-fall':'rs-confetti-fall linear forwards',
      },
    },
  },
  plugins: [],
};

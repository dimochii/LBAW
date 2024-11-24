/** @type {import('tailwindcss').Config} */
import typography from '@tailwindcss/typography';

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.css",
  ],
  theme: {
    extend: {
      colors: {
        pastelYellow: '#EDD75A',
        pastelGreen: '#A6B37D',
        pastelRed: '#C96868',
        pastelBlue: '#7EACB5',
      },
      fontFamily: {
        inter: ["Inter", "sans-serif"],
        vollkorn: ["Vollkorn", "sans-serif"],
        grotesk: ["Hanken Grotesk", "sans-serif"]
      }
    },
  },
  plugins: [
    typography,
  ],
  
  safelist: [
    {
      pattern: /bg-+/,
    },
    'underline-effect',
    'underline-effect-light',
  ],
}


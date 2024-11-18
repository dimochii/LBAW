/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
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
    require('@tailwindcss/typography'),
  ],

  safelist: [
    {
      pattern: /bg-+/,
    },
  ],
}


/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",

  ],
  theme: {
    extend: {
      colors: {
        success: {
          100: '#d4edda',
          500: '#28a745',
          700: '#155724',
        },
        error: {
          100: '#f8d7da',
          500: '#dc3545',
          700: '#721c24',
        },
        warning: {
          100: '#fff3cd',
          500: '#ffc107',
          700: '#856404',
        },
        info: {
          100: '#d1ecf1',
          500: '#17a2b8',
          700: '#0c5460',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
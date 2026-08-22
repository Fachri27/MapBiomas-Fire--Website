/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme')

export default {
    darkMode: 'false',
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        './vendor/masmerise/livewire-toaster/resources/views/*.blade.php', // 👈
      ],
  theme: {
    extend: {
        colors: {
            newgray: {
                50: '#f9fafb',
                100: '#f4f5f7',
                200: '#e5e7eb',
                300: '#d5d6d7',
                400: '#9e9e9e',
                500: '#707275',
                600: '#4c4f52',
                700: '#24262d',
                800: '#1a1c23',
                900: '#121317',
              },
            /* Palet landing MapBiomas Fire — merah situs (red-600),
               sama dengan navPC dan tab bahasa. */
            ember: {
                DEFAULT: '#dc2626',
                deep: '#b91c1c',
                soft: '#ef4444',
            },
            maroon: '#7a2418',
            cloud: '#f3f4f6',
            /* Abu footer, disamakan dengan gray-500 pada partials/footer. */
            ash: '#6b7280',
        },
        fontFamily: {
            'sans': ['Open Sans', ...defaultTheme.fontFamily.sans],
            /* Keluarga huruf landing; sans sengaja tetap Open Sans
               supaya halaman lama tidak berubah. */
            'display': ['Poppins', ...defaultTheme.fontFamily.sans],
            'instrument': ['"Instrument Sans"', ...defaultTheme.fontFamily.sans],
            'mono': ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
        },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}


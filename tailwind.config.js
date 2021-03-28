module.exports = {
  purge: {
    content: ['./public/index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
    options: {
      safelist: [/data-theme$/],
    },
  },
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors: require('daisyui/colors'),
    },
  },
  variants: {
    extend: {},
  },
  plugins: [require('daisyui')],
  daisyui: {
    styled: true,
    themes: true,
    rtl: false,
  },
}

module.exports = {
  content: [
    "./src/**/*.{html,ts}",
    "./src/index.html"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#102a41',
        graycustom: '#acaeb2',
        dark: '#061528',
        light: '#fefcf9',
        pink: '#edcfbe',
      },
      fontFamily: {
        primary: ['Poppins', 'sans-serif'],
        secondary: ['Roboto', 'sans-serif'],
      },
    }
  },
  plugins: [],
};

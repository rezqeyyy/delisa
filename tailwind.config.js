/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        coal:'#000000',
        sheet:'#F5F5F5',
        ink50:'rgba(0,0,0,.50)',
        ink40:'rgba(0,0,0,.40)',
        ink28:'rgba(0,0,0,.28)',
        ink82:'rgba(0,0,0,.82)',
        stroke:'#CAC7C7',
        stroke2:'#D0D0D0',
        stroke3:'#D9D9D9',
        grayA0:'#A0A0A0',
        magenta:'#B9257F',
        danger:'#E20D0D',
        warn:'#E2D30D',
        success:'#39E93F',
        blush:'#FF2DA8',
      },
      boxShadow: {
        card: '0 8px 24px rgba(0,0,0,.08)',
      },
      borderRadius: {
        xl2: '1rem', // utk kartu di Figma
      },
      spacing: { 18: '4.5rem' },
      maxWidth: { frame: '1440px' },
      gridTemplateColumns: { sidebar: '260px 1fr' },
    },
  },
  plugins: [],
}

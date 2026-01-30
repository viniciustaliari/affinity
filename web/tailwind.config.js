/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./**/*.html",
    "./**/*.js",
  ],

  safelist: [
    // layout
    'flex',
    'grid',
    'items-center',
    'justify-center',
    'gap-4',
    'gap-5',

    // grids que usas
    'grid-cols-5',
    'grid-cols-7',
    'col-span-2',
    'col-span-3',
    'col-span-7',

    // tamaños
    'w-1/4',
    'w-2/4',
    'w-3/4',
    'h-full',
    'h-[80vh]',

    // colores que usas dinámicamente
    'bg-green-400',
    'bg-green-500',
    'bg-blue-500',
    'bg-gray-700',
    'bg-gray-500',
    'text-white',
  ],

  theme: {
    extend: {},
  },
  plugins: [],
};
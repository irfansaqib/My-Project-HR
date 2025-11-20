/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  safelist: [
    // ✅ Buttons you use in your app
    'bg-green-600', 'hover:bg-green-700', 'text-white',
    'bg-gray-800', 'hover:bg-gray-900',
    'bg-blue-600', 'hover:bg-blue-700',
    'rounded-lg', 'rounded-md', 'shadow-md',
    'focus:ring', 'focus:ring-offset-2',
    'focus:ring-blue-500', 'focus:ring-green-500',
    'transition', 'duration-200', 'ease-in-out',
    'px-4', 'py-2', 'font-semibold', 'text-sm',
  ],
  plugins: [],
};

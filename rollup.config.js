import cleaner from 'rollup-plugin-cleaner'
import { terser } from 'rollup-plugin-terser'
import pkg from './package.json'

export default [
  {
    input: 'resources/js/index.js',
    output: [
      {
        file: pkg.main,
        format: 'cjs',
      },
      {
        file: pkg.module,
        format: 'es',
      },
    ],
    plugins: [cleaner({ targets: ['dist'] })],
  },
  {
    input: 'resources/js/browser.js',
    output: [
      {
        file: pkg.browser,
        format: 'iife',
      },
    ],
    plugins: [terser()],
  },
]

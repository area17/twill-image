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
  },
]

import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import typescript from '@rollup/plugin-typescript';
import terser from '@rollup/plugin-terser';
import replace from '@rollup/plugin-replace';
import peerDepsExternal from 'rollup-plugin-peer-deps-external';
import { visualizer } from 'rollup-plugin-visualizer';

const isDevelopment = process.env.NODE_ENV !== 'production';
const isAnalyze = process.env.ANALYZE === 'true';

// External dependencies that should not be bundled
const external = [
  'react',
  'react-dom',
  '@inertiajs/react',
  // Keep these as dependencies since they're small utilities
  // 'clsx',
  // 'tailwind-merge', 
  // 'class-variance-authority'
];

// Heavy dependencies that should be external in production
const heavyDependencies = [
  '@tanstack/react-table',
  'lucide-react'
];

const plugins = [
  peerDepsExternal(),
  replace({
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development'),
    preventAssignment: true
  }),
  resolve({
    browser: true,
    preferBuiltins: false
  }),
  commonjs(),
  typescript({
    tsconfig: './tsconfig.json',
    sourceMap: isDevelopment,
    inlineSources: isDevelopment
  })
];

// Add minification for production
if (!isDevelopment) {
  plugins.push(
    terser({
      compress: {
        drop_console: true,
        drop_debugger: true,
        pure_funcs: ['console.log', 'console.warn']
      },
      format: {
        comments: false
      }
    })
  );
}

// Add bundle analyzer if requested
if (isAnalyze) {
  plugins.push(
    visualizer({
      filename: 'dist/bundle-analysis.html',
      open: true,
      gzipSize: true,
      brotliSize: true
    })
  );
}

export default {
  input: 'src/index.ts',
  external: [...external, ...(isDevelopment ? [] : heavyDependencies)],
  output: [
    {
      file: 'dist/index.js',
      format: 'cjs',
      exports: 'named',
      sourcemap: isDevelopment,
      interop: 'auto'
    },
    {
      file: 'dist/index.esm.js',
      format: 'esm',
      exports: 'named',
      sourcemap: isDevelopment
    }
  ],
  plugins,
  onwarn(warning, warn) {
    // Suppress circular dependency warnings for React ecosystem
    if (warning.code === 'CIRCULAR_DEPENDENCY') return;
    warn(warning);
  }
};
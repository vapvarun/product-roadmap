import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import rtlPlugin from './vite-plugin-rtl.js'

export default defineConfig({
  plugins: [vue(), rtlPlugin()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern'
      }
    }
  },
  build: {
    outDir: 'dist',
    cssCodeSplit: false,
    rollupOptions: {
      input: {
        main: './src/main.js'
      },
      output: {
        // Classic script on WordPress pages: an IIFE keeps minified top-level
        // names (one was `wp`) from shadowing window.wp and breaking editors.
        format: 'iife',
        inlineDynamicImports: true,
        entryFileNames: 'assets/index.js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/index.[ext]'
      }
    }
  },
  define: {
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV)
  }
})
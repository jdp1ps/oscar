import { defineConfig, splitVendorChunkPlugin } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

console.log("OSCAR BUILDER v3");

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue(), splitVendorChunkPlugin()],
  build: {
    outDir: "../public/js/oscar/vite/dist",
    //outDir: "../dist",
    sourcemap: false,
    emptyOutDir: true,
    manifest: true,
    minify: true,
    // commonjsOptions: {
    //   transformMixedEsModules: true
    // },


    rollupOptions: {
      // make sure to externalize deps that shouldn't be bundled
      // into your library
      input: {
        example: resolve(__dirname, 'src/example.js'),
        //other: resolve(__dirname, 'src/other.js')
      },
      output: {
        chunkFileNames: 'vendor.js'
      },
    },
  },
  resolve: {
    alias: {
      'vue': '/node_modules/vue/dist/vue.runtime.esm-browser.js'
    }
  }
})

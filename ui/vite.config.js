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
        activitydocuments: resolve(__dirname, 'src/ActivityDocuments.js'),
        activityspentdetails: resolve(__dirname, 'src/ActivitySpentDetails.js'),
        activityspentsynthesis: resolve(__dirname, 'src/ActivitySpentSynthesis.js'),
        admintypedocument: resolve(__dirname, 'src/AdminTypeDocument.js'),
        activityworkpackage: resolve(__dirname, 'src/ActivityWorkpackage.js'),
        organizationfiche: resolve(__dirname, 'src/OrganizationFiche.js'),
        documentsobserved: resolve(__dirname, 'src/DocumentsObserved.js'),
        documentsindex: resolve(__dirname, 'src/DocumentsIndex.js'),
        organizationsuborganizations: resolve(__dirname, 'src/OrganizationSubOrganizations.js'),
        declarerslist: resolve(__dirname, 'src/DeclarersList.js'),
        oscarcss: resolve(__dirname, 'src/oscar-css.js'),
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

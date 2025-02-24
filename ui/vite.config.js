import { defineConfig, splitVendorChunkPlugin } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

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
        activity: resolve(__dirname, 'src/Activity.js'),
        activitydocuments: resolve(__dirname, 'src/ActivityDocuments.js'),
        activitymotscles: resolve(__dirname, 'src/ActivityMotsCles.js'),
        activitymotsclesadmin: resolve(__dirname, 'src/ActivityMotsClesAdmin.js'),
        activitynotes: resolve(__dirname, 'src/ActivityNotes.js'),
        activityspentdetails: resolve(__dirname, 'src/ActivitySpentDetails.js'),
        activityspentsynthesis: resolve(__dirname, 'src/ActivitySpentSynthesis.js'),
        admintypedocument: resolve(__dirname, 'src/AdminTypeDocument.js'),
        admintypeorganization: resolve(__dirname, 'src/AdminTypeOrganization.js'),
        adminroleorganization: resolve(__dirname, 'src/AdminRoleOrganization.js'),
        activityworkpackage: resolve(__dirname, 'src/ActivityWorkpackage.js'),
        declarerslist: resolve(__dirname, 'src/DeclarersList.js'),
        organizationfiche: resolve(__dirname, 'src/OrganizationFiche.js'),
        documentsobserved: resolve(__dirname, 'src/DocumentsObserved.js'),
        documentsindex: resolve(__dirname, 'src/DocumentsIndex.js'),
        organizationsuborganizations: resolve(__dirname, 'src/OrganizationSubOrganizations.js'),
        organizations_roled: resolve(__dirname, 'src/EntityWithRoleOrganizations.js'),
        oscarcss: resolve(__dirname, 'src/oscar-css.js'),
        ProjectActivityLogs: resolve(__dirname, 'src/ProjectActivityLogs.js'),
        ProjectActivitySpentSynthesis: resolve(__dirname, 'src/ProjectActivitySpentSynthesis.js'),
        timesheetpersonresume: resolve(__dirname, 'src/TimesheetPersonResume.js'),
        timesheetdeclarations: resolve(__dirname, 'src/TimesheetDeclaration.js'),
        persons_roled: resolve(__dirname, 'src/EntityWithRolePersons.js'),
        sticky: resolve(__dirname, 'src/Sticky.js'),
        //other: resolve(__dirname, 'src/other.js')
      },
      output: {
        chunkFileNames: 'vendor.js'
      },
    },
  },
  // resolve: {
  //   alias: {
  //     'vue': '/node_modules/vue/dist/vue.runtime.esm-browser.js'
  //   }
  // },
  test:{
    globals: true,
    environment: "jsdom",
    //setupFiles: './test/setup.js',
  }
});

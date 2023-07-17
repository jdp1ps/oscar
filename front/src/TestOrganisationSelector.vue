<template>
  <div>
    <pre>
      SELECTED : {{ selectedId }}
    </pre>
    <organizationselector v-model="selectedId" @change="handlerChangeData" />
  </div>
</template>
<script>

/**
node node_modules/.bin/vue-cli-service build --name TestOrganisationSelector --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/TestOrganisationSelector.vue
**/

import OrganizationAutoCompleter2 from "./components/OrganizationAutoCompleter2";

export default {
  components: {
    organizationselector: OrganizationAutoCompleter2,
  },

  props: {

  },

  data() {
    return {
      selectedId: null,
    };
  },

  methods: {

    handlerChangeData(data) {
      console.log(data);
    }
  },

  mounted() {

  }
}

</script>
<template>
  <documents-list :documents="documents" :display-activity="true"/>
</template>
<script>
import DocumentsList from "./DocumentsList.vue";
import axios from "axios";

export default {
  components: { DocumentsList },

  props:{
    manage: { default: false },
    url: { required: true }
  },

  data(){
    return {
      documents: []
    }
  },

  methods:{
    fetch(){
      axios.get(this.url).then(response => { this.documents = response.data.documents})
    }
  },

  mounted() {
    this.fetch();
  }
}
</script>
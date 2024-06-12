<template>
  <section class="documents-content">
    <div class="tab-content">
  <documents-list :documents="documents" :display-activity="true" @fetchall="fetch"/>
    </div>
  </section>
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
      console.log('fetch()');
      axios.get(this.url).then(response => { this.documents = response.data.documents})
    }
  },

  mounted() {
    this.fetch();
  }
}
</script>
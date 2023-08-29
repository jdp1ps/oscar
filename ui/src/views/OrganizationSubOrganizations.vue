<template>
  <div v-if="remote">
    Message: "{{ remote }}" // URL: "{{ url }}"
  </div>

  <hr>
  <button class="btn btn-primary" @click="fetch">
    Recharger
  </button>
</template>
<script>
import axios from "axios";
export default {
  props: {
    url: { require: true }
  },
  data(){
    return {
      remote: "Initialisation"
    }
  },
  methods: {
    fetch(){
      this.remote = "Chargement des sous-structures";
      axios.get(this.url).then(ok => {
        this.remote = "";
        console.log(ok)
      }, ko => {
        this.remote = "Erreur de chargement";
        console.log("ERREUR", ko)
      })
    }
  },
  mounted() {
    this.fetch()
  }
}
</script>
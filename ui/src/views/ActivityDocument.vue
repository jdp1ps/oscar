<template>
  <section>
    DOCUMENTS
  </section>
</template>
<script>
import axios from "axios";

export default {
  props: {
    url: {
      required: true
    }
  },

  computed:{

  },

  data(){
    return {
      pendingMsg: null
    }
  },

  methods: {
    fetch(){
      this.pendingMsg = "Chargement des données financières";
      axios.get(this.url).then(
          ok => {
            console.log("OK",ok);
            this.infos = ok.data.synthesis;
            this.masses = ok.data.masses;
          },
          ko => {
            console.log(ko)
          }
      ).then(foo=> {
        this.pendingMsg = false;
      })
    }
  },

  mounted() {
    this.fetch()
  }
}
</script>
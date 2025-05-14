<template>
  TEST
  <br>
  <button @click="handlerTestErrorFullscreen" class="btn btn-primary">Test error fullscreen</button>
  <br>
  <button @click="handlerLoad" class="btn btn-primary">Test loading</button>

  <pre>date: {{ foo.dateInput }}</pre>
  <datepicker v-model="foo.dateInput" />

  <pre>
    {{ response }}
  </pre>
</template>
<script>
import AxiosOscar from "../utils/AxiosOscar.js";
import GlobalModel from "../models/GlobalModel.js";
import Datepicker from "../components/Datepicker.vue";
export default {
  name: 'Test',
  components: {Datepicker},
  data(){
    return {
      response:null,
      foo: {
        dateInput: "2022-01-01"
      }
    }
  },
  methods:{
    fetch(){

    },
    handlerTestErrorFullscreen(){
      GlobalModel.commit('addErrorFullScreen', "Test fullscreen");
    },
    handlerLoad(){
      AxiosOscar.get('?sleep=5',{pendingBack:true, pendingMsg:"Chargement des données de test"}).then(res=>{
        this.response = res;
      })
    }
  },
  mounted() {
    this.fetch()
  }
}
</script>
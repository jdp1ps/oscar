<template>
  TEST
  <br>
  <button @click="handlerTestErrorFullscreen" class="btn btn-primary">Test error fullscreen</button>
  <br>
  <button @click="handlerLoad" class="btn btn-primary">Test loading</button>
  <pre>
    {{ response }}
  </pre>
</template>
<script>
import AxiosOscar from "../utils/AxiosOscar.js";
import GlobalModel from "../models/GlobalModel.js";
export default {
  name: 'Test',
  datas(){
    return {
      response:null
    }
  },
  methods:{
    fetch(){
      GlobalModel.commit('addErrorFullScreen', "Test fullscreen");
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
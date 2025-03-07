<template>
  <div>
    {{ change.label }} : <Datepicker v-model="date" />
  </div>
</template>
<script>
import Datepicker from "../../components/Datepicker.vue";
export default {
  name: "AvenantDate",
  emits: ["update:change"],
  props: ['change'],
  computed:{
    date: {
      get(){
        return this.change.value;
      },
      set(value){
        this.change.value = value;
        this.$emit("update:change", change);
      }
    }
  },
  components: {
    Datepicker
  }
}
</script>
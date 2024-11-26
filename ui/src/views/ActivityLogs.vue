<template>


  <modal title="Logs" title-icon="icon-signal" pending-message="Chargement des logs de l'activité" :visible="modal" @modal-valid="modal = false" :pending="pending" @modal-cancel="modal = false">
    <section class="activities timeline">
      <article class="timeline-item" :class="'type-'+item.type" v-for="item in traces">
        <time datetime="">
          <strong>{{ $filters.timeAgo(item.dateCreated.date) }}</strong>
          <div class="duration">{{ $filters.date(item.dateCreated.date) }}</div>
        </time>
        <div class="content" v-html="$filters.log(item.message)"></div>
      </article>
    </section>
    <template #buttons>
      <button class="btn btn-default" @click="handlerClickClose">
        Fermer
      </button>
    </template>
  </modal>

  <button @click="fetch" class="btn btn-primary">
    <i class="icon-signal"></i>
    Afficher les logs
  </button>
</template>
<script>
import axios from "axios";
import Modal from "../components/Modal.vue";
import AxiosMessage from "../utils/AxiosMessage.js";
export default {
  name: 'ActivityLogs',
  components: {Modal},
  props: {
    url: { required: true }
  },
  data() {
    return {
      traces: [],
      modal: false,
      pending: false,
      out: false,
    }
  },
  methods: {
    fetch(){
      this.pending = true;
      this.modal = true;
      axios.get(this.url).then(response => {
        this.traces = response.data.traces;
        this.pending = false;
      }, error => {
        console.log(AxiosMessage.manageErrorResponse(error));
        this.pending = false;
        this.modal = false;
        this.$emit('error', AxiosMessage.manageErrorResponse(error));
      });
    },
    handlerClickClose(){
      this.modal = false;
    }
  }
}
</script>
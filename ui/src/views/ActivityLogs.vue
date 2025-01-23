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
import GlobalModel from "../models/GlobalModel.js";
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
      console.log(this.url);
      this.pending = true;
      this.modal = true;
      axios.get(this.url).then(response => {
        this.traces = response.data.traces;
        this.pending = false;
      }, error => {
        this.pending = false;
        this.modal = false;
        GlobalModel.commit("addError", AxiosMessage.manageErrorResponse(error));
      });
    },
    handlerClickClose(){
      this.modal = false;
    }
  }
}
</script>

<style scoped lang="scss">
.timeline-item {

  margin-left: 1em;
  position: relative;
  margin-bottom: .5rem;
  display: flex;

  time {
    width: 150px;
    font-size: 1em;
    line-height: 1em;
    text-align: right;
    padding: .5em;
    .duration {
      font-weight: 100;
      font-size: .75em;
    }
  }
  .content {
    border-bottom: solid 1px rgba(#EEE, .5);
    border-left: solid .5em #9d9d9d;
    font-size: 1em;
    padding: .5em 1em;
  }
}
</style>
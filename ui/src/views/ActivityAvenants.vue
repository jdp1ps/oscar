<template>
  <h1>Avenants</h1>
  <modal title="Nouvelle avenant" :visible="edit">
    <form action="">
      <div class="form-group">
        <label for="name">Date (signature de l'avenant)</label>
        <datepicker v-model="edit.dateAvenant" />
      </div>
      <div class="form-group">
        <label for="name">Commentaire</label>
        <textarea v-model="edit.comment" class="form-control"></textarea>
      </div>
      <div class="form-group">
        <label for="name">Modifications</label>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Modifications <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#">Ajout d'une personne</a></li>
            <li><a href="#">Suppression d'une personne</a></li>
            <li><a href="#">Ajout d'une organisation</a></li>
            <li><a href="#">Suppression d'une organisation</a></li>
            <li><a href="#">Modification du montant</a></li>
            <li><a href="#">Modification de date</a></li>
          </ul>
        </div>
      </div>
    </form>
  </modal>
  <button class="btn btn-primary" @click="handlerNew">
    Nouvel avenant
  </button>
  {{ edit }}
</template>
<script>

import Modal from "../components/Modal.vue";
import Datepicker from "../components/Datepicker.vue";

export default {
  name: 'ActivityAvenants',
  components: {
    Datepicker,
    Modal
  },
  data() {
    return {
      edit: null,
    }
  },
  methods: {
    handlerNew() {
      this.edit = {
        id: null,
        dateAvenant: null,
        comment: "",
        changes: []
      }
    }
  },
  mounted() {
    console.log("mounted")
  }
}
</script>
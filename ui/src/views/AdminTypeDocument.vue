<template>
  <section>
    <oscar-dialog :options="dialogDelete"/>
    <div class="vue-loader" v-if="loading">
      <span> {{ loadingMsg }}</span>
    </div>
    <transition name="popup">
      <div class="form-wrapper" v-if="form">
        <form action="" @submit.prevent="handlerSave" class="container oscar-form">
          <header>
            <h1>
              <span v-if='form-id'>Modification de <strong>{{ form.label }}</strong></span>
              <span v-else>Nouveau type de documents</span>
            </h1>
          </header>

          <div class="form-group">
            <label>Intitulé</label>
            <input id='typedoc_label' type="text" class="form-control" v-model="form.label" name="label"/>
          </div>

          <div class="form-group">
            <label for="typedoc_default">
              Par défaut
              <input id='typedoc_default' type="checkbox" class="form-control" v-model="form.default" name="default"/>
            </label>
            <div class="alert alert-info">
              L'option <strong>par défaut</strong> définira ce type de document comme celui à utilisé si rien n'est
              précisé.
              Par exemple, lors des demandes d'activité, les documents envoyés par les utilisateurs seront qualifiés
              avec
              ce type (cela peut être modifié par la suite).
            </div>
          </div>

          <footer class="buttons-bar">
            <div class="btn-group">
              <button type="submit" class="btn btn-primary">
                <i class="icon-floppy"></i>
                Enregistrer
              </button>
              <button type="reset" class="btn btn-default" @click="form=null">
                <i class="icon-floppy"></i>
                Annuler
              </button>
            </div>
          </footer>
        </form>
      </div>
    </transition>
    <div class="row">
      <div class="col-md-8">
        <h1>Types de documents disponibles</h1>
        <!-- Vue principale pour les types de documents -->
        <article v-for="typedoc in types" class="card xs" :class="{'selected active': typedoc.default}">
          <h1 class="card-title">
            <span>
              {{ typedoc.label }}
              <small v-if="typedoc.default"> (par défaut)</small>
              -
              <a :href="typedoc.documents_view" v-if="typedoc.documents_total"
                 title="Afficher les activités contenant ce type de document">
                {{ typedoc.documents_total }} document(s)
              </a>
              <em v-else>Aucun document</em>
            </span>
          </h1>
          <nav class="card-footer" v-if="manage">
            <button class="btn btn-xs btn-primary" @click="handlerEdit(typedoc)">
              <i class="icon-pencil"></i>
              Éditer
            </button>
            <button class="btn btn-xs btn-default" @click="remove(typedoc)">
              <i class="icon-trash"></i>
              Supprimer
            </button>
          </nav>
        </article>
      </div>
      <div class="col-md-4">
        <h2>Informations</h2>
        <div class="alert alert-info">
          Les <strong>types de document</strong> permettent que qualifier les documents
        </div>
        <template v-if="untyped_documents">
          <div class="alert alert-danger">
            <h3>
              <i class="icon-attention-1"></i>
              Documents non-typés détéctés</h3>
            <strong>Attention</strong>, il y'a <strong>{{ untyped_documents }}</strong> documents sans type de document.
            Ils correspondent généralement à des documents envoyés via les demandes d'activités ou envoyés dans une version plus
            ancienne de Oscar. <br>
            Vous pouvez leurs attribuer automatiquement un type avec la procédure ci-dessous :
            <hr>
            <form action="" method="post">
              <input type="hidden" name="action" value="migrate">
              <label for="migrator">
              Migrer vers ce type
              <select v-model="migrate_dest" id="migrator" class="form-control" name="destination">
                <option v-for="t in types" :value="t.id">{{ t.label }}</option>
              </select>
              </label>
              <button type="submit" class="btn btn-success" :class="{'disabled': !migrate_dest }" >
                Migrer les documents non-typés
              </button>
            </form>
          </div>
        </template>
      </div>
    </div>
    <button @click="formNew" class="btn btn-primary" v-if="manage">
      <i class="icon-circled-plus"></i>
      Ajouter
    </button>
  </section>
</template>
<script>

import axios from "axios";
import OscarDialog from "../components/OscarDialog.vue";

export default {
  components: {
    OscarDialog
  },
  props: {
    url: {required: true},
    manage: false,
    bootbox: {required: true}
  },
  data() {
    return {
      types: [],
      loadingMsg: null,
      form: null,
      dialogDelete: {display: false},
      untyped_documents: 0,
      migrate_dest: null
    }
  },
  computed: {
    loading() {
      return this.loadingMsg != null;
    }
  },
  methods: {

    formNew() {
      this.form = {
        label: "",
        description: ""
      }
    },

    handlerEdit(type) {
      this.form = JSON.parse(JSON.stringify(type));
    },

    handlerSave() {
      if (this.form.id) {
        this.loadingMsg = "Mise à jour du type de document...";

        let json = {
          'typedocumentid': this.form.id,
          'label': this.form.label,
          'description': this.form.description,
          'default': this.form.default ? 'on' : ''
        };

        axios.put(this.url + "", json).then(
            (res) => {
              flashMessage('success', 'Type de document mis à jour');
              this.fetch();
            },
            (err) => {
              flashMessage('error', err.response.data);
            }
        ).then(() => {
          this.loadingMsg = null;
          this.form = null;
        });
      } else {
        this.loadingMsg = "Ajout d'un nouveau type de document...";
        var datas = new FormData();
        datas.append('label', this.form.label);
        datas.append('description', this.form.description);
        datas.append('default', this.form.default ? 'on' : '');

        axios.post(this.url, datas).then(
            (res) => {
              flashMessage('success', 'Type de document créé');
              this.fetch()
            },
            (err) => {
              flashMessage('error', err.response.data);
            }
        ).then(() => {
          this.loadingMsg = null;
          this.form = null;
        });
      }
    },

    remove(typedoc) {
      this.dialogDelete.title = "Supprimer ce type de document ?";
      this.dialogDelete.message = `La suppression du type de document '${typedoc.label}' sera définitive.`;
      this.dialogDelete.display = true;
      this.dialogDelete.onSuccess = () => this.removeConfirm(typedoc);
    },

    removeConfirm(typedoc) {
      this.loadingMsg = "Suppression du type de document...";
      axios.delete(this.url + '?typedocumentid=' + typedoc.id).then(
          (res) => {
            this.fetch();
          },
          (err) => {
            flashMessage('error', err.body);
          }
      ).then(() => {
        this.loadingMsg = null, this.form = null;
      });
    },


    /**
     * Chargement des types
     */
    fetch() {
      this.loadingMsg = "Chargement des types de documents";
      axios.get(this.url)
          .then(response => {
            this.types = response.data.types;
            this.untyped_documents = response.data.untyped_documents;
          })
          .catch(err => flashMessage('error', "Impossible de charger les types de documents : " + err))
          .finally(() => {
            this.loadingMsg = null
          });
    }
  },
  created() {
    this.fetch();
  }
}
</script>
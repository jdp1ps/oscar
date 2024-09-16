<template>
  <section>
    <div class="vue-loader" v-if="loading">
      <span>{{ loadingMsg }}</span>
    </div>

    <!-- Ecran de récapitulatif de suppression -->
    <div class="overlay" v-if="deleteRole">
      <div class="overlay-content container">
        <h3>Supprimer le role <strong>{{ deleteRole.label }}</strong> ?</h3>
        <nav>
          <button type="button" class="btn btn-danger" @click="performDelete()">Supprimer</button>
          <button type="button" @click="deleteRole = null" class="btn btn-default">Annuler</button>
        </nav>
      </div>
    </div>

    <!-- Ecran pour configurer le merge de rôle des organisation -->
    <div class="overlay" v-if="merged">
      <div class="overlay-content container">
        <h3>Migration du rôle <strong>{{ merged.label }}</strong> ?</h3>
        Migration du rôle <strong>{{ merged.label }}</strong> vers le rôle
        <select v-model="merged_dest">
          <option v-for="r in roles" :value="r">{{ r.label }}</option>
        </select>
        <div v-if="merged_dest" class="alert alert-danger">
          Les utilisation du rôle <strong>{{ merged.label }}</strong> vont être remplacées par
          le rôle <strong>{{ merged_dest.label }}</strong>, cette modification sera appliquées sur
          toutes les activités de recherche / projets.
        </div>
        <nav>
          <button type="button" class="btn btn-danger" @click="performMerge()">Migrer</button>
          <button type="button" @click="merged = null" class="btn btn-default">Annuler</button>
        </nav>
      </div>
    </div>

    <!-- Formulaire de modification/création -->
    <transition name="popup">
      <div class="form-wrapper" v-if="form">
        <form action="" @submit.prevent="save" class="container oscar-form">
          <header>
            <h1>
              <span v-if="form.id">Modification de <strong>{{ form.label }}</strong></span>
              <span v-else>Nouveau rôle</span>
            </h1>
            <a href="#" @click="form = null" class="closer">
              <i class="glyphicon glyphicon-remove"></i>
            </a>
          </header>

          <div class="form-group">
            <label>Nom du rôle</label>
            <input id="role_roleid" type="text" class="form-control" placeholder="Role" v-model="form.label"
              name="label" />
          </div>
          <div class="form-group">
            <label>Principal (<span
                :style="{ 'text-decoration': form.principal ? 'underline' : 'line-through' }">Activation des
                privilèges</span>)</label>
            <p class="alert alert-warning">
              <i class="icon-help-circled"></i>
              Un rôle définit comme principal débloque les droits des membres de l'organisation lorsqu'elle est affectée
              avec ce rôle à une activités
            </p>
            <input type="checkbox" class="form-control" v-model="form.principal" />
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" v-model="form.description"></textarea>
          </div>

          <footer class="buttons-bar">
            <div class="btn-group">
              <button type="submit" class="btn btn-primary">
                <i class="icon-floppy"></i>
                Enregistrer
              </button>
              <button type="button" class="btn btn-default" @click="form = null">
                <i class="icon-block"></i>
                Annuler
              </button>
            </div>
          </footer>
        </form>
      </div>
    </transition>
    <article v-for="role in roles" class="card xs" :class="{ 'active': role.principal }">
      <h1 class="card-title">
        <span>
          <i v-if="role.principal" class="icon-asterisk"></i>
          {{ role.label }}
          <span class="cartouche xs" :class="{ 'primary': role.in_activity || role.in_project }">
            <strong>{{ role.in_activity + role.in_project }}</strong>
            utilisation(s)
          </span>
        </span>

      </h1>
      <p v-if="role.principal" class="alert alert-warning">
        <i class="icon-attention-1"></i>
        Ce rôle <strong style="text-decoration: underline">débloque les privilèges</strong> de ces membres lorsqu'il est
        utilisé pour qualifier le rôle d'une organisation sur une activité/un projet
      </p>
      <p>{{ role.description }}</p>
      <nav class="card-footer" v-if="manage">
        <button class="btn btn-xs btn-default" @click="form = JSON.parse(JSON.stringify(role))">
          <i class="icon-pencil"></i>
          Éditer
        </button>
        <button class="btn btn-xs btn-info" @click="mergeUi(role)">
          <i class="icon-fork"></i>
          Migrer
        </button>
        <button class="btn btn-xs btn-danger" @click="remove(role)">
          <i class="icon-trash"></i>
          Supprimer
        </button>
      </nav>
    </article>
    <button @click="formNew" class="btn btn-default" v-if="manage">
      <i class="icon-circled-plus"></i>
      Ajouter un nouveau rôle
    </button>
  </section>
</template>
<script>

import axios from 'axios';

export default {
  data() {
    return {
      roles: [],
      loadingMsg: null,
      form: null,
      manage: false,
      deleteRole: null,
      merged: null,
      merged_dest: null
    }
  },
  props: {
    url: { required: true },
    manage: { default: false }
  },
  computed: {
    loading() {
      return this.loadingMsg != null;
    }
  },
  methods: {

    /**
     * Affichage de l'interface de fusion des rôles.
     * 
     * @param role 
     */
    mergeUi(role) {
      this.merged_dest = null;
      this.merged = role;
    },

    /**
     * Déclenchement de la fusion des rôles.
     */
    performMerge() {
      let data = {
        action: 'merge',
        from: this.merged,
        to: this.merged_dest
      };
      axios.post(this.url, data).then(
        ok => {
          console.log('ok');
          this.fetch();
        }, err => {
          console.log(err);
          flashMessage('error', err.response.data);
        }
      ).then(() => {
        this.loadingMsg = null;
        this.merged = null;
      });
    },

    /**
     * Affichage du formulaire pour créer
     */
    formNew() {
      this.form = {
        label: "",
        description: "",
        principal: false
      };
    },

    /**
     * Enregistrement (Ajout/Modification)
     */
    save() {
      if (this.form.id) {
        this.loadingMsg = "Mise à jour du rôle...";
        axios.put(this.url + '/' + this.form.id + "", this.form).then(
          (res) => {
            for (let i = 0; i < this.roles.length; i++) {
              if (this.roles[i].id === this.form.id) {
                this.roles.splice(i, 1, res.data);
                flashMessage('success', 'le rôle a bien été mis à jour.');
                return;
              }
            }
          },
          (err) => {
            flashMessage('error', err.response.data);
          }
        ).then(() => {
          this.loadingMsg = null;
          this.form = null;
        });
      } else {
        this.loadingMsg = "Ajout du nouveau rôle...";
        axios.post(this.url + '', this.form).then(
          (res) => {
            this.roles.push(res.data);
            flashMessage('success', 'le rôle a bien été ajouté.');
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

    performDelete() {
      this.loadingMsg = "Suppression du rôle...";
      let role = this.deleteRole;
      axios.delete(this.url + '/' + role.id + '', this.form).then(
        (res) => {
          this.roles.splice(this.roles.indexOf(role), 1);
          flashMessage('success', 'le rôle a bien été supprimé.');
        },
        (err) => {
          flashMessage('error', err.response.data);
        }
      ).then(() => {
        this.loadingMsg = null;
        this.form = null;
        this.deleteRole = null
      });
    },

    remove(role) {
      this.deleteRole = role;
    },

    fetch() {
      this.loadingMsg = "Chargement des rôles";
      axios.get(this.url).then(
        (res) => {
          this.roles = res.data;
        }, (err) => {
          flashMessage('error', err.body);
        }).then(() => this.loadingMsg = null);
    }
  },
  created() {
    this.fetch();
  }
}
</script>
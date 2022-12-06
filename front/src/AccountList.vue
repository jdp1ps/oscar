<template>
  <!-- Liste des comptes -->
  <section class="account-list admin">

    <transition name="fade">
      <div class="overlay" v-if="editedAccount">
        <div class="overlay-content">
          <h3>
            Modification de la masse budgétaire pour le compte
            <strong>{{ editedAccount.label }}</strong>
            <span class="overlay-closer" @click="editedAccount = null">X</span>
          </h3>
          <p>
            Code OSCAR : <strong>{{ editedAccount.code }}</strong>
            Code Comptable (SIFAC) : <strong>{{ editedAccount.codeFull }}</strong>
          </p>

          <p>
            Choisissez une annexe budgétaire :
            <select name="" id="" class="form-control" v-model="editedAccount.annexe">
              <option value="0">Ignorer</option>
              <option value="1">Traiter comme une recette</option>
              <option :value="masse" v-for="text,masse in masses">{{ text }}</option>
            </select>
          </p>
          <hr>
          <button @click="editedAccount = null" class="btn btn-danger"><i class="icon-cancel-circled"></i>Annuler</button>
          <button @click="handlerPerformEdit" class="btn btn-success"><i class="icon-floppy"></i>Enregistrer</button>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="overlay" v-if="error">
        <div class="overlay-content">
          <h3><i class="icon-bug"></i> ERREUR</h3>
          <pre class="alert-danger alert">{{ error }}</pre>
          <nav class="buttons text-center">
            <button @click="error = null" class="btn btn-default">
              Fermer
            </button>
          </nav>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="overlay" v-if="pending">
        <div class="overlay-content">
          <p class="text-center">
            <i class="animate-spin icon-spinner"></i>
            {{ Pending }}
          </p>
        </div>
      </div>
    </transition>

    <p class="alert alert-info">
      <i class="icon-info-outline"></i>
      Vous trouverez ci-dessous la liste des comptes utilisés dans la remontée des dépenses. Ceux apparaissant en rouge dans cette liste n'ont pas de masse attribués et seront affichés en rouge dans une catégorie <strong>Hors-Masse</strong> dans la zone de synthèse des dépenses de la fiche activité.
    </p>

    <article v-for="a in accounts" class="card account-infos"
             :class="{
                'missing': a.annexe == null,
                'ignored': a.annexe == 0,
                'input': a.annexe == 1
              }">
      <h3>
        <code title="Code utilisé dans SIFAC">{{ a.codeFull }}</code>
        <strong>
          {{ a.label }}
          <small title="Numéro dans OSCAR">({{ a.code }})</small>
          <a href="#" @click.prevent="handlerEdit(a)" class="btn btn-xs" :class="{ 'btn-primary': a.annexe == null, 'btn-default': a.annexe != null }">
            <i class="icon-edit"></i>
            Modifier l'annexe budgétaire</a>
        </strong>



        <em v-if="a.annexe == '0'" class="off">Ignorée</em>
        <em v-else-if="a.annexe == '1'" class="plus">Recette</em>
        <em v-else-if="a.annexe" class="minus">{{ masses[a.annexe] }}</em>
        <em v-else class="value-missing">AUCUNE</em>
      </h3>
    </article>
  </section>
</template>
<script>
/**
 node node_modules/.bin/vue-cli-service build --name AccountList --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/AccountList.vue
 */
  export default {

    props: {
      url: { default: "" },
      manage: { default: "" }
    },

    data(){
      return {
        accounts: [],
        masses: [],
        editedAccount: null,
        error: "",
        pending: ""
      }
    },

    methods: {

      /**
       * Chargement des comptes utilisés dans OSCAR
       */
      fetch(){
        this.pending = "Chargement des comptes utilisés";
        this.$http.get(this.url).then( ok => {
          this.accounts = ok.data.accounts;
          this.masses = ok.data.masses;
        }, ko => {
          let message = "Erreur inconnue";
          try {
            message = ko.body;
          } catch (e) {
            message = "Erreur JS : " + e;
          }
          this.error = "Impossible de charger des comptes utilisés " + message;
        }).then( this.pending = null )
      },

      /**
       * Affichage de la fenêtre de modification des annexes budgétaires.
       *
       * @param account
       */
      handlerEdit(account){
        this.editedAccount = JSON.parse(JSON.stringify(account));
      },

      /**
       * Envoi des modifications.
       */
      handlerPerformEdit(){
        this.pending = "Enregistrement en cours";
        let accountId = this.editedAccount.id;
        let annexe = this.editedAccount.annexe;
        let data = new FormData();
        data.append('id', accountId);
        data.append('annexe', annexe);
        data.append('action', 'annexe');
        this.$http.post(this.manage, data).then(
          ok => {
            this.editedAccount = null;
            this.fetch();
          }, ko => {
              let message = "";
              console.log(ko.body);
              try {
                message = ko.body;
              } catch (e) {
                message = "Erreur JS : " + e;
              }
              console.log(message);
              this.error = "Impossible de modifier l'annexe budgétaire : " + message;
            }
        );
      }
    },

    mounted() {
      this.fetch();
    }
}
</script>
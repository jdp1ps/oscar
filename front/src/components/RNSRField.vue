<template>
    <div id="rnsr">
      <transition name="fade">
        <div id="rnsr-modal" v-if="modal" class="overlay">
          <div class="overlay-content">
            <h2>
              Recherche le numéro dans le <strong>Répertoire National des Structures de Recherche</strong>
              <span href="#" class="overlay-closer" @click="modal = false">X</span>
            </h2>
            <input type="search" v-model="search" class="form-control" />
            <hr>
            <section class="list-selector" v-if="results">
              <div v-for="a in results" >
                <article @click="handlerSelectItem(a)" class="">
                  <code title="Numéro National des Structures" class="cartouche grey xs">{{ a.fields.numero_national_de_structure }}</code>
                  <strong>{{ a.fields.libelle }}</strong>
                  <small> <i class="icon-location"></i> {{ a.fields.commune }}</small>
                </article>
              </div>
            </section>
            <div class="alert alert-info" v-else>
              Aucun résultat
            </div>

          </div>
        </div>
      </transition>
      <a class="btn btn-default" title="Rechercher dans le répertoire National des Structures de recherche (RNSR)"
         @click="handlerShow">
        <i class="icon-search-outline"></i>
        Rechercher
      </a>
    </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_module/.bin/gulp RNSRFieldWatch

Pour compiler :
node node_module/.bin/gulp RNSRField

 */
    export default {
        props: {
            class: { default: 'form-control' },
            code: { default: '' }
        },

        data() {
            return {
              search: "",
              modal: false,
              results: {},
              error: null,
              first: true
            }
        },

        watch: {
            search(val){
                this.find(val);
            }
        },

        methods: {
          find(exp){
            this.$http.get('https://data.enseignementsup-recherche.gouv.fr/api/records/1.0/search/?dataset=fr-esr-structures-recherche-publiques-actives&q=' + this.search).then(
                ok => {
                  this.results = ok.data.records;
                },
                fail => {
                  this.error = fail
                }
            )
          },

          handlerShow(){
            if( this.code && this.first ){
              this.search = this.code;
              this.find(this.code);
              this.first = false;
            }
            this.modal = !this.modal;
          },

          handlerSelectItem(item){
            this.$emit('select', item.fields.numero_national_de_structure);
            this.modal = false;
          }
        },

        mounted() {

        }
    }
</script>
<template>
  <div class="validators">
    <div class="overlay" v-if="mode == 'select-person'">
      <div class="overlay-content" style="overflow: visible">
        <h3>Choisissez une personne : </h3>
        <personselector @change="handlerPersonSelect"/>
      </div>
    </div>
    <section class="row">
      <div class="col-md-4">
        <h1>
          <i class="icon-cube"></i>
          Validation projet
          <hr>
        </h1>
        <section class="persons">
          <article class="card" v-for="p in validatorsPrj">
            <h4>
              <strong>{{ p.person }}</strong><br>
              <small><i class="icon-mail"></i>{{ p.mail }}</small>
            </h4>
            <nav>
              <button class="btn btn-danger xs btn-xs" @click="handlerRemove(p.person_id, 'prj')">
                <i class="icon-trash"></i>
                Supprimer
              </button>
            </nav>
          </article>
        </section>
        <button @click="handlerAddPerson('prj')" class="btn btn-primary">
          <i class="icon-user"></i>
          Ajouter
        </button>
      </div>

      <div class="col-md-4">
        <h1>
          <i class="icon-beaker"></i>
          Validation scientifique
        </h1>
        <section class="persons">
          <article class="card" v-for="p in validatorsSci">
            <h4>
              <strong>{{ p.person }}</strong><br>
              <small><i class="icon-mail"></i>{{ p.mail }}</small>
            </h4>
            <nav>
              <button class="btn btn-danger xs btn-xs" @click="handlerRemove(p.person_id, 'sci')">
                <i class="icon-trash"></i>
                Supprimer
              </button>
            </nav>
          </article>
        </section>
        <button @click="handlerAddPerson('sci')" class="btn btn-primary">
          <i class="icon-user"></i>
          Ajouter
        </button>
      </div>

      <div class="col-md-4">
        <h1>
          <i class="icon-book"></i>
          Validation administrative
        </h1>
        <section class="persons">
          <article class="card" v-for="p in validatorsAdm">
            <h4>
              <strong>{{ p.person }}</strong><br>
              <small><i class="icon-mail"></i>{{ p.mail }}</small>
            </h4>
            <nav>
              <button class="btn btn-danger xs btn-xs" @click="handlerRemove(p.person_id, 'adm')">
                <i class="icon-trash"></i>
                Supprimer
              </button>
            </nav>
          </article>
        </section>
        <button @click="handlerAddPerson('adm')" class="btn btn-primary">
          <i class="icon-user"></i>
          Ajouter
        </button>
      </div>

    </section>
    <nav>
      <button class="btn btn-default" @click="fetch">
        <i class="icon-rewind-outline"></i>
        FETCH
      </button>
    </nav>
  </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_modules/.bin/gulp activityValidatorWatch

Pour compiler :
node node_modules/.bin/gulp activityValidator

 */

const WHERE_PRJ = 'prj';
const WHERE_SCI = 'sci';
const WHERE_ADM = 'adm';

import PersonSelector from './components/PersonAutoCompleter'

export default {

  components: {
    'personselector': PersonSelector
  },

  props: {
    url: {required: true, default: ''},
    documentTypes: {required: true},
    urlDocumentType: {required: true}
  },

  data() {
    return {
      validatorsPrj: [],
      validatorsSci: [],
      validatorsAdm: [],
      where: null,
      mode: ""
    }
  },

  methods: {

    handlerSuccess(){
      console.log('handlerSuccess', arguments);
    },

    handlerPersonSelect(person){
      console.log('handlerPersonSelect', arguments);
      this.addPerson(person, this.where);
      this.mode = "";
      this.where = null;
    },

    handlerAddPerson(where){
      this.where = where;
      this.mode = 'select-person';
    },

    handlerRemove(personId, where){
      this.$http.delete(this.url+'?p='+personId+'&w='+where).then(
          ok => {
            this.fetch();
          },
          ko => {
            console.log(ko);
          }
      )
    },

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    addPerson(person, where){
      let send = new FormData();
      send.append('person_id', person.id);
      send.append('where', where);
      this.$http.post(this.url, send).then(
          ok => {
            this.fetch();
          },
          ko => {
            console.log(ko);
          }
      )

      console.log(send);
    },

    fetch() {
      this.$http.get(this.url).then(
          ok => {
            console.log(ok);
            this.validatorsPrj = ok.data.validators.validators_prj;
            this.validatorsSci = ok.data.validators.validators_sci;
            this.validatorsAdm = ok.data.validators.validators_adm;
          },
          ko => {
            console.log(ko);
          }
      )
    }
  },

  mounted() {
    this.fetch();
  }

}
</script>
<template>
  <div class="validators">
    <div class="overlay" v-if="mode == 'select-person'">
      <div class="overlay-content" style="overflow: visible">
        <div class="overlay-closer" @click="mode = ''">
          x
        </div>
        <h3>Choisissez une personne : </h3>
        <personselector @change="handlerPersonSelect"/>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="">
          <h2>
            <i class="icon-archive"></i>
            Lots de travail
          </h2>
          <section class="workpackages">
            <div class="card xs workpackage" v-for="wp in workpackages">
              <h3>
                <i class="icon-archive"></i>
                <code>[{{ wp.code }}]</code>
                <em>{{ wp.label }}</em>
              </h3>
              <p v-if="wp.description">{{ wp.description }}</p>
              <section class="declarers" v-if="wp.persons">
                <h5>Déclarants</h5>
                <article class="declarer" v-for="d in wp.persons" class="personcard">
                  <h5 class="personcard-header">
                    <img :src="'//www.gravatar.com/avatar/' + d.person.mailMd5 +'?s=40'" alt=""
                         class="personcard-gravatar">
                    <div class="personcard-infos">
                      <strong>{{ d.person.displayname }}</strong><br>
                      <small>
                        <i class="icon-mail"></i>
                        {{ d.person.mail }}
                    </div>
                  </h5>
                </article>
              </section>
              <div class="alert-warning alert" v-else>
                Aucun déclarant indentifié pour ce lot de travail
              </div>
              <!--
              <div class="personcard card button">
                Ajouter un déclarant
              </div>
              -->
            </div>

          </section>

        </div>
        <!-- <pre>{{ workpackages }}</pre> -->

        <div class="validators">
          <h2>
            <i class="icon-users"></i>
            Validateurs
          </h2>
          <section class="row">
            <div class="col-md-4">
              <h3>
                <i class="icon-cube"></i>
                Validation projet
              </h3>
              <section class="persons">
                <article class="personcard card" v-for="p in validatorsPrj">
                  <h5 class="personcard-header">
                    <img :src="'//www.gravatar.com/avatar/' + p.mailMd5 +'?s=40'" alt="" class="personcard-gravatar">
                    <div class="personcard-infos">
                      <strong>{{ p.person }}</strong><br>
                      <small>
                        <i class="icon-mail"></i>
                        {{ p.mail }}
                      </small>
                    </div>
                  </h5>
                  <nav class="buttons text-center">
                    <button class="btn btn-danger btn-xs xs" @click="handlerRemove(p.person_id, 'prj')">
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
              <h3>
                <i class="icon-beaker"></i>
                Validation scientifique
              </h3>
              <section class="persons">
                <article class="personcard card" v-for="p in validatorsSci">
                  <h5 class="personcard-header">
                    <img :src="'//www.gravatar.com/avatar/' + p.mailMd5 +'?s=40'" alt="" class="personcard-gravatar">
                    <div class="personcard-infos">
                      <strong>{{ p.person }}</strong><br>
                      <small>
                        <i class="icon-mail"></i>
                        {{ p.mail }}
                      </small>
                    </div>
                  </h5>
                  <nav class="buttons text-center">
                    <button class="btn btn-danger btn-xs xs" @click="handlerRemove(p.person_id, 'sci')">
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
              <h3>
                <i class="icon-book"></i>
                Validation administrative
              </h3>
              <section class="persons">
                <article class="personcard card" v-for="p in validatorsAdm">
                  <h5 class="personcard-header">
                    <img :src="'//www.gravatar.com/avatar/' + p.mailMd5 +'?s=40'" alt="" class="personcard-gravatar">
                    <div class="personcard-infos">
                      <strong>{{ p.person }}</strong><br>
                      <small>
                        <i class="icon-mail"></i>
                        {{ p.mail }}
                      </small>
                    </div>
                  </h5>
                  <nav class="buttons text-center">
                    <button class="btn btn-danger btn-xs xs" @click="handlerRemove(p.person_id, 'adm')">
                      <i class="icon-trash"></i>
                      Supprimer
                    </button>
                  </nav>
                </article>
              </section>

              <a @click="handlerAddPerson('adm')" class="personcard button">
                Ajouter un validateur administratif
              </a>
            </div>

          </section>
        </div>
      </div>
      <div class="col-md-4">
        <section class="members">
          <h2>
            <i class="icon-users"></i>
            Membres identifiés
          </h2>

          <article class="personcard card" v-for="p in members">
            <h5 class="personcard-header">
              <img :src="'//www.gravatar.com/avatar/' + p.mailMd5 +'?s=40'" alt="" class="personcard-gravatar">
              <div class="personcard-infos">
                <strong>{{ p.person }}</strong><br>
                <small>
                  <i class="icon-mail"></i>
                  {{ p.mail }}
                </small><br>
                Rôle(s) : <strong>{{ p.roles.join(', ') }}</strong>
              </div>
            </h5>
          </article>
        </section>

        <section class="validations">
          <h2>
            <i class="icon-calendar"></i>
            Validations
          </h2>

          <article class="card" :class="'status-'+v.status" v-for="v in validations">
            <h5>
              <i :class="'icon-'+v.status"></i>
              {{ v.period }} | <strong>{{ v.declarer }}</strong>
            </h5>
            <div>
              <small>
                <i class="icon-cube"></i>
                {{ v.validatedPrjBy }}
              </small>

              <small>
                <i class="icon-beaker"></i>
                {{ v.validatedSciBy }}
              </small>

              <small>
                <i class="icon-book"></i>
                {{ v.validatedAdmBy }}
              </small>

            </div>
          </article>

        </section>

      </div>
    </div>


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
      workpackages: [],
      declarers: [],
      validations: [],
      members: [],
      where: null,
      mode: ""
    }
  },

  methods: {

    handlerSuccess() {
      console.log('handlerSuccess', arguments);
    },

    handlerPersonSelect(person) {
      console.log('handlerPersonSelect', arguments);
      this.addPerson(person, this.where);
      this.mode = "";
      this.where = null;
    },

    handlerAddPerson(where) {
      this.where = where;
      this.mode = 'select-person';
    },

    handlerRemove(personId, where) {
      this.$http.delete(this.url + '?p=' + personId + '&w=' + where).then(
          ok => {
            this.fetch();
          },
          ko => {
            console.log(ko);
          }
      )
    },

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    addPerson(person, where) {
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
    },

    fetch() {
      this.$http.get(this.url).then(
          ok => {
            this.validatorsPrj = ok.data.validators.validators_prj;
            this.validatorsSci = ok.data.validators.validators_sci;
            this.validatorsAdm = ok.data.validators.validators_adm;
            this.workpackages = ok.data.workpackages;
            this.members = ok.data.members;
            this.validations = ok.data.validations;
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
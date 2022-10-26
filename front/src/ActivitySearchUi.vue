<template>
  <div>
    <div class="overlay" v-if="error">
      <div class="overlay-content">
        <a href="#" @click="error = ''">Fermer</a>
        {{ error }}
      </div>
    </div>

    <div class="overlay" v-if="debug">
      <div class="overlay-content">
        <a href="#" @click="debug = ''">
          <i class="icon-bug"></i>
          Fermer</a>
        <pre>
          {{ debug }}
        </pre>
      </div>
    </div>

    <transition name="fade">
      <div class="vue-loader" v-show="loaderMsg">
        <div class="content-loader">
          <i class="icon-spinner animate-spin"></i>
          {{ loaderMsg }}
        </div>
      </div>
    </transition>

    <h1>
      <i class="icon-cube"></i>
      {{ title }}
    </h1>

    <form action="">
      <div class="input-group input-group-lg">
        <input placeholder="Rechercher dans l'intitulé, code PFI...…"
               class="form-control input-lg" name="q"
               v-model="search" type="search">
        <span class="input-group-btn">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </span>
      </div>
      <section v-if="showCriteria">
        <h3>Critères de recherche</h3>
        <div class="row">
          <div class="col-md-4">
            <h5>Filtres</h5>
            <select class="form-control" @change="handlerSelectFilter" v-model="selecting_filter">
              <option value="">Ajouter un filtre&hellip;</option>
              <option :value="f" v-for="label,f in filters">{{ label }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <h5>Status</h5>
            <super-select :options="status"
                          :name="'st'"
                          @change="updateSelected"
                          v-model="used_status"
                          style="min-width: 250px"/>
          </div>
          <div class="col-md-2">
            <h5>Trier par</h5>
            <select v-model="sorter" name="t" class="form-control">
              <option :value="s" v-for="text, s in sortters">{{ text }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <h5>Ordre</h5>
            <select v-model="direction" name="d" class="form-control">
              <option :value="s" v-for="text, s in directions">{{s}} - {{ text }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <h5>Ignorer si null</h5>

            <label for="ui_vuecompact" class="label-primary">
              Mode compact
              <input id="ui_vuecompact" name="ui_vuecompact" v-model="ui_vuecompact" type="checkbox">
            </label>

          </div>
        </div>

        <hr>

        <section v-for="f in filters_obj">
          <a-s-filter-person v-if="f.type == 'ap'" :type="'ap'"
                             :value1="f.value1" :value2="f.value2"
                             :roles_values="roles_person"
                             :error="f.error"
                             @delete="handlerDeleteFilter(f)"/>

          <a-s-filter-person v-else-if="f.type == 'pm'" :type="'pm'"
                             :value1="f.value1" :value2="f.value2"
                             :multiple="true"
                             :roles_values="roles_person"
                             :error="f.error"
                             @delete="handlerDeleteFilter(f)"/>

          <a-s-filter-organization v-else-if="f.type == 'ao'" :type="'ao'"
                             :value1="f.value1" :value2="f.value2"
                             :roles_values="roles_organizations"
                             :error="f.error"
                             @delete="handlerDeleteFilter(f)"/>

          <a-s-filter-organization v-else-if="f.type == 'so'" :type="'so'"
                                   :label="'N\'impliquant pas'"
                                   :value1="f.value1" :value2="f.value2"
                                   :roles_values="roles_organizations"
                                   :error="f.error"
                                   @delete="handlerDeleteFilter(f)"/>

          <a-s-filter-person v-else-if="f.type == 'sp'" :type="'sp'"
                             :value1="f.value1" :value2="f.value2"
                             :label="'N\'impliquant pas'"
                             :roles_values="roles_person"
                             :error="f.error"
                             @delete="handlerDeleteFilter(f)"/>

          <a-s-filter-select v-else-if="f.type == 'cnt'" :type="'cnt'"
                             :value1="f.value1"
                             :label="'Pays (d\'une organisation)'"
                             :icon="'icon-flag'"
                             :error="f.error"
                             :options="options_pays"
                             @delete="handlerDeleteFilter(f)"/>

          <select-key-value v-else-if="f.type == 'tnt'" :type="'tnt'"
                             :value1="f.value1"
                             :label="'Type d\'organisation'"
                             :icon="'icon-tag'"
                             :error="f.error"
                             :options="options_organization_types"
                             @delete="handlerDeleteFilter(f)"/>

          <single-date-field v-else-if="f.type == 'add'" :type="f.type"
                             :moment="moment"
                             :value1="f.value1"
                             :value2="f.value2"
                             :label="'Date de début'"
                             :error="f.error"
                             @delete="handlerDeleteFilter(f)"/>

          <single-date-field v-else-if="f.type == 'adf'" :type="f.type"
                             :moment="moment" :error="f.error"
                             :value1="f.value1" :value2="f.value2"
                             :label="'Date de fin'"
                             @delete="handlerDeleteFilter(f)"/>

          <single-date-field v-else-if="f.type == 'adc'" :type="f.type"
                             :moment="moment" :error="f.error"
                             :value1="f.value1" :value2="f.value2"
                             :label="'Date de Création'"
                             @delete="handlerDeleteFilter(f)"/>

          <single-date-field v-else-if="f.type == 'adm'" :type="f.type"
                             :moment="moment" :error="f.error"
                             :value1="f.value1" :value2="f.value2"
                             :label="'Date de Mise à jour'"
                             @delete="handlerDeleteFilter(f)"/>


          <div v-else class="card critera">
            non géré {{ f }}
          </div>
        </section>

        <nav class="text-right">
          <button type="reset" class="btn btn-default">
            Réinitialiser le recherche
          </button>
          <button type="submit" class="btn btn-primary">
            Actualiser la recherche
          </button>
        </nav>
      </section>
    </form>

    <section v-if="search !== null">
      <h2 class="text-right">{{ totalResultQuery }} résultat(s)</h2>
      <transition-group name="list" tag="div">
        <activity :activity="activity" v-for="activity in activities"
                  @debug="catchDebug"
                  :key="activity.id" :compact="ui_vuecompact"/>
      </transition-group>
    </section>

  </div>
</template>
<script>
import ActivitySearchItem from './ActivitySearchItem.vue';
import SuperSelect from './components/SuperSelect.vue';
import PersonAutoCompleter from "./components/PersonAutoCompleter2";
import OrganizationAutoCompleter from "./components/OrganizationAutoCompleter";
import vSelect from 'vue-select';

// Filtres
import ASFilterPerson from "./searchfilters/ASFilterPerson";
import ASFilterOrganization from "./searchfilters/ASFilterOrganization";
import ASFilterSelect from "./searchfilters/ASFilterSelect";
import SelectKeyValue from "./searchfilters/SelectKeyValue";
import SingleDateField from "./searchfilters/SingleDateField";

// import OscarGrowl from './OscarGrowl.vue';
// import OscarBus from './OscarBus.js';

/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :
cd front
Pour compiler en temps réél :
node node_modules/.bin/vue-cli-service build --name ActivitySearchUi --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/ActivitySearchUi.vue --watch
 */

//node node_modules/.bin/poi watch --format umd --moduleName  ActivitySearchUi --filename.css ActivitySearchUi.css --filename.js ActivitySearchUi.js --dist public/js/oscar/dist public/js/oscar/src/ActivitySearchUi.vue


export default {
  props: {
    url: {required: true},
    first: {required: true, typ: Boolean},
    title: {default: "Activités de recherche"},
    sortters: {required: true},
    moment: {require: true },
    filters: {required: true},
    directions: {required: true},
    direction: { default: 'desc' },
    sorter: { default: 'hit' },
    status: {required: true},
    roles_person: {required: true},
    roles_organizations: {required: true},
    search: { require: false, default: "" },
    selected_status: { default: [] },
    options_pays: { default: [] },
    options_organization_types: { default: [] },
    used_filters: { require: false, default: []},
    used_status: { require: false, default: []},
    showCriteria: { default: true },
    selectedOrganization: null
  },

  components: {
    ASFilterOrganization,
    activity: ActivitySearchItem,
    SuperSelect,
    PersonAutoCompleter,
    vSelect,
    OrganizationAutoCompleter,
    ASFilterPerson,
    ASFilterSelect,
    SelectKeyValue,
    SingleDateField
  },

  data() {
    return {
      loaderMsg: "",
      page: 1,
      totalPages: 0,
      totalResultQuery: 0,
      previous: null,
      activities: [],
      ui_vuecompact: true,
      filters_obj: [],
      selecting_filter: "",
      debug: ''
    }
  },

  computed: {
    displayedFilters(){
      return [];
    },

    urlSearch() {
      // Filtres
      let filters = [];
      this.filters_obj.forEach(f => {
        filters.push('f[]=' +f.type +';' +f.value1 +';' +f.value2);
      })


      return this.url
          + "?q=" + this.search
          + "&p=" + this.page
          + "&t=" + this.sorter
          + "&d=" + this.direction
          + '&st=' + this.used_status
          + '&' +filters.join('&');
    }
  },

  methods: {
    catchDebug(arg){
      this.debug = arg;
    },

    ///////////////////////////////////////////////////////////
    // Capture des interactions
    handlerSelectPerson(dataPerson, filter) {
      console.log(dataPerson, filter);
      filter.value1 = dataPerson.id;
      filter.value1displayed = dataPerson.displayname;
    },

    handlerDeleteFilter(filter) {
      this.filters_obj.splice(this.filters_obj.indexOf(filter), 1);
    },

    handlerSelectPersonRole( role, filter ){
      filter.value2 = role.target.value;
    },

    handlerSelectFilter() {
      this.addNewFilter(this.selecting_filter);
      this.selecting_filter = "";
    },

    handlerSubmit() {
      this.performSearch(this.search, 1, 'Recherche...')
    },

    addNewFilter(filterKey, value1 = "", value2 = "") {
      console.log('Ajout du filtre', filterKey);
      this.filters_obj.push({
        type: filterKey,
        value1: value1,
        value2: value2
      })
    },

    updateSelected(evt) {
      console.log('evt', evt);
    },

    /**
     * Retourne le filtre (objet) en fonction de l'entrée.
     *
     * @param str
     * @return {null|*}
     */
    getFilterByStr( str ){
      for( let i = 0; i<this.filters_obj.length; i++ ){
        if( this.filters_obj[i].input == str ){
          return this.filters_obj[i];
        }
      }
      return null;
    },

    /**
     * Déclenche la recherche.
     *
     * @param what
     * @param page
     * @param msg
     */
    performSearch(what, page, msg) {
      this.loaderMsg = msg;
      this.search = what === null ? '' : what;
      this.$http.get(this.urlSearch).then(
          (ok) => {
            if (ok.body.page == 1) {
              this.activities = [];
            }
            this.activities = this.activities.concat(ok.body.datas.content);
            this.totalResultQuery = ok.body.result_total;
            this.totalPages = ok.body.totalPages;
            this.page = ok.body.page;

            ok.body.filters_infos.forEach( info => {
              if( info.error ){
                let f = this.getFilterByStr(info.input);
                if( f ){
                  f.error = info.error;
                }
              }
            });
          },
          (ko) => {
            console.log(ko);
            this.error = "Impossible de charger le résultat de la recherche !";
            this.activities = [];
            this.totalResultQuery = 0;
            this.totalPages = 0;
            this.page = 0;
            if (ko.status == 403) {
              this.error += " Vous avez probablement été déconnecté de l'application"
            }
          }
      ).then(foo => {
        this.loaderMsg = "";
      });
    },

    loadNextPage() {
      if (this.page < this.totalPages) {
        this.page++;
        this.performSearch(this.search, this.page, 'Chargement de la page ' + this.page + "/" + this.totalPages);
      }
    }
  },

  mounted() {

    let params = new URLSearchParams(window.location.search);

    // Filtres
    this.used_filters.forEach(filterStr => {
      console.log("Traitement du filtre ", filterStr);
      let spt = filterStr.split(';');
      let obj = {
        type: spt[0],
        value1: spt[1],
        value2: spt[2],
        input: filterStr
      };
      this.filters_obj.push(obj);
    });

    this.handlerSubmit();

    window.onscroll = () => {
      let bottomOfWindow = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;
      if (bottomOfWindow) {
        this.loadNextPage();
      }
    };


  }
}
</script>

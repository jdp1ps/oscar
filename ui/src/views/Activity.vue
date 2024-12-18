<template>

  <loader text="Chargement de l'activité" :visible="loading"/>

  <modal title="Debugger" title-icon="icon-bug" :visible="debug_displayed" @modal-cancel="debug_displayed = false">
    <VueJsonPretty :data="debug_content"/>
    <template #buttons>
      <button class="btn btn-default" @click="handlerDebugHide">FERMER</button>
    </template>
  </modal>

  <modal title="Une erreur est survenue" title-icon="icon-bug" :visible="error != null">
    <div class="alert alert-danger">{{ error }}</div>
    <template #buttons>
      <button class="btn btn-default" @click="error=null">FERMER</button>
    </template>
  </modal>

  <div class="overlay" v-if="duplicateDatas != null">
    <div class="overlay-content">
      <h1 class="overlay-title">Dupliquer cette activité</h1>
      <a class="overlay-closer" @click="duplicateDatas = null">x</a>

      <div class="row" style="width: 80%">
        <h1 class="col-md-6 col-md-offset-3">
          <small>Options de copie pour</small> <br>

          <strong>{{ core.label }}</strong></h1>

        <div class="col-md-6 col-md-offset-3">
          <div class="list-group-item separator-bottom">
            <strong>
              <i class="icon-group"></i>
              Reprendre les personnes
            </strong>
            <div class="material-switch pull-right">
              <input id="keeppersons" name="keeppersons" type="checkbox" v-model="duplicateDatas.keepPersons"/>
              <label for="keeppersons" class="label-primary"></label>
            </div>
          </div>

          <div class="list-group-item separator-bottom">
            <strong>
              <i class="icon-calendar"></i>
              Reprendre les jalons
            </strong>
            <div class="material-switch pull-right">
              <input id="keepmilestones" name="keepmilestones" type="checkbox" v-model="duplicateDatas.keepMilestones"/>
              <label for="keepmilestones" class="label-primary"></label>
            </div>
          </div>

          <div class="list-group-item separator-bottom">
            <strong>
              <i class="icon-building-filled"></i>
              Reprendre les organisations
            </strong>
            <div class="material-switch pull-right">
              <input id="keeporganizations" name="keeporganizations" type="checkbox"
                     v-model="duplicateDatas.keepOrganizations"/>
              <label for="keeporganizations" class="label-primary"></label>
            </div>
          </div>

          <div class="list-group-item separator-bottom">
            <strong>
              <i class="icon-archive"></i>
              Reprendre les lots de travail
            </strong>

            <div class="material-switch pull-right">
              <input id="keepworkpackages" name="keepworkpackages" type="checkbox"
                     v-model="duplicateDatas.keepWorkpackages"/>
              <label for="keepworkpackages" class="label-primary"></label>
            </div>
          </div>

          <div class="list-group-item separator-bottom">
            <strong>
              <i class="icon-archive"></i>
              Reprendre les données de base
            </strong><br>
            <small>Données du formulaire de créaton : Dates de début / fin, intitulé, description, type, etc...</small>

            <div class="material-switch pull-right">
              <input id="keepadmdata" name="keepadmdata" type="checkbox" v-model="duplicateDatas.keepAdmData"/>
              <label for="keepadmdata" class="label-primary"></label>
            </div>
          </div>

          <nav class="text-center">
            <a href="#" class="btn btn-primary" @click="duplicateDatas = null">Annuler</a>
            <a href="#" class="btn btn-default" @click="handlerDuplicateDo">Dupliquer l'activité</a>
          </nav>
        </div>
      </div>

    </div>
  </div>

  <div v-if="core && credentials && credentials.read">
    <nav class="navbar navbar-default navbar-fixed-top" style="top: 50px; z-index:500">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                  data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">
            <i class="icon-cube"></i>
            <strong>{{ core.numOscar }}</strong>
            <span> / {{ labelReduced }}</span>
            <i class="icon-pin" :style="{'opacity': isSticky ? 1.0 : 0.3}" @click="toogleSticky"></i>
          </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li><a href="#members" v-if="credentials.persons.read">Membres</a></li>
            <li><a href="#partners" v-if="credentials.organizations.read">Partenaires</a></li>
            <li><a href="#milestones" v-if="credentials.milestones.read">Jalons</a></li>
            <li><a href="#notes" v-if="credentials.notes.read">Notes</a></li>
            <li><a href="#payments" v-if="credentials.budget.read">Versements</a></li>
            <li><a href="#spents" v-if="credentials.spents.read">Dépenses</a></li>
            <li><a href="#timesheets" v-if="credentials.timesheets.read">Feuilles de temps</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">

            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                 aria-expanded="false">
                <i class="icon-pin text-primary"></i>
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu">
                <li v-if="isSticky"><a href="#" @click="handlerUnSticky">Désépingler</a></li>
                <li v-else><a href="#" @click.prevent="handlerSticky">
                  <i class="icon-pin-outline"></i>
                  Epingler</a></li>
                <li role="separator" class="divider"></li>
                <li v-for="a in sticky" :class="a.id == core.id ? 'disabled':''">
                  <a href="#" @click="handlerNavigateSticky(a)">
                    <i class="icon-cube"></i>
                    <strong>{{ a.num }}</strong>
                    <em>{{ a.label }}</em>
                    <i class="icon-link-ext" v-if="a.id != core.id"></i>
                  </a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                  <a href="#" @click.prevent="handlerPurgeSticky">
                    <i class="icon-trash"></i>
                    Supprimer les épingles
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>
    <header class="jumbotron activity-header oscar-header" style="margin-top: 60px">
      <div class="row line-bottom">
        <div class="col-md-9">
          <h4 class="activity-project">
            <i class="icon-cubes"></i>&nbsp;
            <em v-if="core.project === null">Aucun Projet</em>
            <span v-else>
              <a v-if="credentials.project.read" :href="core.project.url_show">
                <strong v-if="core.project.acronym">{{ core.project.acronym }}&nbsp;</strong>
                <em>{{ core.project.label }}</em>
              </a>
              <span v-else>
                <strong>{{ core.project.acronym }} </strong>
                <em>{{ core.project.label }}</em>
              </span>
            </span>
          </h4>

          <h3>
            <span class="picto status-" :class="'status-'+core.status">
              <i class="icon" :class="'icon-'+core.status"></i>
              {{ core.status_label }}
            </span>
            <span class="activity-type">
              <i class="icon-tag"></i>
              <span class="type-chain">
                <i :class="core.type_slug"></i>
                <span v-for="t in core.type_chain">
                  {{ t.label }}
                </span>
              </span>
            </span>
          </h3>

          <h1>
            <span><i class="icon-cube"></i> {{ core.label }}</span>
          </h1>
        </div>
        <div class="col-md-3">
          <div class="budget" v-if="budget && credentials.budget.read">
            <em>Montant</em>
            <strong class="text-private amount">{{ $filters.money(budget.montant) }} {{ budget.currency.symbol }}</strong>
            <div class="details">
              <small>
                Frais de gestion :
                <b class="text-private">{{ budget.fraisDeGestion }}</b>
              </small>

              <small>
                Part unité :
                <b v-if="budget.fraisDeGestionPartUnite" class="text-private">
                  {{ budget.fraisDeGestionPartUnite }}
                </b>
              </small>

              <small>
                Part hébergeur :
                <b class="text-private">
                  {{ budget.fraisDeGestionPartHebergeur }}
                </b>
              </small>

              <small>
                Part Gestionnaire :
                <b class="text-private">
                  {{ budget.fraisDeGestionPartGestionnaire }}
                </b>
              </small>

              <small>
                TVA :
                <b class="text-private">{{ budget.tva }}</b>
              </small>
            </div>
          </div>
        </div>
      </div>

      <p class="baseline" :class="{'descriptionPacked': !descriptionFull}" @click="descriptionFull=!descriptionFull"
         v-if="core.description">
        <small>{{ core.description }}</small>
      </p>

      <div class="row line-bottom">
        <div class="col-md-4">
          <h4><i class="icon-calendar"></i>Dates</h4>
          <p class="texthighlight baseline">
            Début :
            <time>{{ $filters.date(core.dateStart) }}</time>
            <small class="aggo"> ({{ $filters.timeAgo(core.dateStart) }})</small>
            <br>
            Fin :
            <time>{{ $filters.dateFull(core.dateEnd) }}</time>
            <small class="aggo"> ({{ $filters.timeAgo(core.dateEnd) }})</small>
            <br>
            Signé le :
            <time>{{ $filters.dateFull(core.dateSigned) }}</time>
            <small class="aggo"> ({{ $filters.timeAgo(core.dateSigned) }})</small>
          </p>
          <h4><i class="icon-tags"></i>Métas-données</h4>
          <p class="texthighlight baseline">
            Disciplines :
            <span class="cartouche xs" v-for="d in core.disciplines">{{ d }}</span>
          </p>
        </div>

        <div class="col-md-4">
          <h4><i class="icon-briefcase"></i>Numérotations</h4>
          <p class="texthighlight baseline">
            N° Oscar" : <strong>{{ core.numOscar }}</strong><br/>
            Numéro financier :
            <strong v-if="core.pfi" class="text-private">{{ core.pfi }}</strong>
            <strong class="text-private" v-else>AUCUN</strong> -
            Ouverture du PFI le
            <time>{{ $filters.dateFull(core.dateOpened) }}</time>
            <br>
          </p>
          <p class="texthighlight baseline" v-for="(n, label) in core.numeros">
            {{ label }} : <strong>{{ n }}</strong>
          </p>
        </div>

        <div class="col-md-4">
          <h4><i class="icon-database-1"></i>Divers</h4>
          <p class="texthighlight baseline">
            Création
            <time>{{ $filters.dateFull(core.dateCreated) }}</time>
            <br>
            Dernière MAJ
            <time>{{ $filters.dateFull(core.dateUpdated) }}</time>
          </p>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <nav class="admin-bar">
            <a class="btn btn-primary btn-xs" v-if="credentials.core.edit" :href="core.urls.edit">
              <i class="icon-pencil"></i>
              Modifier les informations</a>

            <a class="btn btn-xs btn-default" v-if="core.urls.change_project" :href="core.urls.change_project">
              <i class="icon-cubes"></i>
              Modifier le projet</a>

            <a class="btn btn-xs btn-default" v-if="core.urls.new_project" :href="core.urls.new_project">
              <i class="icon-cubes"></i>
              Créer un nouveau projet</a>

            <a class="btn btn-xs btn-default" v-if="core.urls.duplicate" @click="handlerDuplicate">
              <i class="icon-paste"></i>
              Dupliquer</a>

            <a class="btn btn-xs btn-warning" v-if="debugEnabled" @click="handlerDebugShow($data)">
              <i class="icon-bug"></i>
              Afficher le modèle</a>

            <a class="btn btn-xs btn-warning" v-if="debugEnabled" @click="fetch">
              <i class="icon-bug"></i>
              Recharger le modèle</a>
          </nav>
        </div>
      </div>
    </header>

    <div class="container-fluid">
      <div class="col-md-8">
        <section class="section-infos" id="members" v-if="credentials.persons.read">
          <h2>
            <i class="icon-group"></i>Membres
          </h2>
          <EntityWithRole title="Personne"
                          :standalone="false"
                          :entity-link-show="credentials.persons.show"
                          :manage="credentials.persons.edit"
                          :roles="rolesPersons"
                          :items="persons"
                          :url-new="personsUrlNew"
                          :url="personsUrl"
                          :debug-enabled="debugEnabled"
                          @updated="handlerUpdatePersons"
          />
        </section>

        <section class="section-infos" id="partners" v-if="credentials.organizations.read">
          <h2><i class="icon-building-filled"></i>Partenaires</h2>
          <EntityWithRole title="Organisation"
                          :standalone="false"
                          :debug-enabled="debugEnabled"
                          :entity-link-show="credentials.organizations.show"
                          :manage="credentials.organizations.edit"
                          :roles="rolesOrganizations"
                          :items="organizations"
                          :url="organizationsUrl"
                          :url-new="organizationsUrlNew"
                          @updated="handlerUpdateOrganizations"
          />
        </section>

        <section class="section-infos" id="documents" v-if="credentials.documents.read && documents">
          <h2><i class="icon-book"></i>Documents</h2>
          <activity-document
              @debug="handlerDebug"
              @updated="handlerUpdateDocuments"
              :debug-enabled="debugEnabled"

              :standalone="true"
              :sa-tabs="documents.tabs"
              :sa-types="documents.types"
              :sa-generated-documents="documents.generatedDocuments"
              :sa-computed-documents="documents.computedDocuments"
              :sa-process-datas="documents.processDatas"
              :sa-credentials="credentials.documents"
              :url="documents.url"
              :url-upload-new-doc="documents.url_upload_new_doc"
              :url-sign-document="documents.url_sign_document"
          />
        </section>

        <section class="section-infos" id="notes" v-if="credentials.notes.read">
          <h2><i class="icon-comment"></i>Notes</h2>
          <activity-notes
              :url="notes_url"
              :showallowed="credentials.notes.read"
              :manageuserallowed="credentials.notes.edit"
              :manageadminallowed="credentials.notes.manage"
              :items="notes"
              @update="handlerUpdateNotes"
          />
        </section>

        <section id="timesheets" class="section-infos" v-if="credentials.timesheets.read">
          <h2><i class="icon-book"></i>Feuille de temps</h2>

          <h3>Général</h3>
          <a :href="timesheetsUrl"
             class="btn btn-primary">
            <i class="icon-calendar"></i>
            Informations générales et lots de travails
          </a>

          <a :href="timesheetsUrlSynthesis"
             class="btn btn-primary">
            <i class="icon-book"></i>
            Résumé et documents
          </a>

          <section v-if="workpackages">
            <h3>Déclarants</h3>
            <a :href="d.url_details" v-for="d in timesheetsDeclarers" class="btn"
               :class="d.hasDeclaration ? 'btn-primary':'btn-default'">
              <PersonDisplay :person="d" />
              <small v-if="d.hasDeclaration == false">
                (Aucune déclaration)
              </small>
            </a>

            <h3>Valideurs</h3>
            <div class="row">
              <div class="col-md-4">
                <h4>Validation projet</h4>
                <span class="cartouche primary person" v-for="p in timesheetsValidators.prj">
                  {{ p.firstname }}
                  <span class="lastname text-private">{{ p.lastname }}</span>
                </span>
              </div>
              <div class="col-md-4">
                <h4>Validation scientifique</h4>
                <span class="cartouche person primary" v-for="p in timesheetsValidators.sci">
                  {{ p.firstname }}
                  <span class="lastname text-private">{{ p.lastname }}</span>
                </span>
              </div>
              <div class="col-md-4">
                <h4>Validation administrative</h4>
                <span class="cartouche person primary" v-for="p in timesheetsValidators.adm">
                  {{ p.firstname }}
                  <span class="lastname text-private">{{ p.lastname }}</span>
                </span>
              </div>
            </div>

            <h3>Lots de travail</h3>
            <workpackages-activity
                :debug-enabled="debugEnabled"
                :editable="credentials.workpackages.edit"
                :workpackages="workpackages"
                :url="workpackagesUrl"
                :persons="personsWP"
                @update="handlerUpdateWorkpackages"
            />
          </section>
        </section>
      </div>

      <aside class="col-md-4">
        <section id="milestones" class="section-infos" v-if="credentials.milestones.read">
          <h2><i class="icon-calendar"></i>Jalons</h2>

          <Milestones :url="milestonesUrl"
                      :manage="credentials.milestones.edit"
                      :payments="payments"
                      :items="milestones"
                      :types="milestonesTypes"
          />

          <a v-if="milestonesUrlNotifications" :href="milestonesUrlNotifications"
             class="btn btn-primary">
            <i class="icon-bell"></i>
            Voir les notifications planifiées
          </a>
        </section>

        <section id="payments" class="section-infos" v-if="credentials.payments.read">
          <h2><i class="icon-bank"></i>Versements</h2>
          <Payments :url="paymentsUrl"
                    :manage="credentials.payments.edit"
                    :amount="budget.amount"
                    :payments="payments"
                    @debug="handlerDebugShow"
                    @update="handlerUpdatePayments"
          />
        </section>


        <section id="spents" class="section-infos" v-if="credentials.spents.read">
          <h2><i class="icon-bank"></i>Dépenses </h2>
          <ActivitySpentSynthesis
              :standalone="false"
              :datas="spents"
          />
          <nav class="buttons xs">
            <a :href="spentsUrlDetails" class="btn btn-primary btn" v-if="credentials.spents.details">
              <i class="icon-file-excel"></i>
              Détails des dépenses</a>
            <a :href="spentsUrlPrevisionnel" class="btn btn-primary btn"
               v-if="credentials.spents.previsionnel">
              <i class="icon-file-excel"></i>
              Dépenses prévisionnelles (beta)</a>
          </nav>

        </section>

      </aside>

    </div>
    <div class="container-fluid activity-fiche" v-if="credentials.administration.read">
      <div class="row">
        <div class="col-md-12">
          <h2>
            <i class="icon-cog"></i>
            Technique</h2>
          <ActivityLogs :url="core.urls.logs" @error="handlerError"/>
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    Données inaccessibles
  </div>
</template>
<script>

import ActivityDocument from "./ActivityDocument.vue";
import ActivityLogs from "./ActivityLogs.vue";
import ActivityNotes from "./ActivityNotes.vue";
import ActivitySpentSynthesis from "./ActivitySpentSynthesis.vue";
import axios from 'axios';
import AxiosMessage from "../utils/AxiosMessage.js";
import EntityWithRole from "./EntityWithRole.vue";
import Loader from "../components/Loader.vue";
import Milestones from "./Milestones.vue";
import Modal from "../components/Modal.vue";
import Payments from "./Payments.vue";
import PersonCartouche from "../components/PersonCartouche.vue";
import VueJsonPretty from 'vue-json-pretty';
import WorkpackagesActivity from "./WorkpackagesActivity.vue";
import 'vue-json-pretty/lib/styles.css';
import PersonDisplay from "../components/PersonDisplay.vue";

axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const storage_key = 'activities_sticky';

export default {
  name: 'Activity',

  components: {
    PersonDisplay,
    ActivityNotes,
    ActivityLogs,
    ActivityDocument,
    ActivitySpentSynthesis,
    EntityWithRole,
    Loader,
    Payments,
    PersonCartouche,
    Milestones,
    Modal,
    VueJsonPretty,
    WorkpackagesActivity
  },

  props: {
    url: {required: true},
    debugEnabled: {required: false, type: Boolean, default: false},
  },

  data() {
    return {
      budget: null,
      core: null,
      credentials: null,

      documents: [],

      milestones: [],
      milestonesTypes: [],
      milestonesUrl: null,
      milestonesUrlNotifications: null,

      notes: null,
      notes_url: null,

      payments: [],
      paymentsUrl: null,

      persons: null,
      personsUrl: null,
      personsUrlNew: null,

      rolesOrganizations: null,
      rolesPersons: null,

      timesheetsDeclarers: null,
      timesheetsValidators: null,
      timesheetsUrl: null,
      timesheetsUrlSynthesis: null,

      organizations: null,
      organizationsUrl: null,
      organizationsUrlNew: null,

      workpackages: null,
      workpackagesUrl: null,

      spents: null,


      activity: {},

      duplicateDatas: null,
      sticky: [],
      descriptionFull: false,
      debug_content: null,
      debug_displayed: false,
      error: null,
      loading: true
    }
  },

  computed: {
    personsWP() {
      if (!this.persons) {
        return [];
      } else {
        let out = {};
        this.persons.forEach(person => {
          if (!out.hasOwnProperty(person.enrolled)) {
            out[person.enrolled] = {
              id: person.enrolled,
              displayname: person.enrolledLabel,
            };
          }
        });
        return Object.values(out);
      }
    },

    storage() {
      return localStorage.getItem(storage_key);
    },

    isSticky() {
      return this.sticky.find(item => item.id == this.core.id);
    },
    labelReduced() {
      let label = this.core.label;
      if (label.length > 50) {
        return label.substr(0, 50) + '...';
      } else {
        return label;
      }
    }
  },

  methods: {
    handlerDebug(debugData) {
      this.debug_content = debugData;
      this.debug_displayed = true;
    },

    handlerDebugHide() {
      this.debug_displayed = false;
    },

    handlerDebugShow(content) {
      this.debug_content = JSON.parse(JSON.stringify(content));
      this.debug_displayed = true;
    },

    handlerError(err) {
      this.error = err.message;
    },

    handlerUpdateWorkpackages(d) {
      this.workpackages = d.entities;
    },

    handlerUpdateNotes(d) {
      this.notes = d.entities;
    },

    handlerUpdatePayments(p) {
      console.log("updatePayments", p);
      this.payments = p.entities;
    },

    handlerUpdatePersons(d) {
      this.persons = d.datas.items;
    },

    handlerUpdateOrganizations(d) {
      this.organizations = d.datas.items;
    },

    handlerUpdateDocuments(res){
      console.log("updateDocuments", res);
      this.documents = res.documents;
    },

    ////////////////////////////////////////// Système d'épingle
    handlerPurgeSticky() {
      this.sticky = [];
      localStorage.removeItem(storage_key);
    },

    toogleSticky() {
      if (this.isSticky) {
        this.handlerUnSticky()
      } else {
        this.handlerSticky()
      }
    },

    handlerUnSticky() {
      this.sticky.forEach((item, id) => {
        if (item.id == this.core.id) {
          this.sticky.splice(id, 1);
        }
      })
      localStorage.setItem(storage_key, JSON.stringify(this.sticky));
    },

    handlerSticky() {
      this.sticky.push({
        id: this.core.id,
        label: this.core.label,
        num: this.core.numOscar,
        location: document.location.href,
      });
      localStorage.setItem(storage_key, JSON.stringify(this.sticky));
    },

    handlerNavigateSticky(sticked) {
      if (sticked.location) {
        document.location = sticked.location;
      }
    },

    ////////////////////////////////////////// Système d'épingle
    test() {
      console.log(localStorage.getItem());
    },

    fetch() {
      this.loading = true;
      axios.get(this.url).then(response => {
        console.log(response.data);
        // TODO tester la clef activity.datas


        this.budget = response.data.activity.datas.budget;
        this.core = response.data.activity.datas.core;

        if (response.data.activity.datas.spents) {
          this.spents = response.data.activity.datas.spents;
        }

        if (response.data.activity.datas.documents) {
          this.handlerUpdateDocuments(response.data.activity.datas);
        }

        if (response.data.activity.datas.notes) {
          this.notes = response.data.activity.datas.notes.entities;
          this.notes_url = response.data.activity.datas.notes.url;
        }

        if (response.data.activity.datas.milestones) {
          this.milestones = response.data.activity.datas.milestones.entities;
          this.milestonesTypes = response.data.activity.datas.milestones.types;
          this.milestonesUrl = response.data.activity.datas.milestones.url;
          this.milestonesUrlNotifications = response.data.activity.datas.milestones.urlNotifications;
        }

        if (response.data.activity.datas.organizations) {
          this.organizations = response.data.activity.datas.organizations.entities;
          this.organizationsUrl = response.data.activity.datas.organizations.url;
          this.organizationsUrlNew = response.data.activity.datas.organizations.urlNew;
          this.rolesOrganizations = response.data.activity.datas.organizations.roles;
        }

        if (response.data.activity.datas.payments) {
          this.payments = response.data.activity.datas.payments.entities;
          this.paymentsUrl = response.data.activity.datas.payments.url;
        }

        if (response.data.activity.datas.persons) {
          this.persons = response.data.activity.datas.persons.entities;
          this.personsUrlNew = response.data.activity.datas.persons.urlNew;
          this.personsUrl = response.data.activity.datas.persons.url;
          this.rolesPersons = response.data.activity.datas.persons.roles;
        }

        if (response.data.activity.datas.timesheets) {
          this.timesheetsValidators = response.data.activity.datas.timesheets.validators;
          this.timesheetsDeclarers = response.data.activity.datas.timesheets.declarers;
          this.timesheetsUrl = response.data.activity.datas.timesheets.url;
          this.timesheetsUrlSynthesis = response.data.activity.datas.timesheets.urlSynthesis;
        } else {
          console.log("pas de donnée FEUILLE DE TEMPS");
        }

        if (response.data.activity.datas.workpackages) {
          this.workpackages = response.data.activity.datas.workpackages.entities;
          this.workpackagesUrl = response.data.activity.datas.workpackages.url;
        } else {
          console.log("pas de donnée LOTS DE TRAVAIL");
        }

        this.credentials = response.data.activity.credentials;

      }, error => {
        this.handlerError(AxiosMessage.manageErrorResponse(error));
      }).finally(f => {
        this.loading = false;
      })
    },

    ///////////////////////////////////////////////// DUPLICATION
    handlerDuplicate() {
      this.duplicateDatas = {
        displayed: false,
        keepPersons: true,
        keepOrganizations: true,
        keepMilestones: true,
        keepWorkpackages: false,
        keepAdmData: false
      };
    },

    handlerDuplicateDo() {
      document.location = this.core.urls.duplicate + "?"
          + (this.duplicateDatas.keepPersons ? '&keeppersons=on' : '')
          + (this.duplicateDatas.keepOrganizations ? '&keeporganizations=on' : '')
          + (this.duplicateDatas.keepMilestones ? '&keepmilestones=on' : '')
          + (this.duplicateDatas.keepWorkpackages ? '&keepworkpackage=on' : '')
          + (this.duplicateDatas.keepAdmData ? '&keepadmdata=on' : '');
    },

    handlerShowProject() {
      if (this.activity.project.url_show) {
        document.location = this.activity.project.url_show
      }
    }
  },
  mounted() {
    this.fetch();
    let saved = localStorage.getItem(storage_key);
    if (saved) {
      saved = JSON.parse(saved);
    } else {
      saved = [];
    }
    this.sticky = saved;
  }
}
</script>

<style lang="scss" scoped>

.activity-fiche {
  position: relative;
  padding-bottom: 4em;
}

.section-infos {
  margin-top: 1em;
  scroll-margin-top: 120px;

  &:target h2 {
    -webkit-animation-name: animation;
    -webkit-animation-duration: 2s;
    -webkit-animation-timing-function: ease-in-out;
    -webkit-animation-iteration-count: 1;
    -webkit-animation-play-state: running;

    animation-name: animation;
    animation-duration: 2s;
    animation-timing-function: ease-in-out;
    animation-iteration-count: 1;
    animation-play-state: running;
  }

  > h2 {
    border-bottom: 1px solid #a2a7af;
    margin: 0;
    padding: .2em 0;
  }
}


@-webkit-keyframes animation {
  0% {
    color: #333333;
  }
  15.0% {
    color: #0a53be;
  }
  30.0% {
    color: #333333;
  }
  50.0% {
    color: #0a53be;
  }
  100.0% {
    color: #333333;
  }
}

@keyframes animation {
  0% {
    color: #333333;
  }
  15.0% {
    color: #0a53be;
  }
  30.0% {
    color: #333333;
  }
  50.0% {
    color: #0a53be;
  }
  100.0% {
    color: #333333;
  }
}

.activity-project {
  padding: .25em 1em .25em .25em;
  border-bottom: #eee solid thin;
}

.activity-type {
  background: #EEE;
  padding: .25em 1em .25em .25em;
  .icon-tag {
    color: #999;
  }
}

.type-chain span {
  font-weight: 400
}

.type-chain span:last-child {
  font-weight: 700
}

.type-chain span:last-child:after {
  content: '';
}

.type-chain span {
  margin: auto;
}

.type-chain span:after {
  content: ' > ';
  color: #CCC;
}

.buttons {
  padding: 1em;
  text-align: right;
}

header {
  .line-bottom {
    border-bottom: #dee2ea thin solid;
    padding-bottom: 1em;
  }

  h3 {
    font-size: 1em;
  }

  h4 {
    color: #95afe3
  }

  .texthighlight {
    color: #111;

    strong, time {
      color: #000;
    }
  }
}

.descriptionPacked {
  cursor: pointer;
  max-height: 4em;
  overflow: hidden;
}

.budget {
  border-left: #dee2ea thin solid;
  .amount {
    font-size: 1.4em;
    border-top: solid thin #EEE;
    border-bottom: solid thin #EEE;
  }

  .details {
    font-size: .8em;

    small {
      display: block;
    }
  }
}
</style>
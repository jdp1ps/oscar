<template>

  <loader text="Chargement de l'activité" :visible="loading"/>

  <modal title="Debugger" title-icon="icon-bug" :visible="debug_displayed" @modal-cancel="debug_displayed = false">
    <pre>{{ debug_content }}</pre>
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

  <div class="overlay" v-if="duplicateDatas">
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
          <h4>
            <i class="icon-cubes"></i> :
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
            <span class="picto status-" :class="'status-'+core.statut">
              <i class="icon"></i>
              {{ core.statut_label }}
            </span>
            :::

            <span class="type-chain">
            <i :class="core.type_slug"></i>
            <span v-for="t in core.type_chain">
              {{ t.label }}
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
            <strong>{{ $filters.money(budget.montant) }} {{ budget.currency.symbol }}</strong>
            <div class="details">
              <small>
                Frais de gestion :
                <b>{{ budget.fraisDeGestion }}</b>
              </small>

              <small>
                Part unité :
                <b v-if="budget.fraisDeGestionPartUnite">
                  {{ budget.fraisDeGestionPartUnite }}
                </b>
                <i v-else>
                  ~
                </i>
              </small>

              <small>
                Part hébergeur :
                <b>
                  {{ budget.fraisDeGestionPartHebergeur }}
                </b>
              </small>

              <small>
                Part Gestionnaire :
                <b>
                  {{ budget.fraisDeGestionPartGestionnaire }}
                </b>
              </small>

              <small>
                TVA :
                <b>{{ budget.tva }}</b>
              </small>
            </div>
          </div>
          <div v-else>
            Budget: {{ budget }}
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
            Numéro financier : <strong v-if="core.pfi">{{ core.pfi }}</strong><strong
              v-else>AUCUN</strong> -
            Ouverture du PFI le
            <time>{{ $filters.dateFull(core.dateOpened) }}</time>
            <br>
          </p>
          <p class="texthighlight baseline" v-for="n, label in core.numeros">
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
          <nav class="buttons xs">
            <a class="btn btn-primary btn-xs" v-if="core.urls.edit" :href="core.urls.edit">
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

            <a class="btn btn-xs btn-danger" v-if="debugEnabled" @click="handlerDebugShow($data)">
              <i class="icon-bug"></i>
              Afficher le modèle</a>

            <a class="btn btn-xs btn-danger" v-if="debugEnabled" @click="fetch">
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
                          @updated="handlerUpdatePersons"
          />
        </section>

        <section class="section-infos" id="partners" v-if="credentials.organizations.read">
          <h2><i class="icon-building-filled"></i>Partenaires</h2>
          <EntityWithRole title="Organisation"
                          :standalone="false"
                          :entity-link-show="false"
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
              :debug-enabled="debugEnabled"
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

        <!--
        <section id="timesheets" class="section-infos" v-if="activity.timesheets.readable">
          <h2><i class="icon-book"></i>Feuille de temps</h2>
          <section id="timesheets" v-if="activity.timesheets.enabled">
            <section v-if="activity.timesheets.declarers.length">
              <h3>Général</h3>
              <a :href="activity.timesheets.url_global"
                 class="btn btn-primary">
                <i class="icon-calendar"></i>
                Informations générales
              </a>
              <a :href="activity.timesheets.url_synthesis"
                 class="btn btn-primary">
                <i class="icon-book"></i>
                Résumé et documents
              </a>
            </section>

            <section class="declarers">
              <h3>Déclarants</h3>
              <div v-if="activity.timesheets.declarers.length">
                <div class="alert alert-info">
                  <i class="icon-info-outline"></i> Pour nommer un déclarant, affectez un membre de l'activité à un des
                  lots de travail.
                </div>
                <a :href="d.url_details" v-for="d in activity.timesheets.declarers" class="btn"
                   :class="d.hasDeclaration ? 'btn-primary':'btn-default'">
                  {{ d.label }}
                </a>
              </div>
              <div v-else class="alert alert-info">
                Aucun déclarant désigné sur un lot de travail
              </div>
            </section>

            <section class="validators">
              <h3>Validateurs</h3>
              <div class="row">
                <div class="col-md-4">
                  <h4><i class="icon-cube"></i>Validation PROJET</h4>
                  <section class="persons" v-if="activity.timesheets.validators.prj.length">
                    <PersonCartouche :person="p" v-for="p in activity.timesheets.validators.prj"
                                     class="cartouche primary"/>
                  </section>
                  <div class="alert alert-warning" v-else>
                    Aucun validateur désigné pour cette étape
                  </div>
                </div>
                <div class="col-md-4">
                  <h4><i class="icon-beaker"></i>Validation SCIENTIFIQUE</h4>
                  <section class="persons" v-if="activity.timesheets.validators.sci.length">
                    <PersonCartouche :person="p" v-for="p in activity.timesheets.validators.sci"
                                     class="cartouche primary"/>
                  </section>
                  <div class="alert alert-warning" v-else>
                    Aucun validateur désigné pour cette étape
                  </div>
                </div>
                <div class="col-md-4">
                  <h4><i class="icon-hammer"></i>Validation ADMINISTRATIVE</h4>
                  <section class="persons" v-if="activity.timesheets.validators.adm.length">
                    <PersonCartouche :person="p" v-for="p in activity.timesheets.validators.adm"
                                     class="cartouche primary"/>
                  </section>
                  <div class="alert alert-warning" v-else>
                    Aucun validateur désigné pour cette étape
                  </div>
                </div>
              </div>
            </section>
                    <section class="lots">
                      <h3>Lots de travail</h3>
                      <WorkpackageUI :url="activity.workpackages.url" :outsidePerson="persons"/>
                    </section>
                  </section>
                  <div class="alert alert-warning" v-else>
                    {{ activity.timesheets.enabled_details }}
                  </div>
                </section>

              -->
      </div>
      <aside class="col-md-4">
        Credentials:
        <pre>Credentials: {{ credentials }}</pre>
        <!--
        <section id="milestones" class="section-infos">
          <h2><i class="icon-calendar"></i>Jalons</h2>
          <Milestones :url="activity.milestones.url" :editable="activity.milestones.editable" :payments="payments"/>
          <a v-if="activity.milestones.url_notifications" :href="activity.milestones.url_notifications"
             class="btn btn-primary">
            <i class="icon-bell"></i>
            Voir les notifications planifiées
          </a>
        </section>

        <section id="payments" class="section-infos" v-if="activity.payments.readable">
          <h2><i class="icon-bank"></i>Versements</h2>
          <Payments :url="activity.payments.url" :manage="activity.payments.editable"
                    :amount="core.amount"
                    @update="handlerPaymentsUpdate"
          />
        </section>

        <section id="spents" class="section-infos" v-if="activity.spents.readable">
          <h2><i class="icon-bank"></i>Dépenses</h2>
          <ActivitySpentSynthesis :url="activity.spents.url"/>
          <nav class="buttons xs">
            <a :href="activity.spents.url_details" class="btn btn-primary btn" v-if="activity.spents.url_details">
              <i class="icon-file-excel"></i>
              Détails des dépenses</a>
            <a :href="activity.spents.url_previsionnel" class="btn btn-primary btn"
               v-if="activity.spents.url_previsionnel">
              <i class="icon-file-excel"></i>
              Dépenses prévisionnelles (beta)</a>
          </nav>

        </section>
        -->
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
  <div v-else>Données inaccessibles <br> core : {{ core }}<br>Credentials: {{ credentials }}</div>
</template>
<script>

import axios from 'axios';
import ActivityDocument from "./ActivityDocument.vue";
import ActivityLogs from "./ActivityLogs.vue";
import ActivitySpentSynthesis from "./ActivitySpentSynthesis.vue";
import EntityWithRole from "./EntityWithRole.vue";
import Loader from "../components/Loader.vue";
import Milestones from "./Milestones.vue";
import Modal from "../components/Modal.vue";
import Payments from "./Payments.vue";
import PersonCartouche from "../components/PersonCartouche.vue";
import Workpackage from "./Workpackage.vue";
import WorkpackageUI from "./WorkpackageUI.vue";
import AxiosMessage from "../utils/AxiosMessage.js";
import ActivityNotes from "./ActivityNotes.vue";

axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const storage_key = 'activities_sticky';

export default {
  name: 'Activity',

  components: {
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
    Workpackage,
    WorkpackageUI,
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
      documents: null,
      notes: null,
      notes_url: null,
      persons: null,
      personsUrl: null,
      personsUrlNew: null,
      payments: null,
      rolesOrganizations: null,
      rolesPersons: null,
      organizations: null,
      organizationsUrl: null,
      organizationsUrlNew: null,


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

    handlerUpdateNotes(d){
      this.notes = d.entities;
    },

    handlerUpdatePayments(p) {
      this.payments = p;
    },

    handlerUpdatePersons(d) {
      console.log("Update persons", d);
      this.persons = d.datas.items;
    },

    handlerUpdateOrganizations(d) {
      console.log("Update organizations", d);
      this.organizations = d.datas.items;
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
        this.budget = response.data.activity.datas.budget;
        this.core = response.data.activity.datas.core;
        this.persons = response.data.activity.datas.persons.entities;
        this.documents = response.data.activity.datas.documents;

        this.notes = response.data.activity.datas.notes.entities;
        this.notes_url = response.data.activity.datas.notes.url;

        this.personsUrlNew = response.data.activity.datas.persons.urlNew;
        this.personsUrl = response.data.activity.datas.persons.url;
        this.rolesPersons = response.data.activity.datas.persons.roles;
        this.organizations = response.data.activity.datas.organizations.entities;
        this.organizationsUrl = response.data.activity.datas.organizations.url;
        this.organizationsUrlNew = response.data.activity.datas.organizations.urlNew;
        this.rolesOrganizations = response.data.activity.datas.organizations.roles;
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

.type-chain span:last-child {
  font-weight: bold
}

.type-chain span:last-child:after {
  content: '';
}

.type-chain span {
  margin: auto;
}

.type-chain span:after {
  content: ' > ';
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

  .details {
    font-size: .8em;

    small {
      display: block;
    }
  }
}
</style>
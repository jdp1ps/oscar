<template>

  <div class="overlay" v-if="duplicateDatas">
    <div class="overlay-content">
      <h1 class="overlay-title">Dupliquer cette activité</h1>
      <a class="overlay-closer" @click="duplicateDatas = null">x</a>

      <div class="row" style="width: 80%">
        <h1 class="col-md-6 col-md-offset-3">
          <small>Options de copie pour</small> <br>

          <strong>{{ activity.infos.label }}</strong></h1>

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

  <div v-if="activity.infos">
    <nav class="navbar navbar-default navbar-fixed-top" style="top: 50px; z-index:500">
      <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
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
            <strong>{{ activity.infos.numOscar }}</strong>
            <span>{{ labelReduced }}</span>
            <i class="icon-pin" :style="{'opacity': isSticky ? 1.0 : 0.3}" @click="toogleSticky"></i>
          </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li><a href="#members">Membres</a></li>
            <li><a href="#partners">Partenaires</a></li>
            <li><a href="#milestones">Jalons</a></li>
            <li><a href="#payments">Versements</a></li>
            <li><a href="#spents" @click="test">Dépenses</a></li>
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
                <li v-for="a in sticky" :class="a.id == activity.infos.id ? 'disabled':''">
                  <a href="#" @click="handlerNavigateSticky(a)">
                    <i class="icon-cube"></i>
                    <strong>{{ a.num }}</strong>
                    <em>{{ a.label }}</em>
                    <i class="icon-link-ext" v-if="a.id != activity.infos.id"></i>
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
        <div class="col-md-10">
          <h4>
            <i class="icon-cubes"></i> Projet :
            <span :class="activity.project.url_show ? 'link' : ''" @click="handlerShowProject()" v-if="activity.project">
              <strong v-if="activity.project.acronym">{{ activity.project.acronym }}</strong>
              <em>&nbsp;{{ activity.project.label }}</em>
            </span>
            <em v-else>
              Aucun projet
            </em>
          </h4>

          <h3>
            <span class="picto status-" :class="'status-'+activity.infos.statut">
              <i class="icon"></i>
              {{ activity.infos.statut_label }}
            </span>
            :::

            <span class="type-chain">
            <i :class="activity.infos.type_slug"></i>
            <span v-for="t in activity.infos.type_chain">
              {{ t.label }}
            </span>
          </span>
          </h3>

          <h1>
            <span><i class="icon-cube"></i> {{ activity.infos.label }}</span>
          </h1>
        </div>
        <div class="col-md-2">
          <div class="budget" v-if="activity.budget">
            <em>Montant</em>
            <strong>{{ $filters.money(activity.budget.montant) }} {{ activity.budget.currency.symbol }}</strong>
            <div class="details">
              <small>
                Frais de gestion :
                <b>{{ $filters.money(activity.budget.fraisDeGestion) }} €</b>
              </small>

              <small>
                Part unité :
                <b v-if="activity.budget.fraisDeGestionPartUnite">
                  {{ $filters.money(activity.budget.fraisDeGestionPartUnite) }} {{ activity.budget.currency.symbol }}
                </b>
                <i v-else>
                  ~
                </i>
              </small>

              <small>
                Part hébergeur :
                <b>{{ $filters.money(activity.budget.fraisDeGestionPartHebergeur) }} {{ activity.budget.currency.symbol
                  }}</b>
              </small>

              <small>
                TVA :
                <b>{{ activity.budget.tva }}</b>
              </small>
            </div>
            <div>
            </div>
          </div>
        </div>
      </div>
      <div>

      </div>

      <p class="baseline" :class="{'descriptionPacked': !descriptionFull}" @click="descriptionFull=!descriptionFull" v-if="activity.infos.description">
        <small>{{ activity.infos.description }}</small>
      </p>

      <div class="row line-bottom">
        <div class="col-md-4">
          <h4><i class="icon-calendar"></i>Dates</h4>
          <p class="texthighlight baseline">
            Début :
            <time>{{ $filters.date(activity.infos.dateStart) }}</time>
            <small class="aggo"> ({{ $filters.timeAgo(activity.infos.dateStart) }})</small>
            <br>
            Fin :
            <time>{{ $filters.dateFull(activity.infos.dateEnd) }}</time>
            <small class="aggo"> ({{ $filters.timeAgo(activity.infos.dateEnd) }})</small>
            <br>
            Signé le :
            <time>{{ $filters.dateFull(activity.infos.dateSigned) }}</time>
            <small class="aggo"> ({{ $filters.timeAgo(activity.infos.dateSigned) }})</small>
          </p>
          <h4><i class="icon-tags"></i>Métas-données</h4>
          <p class="texthighlight baseline">
            Disciplines :
            <span class="cartouche xs" v-for="d in activity.infos.disciplines">{{ d }}</span>
          </p>
        </div>

        <div class="col-md-4">
          <h4><i class="icon-briefcase"></i>Numérotations</h4>
          <p class="texthighlight baseline">
            N° Oscar" : <strong>{{ activity.infos.numOscar }}</strong><br/>
            Numéro financier : <strong v-if="activity.infos.PFI">{{ activity.infos.PFI }}</strong><strong
              v-else>AUCUN</strong> -
            Ouverture du PFI le
            <time>{{ $filters.dateFull(activity.infos.dateOpened) }}</time>
            <br>
          </p>
          <p class="texthighlight baseline" v-for="n, label in activity.infos.numeros">
            {{ label }} : <strong>{{ n }}</strong>
          </p>
        </div>

        <div class="col-md-4">
          <h4><i class="icon-database-1"></i>Divers</h4>
          <p class="texthighlight baseline">
            Création
            <time>{{ $filters.dateFull(activity.infos.dateCreated) }}</time>
            <br>
            Dernière MAJ
            <time>{{ $filters.dateFull(activity.infos.dateUpdated) }}</time>
          </p>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <nav class="buttons xs">
            <a class="btn btn-primary btn-xs" v-if="activity.urls.edit" :href="activity.urls.edit">
              <i class="icon-pencil"></i>
              Modifier les informations</a>

            <a class="btn btn-xs btn-default" v-if="activity.urls.change_project" :href="activity.urls.change_project">
              <i class="icon-cubes"></i>
              Modifier le projet</a>

            <a class="btn btn-xs btn-default" v-if="activity.urls.new_project" :href="activity.urls.new_project">
              <i class="icon-cubes"></i>
              Créer un nouveau projet</a>

            <a class="btn btn-xs btn-default" v-if="activity.urls.duplicate" @click="handlerDuplicate">
              <i class="icon-paste"></i>
              Dupliquer</a>
          </nav>
        </div>
      </div>
    </header>
    <div class="container-fluid">
      <div class="col-md-8">
        <section class="section-infos" id="members" v-if="activity.persons.readable">
          <h2><i class="icon-group"></i>Membres</h2>
          <EntityWithRole title="Personne" :url="activity.persons.url" @updated="handlerUpdatePersons"/>
        </section>

        <section class="section-infos" id="partners" v-if="activity.organizations.readable">
          <h2><i class="icon-building-filled"></i>Partenaires</h2>
          <EntityWithRole title="Organisation" :url="activity.organizations.url"/>
        </section>

        <section class="section-infos" id="documents" v-if="activity.documents.readable">
          <h2><i class="icon-book"></i>Documents</h2>
          <activity-document :url="activity.documents.url" url-upload-new-doc=""/>
        </section>

      </div>
      <aside class="col-md-4">
        <section id="milestones" class="section-infos">
          <h2><i class="icon-calendar"></i>Jalons</h2>
          <Milestones :url="activity.milestones.url" :editable="activity.milestones.editable" :payments="payments"/>
        </section>

        <section id="payments" class="section-infos" v-if="activity.payments.readable">
          <h2><i class="icon-bank"></i>Versements</h2>
          <Payments :url="activity.payments.url" :manage="activity.payments.editable"
                    :amount="activity.infos.amount"
                    @update="handlerPaymentsUpdate"
          />
        </section>

        <section id="spents" class="section-infos" v-if="activity.spents.readable">
          <h2><i class="icon-bank"></i>Dépenses</h2>
          <ActivitySpentSynthesis :url="activity.spents.url" />
        </section>
      </aside>
    </div>
    <section id="timesheets" class="section-infos" v-if="activity.timesheets.readable">
      <h2><i class="icon-book"></i>Feuille de temps</h2>
      <section id="timesheets" v-if="activity.timesheets.enabled">
        <a  :href="activity.timesheets.url_global"
            class="btn btn-primary">
          <i class="icon-calendar"></i>
          Informations générales
        </a>
        <a  :href="activity.timesheets.url_synthesis"
            class="btn btn-primary">
          <i class="icon-book"></i>
          Résumé et documents
        </a>
        <section class="workpackages">
          <WorkpackageUI :url="activity.workpackages.url" :outsidePerson="persons" />
        </section>
      </section>
      <div class="alert alert-warning" v-else>
        {{ activity.timesheets.enabled_details }}
      </div>
    </section>
  </div>
  <pre></pre>
</template>
<script>

import axios from 'axios';
import EntityWithRole from "./EntityWithRole.vue";
import ActivityDocument from "./ActivityDocument.vue";
import Milestones from "./Milestones.vue";
import Workpackage from "./Workpackage.vue";
import WorkpackageUI from "./WorkpackageUI.vue";
import ActivitySpentSynthesis from "./ActivitySpentSynthesis.vue";
import Payments from "./Payments.vue";

axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const storage_key = 'activities_sticky';

export default {
  name: 'Activity',

  components: {
    ActivityDocument,
    ActivitySpentSynthesis,
    EntityWithRole,
    Payments,
    Milestones,
    Workpackage,
    WorkpackageUI,
  },

  props: {
    url: {required: true}
  },

  computed: {
    storage() {
      return localStorage.getItem(storage_key);
    },
    isSticky() {
      return this.sticky.find(item => item.id == this.activity.infos.id);
    },
    labelReduced(){
      let label = this.activity.infos.label;
      if(label.length > 50){
        return label.substr(0,50) + '...';
      } else {
        return label;
      }
    }
  },

  data() {
    return {
      activity: {},
      duplicateDatas: null,
      sticky: [],
      descriptionFull: false,
      persons: [],
      payments: [],
    }
  },

  methods: {
    handlerPaymentsUpdate(p){
      this.payments = p;
    },
    handlerUpdatePersons(d){
      this.persons = d.entries;
    },
  ////////////////////////////////////////// Système d'épingle

    handlerPurgeSticky() {
      this.sticky = [];
      localStorage.removeItem(storage_key);
    },

    toogleSticky() {
      if( this.isSticky ){
        this.handlerUnSticky()
      } else {
        this.handlerSticky()
      }
    },

    handlerUnSticky() {
      this.sticky.forEach((item, id) => {
        if( item.id == this.activity.infos.id ){
          this.sticky.splice(id, 1);
        }
      })
      localStorage.setItem(storage_key, JSON.stringify(this.sticky));
    },

    handlerSticky() {
      this.sticky.push({
        id: this.activity.infos.id,
        label: this.activity.infos.label,
        num: this.activity.infos.numOscar,
        location: document.location.href,
      });
      localStorage.setItem(storage_key, JSON.stringify(this.sticky));
    },

    handlerNavigateSticky(sticked){
      if(sticked.location) {
        document.location = sticked.location;
      }
    },

    test() {
      console.log(localStorage.getItem());
    },

    fetch() {
      axios.get(this.url).then(response => {
        this.activity = response.data.activity
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
      document.location = this.activity.urls.duplicate + "?"
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
  >h2 {
    border-bottom: 1px solid #a2a7af;
    margin: 0;
    padding: .2em 0;
  }
}


@-webkit-keyframes animation {
  0%     {color:#333333;}
  15.0%  {color:#0a53be;}
  30.0%  {color:#333333;}
  50.0%  {color:#0a53be;}
  100.0%  {color:#333333;}
}

@keyframes animation {
  0%     {color:#333333;}
  15.0%  {color:#0a53be;}
  30.0%  {color:#333333;}
  50.0%  {color:#0a53be;}
  100.0%  {color:#333333;}
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
<template>
  <!-- Détails du processus -->
  <div class="overlay" v-if="processDetails">
    <div class="overlay-content" style="max-width: 50%">
      <h2>
        <small><i class="icon-edit"></i>
          Procédure</small> <br><strong>{{ processDetails.label }}</strong> <br>
        <span class="signature-status-101">
          {{ processDetails.status_text }} -
          <em>étape {{ processDetails.current_step }} / {{ processDetails.total_steps }}</em>
        </span>
        <span class="overlay-closer" @click="handlerProcessDetailsOff">X</span>
      </h2>

      <section class="signature" :class="'signature-status-'+s.status" v-for="s in processDetails.steps">
        <h4>
          <small>étape {{ s.order }} : </small>
          <strong>{{ s.label }}</strong>
          <span class="status"> ({{ s.status_text }})</span>
        </h4>

        <ul class="metas">
          <li class="meta">Parapheur <strong>{{ s.letterfile }}</strong></li>
          <li class="meta">Niveau <strong>{{ s.level }}</strong></li>
        </ul>

        <article class="recipient" :class="'signature-status-'+r.status" v-for="r in s.recipients">
          <strong class="fullname">{{ r.fullname }}</strong>
          <em class="email">{{ r.email }}</em>
          <small>{{ $filters.dateFull(r.dateFinished) }}</small>
          <span class="status">
            <span class="status-text">{{ r.status_text }}</span>
          </span>
        </article>
        <strong>Observateurs : </strong>
        <span v-for="o in s.observers" class="observer-inline">
          <small>{{ o.firstname }}</small> <span>{{ o.lastname }}</span>
        </span>
      </section>

      <div class="buttons-bar">
        <button class="btn btn-default" @click="handlerProcessDetailsOff">
          <i class="icon-cancel-outline"></i> Fermer
        </button>
      </div>
    </div>
  </div>

  <section class="documents-content">
    <div class="tab-content">
      <article class="card xs" v-for="doc in documents" :key="doc.id" :class="{'private-document': doc.private }">
        <div class="card-title">
          <i class="picto icon-anchor-outline" v-if="doc.location == 'link'"></i>
          <i class="picto icon-doc" :class="'doc' + doc.extension" v-else></i>
          <small class="text-light">{{ doc.category.label }} ~ </small>
          <strong>{{doc.fileName}}</strong>
          <small class="text-light" :title="doc.fileSize + ' octet(s)'" v-if="doc.location != 'url'">&nbsp;
            Version {{ doc.version }}
          </small>
        </div>
        <small>
          <i class="icon-briefcase"></i> Taille <strong>{{ $filters.filesize(doc.fileSize) }}</strong>
          <i class="icon-calendar"></i> Envoyé <strong>{{ $filters.timeAgo(doc.dateSend) }}</strong>
          <i class="icon-calendar"></i> Déposé <strong>{{ $filters.dateFull(doc.dateDeposit) }}</strong>
          <i class="icon-calendar"></i> Uploadé <strong>{{ $filters.dateFull(doc.dateUpload) }}</strong>
          <i class="icon-user"></i> par <strong>{{ doc.uploader.displayname }}</strong>
        </small>
        <p>{{ doc.information }}</p>
        <section v-if="doc.private">
          <i class="icon-lock"/>
          Ce document est privé, accessible par :
          <span class="cartouche" v-for="p in doc.persons">
                  {{ p.personName }}
                </span>
        </section>
        <div class="card-content">
          <section v-if="doc.process" class="alert"
                   :class="{'alert-success':doc.process.status == 201,
                              'alert-danger':doc.process.status >= 400,
                              'alert-info':doc.process.status < 200
            }">
            <i class="icon-hammer"></i>
            Procédure de signature <strong>{{ doc.process.label }}</strong> (<em>{{ doc.process.status_text }}</em>
            <span> - étape {{ doc.process.current_step }} / {{ doc.process.total_steps }}</span>)
            <button class="btn btn-xs btn-info" @click="handlerProcessDetailsOn(doc.process)">
              Détails
            </button>
            <button v-if="doc.manage_process" class="btn btn-default btn-xs" @click="handlerProcessReload(doc)">
              <i class="icon-cw-outline"></i>
              Actualiser
            </button>
          </section>

          <section v-if="displayActivity">
            Activité :
            <span :class="{'link': doc.activity.url_show}" @click="urlShow(doc.activity.url_show)">
              <strong>
                <i class="icon-cube"></i> / {{ doc.activity.num }}
              </strong>&nbsp;
              <em>
                {{ doc.activity.label }}
              </em>
              <small v-if="doc.activity.project_id">
                (<i class="icon-cubes"></i>{{ doc.activity.project_acronym }})
              </small>
              {{ doc.activity}}
            </span>

          </section>


          <div v-if="doc.versions && doc.versions.length">
            <div class="exploder">
              Versions précédentes :
            </div>
            <article v-for="sub in doc.versions" class="subdoc text-highlight">
              <i class="picto icon-doc" :class="'doc' + sub.extension"></i>
              <strong>{{ sub.fileName }}</strong>
              version <em>{{ sub.version }} </em>,
              téléchargé le
              <time>{{ sub.dateUpload | dateFullSort }}</time>
              <span v-if="sub.uploader">
                        par <strong>{{ sub.uploader.displayname }}</strong>
                        </span>

              <a :href="sub.urlDownload">
                <i class="icon-download-outline"></i>
                Télécharger cette version
              </a>
            </article>
          </div>

          <nav class="text-right show-over">
            <a class="btn btn-default btn-xs"
               :href="doc.basename"
               v-if="doc.location == 'url'" target="_blank">
              <i class="icon-link-ext"></i>
              Accéder au lien
            </a>
            <a class="btn btn-default btn-xs"
               href="#" v-if="doc.process_triggerable" @click="handlerSignDocument(doc)">
              <i class="icon-bank"></i>
              Signer ce document
            </a>
            <a class="btn btn-default btn-xs"
               :href="doc.urlDownload" v-if="doc.urlDownload && doc.location != 'url'">
              <i class="icon-upload-outline"></i>
              Télécharger
            </a>
            <button v-on:click="handlerNewVersion(doc)" class="btn btn-default btn-xs"
                    v-if="doc.allowNewVersion">
              <i class="icon-download-outline"></i>
              Nouvelle Version
            </button>
            <a class="btn btn-default btn-xs" @click.prevent="deleteDocument(doc)" v-if="doc.allowDelete">
              <i class="icon-trash"></i>
              Supprimer
            </a>
            <a class="btn btn-xs btn-default" href="#" @click.prevent="handlerEdit(doc)"
               v-if="doc.allowEdit">
              <i class="icon-pencil"></i>
              Modifier
            </a>
          </nav>
        </div>
      </article>
    </div>
  </section>
</template>
<script>
export default {
  props: {
    editable: {default: false},
    displayActivity: {default: false},
    documents: {default: []}
  },

  data() {
    return {
      processDetails: null
    }
  },

  methods: {
    urlShow( url=null ){
      console.log(url);
      if(url){
        document.location = url;
      } else {
        return false;
      }
    },

    /**
     * Affichage des détails d'une procédure de signature
     * @param process
     */
    handlerProcessDetailsOn(process) {
      this.processDetails = process;
    },

    /**
     * Masquer les détails d'une procédure de signature
     *
     * @param process
     */
    handlerProcessDetailsOff() {
      this.processDetails = null;
    },

  }
}
</script>

<style scoped>
.step-content {
  border: thin solid #aaa;
  border-top: none;
  padding: 1em;
}

.card .alert {
  padding: 0 .5em;
  margin: .1em;
}

.step {
  flex: 1;
  padding: .25em 1em 1em;
  text-align: left;
  border: 1px solid #aaa;
  border-left: 4px solid #aaa;
  margin: .5em;
}

.step.error {
  border-color: #990000;
}

.step.ok {
  /*border-color: #339900;*/
}

.step .alert {
  margin: 0;
  padding: .25em 1em;
}

.current {
  color: #333;
  background: white;
  border-bottom-color: white;
}

.private {
  color: #777;
  text-shadow: 1px -1px 1px rgba(255, 255, 255, .3);
  font-weight: 100;
}

.stepHandler {
  border-radius: 18px;
  outline: 0;
  padding: 8px 12px;
  text-align: center;
  transition: all 0.3s ease-out;
  display: inline-block;
  margin-left: 100px;
}

.stepHandler:before {
  font-family: "fontello";
  content: "";
  /*content: "♥";*/
}

.stepHandler:hover,
.stepHandler:focus {
  color: #333;
  background: #8E969F;
}

.process, .signature {
  background: white;
  padding: .5em 1em;
  margin: 1em;
  border: thin solid #92b2ae;
  border-left-width: 8px
}

.signature h2 {
  font-size: 1.5em;
  margin: 0;
  border-bottom: solid thin #c5c5c5;
  padding: .1em 0;
  display: flex
}

.signature h2 span {
  flex: 1 1 auto
}

.signature h2 .status-flag {
  flex: 0 0 6em;
  text-align: right
}

.buttons-bar {
  padding: .5em
}

.recipient {
  border: solid thin #92b2ae;
  border-left-width: 8px;
  display: flex;
  text-shadow: 1px -1px 0 rgb(255, 255, 255);
  padding: .25em 0 .25em .5em;
  margin-right: .5em
}

.recipient .email, .recipient .fullname {
  flex: 1
}

.recipient .status-flag {
  flex: 0 0 6em
}

.metas {
  display: inline-block;
  margin: 0;
  padding: .5em 0
}

.metas .meta {
  display: inline-block;
  background: #d4dedd;
  padding: 0 .5em;
  border-radius: 8px
}

.recipient-signature {
  background: white;
  margin: .5em 0;
  padding: .7em 1em .1em;
  border-left: solid 4px #666666
}

.observer-inline{
  background: #e1ece6;
  display: inline-block;
  border-radius: 4px;
  padding: 0 .5em;
  margin: 0 .3em;
}

.recipient-signature h3 {
  margin: 0
}

.status:hover .status-text {
  opacity: 1
}

.status .status-text {
  transition: opacity .3s;
  font-weight: 700;
  color: #fff;
  text-shadow: -1px 1px 0 rgba(0, 0, 0, .5);
  margin: 0;
  padding: 0 .25em;
  font-size: .75em
}

.status-flag {
  display: none
}

.signature-status-501 {
  border-color: #83243a
}

.signature-status-501 .status-text, .signature-status-501 .status-flag {
  background-color: #83243a
}

.signature-status-501 .status-flag:before {
  content: ""
}

.signature-status-401 {
  border-left-color: #8d7260
}

.signature-status-401 .status-text, .signature-status-401 .status-flag {
  background-color: #8d7260
}

.signature-status-401 .status-flag:before {
  content: ""
}

.signature-status-105 {
  border-left-color: #8eb4c9
}

.signature-status-105 .status-text, .signature-status-105 .status-flag {
  background-color: #8eb4c9
}

.signature-status-105 .status-flag:before {
  content: ""
}

.signature-status-101 {
  border-left-color: #92b2ae
}

.signature-status-101 .status-text, .signature-status-101 .status-flag {
  background-color: #92b2ae
}

.signature-status-101 .status-flag:before {
  content: ""
}

.signature-status-201 {
  border-left-color: #447c44
}

.signature-status-201 .status-text, .signature-status-201 .status-flag {
  background-color: #447c44
}

.signature-status-201 .status-flag:before {
  content: ""
}

</style>
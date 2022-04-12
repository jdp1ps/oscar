<template>
    <!-- MODAL DE SUPPRESSION DE DOCUMENT -->
    <section style="position: relative; min-height: 100px">
        <ajax-oscar :oscar-remote-data="remoterState" />
        <div class="overlay" v-if="deleteData">
            <div class="overlay-content">
                <h2>
                    Suppression du fichier ?
                    <span class="overlay-closer" @click="deleteData = null">X</span>
                </h2>
                <p class="alert-danger alert">
                    <i class="icon-attention-1"></i>
                    Souhaitez-vous supprimer le fichier <strong>{{ deleteData.fileName }}</strong> ?
                </p>

                <button class="btn btn-danger" @click="deleteData = null">
                    <i class="icon-cancel-alt"></i> Annuler
                </button>

                <a class="btn btn-success" :href="deleteData.urlDelete">
                    <i class="icon-valid"></i> Confirmer
                </a>
            </div>
        </div>

        <!-- MODAL DE MODIFICATIONS DU TYPE DE DOCUMENT -->
        <div class="overlay" v-if="editData">
            <div class="overlay-content">
                <h2>
                    Modification du document
                    <span class="overlay-closer" @click="editData = null">X</span>
                </h2>

                <label for="typedocument">Type de document</label>
                <div>
                    <select name="type" id="typedocument" v-model="editData.documentype_id">
                        <option :value="id" v-for="t, id in documentTypes" :key="id">{{ t }}</option>
                    </select>
                </div>
                <!--
                <label for="filename">Nom du fichier</label>
                <p class="help">
                    Il s'agit du nom du fichier par défaut lors du téléchargement. Le nom d'archivage ne sera pas modifié.
                </p>
                <input type="text" id="filename" class="form-control" v-model="editData.basename" />
                -->
                <button class="btn btn-danger" @click="editData = null">
                    <i class="icon-cancel-alt"></i> Annuler
                </button>

                <a class="btn btn-success" href="#" @click.prevent="performEdit()">
                    <i class="icon-valid"></i> Enregistrer
                </a>
            </div>
        </div>

      <!-- MODAL DE TELEVERSEMENT NOUVEAU DOCUMENT -->
      <div class="overlay" v-if="uploadNewDocData.init">
        <div class="overlay-content">
          <h1>
            {{ uploadNewDocData }}
          </h1>
          <h2>
            Téléverser un nouveau document
            <span class="overlay-closer" @click="uploadNewDocData.init = null">X</span>
          </h2>
          <div>
                <div class="row">
                  <div class="col-md-6">
                    <label for="file">Fichier</label>
                    <input type="file" class="form-control" name="file" id="file" />

                    <label for="dateDeposit">Date de dépôt</label>
                    <p class="help">Date à laquelle le fichier a été reçu</p>
                    <input v-model="dateDeposit" type="text" class="datetime input-date form-control" name="dateDeposit" id="dateDeposit" value=""/>

                    {{ dateDeposit }} <br/>

                    <label for="dateSend">Date d'envoi</label>
                    <p class="help">Date à laquelle le fichier a été envoyé</p>
                    <input v-model="dateSend" type="text" class="input-date form-control" name="dateSend" id="dateSend" value=""/>

                    {{ dateSend }}<br/>

                  </div>

                  <div class="col-md-6">
                    <!-- TYPE DE DOCUMENT -->
                    <label for="type">Type de document</label>
                    <select v-model="selectedIdTypeDocument" name="type" id="type" class="form-control">
                      <option v-for="(label, id) in documentTypes" :value="id" :key="id">
                         {{ label }}
                      </option>
                    </select>

                    {{ selectedIdTypeDocument }}<br/>

                    <div class="row" style="margin-top: 20px;">
                      <div class="col-md-6">
                        <label for="private">Document privé</label>
                      </div>
                      <div class="col-md-6">
                        <input type="checkbox" name="private" id="private" class="form-control" v-model="privateDocument" >
                      </div>

                      {{ privateDocument }} <br/>

                    </div>

                    <!-- ONGLET (TAB) DE DOCUMENT -->
                    <span v-if="privateDocument === false">
                      <label for="tab">Onglet document</label>
                      <select v-model="selectedIdTabDocument" name="tab" id="tab" class="form-control">
                        <option :value="tab.id" v-for="tab in tabsWithDocuments" :key="id">
                          {{ tab.label }}
                        </option>
                      </select>
                    </span>

                    {{ selectedIdTabDocument }}<br/>

                  </div>

                  <!-- INFORMATIONS COMPLEMENTAIRES -->
                  <div class="row">
                    <div class="col-md-12">
                      <label for="informations">Note</label>
                      <textarea v-model="informationsDocument" name="informations" id="informations" class="form-control" cols="30" rows="10"></textarea>
                    </div>
                  </div>
                    <button class="btn btn-danger" @click="uploadNewDocData.init = null">
                      <i class="icon-cancel-alt"></i> Annuler
                    </button>
                    <a class="btn btn-success" href="#" @click.prevent="performUpload()">
                      <i class="icon-valid"></i> Enregistrer
                    </a>
                  </div>
          </div>
        </div>
      </div>

        <!-- Barre de tri des documents -->
        <div>
            <div class="oscar-sorter">
                <i class=" icon-sort"></i>
                Tier les résultats par :
                <a @click.prevent="order('dateUpload')" href="#" :class="cssSort('dateUpload')" class="oscar-sorter-item">
                    Date d'upload
                    <i class="icon-angle-down" v-show="sortDirection == 1"></i>
                    <i class="icon-angle-up" v-show="sortDirection == -1"></i>
                </a>
                <a @click.prevent="order('fileName')" href="#" :class="cssSort('fileName')" class="oscar-sorter-item">
                    Nom du fichier
                    <i class="icon-angle-down" v-show="sortDirection == 1"></i>
                    <i class="icon-angle-up" v-show="sortDirection == -1"></i>
                </a>
                <a @click.prevent="order('categoryText')" href="#" :class="cssSort('categoryText')" class="oscar-sorter-item">
                    Type de document
                    <i class="icon-angle-down" v-show="sortDirection == 1"></i>
                    <i class="icon-angle-up" v-show="sortDirection == -1"></i>
                </a>
            </div>
        </div>


      <!-- ############################### Informations par document / Onglet associé ############################### -->
      <div v-for="tab in tabsWithDocuments" :key="tab.id">
        <nav class="admin-bar">
          <span style="cursor:pointer;" v-on:click="activeTab(tab.id)"  class="cartouche primary">
              <i class="picto icon-doc"></i>{{ tab.label }}
              <span v-on:click="handlerUploadNewDoc(tab.id)" class="addon">
                 Téléverser un document<i class="icon-book icon-clickable"></i>
                <!--
                <a :href='urlUploadNewDoc+"/"+tab.id' class="smoke">
                  Téléverser un document<i class="icon-book icon-clickable"></i>
                </a>
                -->
              </span>
            <span v-if="isTabActive === tab.id">
              &nbsp;<i class="icon-docs"></i>
            </span>
          </span>
        </nav>
        <!--v-for="document in documentsPacked" :key="document.id">-->
        <article v-if="isTabActive === tab.id" class="card xs" v-for="doc in tab.documents" :key="doc.id">
          <div class="card-title">
            <i class="picto icon-doc" :class="'doc' + doc.extension"></i>
            <small class="text-light">{{ doc.categoryText }} ~ </small>
            <strong>{{doc.fileName}}</strong>
            <small class="text-light" :title="doc.fileSize + ' octet(s)'">&nbsp;({{doc.fileSize | filesize}})</small>
          </div>
          <p>
            {{ doc.information }}
          </p>
          <div class="card-content">
            <p class="text-highlight">
              Fichier <strong>{{ doc.extension}}</strong>
              version {{ doc.version }},
              téléversé le <time>{{ doc.dateUpload | dateFull }}</time>
              <span v-if="doc.uploader"> par <strong>{{ doc.uploader.displayname }}</strong></span>
            </p>
            <!--
            <div class="exploder" v-if="doc.previous.length" @click="doc.explode = !doc.explode">
              Versions précédentes <i class="icon-angle-down" v-show="!doc.explode"></i>
              <i class="icon-angle-up" v-show="doc.explode"></i>
            </div>

            <div v-if="doc.previous.length" v-show="doc.explode">
              <article v-for="sub in doc.previous" class="subdoc text-highlight" :key="sub.id">
                <i class="picto icon-doc" :class="'doc' + sub.extension"></i>
                <strong>{{ sub.fileName }}</strong>
                version <em>{{ sub.version }} </em>,
                téléchargé le <time>{{ sub.dateUpload | dateFullSort }}</time>
                <span v-if="sub.uploader">
                        par <strong>{{ sub.uploader.displayname }}</strong>
                        </span>
                <a :href="sub.urlDownload">
                  <i class="icon-download-outline"></i>
                  Télécharger cette version
                </a>
              </article>
            </div>
            -->
            <nav class="text-right show-over">
              <a class="btn btn-default btn-xs" :href="doc.urlDownload" v-if="doc.urlDownload">
                <i class="icon-upload-outline"></i>
                Télécharger
              </a>

              <a class="btn btn-default btn-xs" :href="doc.urlReupload" v-if="doc.urlReupload">
                <i class="icon-download-outline"></i>
                Nouvelle version
              </a>

              <a class="btn btn-default btn-xs" @click.prevent="deleteDocument(doc)">
                <i class="icon-trash"></i>
                Supprimer
              </a>
              <a class="btn btn-xs btn-default" href="#" @click.prevent="handlerEdit(doc)">
                <i class="icon-pencil"></i>
                Modifier
              </a>
            </nav>
          </div>
        </article>
      </div>

      <!-- Section boucle documents -->
      <!--
        <article class="card xs" v-for="document in documentsPacked" :key="document.id">
            <div class="card-title">
                <i class="picto icon-doc" :class="'doc' + document.extension"></i>
                <small class="text-light">{{ document.categoryText }} ~ </small>
                <strong>{{document.fileName}}</strong>
                <small class="text-light" :title="document.fileSize + ' octet(s)'">&nbsp;({{document.fileSize | filesize}})</small>
            </div>
            <p>
                {{ document.information }}
            </p>
            <p v-if="document.tabDocument">
              ################## INFORMATIONS COMPLEMENTAIRES LE ID DU TAB : {{ document.tabDocument.id }} ########################
            </p>
            <div class="card-content">
                <p class="text-highlight">
                    Fichier <strong>{{ document.extension}}</strong>
                    version {{ document.version }},
                    téléversé le <time>{{ document.dateUpload | dateFull }}</time>
                    <span v-if="document.uploader">
                        par <strong>{{ document.uploader.displayname }}</strong>
                    </span>
                </p>
                <div class="exploder" v-if="document.previous.length" @click="document.explode = !document.explode">
                    Versions précédentes <i class="icon-angle-down" v-show="!document.explode"></i>
                    <i class="icon-angle-up" v-show="document.explode"></i>
                </div>
                <div v-if="document.previous.length" v-show="document.explode">
                    <article v-for="sub in document.previous" class="subdoc text-highlight" :key="sub.id">
                        <i class="picto icon-doc" :class="'doc' + sub.extension"></i>

                        <strong>{{ sub.fileName }}</strong>
                        version <em>{{ sub.version }} </em>,
                        téléchargé le <time>{{ sub.dateUpload | dateFullSort }}</time>
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
                    <a class="btn btn-default btn-xs" :href="document.urlDownload" v-if="document.urlDownload">
                        <i class="icon-upload-outline"></i>
                        Télécharger
                    </a>

                    <a class="btn btn-default btn-xs" :href="document.urlReupload" v-if="document.urlReupload">
                        <i class="icon-download-outline"></i>
                        Nouvelle version
                    </a>

                    <a class="btn btn-default btn-xs" @click.prevent="deleteDocument(document)">
                        <i class="icon-trash"></i>
                        Supprimer
                    </a>
                    <a class="btn btn-xs btn-default" href="#" @click.prevent="handlerEdit(document)">
                        <i class="icon-pencil"></i>
                        Modifier
                    </a>
                </nav>
            </div>
        </article>
        -->
    </section>
</template>
<script>

    /******************************************************************************************************************/
    /* ! DEVELOPPEUR

    Depuis la racine OSCAR :

    cd front

    Pour compiler en temps réél :
    node node_modules/.bin/gulp activityDocumentWatch
    node node_modules/.bin/vue-cli-service build --name ActivityDocument --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib ./src/ActivityDocument.vue --watch

    Pour compiler :
    node node_modules/.bin/gulp activityDocument
     */

    import AjaxOscar from "./remote/AjaxOscar";
    import OscarRemoteData from "./remote/OscarRemoteData";

    // test
    let oscarRemoteData = new OscarRemoteData();

    function flashMessage(){

    }

    export default {

        components: {
            "ajax-oscar": AjaxOscar
        },

        props: {
            urlUploadNewDoc: { required: true },
            url: { required: true },
            documentTypes: { required: true },
            urlDocumentType: { required: true }
        },

        data(){
            return {
              // Formulaire Upload
              dateDeposit:'',
              dateSend:'',
              privateDocument: false,
              selectedIdTypeDocument: null,
              selectedIdTabDocument:null,
              informationsDocument:'',
              // Objet hydraté et envoyé au téléversement d'un document
              uploadNewDocData: {
                'dateDeposit': this.dateDeposit,
                'dateSend': this.dateSend,
                'private': this.privateDocument,
                'type': this.selectedIdTypeDocument,
                'tab': this.selectedIdTabDocument,
                'informations': this.informationsDocument,
                'baseUrlUpload': this.urlUploadNewDoc,
                'init': false
              },

              tabId: null,
              tabsWithDocuments: null,
              formData: null,
              error: null,
              deleteData: null,
              editData: null,
              documents: [],
              loading: true,
              sortField: 'dateUpload',
              sortDirection: -1,
              editable: true,
              remoterState: oscarRemoteData.state
            }
        },

        computed:{
            /**
             * Retourne les documents triés.
             * @returns {Array}
             */
            documentsPacked(){
                let out = [];
                if( this.documents ){
                    let documents = this.documents;
                    out = documents.sort(function(a, b) {
                            if (a[this.sortField] < b[this.sortField])
                                return -1 * this.sortDirection;
                            if( a[this.sortField] > b[this.sortField] )
                                return 1 * this.sortDirection;
                            return 0;
                        }.bind(this));
                };
                return out;
            },
            isTabActive(){
              return this.tabId;
            }
        },

        methods:{
          activeTab(tabId){
            this.tabId = tabId;
          } ,

          deleteDocument(document) {
              this.deleteData = document;
          },

          order: function (field) {
              if( this.sortField == field ){
                  this.sortDirection *= -1;
              } else {
                  this.sortField = field;
              }
          },

          cssSort: function(compare){
              return compare == this.sortField ? "active" : "";
          },

          handlerEdit(document){
              //console.log(document);
              this.editData = {
                  'documentype_id': document.category.id,
                  'basename': document.basename,
                  'document': document
              };
          },

          // Déclenche ouverture Modal
          handlerUploadNewDoc(tabId){
            this.uploadNewDocData.init=true;
            this.uploadNewDocData.baseUrlUpload = this.urlUploadNewDoc+'/'+tabId
          },

          // Upload nouveau Document bouton "submit" formulaire nouveau Document
          performUpload(){
            /*
              let formData = new FormData();
              formData.append('documentId', documentId);
              formData.append('type', newType);
              oscarRemoteData
                  .setPendingMessage("Modification du type de document")
                  .setErrorMessage("Impossible de modifier le type de document")
                  .performPost(this.urlDocumentType, formData, (response) => {
                      this.fetch();
                  });
             */
            this.uploadNewDocData.dateDeposit = this.dateDeposit;
            this.uploadNewDocData.dateSend = this.dateSend;
            this.uploadNewDocData.private = this.privateDocument;
            this.uploadNewDocData.type = this.selectedIdTypeDocument;
            this.uploadNewDocData.tab = this.selectedIdTabDocument;
            this.uploadNewDocData.informations = this.informationsDocument;
            console.log("Upload document");
            console.log(this.uploadNewDocData);
          //@click="privateDocument = (privateDocument === false) ? true : false"
          },

          // Modification du type de document
          performEdit(){
              let documentId = this.editData.document.id;
              let newType = this.editData.documentype_id;
              this.editData = null;
              let formData = new FormData();
              formData.append('documentId', documentId);
              formData.append('type', newType);
              oscarRemoteData
                  .setPendingMessage("Modification du type de document")
                  .setErrorMessage("Impossible de modifier le type de document")
                  .performPost(this.urlDocumentType, formData, (response) => {
                      this.fetch();
                  });
          },

          // Méthode appelée lors de l'appel de la méthode fetch démarrage du module
          handlerSuccess(success){
              let data = success.data.datas;
              let tabsObjectsDocuments = success.data.tabsWithDocuments;
              this.tabsWithDocuments = tabsObjectsDocuments;
              let documentsOrdered = [];
              let documents = {};

              data.forEach(function(doc){
                  doc.categoryText = doc.category ? doc.category.label : "";
                  doc.explode = true;
                  var filename = doc.fileName;
                  if( ! documents[filename] ){
                      documents[filename] = doc;
                      documents[filename].previous = [];
                      documentsOrdered.push(doc);
                  } else {
                      documents[filename].previous.push(doc);
                  }
              });
              this.documents = documentsOrdered;
              //console.log(this.documents);
          },

          fetch(){
            // Object JS Ajax
              oscarRemoteData
                  .setPendingMessage("Chargement des documents")
                  .setErrorMessage("Impossible de charger les documents")
                  .performGet(this.url, this.handlerSuccess);
          }
      },

      mounted(){
          // Au chargement du module dans la page appel méthode initialisation fetch()
          this.fetch();
      }
    }
</script>

<style scoped>

</style>

<template>
    <!-- Modal de suppression de document -->
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
        <!-- Modal de modification du type -->
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

      <!-- Informations par document -->
      <!-- Section Onglets -->
        <ul v-for="tab in tabs" :key="tab.id">
          <li style="cursor:pointer;" v-on:click="activeTab(tab.id)">
            <i class="picto icon-Activity"></i> {{ tab.label }}
          </li>
        </ul>
      <!-- Section boucle documents -->
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
    </section>
</template>
<script>
    /******************************************************************************************************************/
    /* ! DEVELOPPEUR
    Depuis la racine OSCAR :

    cd front

    Pour compiler en temps réél :
    node node_modules/.bin/gulp activityDocumentWatch

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
            url: { required: true },
            documentTypes: { required: true },
            urlDocumentType: { required: true }
        },

        data(){
            return {
              tabs: null,
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
            }
        },

        methods:{
          activeTab(tabId){
            console.log(tabId)
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
              console.log(document);
              this.editData = {
                  'documentype_id': document.category.id,
                  'basename': document.basename,
                  'document': document
              };
          },

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

          handlerSuccess(success){
              let data = success.data.datas;
              let objectsTabs = success.data.tabs;
              //console.log(objectsTabs);
              this.tabs = objectsTabs;
              //console.log(this.tabs);
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
          },

          fetch(){
              oscarRemoteData
                  .setPendingMessage("Chargement des documents")
                  .setErrorMessage("Impossible de charger les documents")
                  .performGet(this.url, this.handlerSuccess);
          }
      },

      mounted(){
          this.fetch();
      }

    }
</script>

<template>
    <section style="position: relative; background: rgba(255,0,0,.1)">
        <ajax-oscar :oscar-remote-data="remoterState" />

        <pre>{{ remoteState }}</pre>
        <button @click="remoterState.loading = !remoterState.loading">TEST REMOTE</button>

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

        <article class="card xs" v-for="document in documentsPacked" :key="document.id">
            <div class="card-title">
                <i class="picto icon-doc" :class="'doc' + document.extension"></i>

                <template v-if="document.editmode">
                    <select @change="changeTypeDocument(document, $event)" @blur="document.editmode = false">
                        <option v-for="(documentType, key) in documentTypes"
                                :value="key"
                                :key="documentType.id"
                                :selected="document.categoryText == documentType">
                            {{ documentType }}
                        </option>
                    </select>
                </template>
                <template v-else>
                    <small class="text-light" @dblclick="document.editmode = true">{{ document.categoryText }} ~ </small>
                </template>


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
                        <i class="icon-download-outline"></i>
                        Télécharger le fichier
                    </a>

                    <a class="btn btn-default btn-xs" :href="document.urlReupload" v-if="document.urlReupload">
                        <i class="icon-download-outline"></i>
                        Nouvelle version
                    </a>

                    <a class="btn btn-default btn-xs" @click.prevent="deleteDocument(document)">
                        <i class="icon-trash"></i>
                        supprimer le fichier
                    </a>
                </nav>
            </div>
        </article>
    </section>
</template>
<script>

    import AjaxOscar from "./remote/AjaxOscar";
    import OscarRemoteData from "./remote/OscarRemoteData";

    // test
    let oscarRemoteData = new OscarRemoteData();

    function flashMessage(){
        // TODO
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
                formData: null,
                error: null,
                deleteData: null,
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

            changeTypeDocument: function( document, event ){

                /***
                 oscarRemoteData
                 .setPendingMessage("Chargement des documents")
                 .setErrorMessage("Impossible de charger les documents")
                 .performGet(this.url, this.handlerSuccess);
                 */

                var newType = event.target.selectedOptions[0].text;

                oscarRemoteData
                    .setPendingMessage("Modification du type de document")
                    .setErrorMessage("Impossible de modifier le type de document")
                    .performPost(this.urlDocumentType, {
                        documentId: document.id,
                        type: newType

                    }, (response) => {
                        // Modification du type
                        document.categoryText = newType;
                        document.editMode = false;
                        document.editMode = false;
                        this.$forceUpdate();

                    }, () => {
                        document.editMode = false;
                        this.$forceUpdate();
                    })

                // this.$http.post(this.urlDocumentType, {
                //     documentId: document.id,
                //     type: newType
                // }).then(ok => {
                //     flashMessage('success', 'Le document a bien été modifié', ok);
                //     document.categoryText = newType;
                //     document.editMode = false;
                //     this.$forceUpdate();
                // }, error => {
                //     flashMessage('error', 'Erreur' + error.responseText);
                //     document.editMode = false;
                // });
            },

            handlerSuccess(success){
                let data = success.data.datas;
                let documentsOrdered = [];
                let documents = {};

                data.forEach(function(doc){
                    doc.categoryText = doc.category ? doc.category.label : "";
                    doc.editmode = false;
                    doc.explode = false;
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
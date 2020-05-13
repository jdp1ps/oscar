<template>
    <section>
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


        <article class="card xs" v-for="document in documentsPacked">
            <div class="card-title">
                <i class="picto icon-doc" :class="'doc' + document.extension"></i>

                <template v-if="document.editmode">
                    <select @change="changeTypeDocument(document, $event)" @blur="document.editmode = false">
                        <option :value="key" v-for="(documentType, key) in documentTypes" :selected="document.categoryText == documentType">{{ documentType }}</option>
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
                    <article v-for="sub in document.previous" class="subdoc text-highlight">
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
                    <a class="btn btn-default btn-xs" :href="document.urlDownload">
                        <i class="icon-download-outline"></i>
                        Télécharger le fichier
                    </a>

                    <a class="btn btn-default btn-xs" :href="document.urlReupload">
                        <i class="icon-download-outline"></i>
                        Nouvelle version
                    </a>

                    <a class="btn btn-default btn-xs" data-confirm="Êtes-vous sûr de vouloir supprimer ce document ?" :data-href="document.urlDelete">
                        <i class="icon-trash"></i>
                        supprimer le fichier
                    </a>
                </nav>
            </div>
        </article>
    </section>
</template>
<script>

    // nodejs node_modules/.bin/poi watch --format umd --moduleName  DocumentsActivity --filename.js DocumentsActivity.js --dist public/js/oscar/dist public/js/oscar/src/DocumentsActivity.vue

    export default {
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
                editable: true
            }
        },

        computed:{
            /**
             * Retourne les documents triés.
             * @returns {Array}
             */
            documentsPacked(){
                var out = this.documents.sort( function(a,b) {
                    if( a[this.sortField] < b[this.sortField] )
                        return -1 * this.sortDirection;
                    if( a[this.sortField] > b[this.sortField] )
                        return 1 * this.sortDirection;
                    return 0;
                }.bind(this));
                return out;
            }
        },

        methods:{
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
                var newType = $(event.target.selectedOptions[0]).text();

                $.post(this.urlDocumentType, {
                    documentId: document.id,
                    type: newType
                }).then(ok => {
                    flashMessage('success', 'Le document a bien été modifié');
                    document.categoryText = newType;
                    document.editMode = false;
                    this.$forceUpdate();
                    //this.$forceUpdate();
                }, error => {
                    flashMessage('error', 'Erreur' + error.responseText);
                    document.editMode = false;
                });
            },

            fetch(){
                this.$http.get(this.url).then(
                    ok => {
                        let data = ok.data;
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
                    ko => {
                        console.log("ERROR", ko);
                    }
                )

            }
        },
        mounted(){
            console.log("DEBUG");
            this.fetch();
        }
    }
</script>
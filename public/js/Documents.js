/**
 * Created by jacksay on 08/12/16.
 */

define('Documents', ['jquery', 'moment', 'vue', 'LocalDB', 'OscarUI', 'text!templates/document.hbs', 'text!templates/person-item.vue'], function($, moment, Vue, LocalDB, OscarUI, itemTpl, personItemTpl){
    "use strict";


    var HttpURL = {
        documentType: null
    };

    var tooltipTemplate = Vue.compile('<div>{{ displayname }}</div>');
    console.log(tooltipTemplate);

    Vue.component('person', {
        template: personItemTpl,

        props: ['person'],
        computed: {
            hasLink: function(){
                return this.person && this.person.urlPerson ? true : false
            }
        },
        methods: {
            navigateToPerson: function(){
                window.location = this.person.urlPerson;
            },
            tooltip: function(e){
                /*
                if( this.person && this.person.id ){
                    OscarUI.showTooltip('</div><img src="https://www.gravatar.com/avatar/'+ this.person.mailMd5 +'" style="float:left"/>'
                        +'<div><h3>' +this.person.displayname +'</h3>'
                        +'<strong>'+this.person.ucbnSiteLocalisation+'</strong><br>'
                        + (this.person.urlPerson ? '<a href="'+this.person.urlPerson+'">Voir la fiche</a>' : '') +'<br>'
                        + (this.person.mail ? '<i class="icon-mail"></i>' + this.person.mail : '')
                        + '</div>'
                    , e);
                }*/
            }
        }
    });

    Vue.filter('dateFull', function(date) {
        var m = moment(date);
        return "le " + m.format('dddd D MMMM YYYY') + ', ' + moment(date).fromNow();
    });

    Vue.filter('dateFullSort', function(date) {
        var m = moment(date);
        return m.format('D MMMM YYYY') + ', ' + moment(date).fromNow();
    });

    Vue.filter('filesize', function(octets) {
        var sizes = ['Octets', 'KB', 'MB', 'GB', 'TB'];
        if (octets == 0) return '0 Octet';
        var i = parseInt(Math.floor(Math.log(octets) / Math.log(1024)));
        return Math.round(octets / Math.pow(1024, i), 2) + ' ' + sizes[i];
    });

    Vue.component('document', {
       template: itemTpl,
        props: ['document', 'documentTypes'],
        methods: {
            changeTypeDocument: function( document, event ){
                var newType = $(event.target.selectedOptions[0]).text();

                $.post(HttpURL.documentType, {
                    documentId: document.id,
                    type: newType
                }).then(function(){
                    flashMessage('success', 'Le document a bien été modifié');
                    this.document.categoryText = newType;
                    this.document.editMode = false;
                    this.$forceUpdate();
                }.bind(this)).fail(function(error){
                    flashMessage('error', 'Erreur' + error.responseText);
                }.bind(this))
            },
        }
    });

    var OscarDocument = function( options ){

        var conf = new LocalDB('activity_documents', {
            sortField: "dateUpload",
            sortDirection: 1
        });

        HttpURL.documentType = options.urlDocumentType;

        /**
         * Récupération des données.
         */
        $.getJSON(options.url, function(data) {
            var documents = {};
            var documentsOrdered = [];

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

            new Vue({
                el: options.el,
                template: options.template,

                /**************************************************************/
                methods: {
                    order: function (field) {
                        if( this.sortField == field ){
                            this.sortDirection *= -1;
                        } else {
                            this.sortField = field;
                        }
                    },
                    cssSort: function(compare){
                        return compare == this.sortField ? "active" : "";
                    }
                },

                /**************************************************************/
                computed: {
                    /**
                     * Retourne les stacks de documents triés.
                     * @returns {*}
                     */
                    sortedDocument: function(){
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

                /**************************************************************/
                // On enregistre les préférences de trie dans les localStorage.
                watch: {
                    sortField: function( val, oldVal ){
                        conf.set('sortField', val);
                    },
                    sortDirection: function( val, oldVal ){
                        conf.set('sortDirection', val);
                    }
                },

                /**************************************************************/
                data: {
                    loading: true,
                    sortField: conf.get('sortField'),
                    sortDirection: conf.get('sortDirection'),
                    documentTypes: options.documentTypes,
                    editable: true,
                    documents: documentsOrdered,
                    url: options.url
                }
            });
        });
    };
    return OscarDocument;
});

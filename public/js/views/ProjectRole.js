(function (window, $, _, Backbone) {

    'use strict';

    var modal, getModal = function(){
        if (!modal) {
            modal = new RoleModalView();
        }
        return modal;
    };



    /**
     * Fonction de mise en forme du choix proposé dans l'auto-compléteur.
     *
     * @param repo
     * @returns {*}
     */
    function formatRepo(repo) {
        if (repo.loading) return repo.text;

        var markup = '<div class="clearfix">';
        markup += repo.label || repo.text;
        markup += '</div>';

        return markup;
    }

    /**
     * Fonction de mise en forme de la donnée choisie.
     *
     * @param repo
     * @returns {*}
     */
    function formatRepoSelection(repo) {
        return repo.label || repo.text;
    }


    Handlebars.registerHelper('dateFormat', function(context, block){
        if(window.moment){
            var f = block.hash.format || 'D MMMM YYYY';
            return window.moment(context).format(f);
        } else {
            return context;
        }
    });

    var EnrolModel = Backbone.Model.extend({
        defaults: function() {
            return {
                id: null,
                label: "",
                roles: []
            };
        },

        saveRole: function( datas ){
            this.collection.original.saveRole(datas, this.get('id'));
        }
    });

    var EnrolCollection = Backbone.Collection.extend({
        //model: EnrolModel,
        byObject: null,
        owner: null,

        initialize: function () {
            this.on('sync', function () {
                this.orderByObject();
            }.bind(this));
        },

        orderByObject: function () {
            this.byObject = new Backbone.Collection([],{model: EnrolModel});
            this.byObject.original = this;

            this.forEach(function (role) {
                console.log(role);
                var enrolId = role.get('object').id, enrol;
                if (!this.byObject.get(enrolId)) {
                    this.byObject.add(role.get('object'))
                }
                enrol = this.byObject.get(enrolId);
                console.log('enrol', enrol);
                enrol.get('roles').push(role.toJSON());
            }.bind(this));
        },

        getByObject: function () {
            return this.byObject;
        },

        deleteRole: function (id) {
            console.log('Suppression du role ', id);
            $.ajax({
                method: "delete",
                url: this.urlDelete + id
            }).done(function (response) {
                this.reset();
                this.fetch();
            }.bind(this)).fail(function (xhr) {
                console.error('fail', xhr.responseJSON.error);
            });
        },

        saveRole: function (datas, enrolId) {
            if( enrolId ) {
                datas.enrolid = enrolId;
            }
            datas.ownerid = this.ownerId;
            console.log('Enregistrement des données', datas);

            $.ajax({
                url: this.urlInsert,
                method: 'post',
                data: datas
            }).done(function(){
                console.log('done', arguments);
                this.fetch();
            }.bind(this)).fail(function(xhr){
                console.error('fail', xhr.responseJSON.error);
            }).always(function(){
                modal.close();
            });
        }
    });

     /**
     * MainView
     */
    var EnrolCollectionView = Backbone.View.extend({
        initialize: function (options) {
            console.log('RolesCollectionView:', options);
            this.listenTo(this.model, 'sync', this.render);
            $('.addOrganization').on('click', function(){
                getModal().show(this.model);
            }.bind(this));
        },

        render: function () {
            this.$el.html("<h1>" + this.model.getByObject().length + " partenaires(s)</h1>");
            this.model.getByObject().forEach(function (role) {
                this.$el.append(new EnrolView({model: role}).render().el);
            }.bind(this));
        }
    });

    /**
     * Gestion de la modal pour ajouter/modifier un rôle.
     *
     * Données à transmettre :
     *  - URL de traitement
     *  - roleId (pour la modification)
     *  -
     */
    var RoleModalView = Backbone.View.extend({
        className: 'modal',
        template: Handlebars.compile($('#tplModal').html()),

        // Événements
        events: {
            'click .save': 'clickSave'
        },

        clickSave: function(e){
            var datas = {
                dateStart: this.$el.find('[name="role-from"]').val(),
                dateEnd: this.$el.find('[name="role-to"]').val(),
                role: this.$el.find('[name="role"]').val()/*,
                dateStart: this.$el.find('[name="enrol-id"]').val()*/
            };

            var $selector = this.$('.js-data-example-ajax');
            if( $selector ){
                console.log('ENROL SAISI !');
               datas.enrolid = $selector.val();
            }

            if( this.model.roleId ){
                datas.roleId = roleId;
            }
            this.model.saveRole(datas);
        },

        initialize: function () {
            $('body').append(this.el);
        },

        /**
         * @param data Données (ex : organisation) à laquelle on affecte un rôle
         */
        show: function (data) {
            this.model = data;
            this.$el.html(this.template(this.model ? this.model.toJSON() : {}));

            // Activation des datepicker
            $('.input-group.date', this.$el).datepicker({
                format: "yyyy-mm-dd",
                todayBtn: "linked",
                clearBtn: true,
                language: "fr",
                autoclose: true,
                toggleActive: true
            });

            var $selector = this.$('.js-data-example-ajax');

            $selector.select2({
                placeholder: 'Saisissez le nom à ajouter...',
                allowClear: true,
                ajax: {
                    url: this.model.urlSearch,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {

                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, page) {
                        console.log(data);
                        return {results: data.datas};
                    },
                    cache: true
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                minimumInputLength: 2,
                id: function (dt) {
                    return dt.uid;
                },
                templateResult: formatRepo, // omitted for brevity, see the source of this page
                templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
            });

            this.$el.modal('show');
        },

        close: function(){
            this.$el.modal('hide');
        }
    });

    /**
     * Affichage de l'objet ayant des rôles, ex :
     * - L'organisation d'un projet
     * - Une personne d'un projet
     * - Une personne d'une organisation
     */
    var EnrolView = Backbone.View.extend({

        template: Handlebars.compile($('#enrolTpl').html()),

        events: {
            'click .btnDelete': 'deleteRole',
            'click .btnNewRole': 'newRole',
            'click .btnAddRole': 'addRole'
        },

        deleteRole: function (e) {
            this.model.collection.original.deleteRole($(e.target).data('id'));
        },

        // todo A surcharger selon le type d'objet manipuler
        addRole: function (e) {

            // Création du container de base
            getModal().show(this.model);
        },

        render: function () {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });


    window.Oscar = window.Oscar || {};
    window.Oscar.EnrolCollection = EnrolCollection;
    window.Oscar.EnrolCollectionView = EnrolCollectionView;

})(this, jQuery, _, Backbone)


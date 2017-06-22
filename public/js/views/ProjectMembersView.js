/**
 * Created by jacksay on 05/08/15.
 */
(function (root, $, _, Backbone) {


    root.Oscar = root.Oscar || {};


    var modal = null, // @todo Mettre en cache la modal pour éviter de reconstruire le DOM à chaque ajout.
        mainView = null, // Référence à la vue principale
        urlApi = null, // URL pour la liste des personnes
        urlRoleUpdate = null; // URL (post) pour sauver les rôles

    /**
     * Format des dates "User Friendly"
     */
    Handlebars.registerHelper('dateFormat', function(context, block){
        if(root.moment){
            var f = block.hash.format || 'D MMMM YYYY';
            return root.moment(context).format(f);
        } else {
            return context;
        }
    });

    /**
     * Fonction de mise en forme du choix proposé dans l'auto-compléteur.
     *
     * @param repo
     * @returns {*}
     */
    function formatRepo(repo) {
        if (repo.loading) return repo.text;

        var markup = '<div class="clearfix">';

        if (repo.displayname) {
            markup += '<div class="col-md-2">';
            markup += '<img src="//www.gravatar.com/avatar/' + repo.mailMd5 + ' alt="" class="img-rounded trombi pull-left"/>';
            markup += '</div>';
            markup += '<div class="col-md-10">';
            markup += '<h6>' + repo.displayname + '</h5>';
            markup += '<div class="small"><i class="glyphicon glyphicon-screenshot"></i> ' + repo.ucbnSiteLocalisation + '</div>';
            markup += '<div class="small"><i class="glyphicon glyphicon-envelope"></i> ' + repo.mail + '</div><hr/>';
            markup += '</div>';
        } else {
            markup = '<h5>' + repo.text + '</h5>';
        }
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
        return repo.displayname || repo.text;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // VIEWS
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Modal utilisée pour modifier/ajouter un rôle.
     */
    root.Oscar.ProjectRoleModalView = Backbone.View.extend({

        // Template
        template: Handlebars.compile($('#tplModal').html()),

        className: 'modal',

        events: {
            'click button.save': 'save'
        },

        initialize: function (options) {
            console.log('###################### Création de la modal');
            console.log(options);
            this.project = options.project;
            this.person = options.person;
            this.urlApi = options.urlApi;
        },

        /**
         * Enregistre le rôle de la personne.
         */
        save: function () {
            var personId,
                projectId = this.project.id,
                from = this.$('[name="role-from"]').val(),
                to = this.$('[name="role-to"]').val(),
                role = this.$('select[name="role"]').val();

            if (!this.person) {
                personId = this.$('.js-data-example-ajax').val();
            } else {
                personId = this.person.id;
            }

            //console.log('person', personId, "project", projectId, "role", role, 'from', from, 'to', to);
            //return;

            $.ajax({
                type: 'POST',
                url: urlRoleUpdate,
                data: {
                    personid: personId,
                    projectid: projectId,
                    from: from,
                    to: to,
                    role: role
                }
            })
                .done(
                function (json) {
                    console.log(arguments);
                    mainView.refresh(json);
                }.bind(this)
            )
                .fail(
                function (xhr) {
                    mainView.displayError(xhr.responseText);
                }.bind(this)
            )
                .always(
                function () {
                    this.$el.modal('hide');
                }.bind(this)
            );
        },

        render: function () {
            var label = "";
            if (!this.person) {
                label = "Ajout d'une personne";
            } else {
                label = "Ajout d'un rôle à <strong>" + this.person.firstName + " " + this.person.lastName + "</strong>"
            }
            this.$el.html(this.template({
                label: label,
                project: this.project,
                person: this.person
            }));
            this.$el.modal();

            console.log(this.$('.input-group.date'));
            this.$('.input-group.date').datepicker({
                format: "yyyy-mm-dd",
                todayBtn: "linked",
                clearBtn: true,
                language: "fr",
                autoclose: true,
                toggleActive: true
            });

            if (!this.person) {
                var $selector = this.$('.js-data-example-ajax');

                $selector.select2({
                    placeholder: 'Saisissez le nom à ajouter...',
                    allowClear: true,
                    ajax: {
                        url: this.urlApi,
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
                    minimumInputLength: 4,
                    id: function (dt) {
                        return dt.uid;
                    },
                    templateResult: formatRepo, // omitted for brevity, see the source of this page
                    templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
                });
            }

            console.log(this.$el);
            return this;
        }
    });

    /**
     * Affichage et gestion d'une personne de l'équipe.
     */
    root.Oscar.ProjectPersonView = Backbone.View.extend({

        // Template
        templatePerson: Handlebars.compile($('#tplPerson').html()),

        // Événements
        events: {
            'click .btnNewRole': 'newRole',
            'click .btnDelete': 'deleteRole'
        },

        ////////////////////////////////////////////////////////////////////////
        //
        //
        render: function () {
            this.$el.html(this.templatePerson(this.person));
            return this;
        },

        initialize: function (options) {
            console.log('Création de la vue pour une personne');
            this.project = options.project;
            this.person = options.person;
            this.urlApi = options.urlApi;
        },

        newRole: function (e) {
            console.log("Nouveau Rôle !", this.projectData);
            new Oscar.ProjectRoleModalView({
                person: this.person,
                project: this.project,
                urlApi: this.urlApi
            }).render();

        },

        /**
         * Suppression d'un rôle.
         *
         * @param e
         */
        deleteRole: function (e) {
            $.ajax({
                url: $(e.currentTarget).data('url')
            }).done(function (data) {
                mainView.refresh(data);
            }).fail(function (xhr) {
                mainView.displayError(xhr.responseText);
            });
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }
    });


    /**
     * Vue globale. (mainView)
     */
    root.Oscar.ProjectMembersView = Backbone.View.extend({

        // URL de l'API permettant d'interroger les personnes de la BDD.
        url: null,

        // Données JSON
        projectData: null,
        members: null,

        // Templates
        templateProject: Handlebars.compile($('#tplProject').html()),

        // Événements
        events: {
            'click .btnAddMember': 'newMember'
        },

        // CONSTRUCTEUR
        initialize: function (options) {
            console.log("Création de l'instance", options);
            this.url = options.url;
            this.setData(options.data);
            urlApi = this.urlApi = options.urlApi;
            urlRoleUpdate = options.urlRoleUpdate;
            mainView = this;
        },

        refresh: function (newData) {
            this.setData(newData);
            this.render();
        },

        /**
         * Permet de regrouper les rôles endossés par une personne pour
         * simplifier l'affichage.
         *
         * @returns {root.Oscar.ProjectMembersView}
         */
        setData: function (data) {
            var members = {};
            data.members.forEach(function (member) {
                if (!members[member.person.id]) {
                    members[member.person.id] = member.person;
                    members[member.person.id].roles = [];
                }
                members[member.person.id].roles.push(member);
            });
            this.project = data;
            this.members = members;
            return this;
        },

        // RENDER
        render: function () {
            this.$el.html(this.templateProject(this.projectData));
            var $elMember = this.$el.find('.members');
            _.each(this.members, function (person) {
                console.log("create member view", person);
                var personView = new root.Oscar.ProjectPersonView({
                    person: person,
                    project: this.project,
                    urlApi: this.urlApi
                })
                $elMember.append(personView.render().el);
            }.bind(this));
            return this;
        },

        ////////////////////////////////////////////////////////////////////////
        //
        // Handlers
        //
        ////////////////////////////////////////////////////////////////////////
        /**
         * Affiche le modal pour ajouter une personne.
         */
        newMember: function (e) {
            new Oscar.ProjectRoleModalView({
                person: null,
                project: this.project,
                urlApi: this.urlApi
            }).render();
        },

        /**
         * Affiche un message d'erreur.
         *
         * @param message
         */
        displayError: function (message) {
            this.$('.errors').fadeIn(250).find('.card-content').text(message);
        }
    });
})(this, jQuery, _, Backbone);

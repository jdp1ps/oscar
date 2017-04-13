/**
 * Boîte de dialogue utilisée pour gérer les "Enrollement" (Membre, partenaires).
 *
 * Created by jacksay on 17/09/15.
 */
define(['backbone', 'hbs', 'datepicker', 'select2'], function (Backbone, Handlebars) {
    'use strict';

    var formatRepo, formatRepoSelection, modal, EnrolModalView;

    /**
     * Fonction de mise en forme du choix proposé dans l'auto-compléteur.
     *
     * @param repo
     * @returns {*}
     */
    formatRepo = function (repo) {
        if (repo.loading) return repo.text;

        var markup = '<div class="clearfix">';
        markup += repo.label || repo.text;
        markup += '</div>';

        return markup;
    };

    /**
     * Fonction de mise en forme de la donnée choisie.
     *
     * @param repo
     * @returns {*}
     */
    formatRepoSelection = function (repo) {
        return repo.label || repo.text;
    };

    EnrolModalView = Backbone.View.extend({

        className: 'modal',
        template: Handlebars.compile($('#tplModal').html()),

        // Événements
        events: {
            'click .save': 'clickSave'
        },

        clickSave: function (e) {
            var datas = {
                dateStart: this.$el.find('[name="role-from"]').val(),
                dateEnd: this.$el.find('[name="role-to"]').val(),
                role: this.$el.find('[name="role"]').val()/*,
                 dateStart: this.$el.find('[name="enrol-id"]').val()*/
            };

            var $selector = this.$('.js-data-example-ajax');
            if ($selector) {
                console.log('ENROL SAISI !');
                datas.enrolid = $selector.val();
            }

            if (this.model.roleId) {
                datas.roleId = roleId;
            }
            this.model.saveRole(datas);
        },

        initialize: function () {
            $('body').append(this.el);
            console.log($);
        },

        /**
         * @param data Données (ex : organisation) à laquelle on affecte un rôle
         */
        show: function (data) {
            console.log(data);
            this.model = data;
            this.$el.html(this.template(this.model ? this.model.toJSON() : {}));

            require(['datepicker'], function () {
                // Activation des datepicker
                $('.input-group.date', this.$el).datepicker({
                    format: "yyyy-mm-dd",
                    todayBtn: "linked",
                    clearBtn: true,
                    language: "fr",
                    autoclose: true,
                    toggleActive: true
                });
            }.bind(this));


            var $selector = this.$('.js-data-example-ajax');

            $selector.select2({
                placeholder: 'Rechercher...',
                width: '100%',
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

        close: function () {
            this.$el.modal('hide');
        }
    });

    return EnrolModalView;
});

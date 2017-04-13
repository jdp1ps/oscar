/**
 * Affiche une EnrolCollection.
 *
 * Created by jacksay on 17/09/15.
 */
define(['backbone', 'hbs', 'views/EnrolView', 'views/EnrolModalView'], function (Backbone, Handlebars, EnrolView, EnrolModalView) {
    'use strict';

    var modal, EnrolCollectionView = Backbone.View.extend({
        /**
         * Retourne la boîte modale pour la saisie des informations.
         *
         * @returns {*}
         */
        getModal: function () {
            if (!modal) {
                modal = new EnrolModalView({
                    template: Handlebars.compile(this.modalTpl)
                });
                console.log('ECOUTEUR');
                this.model.on('sync', function(){
                   modal.close();
                });

            }
            return modal;
        },

        initialize: function (options) {
            this.modalTpl = options.modalTpl;
            this.text = options.text;
            this.listenTo(this.model, 'sync', this.render);
            $('.addOrganization').on('click', function () {
                this.getModal().show(this.model);
            }.bind(this));
        },

        render: function () {
            this.$el.html("<h1>" + this.model.getByObject().length + " " + this.text + "</h1>");
            this.model.getByObject().forEach(function (role) {
                this.$el.append(new EnrolView({
                    model: role,
                    parent: this
                }).render().el);
            }.bind(this));
        }
    });

    return EnrolCollectionView;
});

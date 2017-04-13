/**
 * Created by jacksay on 17/09/15.
 */
define(['backbone', 'handlebars'], function(Backbone, Handlebars){
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
            'click .btnAddRole': 'addRole'
        },

        initialize: function(options){
            this.parent = options.parent;
            console.log(this.model.get('id'));
        },

        deleteRole: function (e) {
            this.model.collection.original.deleteRole($(e.target).data('id'));
        },

        // todo A surcharger selon le type d'objet manipuler
        addRole: function (e) {
            console.log('addRole()');
            this.parent.getModal().show(this.model);
        },

        render: function () {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });
    return EnrolView;
});

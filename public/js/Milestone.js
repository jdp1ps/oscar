define('Milestone', ['Oscar', 'Backbone', 'hbs', 'text!templates/milestone-item.hbs'], function(Oscar, Backbone, Handlebars, itemTpl){

    "use strict";

    var Milestone = {};

    ////////////////////////////////////////////////////////////////////////////
    //
    // MODEL
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Décrit une échéance.
     */
    Milestone.Model = Backbone.Model.extend({
        defaults: function(){
            return {
                'date': null,
                'type': 'Type inconnu'
            };
        }
    });

    /**
     * Collection d'échéance
     */
    Milestone.Collection = Backbone.Collection.extend({
        model: Milestone.Model
    });

    ////////////////////////////////////////////////////////////////////////////
    //
    // VIEWS
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Vue d'une échéance (de la liste).
     */
    Milestone.ViewItem = Oscar.GenericItemView.extend({
        events: {
            'click .btn-delete': 'processDelete',
            'click .btn-edit': 'processEdit',
        },
        template: Handlebars.compile(itemTpl)
    });

    /**
     * Vue pour la liste des échéance
     */
    Milestone.View = Oscar.GenericCollectionView.extend({
        itemClass: Milestone.ViewItem
    });

    return Milestone;
});


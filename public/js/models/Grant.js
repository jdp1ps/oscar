/**
 * Permet de gérer une source de financement (Contrat)
 * Created by jacksay on 14/09/15.
 */
(function(root, $, _, Backbone, Handlebars){
    'use strict';

    Backbone.emulateHTTP = false;
    Backbone.emulateJSON = true;

    root.Oscar = root.Oscar || {};
    root.Oscar.Model = root.Oscar.Model || {};
    root.Oscar.Collection = root.Oscar.Collection || {};

    var GrantModel, GrantCollection;

    /**
     * Contrats.
     */
    GrantModel = Backbone.Model.extend({
        defaults: function(){
            return {
                id: null,
                amount: 0,
                dateStart: null,
                dateEnd: null,
                idsource: null,
                idtype: null
            }
        },

        initialize: function(){
            console.log('Create instance of Oscar.Model.GrantModel');
        }
    });

    /**
     * Liste des contrats.
     */
    GrantCollection = Backbone.Collection.extend({
        model: GrantModel,
        initialize: function(){
            console.log('Create instance of Oscar.Collection.GrantCollection');
        }
    });

    root.Oscar.Model.GrantModel = GrantModel;
    root.Oscar.Collection.GrantCollection = GrantCollection;

})(this, jQuery, _, Backbone, Handlebars);

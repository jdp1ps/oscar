/**
 * Created by jacksay on 17/09/15.
 */
define(['backbone'], function(Backbone){

    'use strict';

    var EnrolModel = Backbone.Model.extend({

        // Valeurs par défaut
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

    return EnrolModel;
});

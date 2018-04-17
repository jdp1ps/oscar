(function(root){
    "use strict";

    /**
     * Modèle de données pour la fiche activité.
     *
     * @type {{counter: number, name: string, version: string, milestones: Array, payments: Array, persons: Array, organizations: Array, increment: (function())}}
     */
    var ActivityModel = {
        counter: 1,
        name: "Activity Model",
        version: "1.0",
        milestones: [],
        payments: [],
        persons: [],
        organizations: [],
        types: [],
        listenners: {},

        on: function (event, callback){
            if( !this.listenners.hasOwnProperty(event) ){
                this.listenners[event] = [];
            }
            this.listenners[event].push(callback);
        },

        trigger: function(event){
            if( this.listenners.hasOwnProperty(event) ){
                this.listenners[event].forEach( callback => {
                    callback();
                })
            }
        }
    }

    if( root.define ){
        define(function(){
            return ActivityModel;
        });
    } else {
        root.ActivityModel = ActivityModel;
    }
})(this);
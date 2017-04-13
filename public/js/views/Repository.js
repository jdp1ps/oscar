(function(window){
    
    'use strict';
    
    var projects = [],
        persons = [],
        instance = null;
    
    
    var getInstance = function(){
        if( instance === null ){
            instance = {
                getProject: function(id){
                    console.log('getProject', id);
                },
                getPerson: function(id){
                    console.log('getPerson', id);
                },
                getOrganisation: function(id){
                    console.log('getOrganisation', id);
                }
            };
        }
        return instance;
    };
    
    window.Oscar = window.Oscar || {};
    window.Oscar.Repository = {
        getInstance: function(){
            return getInstance()
        }
    };   
})(this, jQuery, _, Backbone)


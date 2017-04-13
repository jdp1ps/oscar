/**
 * Classe utilitaire pour faire persister des données dans le localStorage du
 * navigateur.
 */
(function(root){
    "use strict";

    /**
     * PRIVATE : Permet de tester si les localStorage sont dispos.
     *
     * @param type
     * @returns {boolean}
     */
    function storageAvailable(type) {
        try {
            var storage = window[type],
                x = '__storage_test__';
            storage.setItem(x, x);
            storage.removeItem(x);
            return true;
        }
        catch(e) {
            return false;
        }
    }

    /**
     *
     * @param key Clef d'enregistrement
     * @param datas Données par défaut
     * @returns {*}
     * @constructor
     */
    var LocalDB = function( key, datas ){

        if( !storageAvailable('localStorage' )) {
            return datas;
        }

        this.key = key;

        var store = localStorage.getItem(key);
        if( store ){
            store = JSON.parse(store);
            Object.keys(datas).forEach(function(k,v){
                if( store[k] ){
                    datas[k] = store[k];
                }
            });
        }

        this.get = function( key, defaultValue ){
            return datas[key] || (defaultValue || null);
        };

        this.set = function( key, value ){
             datas[key] = value;
             localStorage.setItem(this.key, JSON.stringify(datas));
        };
    }

    root.LocalDB = LocalDB;

    if( typeof define == "function" ){
        define(function(){
            return LocalDB;
        });
    }

})(this);
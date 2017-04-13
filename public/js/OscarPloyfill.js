(function(root){
    "use strict";
    // On code son module
    var OscarPolyfill = {
        name: "oscar-polyfill",
        version: "0.0.1",
        getLocalStorageItem: function(key, defaultValue){
            if( localStorage && localStorage.getItem(key) ){
                return JSON.parse(localStorage.getItem(key));
            } else {
                return defaultValue;
            }
        },
        setLocalStorageItem: function(key, value){
            if( localStorage ){
                localStorage.setItem(key, JSON.stringify(value));
            }
        }
    }

    if( root.define ){
        define(function(){
            return OscarPolyfill;
        });
    } else {
        root.OscarPolyfill = OscarPolyfill;
    }
})(this);

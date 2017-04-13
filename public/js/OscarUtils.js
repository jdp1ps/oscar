(function(root){
    "use strict";
    // On code son module
    var OscarUtils = {
        name: "oscar-utils",
        version: "0.0.1"
    }

    if( root.define ){
        define(function(){
            return OscarUtils;
        });
    } else {
        root.OscarUtils = OscarUtils;
    }
})(this);

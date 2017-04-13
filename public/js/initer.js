/**
 * Created by jacksay on 09/10/15.
 */
var Initer = (function(){
    var closures = [],
        ready = false;
    return {
        isReady: function(){
            ready = true;
            closures.forEach(function(c){
                c();
            });
        },

        /**
         * Add closure executed when Initer come up.
         */
        ready: function( closure ){
            if( ready ){
                closure();
            } else {
                closures.push(closure);
            }
        }
    }
})();

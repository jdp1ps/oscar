const ERROR_MESSAGE_DEFAULT = "Erreur inconnue";
const ERROR_MESSAGE_DISCONNECTED = "Vous vous êtes déconnecté";

export default {
    error(ko){

        if( ko.request && ko.request.status === 403 ){
            return "Authorisation insuffisante (ou vous avez été déconnecté)";
        }

        if( ko.response ){
            return ko.response.data ? ko.response.data : "Pas de message d'erreur";
        } else {
            return ko.message ? ko.message : "Erreur inconnue";
        }
    },
    manageErrorResponse: function (err){
        console.log(err.response.data);
        let code = 504;
        let message = null;

        if( err && err.response && err.response.data ){
            code = err.response.status;
            // Technique de Jean Baptiste pour récupérer les erreurs HTML
            if (err.response.headers.get('content-type').includes('text/html')) {
                var el = document.createElement('html');
                el.innerHTML = err.response.data;
                let errorHTML = el.querySelector('[id="oscar_fatal_error"]');
                console.log(errorHTML);

                if( !errorHTML ){
                    errorHTML = el.querySelector('[id="contenu-principal"]');
                }

                if (!errorHTML) {
                    errorHTML = el.querySelector('body');
                }
                message = errorHTML.innerHTML;
            } else {
                // Erreur JSON "clean"
                if( err.response.data ){
                    message = err.response.data.error ? err.response.data.error : err.response.data;
                }
                if( code === 403 ){
                    message = ERROR_MESSAGE_DISCONNECTED;
                }
            }
        } else {
            message = err;
        }
        return {
            message: message,
            code: code,
        };
    }
};

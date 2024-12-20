const ERROR_MESSAGE_DEFAULT = "Erreur inconnue";
const ERROR_MESSAGE_DISCONNECTED = "Vous n'êtes pas autorisé à accéder à ces données";

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
        let code = err.response ? err.response.status : '500';
        let message = err;

        if( code === 403 ){
            message = ERROR_MESSAGE_DISCONNECTED;
        }
        else if( err && err.response && err.response.data ){
            code = err.response.status;
            // Technique de Jean Baptiste pour récupérer les erreurs HTML
            if (err.response.headers.get('content-type').includes('text/html')) {
                var el = document.createElement('html');
                el.innerHTML = err.response.data;
                let errorHTML = el.querySelector('[id="oscar_fatal_error"]');
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

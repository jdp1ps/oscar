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
    manageErrorResponse: function (response){
        console.log(response.response.data);
        let code = 500;
        let message = null;
        if( response && response.response ){
            code = response.response.status;
            if( response.response.data ){
                message = response.response.data.error ? response.response.data.error : response.response.data;
            }
            if( code === 403 ){
                message = ERROR_MESSAGE_DISCONNECTED;
            }
        }
        return {
            message: message,
            code: code,
        };
    }
};

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
    }
};

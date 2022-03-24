export default {
    resolve( message, ajaxResponse ){
        let serverMsg = "Erreur inconnue";
        if( ajaxResponse ){
            serverMsg = ajaxResponse.body;

            if( ajaxResponse.status == 403 ){
                serverMsg = "Vous avez été déconnectez de l'application";
            }
        }
        return message + " (Réponse : " + serverMsg +")";
    }
}
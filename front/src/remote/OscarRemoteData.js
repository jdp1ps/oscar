import axios from "axios";

class OscarRemoteData {
    constructor() {
        this.state = {
            loading: false,
            error: "",
            datas: null,
            errorMessage: "Erreur AJAX",
            pendingMessage: "Chargement des données"
        }
    }

    /**
     *
     * @param pendingMessage
     * @returns {OscarRemoteData}
     */
    setPendingMessage(pendingMessage){
        this.state.pendingMessage = pendingMessage;
        return this;
    }

    setErrorMessage(errorMessage){
        this.state.errorMessage = errorMessage;
        return this;
    }

    getAxiosInstance(){
        let instance = axios.create({});
        instance.defaults.headers.common['X_REQUESTED_WITH'] = 'XMLHttpRequest';
        return instance;
    }

    performGet(url, handlerResponse = null, handlerError = null){
        this.state.loading = true;
        this.getAxiosInstance().get(url)
            .then(
                response => {
                    this.state.datas = response.data;
                    if( handlerResponse ){
                        handlerResponse(response);
                    }
                })
            .catch(
                error => {
                    this.state.error = error;
                    if( handlerError ){
                        handlerError(error);
                    }
                })
            .finally( () => {
                this.state.loading = false;
            })
    }

    performPost(url, datas, handlerResponse = null, handlerError = null){
        this.state.loading = true;
        this.getAxiosInstance().post(url, datas)
            .then( response => {
                if( handlerResponse ){
                    handlerResponse(response);
                }
            })
            .catch( error => {
                if( handlerError ){
                    handlerError(error);
                }
            })
            .finally( () => {
                this.state.loading = false;
            })
    }

    delete(url){
        console.log("delete()", url)
    }

    update(url, datas){
        console.log("update()", url, datas)
    }

    push(url, datas){
        console.log("push()", url, datas)
    }

}

export default OscarRemoteData;
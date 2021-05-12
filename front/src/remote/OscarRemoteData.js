import axios from "axios";

class OscarRemoteData {
    constructor() {
        this.state = {
            loading: false,
            debug: false,
            error: "",
            datas: null,
            errorMessage: "",
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

    /**
     * Permet d'activer/désactiver le mode debug
     * @param bool
     */
    setDebug(b){
        this.state.debug = b;
        return this;
    }

    debug(){
        if(this.state.debug){
            console.log.apply(this, arguments);
        }
    }

    performGet(url, handlerResponse = null, handlerError = null){
        this.debug("[ORD] GET " + url);
        this.state.loading = true;
        this.getAxiosInstance().get(url)
            .then(
                response => {
                    this.debug("   > response ", response);
                    this.state.datas = response.data;
                    if( handlerResponse ){
                        handlerResponse(response);
                    } else {
                        this.debug(' > NO handerResponse given');
                    }
                })
            .catch(
                error => {
                    this.debug("   > error ", error);
                    this.debug(error);

                    this.state.error = error;
                    if( handlerError ){
                        handlerError(error);
                    }
                    else {
                        this.debug(' > NO handlerError given');
                    }
                })
            .finally( () => {
                this.state.loading = false;
            })
    }

    performPost(url, datas, handlerResponse = null, handlerError = null){
        console.log("performPost", url, datas );
        this.state.loading = true;
        this.getAxiosInstance().post(url, datas)
            .then( response => {
                if( handlerResponse ){
                    handlerResponse(response);
                }
            })
            .catch( error => {
                console.log(error);
                this.debug("[ERROR] " + error);
                if( handlerError ){
                    handlerError(error);
                } else {
                    this.state.error = error;
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
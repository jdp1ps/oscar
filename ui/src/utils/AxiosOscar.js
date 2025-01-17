import axios from "axios";
import AxiosMessage from "./AxiosMessage.js";
import GlobalModel from "../models/GlobalModel.js";

const log = function(){
    let params = ['[AxiosOscar]'];
    params.push(arguments);
    console.log.apply(params);
};

export default {

    get: (url, params = {}, pendingmsg = "chargement") => {
        log('GET', url, params, pendingmsg);
        GlobalModel.commit('addPending', pendingmsg);
        let response = axios.get(url, params);

        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            GlobalModel.commit('stopPending', pendingmsg);
        });

        return response;
    },

    post: (url, params = {}, pendingmsg = "Envoi des données") => {
        log('POST', url, params, pendingmsg);
        GlobalModel.commit('addPending', pendingmsg);
        let response = axios.post(url, params);
        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            GlobalModel.commit('stopPending', pendingmsg);
        });
        return response;
    },

    put: (url, params = {}) => {
        log('PUT', url, params, pendingmsg);
        GlobalModel.commit('addPending', pendingmsg);
        let response = axios.put(url, params);
        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            GlobalModel.commit('stopPending', pendingmsg);
        });
        return response;
    },
    delete: (url, pendingmsg) => {
        log('DELETE', url, pendingmsg);
        GlobalModel.commit('addPending', pendingmsg);
        let response = axios.delete(url);
        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            GlobalModel.commit('stopPending', pendingmsg);
        });
        return response;
    }
};
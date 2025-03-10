import axios from "axios";
import AxiosMessage from "./AxiosMessage.js";
import GlobalModel from "../models/GlobalModel.js";


axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const log = function(){
    let params = ['[AxiosOscar]'];
    console.log.apply(params);
};

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

const pendingOn = function(options){
    if( options.hasOwnProperty('pendingBack') && options.pendingBack === true ){
        GlobalModel.commit('pendingFullScreen', false);
    } else {
        GlobalModel.commit('pendingFullScreen', true);
    }
    let message = "Chargement des données";
    if( options.hasOwnProperty('pendingMsg') ){
        message = options.pendingMsg;
    }

    GlobalModel.commit('addPending', message);
};

const pendingOff = function(options){
    let message = "Chargement des données";
    if( options.hasOwnProperty('pendingMsg') ){
        message = options.pendingMsg;
    }
    GlobalModel.commit('stopPending', message);
};

export default {

    get: (url, options = {}) => {
        pendingOn(options);

        let response = axios.get(url);

        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            pendingOff(options);
        });

        return response;

    },

    post: (url, params = {}, options = {}) => {
        pendingOn(options);
        let response = axios.post(url, params);
        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            pendingOff(options);
        });
        return response;
    },

    put: (url, params = {}, options = {}) => {
        pendingOn(options);
        let response = axios.put(url, params);
        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            pendingOff(options);
        });
        return response;
    },
    delete: (url, options = {}) => {
        pendingOn(options);
        let response = axios.delete(url);
        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            pendingOff(options);
        });
        return response;
    },
    deleteParams: (url, params, options = {}) => {
        pendingOn(options);
        let response = axios.delete(url, { data: params, 'Content-Type':'application/json'});
        response.catch((error) => {
            GlobalModel.commit('addError', AxiosMessage.manageErrorResponse(error).message);
        }).finally(() => {
            pendingOff(options);
        });
        return response;
    }
};
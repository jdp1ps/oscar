import axios from "axios";
import AxiosMessage from "../utils/AxiosMessage.js";
import {createStore} from "vuex";

const globalStore = createStore({
    state() {
        return {
            rollPersonId: null,
            tooltip: null,
            urlPerson: null,
            pending:[],
            errors: [
            ],
            cachePersons:{}
        };
    },
    getters: {
      tooltipInfos(state){
          return state.tooltip;
      },
        errors(state){
            return state.errors;
        },
        pending(state){
          return state.pending;
        }
    },
    actions: {
        /// AIDE
        displayHelp(tag){

        },

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        tooltipReset({state}){
            if(state.tooltip){
                state.tooltip.display = false;
            }
        },
        tooltipPersonPreShooting({state}, data){
            if(state.tooltip && state.tooltip.type === 'person' && state.tooltip.id === data.id){
                state.tooltip.display = true;
                state.tooltip.x = data.event.pageX;
                state.tooltip.y = data.event.pageY;
                return true;
            }
            return false;
        },
        tooltipPerson({state, commit, dispatch}, tooltipInfos){
            if(state.tooltip && state.tooltip.type === 'person' && state.tooltip.id === tooltipInfos.id){
                console.log("Réactivation de la tooltip");
                state.tooltip.display = true;
                return true;
            } else {

                let posX = 0;
                let posY = 0;
                if( tooltipInfos.event ){
                    posX = tooltipInfos.event.pageX;
                    posY = tooltipInfos.event.pageY;
                }
                if( tooltipInfos && tooltipInfos.type === 'person' ){
                    let url = state.urlPerson + tooltipInfos.id;

                    if( state.cachePersons.hasOwnProperty(tooltipInfos.id) ) {
                        state.cachePersons[tooltipInfos.id].display = true;
                        commit('setTooltip', state.cachePersons[tooltipInfos.id]);
                    } else {
                        axios.get(url).then((response) => {
                            let tooltipDatas = {
                                type: tooltipInfos.type,
                                id: tooltipInfos.id,
                                url: state.urlPerson + tooltipInfos.id,
                                firstname: response.data.firstname,
                                lastname: response.data.lastname,
                                affectation: response.data.affectation,
                                location: response.data.location,
                                gravatar: response.data.gravatar,
                                url_show: response.data.url_show,
                                email: response.data.email,
                                display: true,
                                x: posX,
                                y: posY,
                            };
                            state.cachePersons[tooltipInfos.id] = tooltipDatas;
                            commit('setTooltip', tooltipDatas);
                        }, ko => {
                            commit('addError', AxiosMessage.manageErrorResponse(ko).message);
                        });
                    }
                }
            }
        },
        removeError({state}){
            state.errors.splice(state, 1);
        },
        removeErrors({state}){
            state.errors = [];
        }
    },
    mutations: {
        addPending(state, message){
            state.pending.push(message);
        },
        stopPending(state, message){
            let i = state.pending.indexOf(message);
            if(i >= 0){
                state.pending.splice(i, 1);
            }
        },

        addError(state, msg){
            state.errors.unshift(errorFormat(msg));
        },
        addErrorAxios(state, err){
            state.errors.unshift(errorFormat(AxiosMessage.manageErrorResponse(err).message));
        },
        setTooltip(state, tooltipInfos) {
            if( tooltipInfos ){
                state.tooltip = tooltipInfos;
            } else {
                state.tooltip = null;
            }
        }
    },

    errorFormat(msg){
    }

});

let errorFormat = function(msg){
    return {
        time: new Date().toISOString(),
        message: msg,
        type: 'error'
    };
};

export default globalStore;
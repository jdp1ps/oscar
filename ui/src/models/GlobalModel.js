import axios from "axios";
import AxiosMessage from "../utils/AxiosMessage.js";
import {createStore} from "vuex";

const globalStore = createStore({
    state() {
        return {
            rollPersonId: null,
            tooltip: null,
            urlPerson: null,
            errors: [
            ]
        };
    },
    getters: {
      tooltipInfos(state){
          return state.tooltip;
      },
        errors(state){
            return state.errors;
        },
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

                    // TODO Système de cache

                    axios.get(url).then((response) => {
                        commit('setTooltip', {
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
                        });
                    }, ko => {
                        commit('addError', AxiosMessage.manageErrorResponse(ko).message);
                    });
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
        addError(state, msg){
            state.errors.push(msg);
        },
        addErrorAxios(state, err){
            state.errors.push(AxiosMessage.manageErrorResponse(err).message);
        },
        setTooltip(state, tooltipInfos) {
            if( tooltipInfos ){
                state.tooltip = tooltipInfos;
            } else {
                state.tooltip = null;
            }
        }
    }
});

export default globalStore;
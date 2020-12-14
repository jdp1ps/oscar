import Vue from "vue";
import VueCompositionAPI from '@vue/composition-api'
import VueResource from "vue-resource";
import ActivityDocument from "./ActivityDocument"

Vue.use(VueResource);
Vue.use(VueCompositionAPI);

new Vue({
    render(h){
        return h(ActivityDocument, { "props": {
                "url": "http://localhost:8080/activites-de-recherche/documents-json/9310"
            }
        })
    }
}).$mount("#app")
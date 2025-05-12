import { createApp } from 'vue'
import ActivityNotes from './views/ParametersUserSendFrequency.vue'

// éléments dans le DOM
let elemId = '#parameters-nf';
let elemDatas = document.querySelector(elemId);

// Création de l'App
const app = createApp(ActivityNotes, {
    urlapi: elemDatas.dataset.urlapi,
    editable: elemDatas.dataset.editable
});

// Filtres
app.config.globalProperties.$filters = {
    duration(val) {
        // durée en heure
        let totalminutes = 60 * val;
        let hours = Math.floor(totalminutes / 60);
        let minutes = totalminutes % 60;
        if( minutes < 10 ) minutes = "0" + minutes;
        return `${hours}:${minutes}`;
    }
};

// Affichage de l'app
app.mount(elemId);

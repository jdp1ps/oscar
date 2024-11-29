import { createApp } from 'vue'
import ActivityNotes from './views/ActivityNotes.vue'

// éléments dans le DOM
let elemId = '#activity-notes';
let elemDatas = document.querySelector(elemId);

// Création de l'App
const app = createApp(ActivityNotes, {
    "activityid": elemDatas.dataset.activityid,
    "url": elemDatas.dataset.url,
    "showallowed": elemDatas.dataset.showallowed == "1" ? true : false,
    "manageuserallowed": elemDatas.dataset.manageuserallowed == "1" ? true : false,
    "manageadminallowed": elemDatas.dataset.manageadminallowed == "1" ? true : false,
    "userid": elemDatas.dataset.userid
});

// Affichage de l'app
app.mount(elemId);

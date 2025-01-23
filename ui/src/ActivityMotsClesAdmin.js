import { createApp } from 'vue'
import ActivityMotsClesAdmin from './views/ActivityMotsClesAdmin.vue'

// éléments dans le DOM
let elemId = '#activity-mots-cles-admin';
let elemDatas = document.querySelector(elemId);

// Création de l'App
const app = createApp(ActivityMotsClesAdmin, {
    "url": elemDatas.dataset.url
});

// Affichage de l'app
app.mount(elemId);

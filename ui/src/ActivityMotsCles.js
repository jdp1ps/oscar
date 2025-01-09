import { createApp } from 'vue'
import ActivityMotsCles from './views/ActivityMotsCles.vue'

// éléments dans le DOM
let elemId = '#activity-mots-cles';
let elemDatas = document.querySelector(elemId);

// Création de l'App
const app = createApp(ActivityMotsCles, {
    "url": elemDatas.dataset.url,
    "motsclesselectionnes": elemDatas.dataset.motsclesselectionnes
});

// Affichage de l'app
app.mount(elemId);

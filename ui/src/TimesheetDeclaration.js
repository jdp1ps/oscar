import { createApp } from 'vue';
import TimesheetDeclaration from "./views/TimesheetDeclaration.vue";
import momentFilter from "./utils/MomentFilter.js";
import filesize from "./utils/Filesize.js";
import money from "./utils/MoneyFilter.js";
import traces from "./utils/Traces.js";

// éléments dans le DOM
let elemId = '#timesheet-declaration';
let elemDatas = document.querySelector(elemId);

// Création de l'App
const app = createApp(TimesheetDeclaration, {
    "url": elemDatas.dataset.url,
    "url-validation": elemDatas.dataset.urlValidation,
    "url-import": elemDatas.dataset.urlImport,
    "default-year": elemDatas.dataset.defaultYear,
    "default-month": elemDatas.dataset.defaultMonth,
    "declarationInHours": elemDatas.dataset.declarationInHours,
});

// Filtres
app.config.globalProperties.$filters = {
    timeAgo(date) {
        return momentFilter.timeAgo(date);
    },
    date(date) {
        return momentFilter.date(date);
    },
    dateFull(date) {
        return momentFilter.dateFull(date);
    },
    filesize(size) {
        return filesize.filesize(size);
    },
    money(amount) {
        return money.money(amount);
    },
    duration2(val, lng) {
        // durée en heure
        let totalminutes = 60 * val;
        let hours = Math.floor(totalminutes / 60);
        let minutes = totalminutes % 60;
        return `${hours}:${minutes}`;
    }
};

// Affichage de l'app
app.mount(elemId);
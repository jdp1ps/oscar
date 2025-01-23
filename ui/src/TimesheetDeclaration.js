import { createApp } from 'vue';
import TimesheetDeclaration from "./views/TimesheetDeclaration.vue";
import momentFilter from "./utils/MomentFilter.js";
import filesize from "./utils/Filesize.js";
import money from "./utils/MoneyFilter.js";
import traces from "./utils/Traces.js";

// éléments dans le DOM
let elemId = '#timesheet-declaration';
let elemDatas = document.querySelector(elemId);
const declarationInHours = elemDatas.dataset.declarationInHours === 'true';

// Création de l'App
const app = createApp(TimesheetDeclaration, {
    "url": elemDatas.dataset.url,
    "url-validation": elemDatas.dataset.urlValidation,
    "url-import": elemDatas.dataset.urlImport,
    "default-year": elemDatas.dataset.defaultYear,
    "default-month": elemDatas.dataset.defaultMonth,
    "declaration-in-hours": declarationInHours
});

const statusValidation = {
    'send-prj': 'Validation projet',
    'send-sci': 'Validation scientifique',
    'send-adm': 'Validation administrative',
    'conflict': 'Conflit',
    'valid': 'Validé'
};

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
        if( declarationInHours ){
            // durée en heure
            let totalminutes = 60 * val;
            let hours = Math.floor(totalminutes / 60);
            let minutes = totalminutes % 60;
            if( minutes < 10 ) minutes = "0" + minutes;
            return `${hours}:${minutes}`;
        } else {
            if( lng === undefined ) return val;
            if( val === 0 ) return 0.0;
            return Math.round(100/lng*val)+"%";
        }
    },
    strReduce(str, length = 20){
        if( str.length > length ){
            return str.substring(0, length-3) + '...';
        }
        return str;
    },
    statusLabel(statusId){
        if( statusValidation.hasOwnProperty(statusId) ){
            return statusValidation[statusId];
        }
        return "Brouillon";
    }
};

// Affichage de l'app
app.mount(elemId);
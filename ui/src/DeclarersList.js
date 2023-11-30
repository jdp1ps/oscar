import { createApp } from 'vue'

import DeclarersList from './views/DeclarersList.vue'
import DurationFilter from "./utils/DurationFilter.js"
import PeriodFilter from "./utils/PeriodFilter.js"

let elemId = '#declarers-list';
let elemDatas = document.querySelector(elemId);

const app = createApp(DeclarersList, {
    "urlRecallDeclarer": elemDatas.dataset.entrypoint
});


app.config.globalProperties.$filters = {
    round1(val){
        return DurationFilter.round1(val);
    },
    percent(value){
        return DurationFilter.percent(value);
    },
    formatDuration(heure){
        return DurationFilter.formatDuration(heure);
    },
    period(periodCode){
        return PeriodFilter.period(periodCode);
    }
};

app.mount(elemId);
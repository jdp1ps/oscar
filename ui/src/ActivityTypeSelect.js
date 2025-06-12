import { createApp } from 'vue';
import ActivityTypeSelect from './components/ActivityTypeSelect.vue';
import ActivityTypeItem from "./components/ActivityTypeItem.vue";

// éléments dans le DOM
let elemId = '#activity-type-select';
let elemDatas = document.querySelector(elemId);

console.log(atob(elemDatas.dataset.typesAvailable));

const app = createApp(ActivityTypeSelect, {
    activityInitalSelected: elemDatas.dataset.activityInitalSelected,
    allowNodeSelection: elemDatas.dataset.allowNodeSelection === "true",
    inputName: elemDatas.dataset.inputName,
    typesAvailable: JSON.parse(atob(elemDatas.dataset.typesAvailable)),
});

app.component('activity-type-item', ActivityTypeItem);

app.mount(elemId);


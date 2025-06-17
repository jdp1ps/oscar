import { createApp } from 'vue';
import ActivityTypeSelect from './components/ActivityTypeSelect.vue';
import ActivityTypeItem from "./components/ActivityTypeItem.vue";

// éléments dans le DOM
let elemId = '#activity-type-select';
let elemDatas = document.querySelector(elemId);

const app = createApp(ActivityTypeSelect, {
    initialSelected: elemDatas.dataset.initialSelected,
    allowNodeSelection: elemDatas.dataset.allowNodeSelection === "true",
    inputName: elemDatas.dataset.inputName,
    typesAvailable: JSON.parse(atob(elemDatas.dataset.typesAvailable)),
});

app.component('activity-type-item', ActivityTypeItem);

app.mount(elemId);


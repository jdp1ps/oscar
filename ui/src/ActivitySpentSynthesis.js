import { createApp } from 'vue'
import AdminTypeDocument from './views/ActivitySpentSynthesis.vue'
import MoneyFilter from "./utils/MoneyFilter";


let elemDatas = document.querySelector('#depenses2');
const app = createApp(AdminTypeDocument, {
    "url": elemDatas.dataset.url,
    "syncurl": elemDatas.dataset.syncurl
});
app.config.globalProperties.$filters = {
   money: function (value){
       return MoneyFilter.money(value);
   }
};
app.mount('#depenses2');
import { createApp } from 'vue'
import SpentLinePFI from './views/SpentLinePFI.vue'
import MoneyFilter from "./utils/MoneyFilter";


let elemDatas = document.querySelector('#depensesdetails');
const app = createApp(SpentLinePFI, {
    "url": elemDatas.dataset.url
});
app.config.globalProperties.$filters = {
   money: function (value){
       return MoneyFilter.money(value);
   }
};
app.mount('#depensesdetails');
import { createApp } from "vue";
import momentFilter from "./utils/MomentFilter.js";
import filesize from "./utils/Filesize.js";
import money from "./utils/MoneyFilter.js";
import Authentification from "./views/administration/Authentification.vue";
import traces from "./utils/Traces.js";

let elemDatas = document.querySelector("#authentification");
const app = createApp(Authentification, {
    "url": elemDatas.dataset.url
});


app.config.globalProperties.$filters = {
    timeAgo(date) {
        return momentFilter.timeAgo(date);
    },
    time(date) {
        return momentFilter.time(date);
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
    log(message) {
        return traces.log(message);
    }
};

app.mount("#authentification");
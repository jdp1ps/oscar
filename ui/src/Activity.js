import {createApp} from "vue";
import momentFilter from "./utils/MomentFilter.js";
import filesize from "./utils/Filesize.js";
import money from "./utils/MoneyFilter.js";
import Activity from "./views/Activity.vue";
import traces from "./utils/Traces.js";

let elemDatas = document.querySelector("#activity");
const app = createApp(Activity, {
    "url": elemDatas.dataset.url,
    "debug-enabled": elemDatas.dataset.debugEnabled,
    "manage": elemDatas.dataset.manage
});

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
    log(message) {
        return traces.log(message);
    }
};

app.mount("#activity");
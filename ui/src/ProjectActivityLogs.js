import { createApp } from "vue";
import momentFilter from "./utils/MomentFilter.js";
import ActivityLog from "./views/ActivityLogs.vue";
import traces from "./utils/Traces.js";

let elemDatas = document.querySelector("#activity-logs");
const app = createApp(ActivityLog, {
    "url": elemDatas.dataset.url
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

app.mount("#activity-logs");
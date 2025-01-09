import { createApp } from "vue";
import TimesheetPersonResume from "./views/TimesheetPersonResume.vue";
import momentFilter from "./utils/MomentFilter.js";
import DurationFilter from "./utils/DurationFilter.js";

let elemDatas = document.querySelector("#timesheet-resume");
const app = createApp(TimesheetPersonResume, {
    "url": elemDatas.dataset.url
});

app.config.globalProperties.$filters = {
    period(period) {
      return momentFilter.period(period);
    },
    formatDuration(heure) {
        return DurationFilter.formatDuration(heure);
    }
    // timeAgo(date) {
    //     return momentFilter.timeAgo(date);
    // },
    // date(date) {
    //     return momentFilter.date(date);
    // },
    // dateFull(date) {
    //     return momentFilter.dateFull(date);
    // },
    // filesize(size) {
    //     return filesize.filesize(size);
    // },
    // money(amount) {
    //     return money.money(amount);
    // },
    // log(message) {
    //     return traces.log(message);
    // }
};

app.mount("#timesheet-resume");
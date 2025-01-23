import { createApp } from "vue";
import Test from "./views/Test.vue";

let elemDatas = document.querySelector("#test");
const app = createApp(Test, {
});

//
// app.config.globalProperties.$filters = {
//     timeAgo(date) {
//         return momentFilter.timeAgo(date);
//     },
//     date(date) {
//         return momentFilter.date(date);
//     },
//     dateFull(date) {
//         return momentFilter.dateFull(date);
//     },
//     filesize(size) {
//         return filesize.filesize(size);
//     },
//     money(amount) {
//         return money.money(amount);
//     },
//     log(message) {
//         return traces.log(message);
//     }
// };

app.mount("#test");
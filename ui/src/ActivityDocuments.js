import { createApp } from "vue"
import momentFilter from "./utils/MomentFilter.js"
import filesize from "./utils/Filesize.js"
import ActivityDocument from "./views/ActivityDocument.vue"

let elemDatas = document.querySelector("#activitydocuments");
const app = createApp(ActivityDocument, {
    "url": elemDatas.dataset.url,
    "urlUploadNewDoc": elemDatas.dataset.urlUploadNewDoc,
    "manage": elemDatas.dataset.manage
})

app.config.globalProperties.$filters = {
    timeAgo(date){ return momentFilter.timeAgo(date) },
    date(date){ return momentFilter.date(date) },
    dateFull(date){ return momentFilter.dateFull(date) },
    filesize(size) { return filesize.filesize(size)},
}

app.mount("#activitydocuments")
import { createApp } from "vue"
import momentFilter from "./utils/MomentFilter.js"
import filesize from "./utils/Filesize.js"
import DocumentsObserved from "./views/DocumentsObserved.vue"

let elemDatas = document.querySelector("#documents");
const app = createApp(DocumentsObserved, {
    "url": elemDatas.dataset.url,
    "manage": elemDatas.dataset.manage
})

app.config.globalProperties.$filters = {
    timeAgo(date){ return momentFilter.timeAgo(date) },
    date(date){ return momentFilter.date(date) },
    dateFull(date){ return momentFilter.dateFull(date) },
    filesize(size) { return filesize.filesize(size)},
}

app.mount("#documents")
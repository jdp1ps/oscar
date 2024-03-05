import { createApp } from 'vue'

import ActivityDocument from './views/ActivityDocument.vue'

let elemDatas = document.querySelector('#activitydocuments');
const app = createApp(ActivityDocument, {
    "url": elemDatas.dataset.url,
    "urlUploadNewDoc": elemDatas.dataset.urlUploadNewDoc,
    "manage": elemDatas.dataset.manage
})

/****
 data-url-upload-new-doc="<?= $this->url('contractdocument/upload', ['idactivity' => $entity->getId()]) ?>"
 data-url-url-documentd-type="<?= $this->url('contractdocument/document-change-type') ?>"
 data-url="<?= $this->url('contract/documents-json',['id' => $entity->getId()]) ?>">doc</div>
 */
app.mount('#activitydocuments')
import{o,c as a,a as e,f as l,t,F as d,r as p,n as h,e as c,d as g,p as C,q as w,g as x,j as S,x as P,s as F}from"../vendor.js";import{m as v,f as N}from"../vendor2.js";import{_ as y}from"../vendor3.js";const A={props:{editable:{default:!1},displayActivity:{default:!1},documents:{default:[]}},data(){return{processDetails:null}},methods:{urlShow(n=null){if(console.log(n),n)document.location=n;else return!1},handlerProcessDetailsOn(n){this.processDetails=n},handlerProcessDetailsOff(){this.processDetails=null}}},r=n=>(C("data-v-6c79e186"),n=n(),w(),n),O={key:0,class:"overlay"},V={class:"overlay-content",style:{"max-width":"50%"}},$=r(()=>e("small",null,[e("i",{class:"icon-edit"}),l(" Procédure")],-1)),z=r(()=>e("br",null,null,-1)),B=r(()=>e("br",null,null,-1)),E={class:"signature-status-101"},I={class:"status"},T={class:"metas"},j={class:"meta"},q={class:"meta"},L={class:"fullname"},U={class:"email"},M={class:"status"},R={class:"status-text"},X=r(()=>e("strong",null,"Observateurs : ",-1)),G={class:"observer-inline"},H={class:"buttons-bar"},J=r(()=>e("i",{class:"icon-cancel-outline"},null,-1)),K={class:"documents-content"},Q={class:"tab-content"},W={class:"card-title"},Y={key:0,class:"picto icon-anchor-outline"},Z={class:"text-light"},ee=["title"],se=r(()=>e("i",{class:"icon-briefcase"},null,-1)),te=r(()=>e("i",{class:"icon-calendar"},null,-1)),le=r(()=>e("i",{class:"icon-calendar"},null,-1)),ne=r(()=>e("i",{class:"icon-calendar"},null,-1)),oe=r(()=>e("i",{class:"icon-user"},null,-1)),ae={key:0},ie=r(()=>e("i",{class:"icon-lock"},null,-1)),re={class:"cartouche"},ce={class:"card-content"},ue=r(()=>e("i",{class:"icon-hammer"},null,-1)),_e=["onClick"],de=["onClick"],he=r(()=>e("i",{class:"icon-cw-outline"},null,-1)),pe={key:1},me=["onClick"],fe=r(()=>e("i",{class:"icon-cube"},null,-1)),ve={key:0},ge=r(()=>e("i",{class:"icon-cubes"},null,-1)),be={key:2},ye=r(()=>e("div",{class:"exploder"}," Versions précédentes : ",-1)),ke={class:"subdoc text-highlight"},De={key:0},Ce=["href"],we=r(()=>e("i",{class:"icon-download-outline"},null,-1)),xe={class:"text-right show-over"},Se=["href"],Pe=r(()=>e("i",{class:"icon-link-ext"},null,-1)),Fe=["onClick"],Ne=r(()=>e("i",{class:"icon-bank"},null,-1)),Ae=["href"],Oe=r(()=>e("i",{class:"icon-upload-outline"},null,-1)),Ve=["onClick"],$e=r(()=>e("i",{class:"icon-download-outline"},null,-1)),ze=["onClick"],Be=r(()=>e("i",{class:"icon-trash"},null,-1)),Ee=["onClick"],Ie=r(()=>e("i",{class:"icon-pencil"},null,-1));function Te(n,m,f,D,u,_){return o(),a(d,null,[u.processDetails?(o(),a("div",O,[e("div",V,[e("h2",null,[$,l(),z,e("strong",null,t(u.processDetails.label),1),l(),B,e("span",E,[l(t(u.processDetails.status_text)+" - ",1),e("em",null,"étape "+t(u.processDetails.current_step)+" / "+t(u.processDetails.total_steps),1)]),e("span",{class:"overlay-closer",onClick:m[0]||(m[0]=(...s)=>_.handlerProcessDetailsOff&&_.handlerProcessDetailsOff(...s))},"X")]),(o(!0),a(d,null,p(u.processDetails.steps,s=>(o(),a("section",{class:h(["signature","signature-status-"+s.status])},[e("h4",null,[e("small",null,"étape "+t(s.order)+" : ",1),e("strong",null,t(s.label),1),e("span",I," ("+t(s.status_text)+")",1)]),e("ul",T,[e("li",j,[l("Parapheur "),e("strong",null,t(s.letterfile),1)]),e("li",q,[l("Niveau "),e("strong",null,t(s.level),1)])]),(o(!0),a(d,null,p(s.recipients,i=>(o(),a("article",{class:h(["recipient","signature-status-"+i.status])},[e("strong",L,t(i.fullname),1),e("em",U,t(i.email),1),e("small",null,t(n.$filters.dateFull(i.dateFinished)),1),e("span",M,[e("span",R,t(i.status_text),1)])],2))),256)),X,(o(!0),a(d,null,p(s.observers,i=>(o(),a("span",G,[e("small",null,t(i.firstname),1),l(),e("span",null,t(i.lastname),1)]))),256))],2))),256)),e("div",H,[e("button",{class:"btn btn-default",onClick:m[1]||(m[1]=(...s)=>_.handlerProcessDetailsOff&&_.handlerProcessDetailsOff(...s))},[J,l(" Fermer ")])])])])):c("",!0),e("section",K,[e("div",Q,[(o(!0),a(d,null,p(f.documents,s=>(o(),a("article",{class:h(["card xs",{"private-document":s.private}]),key:s.id},[e("div",W,[s.location=="link"?(o(),a("i",Y)):(o(),a("i",{key:1,class:h(["picto icon-doc","doc"+s.extension])},null,2)),e("small",Z,t(s.category.label)+" ~ ",1),e("strong",null,t(s.fileName),1),s.location!="url"?(o(),a("small",{key:2,class:"text-light",title:s.fileSize+" octet(s)"},"  Version "+t(s.version),9,ee)):c("",!0)]),e("small",null,[se,l(" Taille "),e("strong",null,t(n.$filters.filesize(s.fileSize)),1),te,l(" Envoyé "),e("strong",null,t(n.$filters.timeAgo(s.dateSend)),1),le,l(" Déposé "),e("strong",null,t(n.$filters.dateFull(s.dateDeposit)),1),ne,l(" Uploadé "),e("strong",null,t(n.$filters.dateFull(s.dateUpload)),1),oe,l(" par "),e("strong",null,t(s.uploader.displayname),1)]),e("p",null,t(s.information),1),s.private?(o(),a("section",ae,[ie,l(" Ce document est privé, accessible par : "),(o(!0),a(d,null,p(s.persons,i=>(o(),a("span",re,t(i.personName),1))),256))])):c("",!0),e("div",ce,[s.process?(o(),a("section",{key:0,class:h(["alert",{"alert-success":s.process.status==201,"alert-danger":s.process.status>=400,"alert-info":s.process.status<200}])},[ue,l(" Procédure de signature "),e("strong",null,t(s.process.label),1),l(" ("),e("em",null,t(s.process.status_text),1),e("span",null," - étape "+t(s.process.current_step)+" / "+t(s.process.total_steps),1),l(") "),e("button",{class:"btn btn-xs btn-info",onClick:i=>_.handlerProcessDetailsOn(s.process)}," Détails ",8,_e),s.manage_process?(o(),a("button",{key:0,class:"btn btn-default btn-xs",onClick:i=>n.handlerProcessReload(s)},[he,l(" Actualiser ")],8,de)):c("",!0)],2)):c("",!0),f.displayActivity?(o(),a("section",pe,[l(" Activité : "),e("span",{class:h({link:s.activity.url_show}),onClick:i=>_.urlShow(s.activity.url_show)},[e("strong",null,[fe,l(" / "+t(s.activity.num),1)]),l("  "),e("em",null,t(s.activity.label),1),s.activity.project_id?(o(),a("small",ve,[l(" ("),ge,l(t(s.activity.project_acronym)+") ",1)])):c("",!0),l(" "+t(s.activity),1)],10,me)])):c("",!0),s.versions&&s.versions.length?(o(),a("div",be,[ye,(o(!0),a(d,null,p(s.versions,i=>(o(),a("article",ke,[e("i",{class:h(["picto icon-doc","doc"+i.extension])},null,2),e("strong",null,t(i.fileName),1),l(" version "),e("em",null,t(i.version),1),l(", téléchargé le "),e("time",null,t(i.dateUpload|n.dateFullSort),1),i.uploader?(o(),a("span",De,[l(" par "),e("strong",null,t(i.uploader.displayname),1)])):c("",!0),e("a",{href:i.urlDownload},[we,l(" Télécharger cette version ")],8,Ce)]))),256))])):c("",!0),e("nav",xe,[s.location=="url"?(o(),a("a",{key:0,class:"btn btn-default btn-xs",href:s.basename,target:"_blank"},[Pe,l(" Accéder au lien ")],8,Se)):c("",!0),s.process_triggerable?(o(),a("a",{key:1,class:"btn btn-default btn-xs",href:"#",onClick:i=>n.handlerSignDocument(s)},[Ne,l(" Signer ce document ")],8,Fe)):c("",!0),s.urlDownload&&s.location!="url"?(o(),a("a",{key:2,class:"btn btn-default btn-xs",href:s.urlDownload},[Oe,l(" Télécharger ")],8,Ae)):c("",!0),s.allowNewVersion?(o(),a("button",{key:3,onClick:i=>n.handlerNewVersion(s),class:"btn btn-default btn-xs"},[$e,l(" Nouvelle Version ")],8,Ve)):c("",!0),s.allowDelete?(o(),a("a",{key:4,class:"btn btn-default btn-xs",onClick:g(i=>n.deleteDocument(s),["prevent"])},[Be,l(" Supprimer ")],8,ze)):c("",!0),s.allowEdit?(o(),a("a",{key:5,class:"btn btn-xs btn-default",href:"#",onClick:g(i=>n.handlerEdit(s),["prevent"])},[Ie,l(" Modifier ")],8,Ee)):c("",!0)])])],2))),128))])])],64)}const je=y(A,[["render",Te],["__scopeId","data-v-6c79e186"]]),qe={components:{DocumentsList:je},props:{manage:{default:!1},url:{required:!0}},data(){return{documents:[]}},methods:{fetch(){x.get(this.url).then(n=>{this.documents=n.data.documents})}},mounted(){this.fetch()}};function Le(n,m,f,D,u,_){const s=S("documents-list");return o(),P(s,{documents:u.documents,"display-activity":!0},null,8,["documents"])}const Ue=y(qe,[["render",Le]]);let b=document.querySelector("#documents");const k=F(Ue,{url:b.dataset.url,manage:b.dataset.manage});k.config.globalProperties.$filters={timeAgo(n){return v.timeAgo(n)},date(n){return v.date(n)},dateFull(n){return v.dateFull(n)},filesize(n){return N.filesize(n)}};k.mount("#documents");
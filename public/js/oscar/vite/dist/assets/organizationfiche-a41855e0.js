import{g as c,c as n,a as e,t as s,f as l,e as _,F as u,o as t,s as h}from"../vendor.js";import{_ as f}from"../vendor2.js";c.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";const m={props:{entrypoint:{required:!0}},data(){return{infos:null,loading:"Initialisation"}},methods:{loadInfos(){this.loading="Chargement des données",c.get(this.entrypoint+"?a=infos").then(i=>{this.infos=i.data.infos}).finally(i=>{this.loading=""})}}},p={key:0,class:"loader"},g={key:1},y=e("small",{class:"parents"}," PARENTS ICI ",-1),v={class:"type"},C={class:"fullname"},I={title:"Code interne"},k={class:"row"},S=e("div",{class:"col-md-8"},[e("h3",null,"Sous-Structures"),l(" Structures / Pesonnel "),e("h3",null,"Personnel")],-1),x={class:"col-md-4"},F=e("h3",null,"Informations",-1),q={key:0};function z(i,a,D,E,o,r){return t(),n(u,null,[o.loading?(t(),n("div",p," Chargement ")):(t(),n("header",g,[e("h2",null,[y,e("small",v," Type: "+s(o.infos.type),1),e("div",C,[e("code",I,s(o.infos.code),1),e("strong",null,s(o.infos.shortname),1),e("em",null,s(o.infos.longname),1)])]),l(" Données de la structures ")])),e("section",k,[S,e("div",x,[F,o.infos?(t(),n("pre",q,"        "+s(o.infos)+`
      `,1)):_("",!0)])]),l(" FICHE "),e("footer",null,[e("button",{onClick:a[0]||(a[0]=(...d)=>r.loadInfos&&r.loadInfos(...d))}," Charger ")])],64)}const N=f(m,[["render",z]]);let w=document.querySelector("#organization-view");const B=h(N,{entrypoint:w.dataset.entrypoint});B.mount("#organization-view");
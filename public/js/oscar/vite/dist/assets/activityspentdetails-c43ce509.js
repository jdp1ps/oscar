import{o as l,c as i,b as t,F as m,f,t as n,d as u,a as v,r as D,g as y,u as C,T as x,w,v as E,e as _,k as B,q as I}from"../vendor.js";import{_ as k}from"../vendor4.js";import{M as N}from"../vendor5.js";const S={props:{lines:{required:!0},total:{required:!0}},computed:{total_engage(){let e=0;return this.lines&&Object.keys(this.lines).forEach(d=>{e+=this.lines[d].montant_engage}),e},total_effectue(){let e=0;return this.lines&&Object.keys(this.lines).forEach(d=>{e+=this.lines[d].montant_effectue}),e}},methods:{handlerEditCompte(e){this.$emit("editcompte",e)}}},F={key:0,class:"list table table-condensed table-bordered table-condensed card"},O=t("thead",null,[t("tr",null,[t("th",null,"N°"),t("th",null,"Ligne(s)"),t("th",null,"Statut"),t("th",null,"Type"),t("th",null,"Description"),t("th",{style:{width:"8%"}},"Montant engagé"),t("th",{style:{width:"8%"}},"Montant effectué"),t("th",{style:{width:"8%"}},"Compte Budgetaire"),t("th",{style:{width:"8%"}},"Compte"),t("th",{style:{width:"8%"}},"Date Comptable"),t("th",{style:{width:"8%"}},"Date paiement"),t("th",{style:{width:"8%"}},"Année")])],-1),P=["onClick"],A={class:"cartouche xs",title:"N°SIFAC"},j={key:0},L=t("i",{class:"icon-calculator"},null,-1),R={key:1},V=t("i",{class:"icon-bank"},null,-1),T={key:2},q=t("i",{class:"icon-attention-1"},null,-1),z={style:{"text-align":"right"}},G={style:{"text-align":"right"}},J=["onClick"],H=t("i",{class:"icon-edit"},null,-1),U={style:{"font-weight":"bold","font-size":"1.2em"}},K=t("td",{colspan:"5",style:{"text-align":"right"}},"Total :",-1),Q={style:{"text-align":"right"}},W={style:{"text-align":"right"}},X=t("td",{colspan:"2"}," ",-1),Y={key:1,class:"alert alert-info"};function Z(e,d,h,r,s,o){return l(),i("div",null,[Object.keys(h.lines).length?(l(),i("table",F,[O,t("tbody",null,[(l(!0),i(m,null,f(h.lines,c=>(l(),i("tr",null,[t("td",null,n(c.numpiece),1),t("td",null,[t("button",{onClick:a=>e.$emit("detailsline",c),class:"btn btn-default xs"},n(c.details.length),9,P),(l(!0),i(m,null,f(c.numSifac,a=>(l(),i("span",A,n(a),1))),256))]),t("td",null,[c.btart=="0250"?(l(),i("span",j,[L,u(" Payé ")])):c.btart=="0100"?(l(),i("span",R,[V,u(" Engagé ")])):(l(),i("span",T,[q,u(" Inconnu ")])),u(" "+n(c.btart),1)]),t("td",null,[t("small",null,n(c.types?c.types.join(","):""),1)]),t("td",null,[t("small",null,n(c.text.join(", ")),1)]),t("td",z,[t("strong",null,n(e.$filters.money(c.montant_engage)),1)]),t("td",G,[t("strong",null,n(e.$filters.money(c.montant_effectue)),1)]),t("td",null,n(c.compteBudgetaires.join(", ")),1),t("td",null,[(l(!0),i(m,null,f(c.comptes,a=>(l(),i("span",{class:"cartouche default xs",style:{"white-space":"nowrap"},onClick:p=>o.handlerEditCompte(a)},[u(n(a)+" ",1),H],8,J))),256))]),t("td",null,n(c.dateComptable),1),t("td",null,n(c.datePaiement),1),t("td",null,n(c.annee),1)]))),256))]),t("tfoot",null,[t("tr",U,[K,t("td",Q,n(e.$filters.money(o.total_engage)),1),t("td",W,n(e.$filters.money(o.total_effectue)),1),X])])])):(l(),i("div",Y," Aucune entrée "))])}const $=k(S,[["render",Z]]),tt={props:["url"],components:{SpentLinePFIGrouped:$},data(){return{state:"masse",error:null,pendingMsg:"",spentlines:null,masses:{},details:null,displayIgnored:!0,editCompte:null,informations:null,manageRecettes:!0,url_activity:null,url_sync:null,url_download:null,url_spentaffectation:null}},computed:{totalDepenses(){let e=0;for(let d in this.spentlines.synthesis)d!="0"&&d!="1"&&(e+=this.spentlines.synthesis[d].total);return e},byMasse(){let e={datas:{"N.B":{},recettes:{},ignorés:{}},totaux:{"N.B":0,recettes:0,ignorés:0}};for(let d in this.masses)e.datas[d]={},e.totaux[d]=0;if(this.spentlines)for(let d in this.spentlines.spents){let h=this.spentlines.spents[d],r=h.masse,s=h.btart;r=="1"&&(r="recettes"),r=="0"&&(r="ignorés");let o=h.numPiece;e.datas.hasOwnProperty(r)||(r="N.B"),e.datas[r].hasOwnProperty(o)||(e.datas[r][o]={ids:[],numpiece:o,numSifac:[],text:[],types:[],montant:0,montant_engage:0,montant_effectue:0,btart:s,compteBudgetaires:[],comptes:[],masse:[],dateComptable:h.dateComptable,datePaiement:h.datePaiement,annee:h.dateAnneeExercice,refPiece:h.refPiece,details:[]}),e.datas[r][o].details.push(h);let c=h.texteFacture,a=h.designation,p=h.type,g=h.compteGeneral,b=h.compteBudgetaire;e.datas[r][o].numSifac.indexOf(h.numSifac)==-1&&e.datas[r][o].numSifac.push(h.numSifac),e.datas[r][o].montant+=h.montant,e.datas[r][o].montant_effectue+=h.montant_effectue,e.datas[r][o].montant_engage+=h.montant_engage,c&&e.datas[r][o].text.indexOf(c)<0&&e.datas[r][o].text.push(c),a&&e.datas[r][o].text.indexOf(a)<0&&e.datas[r][o].text.push(a),p&&e.datas[r][o].types.indexOf(p)<0&&e.datas[r][o].types.push(p),g&&e.datas[r][o].comptes.indexOf(g)<0&&e.datas[r][o].comptes.push(g),b&&e.datas[r][o].compteBudgetaires.indexOf(b)<0&&e.datas[r][o].compteBudgetaires.push(b)}return e}},methods:{handlerEditCompte(e){this.editCompte=JSON.parse(JSON.stringify(this.spentlines.comptes[e]))},handlerDetailsLine(e){this.details=e},handlerAffectationCompte(e){let d={};d[e.codeFull]=e.annexe,this.editCompte=null,this.pendingMsg="Modification de la masse pour "+e.codeFull;let h=new FormData;h.append("affectation",JSON.stringify(d)),v.post(this.url_spentaffectation,h).then(r=>{this.editCompte=null,this.fetch()},r=>{r.status==403?this.error="Vous n'avez pas l'autorisation d'accès à ces informations.":this.error=r.data,this.pendingMsg=""})},fetch(){this.pendingMsg="Chargement des dépense",v.get(this.url).then(e=>{this.masses=e.data.spents.masses,this.spentlines=e.data.spents,this.informations=e.data.spents.informations,this.url_sync=e.data.spents.url_sync,this.url_activity=e.data.spents.url_activity,this.url_spentaffectation=e.data.spents.url_spentaffectation,this.url_download=e.data.spents.url_download},e=>{e.status==403?this.error="Vous n'avez pas l'autorisation d'accès à ces informations.":this.error="Impossible de charger les dépenses pour ce PFI : "+e.data}).then(e=>{this.pendingMsg=""})}},mounted(){this.fetch()}},et={class:"spentlines"},st={key:0,class:"error overlay"},nt={class:"overlay-content"},lt=t("i",{class:"icon-warning-empty"},null,-1),it=t("br",null,null,-1),ot=t("i",{class:"icon-cancel-circled"},null,-1),at={key:0,class:"pending overlay"},rt={class:"overlay-content"},dt=t("i",{class:"icon-spinner animate-spin"},null,-1),ct={key:0,class:"overlay"},ut={class:"overlay-content"},ht=t("i",{class:"icon-zoom-in-outline"},null,-1),_t=t("hr",null,null,-1),pt=t("option",{value:"0"},"Ignoré",-1),mt=t("option",{value:"1"},"Recette",-1),ft=["value"],yt=t("i",{class:"icon-cancel-circled-outline"},null,-1),gt=t("i",{class:"icon-valid"},null,-1),bt={key:1,class:"overlay"},vt={class:"overlay-content"},Ct=t("h3",null,[t("i",{class:"icon-zoom-in-outline"}),u("Détails des entrées comptables")],-1),xt={class:"list table table-condensed table-bordered table-condensed card"},kt=t("thead",null,[t("tr",null,[t("th",null,"ID"),t("th",null,"N°SIFAC"),t("th",null,"Btart"),t("th",null,"Description"),t("th",null,"Montant engagé"),t("th",null,"Montant effectué"),t("th",null,"Compte Budgetaire"),t("th",null,"Centre de profit"),t("th",null,"Compte général"),t("th",null,"Masse"),t("th",null,"Date comptable"),t("th",null,"Date paiement"),t("th",null,"Année")])],-1),Mt={class:"text-small"},Dt={style:{"text-align":"right"}},wt={style:{"text-align":"right"}},Et={class:"container-fluid"},Bt={class:"row"},It={class:"col-md-3"},Nt=t("h3",null,[t("i",{class:"icon-help-circled"}),u(" Informations ")],-1),St={key:0,class:"card"},Ft={key:0,class:"table table-condensed card synthesis"},Ot=t("th",null,[t("small",null,"PFI")],-1),Pt={style:{"text-align":"right"}},At=t("th",null,[t("small",null,"N°OSCAR")],-1),jt={style:{"text-align":"right"}},Lt=t("th",null,[t("small",null,"Montant")],-1),Rt={style:{"text-align":"right"}},Vt=t("th",null,[t("small",null,"Projet")],-1),Tt={style:{"text-align":"right"}},qt=t("br",null,null,-1),zt=t("th",null,[t("small",null,"Activité")],-1),Gt={style:{"text-align":"right"}},Jt=["href"],Ht=t("i",{class:"icon-cube"},null,-1),Ut=["action"],Kt=t("input",{type:"hidden",name:"action",value:"update"},null,-1),Qt=t("button",{type:"submit",class:"btn btn-primary btn-xs"},[t("i",{class:"icon-signal"}),u(" Mettre à jour les données depuis SIFAC ")],-1),Wt=[Kt,Qt],Xt=["href"],Yt=t("i",{class:"icon-download"},null,-1),Zt=t("h3",null,[t("i",{class:"icon-calculator"}),u("Dépenses")],-1),$t={key:1,class:"table table-condensed card synthesis"},te=t("thead",null,[t("tr",null,[t("th",null,"Masse"),t("th",{style:{"text-align":"right"}},"Engagé"),t("th",{style:{"text-align":"right"}},"Effectué")])],-1),ee=["href"],se={style:{"text-align":"right"}},ne={style:{"text-align":"right"}},le={class:"total"},ie=t("th",null,"Total",-1),oe={style:{"text-align":"right"}},ae={style:{"text-align":"right"}},re={key:0},de=t("small",null,[t("i",{class:"icon-attention"}),u(" Hors-masse")],-1),ce={href:"#repport-nb",class:"label label-info"},ue={style:{"text-align":"right"}},he={style:{"text-align":"right"}},_e={key:2},pe=t("h3",null,[t("i",{class:"icon-calculator"}),u("Recettes")],-1),me={key:0,class:"table table-condensed card synthesis"},fe={class:"label label-info xs",href:"#repport-1"},ye={style:{"text-align":"right"}},ge={key:3},be={key:0},ve=t("i",{class:"icon-eye-off"},null,-1),Ce={key:1},xe=t("i",{class:"icon-eye"},null,-1),ke={key:0,class:"table table-condensed card synthesis"},Me={class:"label label-info",href:"#repport-0"},De={style:{"text-align":"right"}},we={class:"col-md-9",style:{height:"80vh","overflow-y":"scroll"}},Ee={key:0},Be=["id"],Ie={key:0},Ne=t("h3",{id:"repport-nb"},"Hors-masse",-1),Se=t("div",{class:"alert alert-warning"},[t("i",{class:"icon-attention"}),u(" Les comptes des entrées suivantes ne sont pas qualifié. ")],-1),Fe={key:1},Oe=t("h3",{id:"repport-1"},"Recettes",-1),Pe={key:2},Ae=t("h3",{id:"repport-0"},"Ignorés",-1);function je(e,d,h,r,s,o){const c=D("spent-line-p-f-i-grouped");return l(),i("section",et,[y(x,{name:"fade"},{default:C(()=>[s.error?(l(),i("div",st,[t("div",nt,[lt,u(" "+n(s.error)+" ",1),it,t("a",{href:"#",onClick:d[0]||(d[0]=a=>s.error=null),class:"btn btn-sm btn-default btn-xs"},[ot,u(" Fermer")])])])):_("",!0)]),_:1}),y(x,{name:"fade"},{default:C(()=>[s.pendingMsg?(l(),i("div",at,[t("div",rt,[dt,u(" "+n(s.pendingMsg),1)])])):_("",!0)]),_:1}),s.editCompte?(l(),i("div",ct,[t("div",ut,[t("h3",null,[ht,u("Modification de la masse : "+n(s.editCompte.code)+" - "+n(s.editCompte.label),1)]),_t,w(t("select",{name:"","onUpdate:modelValue":d[1]||(d[1]=a=>s.editCompte.annexe=a)},[pt,mt,(l(!0),i(m,null,f(s.spentlines.masses,(a,p)=>(l(),i("option",{value:p},n(a),9,ft))),256))],512),[[E,s.editCompte.annexe]]),t("button",{class:"btn btn-danger",onClick:d[2]||(d[2]=a=>s.editCompte=null)},[yt,u("Annuler ")]),t("button",{class:"btn btn-success",onClick:d[3]||(d[3]=a=>o.handlerAffectationCompte(s.editCompte))},[gt,u("Valider ")])])])):_("",!0),s.details?(l(),i("div",bt,[t("div",vt,[Ct,t("button",{class:"btn btn-default",onClick:d[4]||(d[4]=a=>s.details=null)},"Fermer"),t("table",xt,[kt,t("tbody",null,[(l(!0),i(m,null,f(s.details.details,a=>(l(),i("tr",Mt,[t("td",null,n(a.syncid),1),t("td",null,n(a.numSifac),1),t("td",null,n(a.btart),1),t("td",null,n(a.texteFacture|a.designation),1),t("td",Dt,n(e.$filters.money(a.montant_engage)),1),t("td",wt,n(e.$filters.money(a.montant_effectue)),1),t("td",null,n(a.compteBudgetaire),1),t("td",null,n(a.centreFinancier),1),t("td",null,[t("strong",null,n(a.compteGeneral),1),u(" : "+n(a.type),1)]),t("td",null,[t("strong",null,n(a.masse),1)]),t("td",null,n(a.dateComptable),1),t("td",null,n(a.datePaiement),1),t("td",null,n(a.dateAnneeExercice),1)]))),256))])])])])):_("",!0),t("div",Et,[t("div",Bt,[t("div",It,[Nt,s.informations?(l(),i("div",St,[s.spentlines?(l(),i("table",Ft,[t("tbody",null,[t("tr",null,[Ot,t("td",Pt,n(s.informations.PFI),1)]),t("tr",null,[At,t("td",jt,n(s.informations.numOscar),1)]),t("tr",null,[Lt,t("td",Rt,n(e.$filters.money(s.informations.amount)),1)]),t("tr",null,[Vt,t("td",Tt,[t("strong",null,n(s.informations.projectacronym),1),qt,t("small",null,n(s.informations.project),1)])]),t("tr",null,[zt,t("td",Gt,[t("small",null,n(s.informations.label),1)])])])])):_("",!0),s.url_activity?(l(),i("a",{key:1,href:s.url_activity,class:"btn btn-default btn-xs"},[Ht,u(" Revenir à l'activité")],8,Jt)):_("",!0),s.url_sync?(l(),i("form",{key:2,action:s.url_sync,method:"post",class:"form-inline"},Wt,8,Ut)):_("",!0),e.urlDownload?(l(),i("a",{key:3,href:e.urlDownload,class:"btn btn-default btn-xs"},[Yt,u(" Télécharger les données (Excel)")],8,Xt)):_("",!0)])):_("",!0),Zt,s.spentlines?(l(),i("table",$t,[te,t("tbody",null,[(l(!0),i(m,null,f(s.spentlines.masses,(a,p)=>(l(),i("tr",null,[t("th",null,[t("small",null,n(a),1),t("a",{class:"label label-info xs",href:"#repport-"+p},n(s.spentlines.synthesis[p].nbr_effectue)+" / "+n(s.spentlines.synthesis[p].nbr_engage),9,ee)]),t("td",se,n(e.$filters.money(s.spentlines.synthesis[p].total_engage)),1),t("td",ne,n(e.$filters.money(s.spentlines.synthesis[p].total_effectue)),1)]))),256))]),t("tbody",null,[t("tr",le,[ie,t("td",oe,n(e.$filters.money(s.spentlines.synthesis.totaux.engage)),1),t("td",ae,n(e.$filters.money(s.spentlines.synthesis.totaux.effectue)),1)])]),t("tbody",null,[s.spentlines.synthesis["N.B"].total!=0?(l(),i("tr",re,[t("th",null,[de,t("a",ce,n(s.spentlines.synthesis["N.B"].nbr),1)]),t("td",ue,n(e.$filters.money(s.spentlines.synthesis["N.B"].total_engage)),1),t("td",he,n(e.$filters.money(s.spentlines.synthesis["N.B"].total_effectue)),1)])):_("",!0)])])):_("",!0),s.manageRecettes?(l(),i("div",_e,[pe,s.spentlines?(l(),i("table",me,[t("tbody",null,[t("tr",null,[t("th",null,[u("Recette "),t("a",fe,n(s.spentlines.synthesis[1].nbr),1)]),t("td",ye,n(e.$filters.money(s.spentlines.synthesis[1].total)),1)])])])):_("",!0)])):_("",!0),e.manageIgnored&&s.spentlines.synthesis[0].total!=0?(l(),i("div",ge,[t("a",{href:"#",onClick:d[5]||(d[5]=B(a=>s.displayIgnored=!s.displayIgnored,["prevent"]))},[s.displayIgnored?(l(),i("span",be,[ve,u(" Cacher")])):(l(),i("span",Ce,[xe,u(" Montrer")])),u(" les données ignorées ")]),s.spentlines&&s.displayIgnored?(l(),i("table",ke,[t("tbody",null,[t("tr",null,[t("th",null,[u(" Ignorées "),t("a",Me,n(s.spentlines.synthesis[0].nbr),1)]),t("td",De,n(e.$filters.money(s.spentlines.synthesis[0].total)),1)])])])):_("",!0)])):_("",!0)]),t("div",we,[s.spentlines!=null?(l(),i("div",Ee,[(l(!0),i(m,null,f(s.masses,(a,p)=>(l(),i("div",null,[t("h3",{id:"repport-"+p},n(a),9,Be),y(c,{lines:o.byMasse.datas[p],total:s.spentlines.synthesis[p].total,onEditcompte:o.handlerEditCompte,onDetailsline:o.handlerDetailsLine},null,8,["lines","total","onEditcompte","onDetailsline"])]))),256)),Object.keys(o.byMasse.datas["N.B"]).length>0?(l(),i("div",Ie,[Ne,Se,y(c,{lines:o.byMasse.datas["N.B"],total:s.spentlines.synthesis["N.B"].total,onEditcompte:o.handlerEditCompte,onDetailsline:o.handlerDetailsLine},null,8,["lines","total","onEditcompte","onDetailsline"])])):_("",!0),s.manageRecettes&&Object.keys(o.byMasse.datas.recettes).length>0?(l(),i("div",Fe,[Oe,y(c,{lines:o.byMasse.datas.recettes,total:s.spentlines.synthesis[1].total,onEditcompte:o.handlerEditCompte,onDetailsline:o.handlerDetailsLine},null,8,["lines","total","onEditcompte","onDetailsline"])])):_("",!0),e.manageIgnored&&Object.keys(o.byMasse.datas.ignorés).length>0?(l(),i("div",Pe,[Ae,y(c,{lines:o.byMasse.datas.ignorés,total:s.spentlines.synthesis[0].total,onEditcompte:o.handlerEditCompte,onDetailsline:o.handlerDetailsLine},null,8,["lines","total","onEditcompte","onDetailsline"])])):_("",!0)])):_("",!0)])])])])}const Le=k(tt,[["render",je]]);let Re=document.querySelector("#depensesdetails");const M=I(Le,{url:Re.dataset.url});M.config.globalProperties.$filters={money:function(e){return N.money(e)}};M.mount("#depensesdetails");
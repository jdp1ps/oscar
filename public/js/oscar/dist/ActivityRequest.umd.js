(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ActivityRequest"] = factory();
	else
		root["ActivityRequest"] = factory();
})((typeof self !== 'undefined' ? self : this), function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fb15");
/******/ })
/************************************************************************/
/******/ ({

/***/ "fb15":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityRequest.vue?vue&type=template&id=32491513&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.loading),expression:"loading"}],staticClass:"alert alert-info"},[_vm._v(_vm._s(_vm.loading))]),_c('transition',{attrs:{"name":"fade"}},[(_vm.formData)?_c('div',{staticClass:"overlay",staticStyle:{"overflow-y":"scroll","padding":"1em"}},[_c('form',{staticStyle:{"min-width":"75vw","max-width":"80%","padding-top":"2em"},attrs:{"action":"?","enctype":"multipart/form-data","method":"post","name":"save"},on:{"submit":function($event){$event.preventDefault();return _vm.handlerSave($event)}}},[(_vm.formData.id)?_c('h1',[_vm._v("Modification de la demande")]):_c('h1',[_vm._v("Nouvelle demande")]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-6"},[_c('strong',[_vm._v("Demandeur : ")]),_c('br'),_c('span',{staticClass:"cartouche"},[_vm._v(" "+_vm._s(_vm.demandeur)+" "),_c('span',{staticClass:"addon"},[_vm._v("Demandeur")])])]),_c('div',{staticClass:"col-md-6"},[_c('strong',[_vm._v("Organisme référent : ")]),_c('br'),(_vm.organisations.length == 0)?_c('p',{staticClass:"alert alert-info"},[_vm._v(" Vous n'êtes associé à aucun organisme. ")]):_c('select',{staticClass:"form-control",attrs:{"name":"organisation_id"}},_vm._l((_vm.organisations),function(org,id){return _c('option',{domProps:{"value":id}},[_vm._v(_vm._s(org))])}),0)])]),_c('hr',{staticClass:"separator"}),_c('div',[_c('label',{attrs:{"for":"form_label"}},[_vm._v("Intitulé")]),(_vm.formData.id)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.formData.id),expression:"formData.id"}],attrs:{"type":"hidden","name":"id"},domProps:{"value":(_vm.formData.id)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.formData, "id", $event.target.value)}}}):_vm._e(),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.formData.label),expression:"formData.label"}],staticClass:"form-control input-lg",attrs:{"type":"text","id":"form_label","name":"label"},domProps:{"value":(_vm.formData.label)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.formData, "label", $event.target.value)}}})]),_c('hr',{staticClass:"separator"}),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-4"},[_c('strong',[_vm._v("Début prévu")]),_c('p',{staticClass:"help"},[_vm._v("Vous pouvez laisser ce champ vide.")]),_c('datepicker',{attrs:{"moment":_vm.moment,"value":_vm.formData.dateStart},on:{"change":function($event){_vm.formData.dateStart = $event}}})],1),_c('div',{staticClass:"col-md-4"},[_c('strong',[_vm._v("Fin prévue")]),_c('p',{staticClass:"help"},[_vm._v("Vous pouvez laisser ce champ vide.")]),_c('datepicker',{attrs:{"moment":_vm.moment,"value":_vm.formData.dateEnd},on:{"change":function($event){_vm.formData.dateEnd = $event}}})],1),_c('div',{staticClass:"col-md-4"},[_c('label',{attrs:{"for":"form_amount"}},[_vm._v("Montant souhaité")]),_c('p',{staticClass:"help"},[_vm._v("Vous pouvez laisser ce champ vide.")]),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.formData.amount),expression:"formData.amount"}],staticClass:"form-control",attrs:{"type":"text","name":"amount","id":"form_amount"},domProps:{"value":(_vm.formData.amount)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.formData, "amount", $event.target.value)}}})])]),_c('hr',{staticClass:"separator"}),_c('div',[_c('label',{attrs:{"for":"form_description"}},[_vm._v("Description")]),_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.formData.description),expression:"formData.description"}],staticClass:"form-control",attrs:{"type":"text","id":"form_description","name":"description"},domProps:{"value":(_vm.formData.description)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.formData, "description", $event.target.value)}}})]),_c('hr',{staticClass:"separator"}),_c('div',[_c('label',{attrs:{"for":"form_files"}},[_vm._v("Fichiers à ajouter")]),_c('p',{staticClass:"help"},[_vm._v("Vous pouvez sélectionner plusieurs fichiers en maintenant la touche CTRL enfoncé lors de la sélection d'un fichier")]),_c('input',{attrs:{"type":"file","name":"files[]","id":"form_files","multiple":""}})]),_c('div',{staticClass:"alert alert-info"},[_vm._v(" Vous pourrez modifier votre saisie, et finaliser la demande en cliquant sur l'action "),_c('strong',[_vm._v("Envoyer la demande")])]),_c('hr'),_c('nav',{staticClass:"text-center"},[_c('button',{staticClass:"btn btn-default",attrs:{"type":"reset"},on:{"click":function($event){$event.preventDefault();return _vm.handlerCancelForm.apply(null, arguments)}}},[_c('i',{staticClass:"icon-cancel-outline"}),_vm._v(" Annuler ")]),_c('button',{staticClass:"btn btn-primary",attrs:{"type":"submit"}},[_c('i',{staticClass:"icon-floppy"}),_vm._v(" Enregistrer ")])])])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.error)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"alert alert-danger"},[_c('h3',[_vm._v("Erreur "),_c('a',{staticClass:"float-right",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.error =null}}},[_c('i',{staticClass:"icon-cancel-outline"})])]),_c('p',[_vm._v(_vm._s(_vm.error))])])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.deleteData)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"alert alert-danger"},[_c('h3',[_vm._v("Supprimer la demande "),_c('strong',[_vm._v(_vm._s(_vm.deleteData.label))]),_vm._v(" ?")]),_c('nav',[_c('button',{staticClass:"btn btn-danger",attrs:{"type":"reset"},on:{"click":function($event){$event.preventDefault();_vm.deleteData = null}}},[_c('i',{staticClass:"icon-cancel-outline"}),_vm._v(" Annuler ")]),_c('button',{staticClass:"btn btn-success",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();return _vm.performDelete.apply(null, arguments)}}},[_c('i',{staticClass:"icon-ok-circled"}),_vm._v(" Confirmer ")])])])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.sendData)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"alert"},[_c('h3',[_vm._v("Envoyer la demande "),_c('strong',[_vm._v(_vm._s(_vm.sendData.label))]),_vm._v(" ?")]),_c('nav',[_c('button',{staticClass:"btn btn-danger",attrs:{"type":"reset"},on:{"click":function($event){$event.preventDefault();_vm.sendData = null}}},[_c('i',{staticClass:"icon-cancel-outline"}),_vm._v(" Annuler ")]),_c('button',{staticClass:"btn btn-success",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();return _vm.performSend.apply(null, arguments)}}},[_c('i',{staticClass:"icon-ok-circled"}),_vm._v(" Confirmer ")])])])]):_vm._e()]),_c('header',{staticClass:"row"},[_c('h1',{staticClass:"col-md-9"},[_vm._v(_vm._s(_vm.title))]),_c('nav',{staticClass:"col-md-3"},[_vm._v("   État des demandes affichées : "),_c('jckselector',{attrs:{"choose":_vm.listStatus,"selected":_vm.selectedStatus},on:{"change":function($event){_vm.selectedStatus = $event}}})],1)]),(_vm.activityRequests.length)?_c('section',_vm._l((_vm.activityRequests),function(a){return _c('article',{staticClass:"demande card",class:'status-' +a.statut},[_c('h3',{staticClass:"card-title"},[_c('strong',[_c('i',{class:'icon-' + a.statutText}),_vm._v(" "+_vm._s(a.label)+" ")]),_c('strong',[_c('i',{staticClass:"icon-bank"}),_vm._v(" "+_vm._s(a.amount)+" ")])]),_c('div',{staticClass:"card-metas text-highlight"},[_c('strong',[_c('i',{class:'icon-' + a.statut}),_vm._v(_vm._s(_vm._f("renderStatus")(a.statut)))]),_vm._v(" Créé le : "),_c('time',[_c('i',{staticClass:"icon-calendar"}),_vm._v(_vm._s(_vm._f("date")(a.dateCreated)))]),_vm._v(" ~ "),(a.worker)?_c('span',[_vm._v(" Demande géré par "),_c('strong',[_vm._v(_vm._s(a.worker))])]):_c('em',[_vm._v(" Non prise en charge pour le moment ")]),_vm._v(" ~ "),_c('strong',[_c('i',{staticClass:"icon-building-filled"}),(a.organisation)?_c('span',[_vm._v(_vm._s(a.organisation))]):_c('em',[_vm._v("Aucune organisation")])])]),_c('hr'),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-6"},[_vm._m(0,true),_c('ul',[_c('li',[_c('i',{staticClass:"icon-bank"}),_vm._v(" Somme demandée : "),_c('strong',[_vm._v(_vm._s(_vm._f("montant")(a.amount)))])]),_c('li',[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Début (prévu) : "),(a.dateStart)?_c('strong',[_vm._v(_vm._s(_vm._f("date")(a.dateStart)))]):_c('em',[_vm._v("pas de date de début prévue")])]),_c('li',[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Fin (prévue) : "),(a.dateEnd)?_c('strong',[_vm._v(_vm._s(_vm._f("date")(a.dateEnd)))]):_c('em',[_vm._v("pas de date de fin prévue")])])]),_c('div',{staticClass:"alert alert-help"},[_c('strong',[_vm._v("Description")]),_vm._v(" : "+_vm._s(a.description)+" ")]),_c('section',{staticClass:"fichiers"},[_vm._m(1,true),(a.files.length == 0)?_c('div',{staticClass:"alert alert-info"},[_vm._v(" Vous n'avez fourni aucun document pour cette demande ")]):_c('ul',_vm._l((a.files),function(f){return _c('li',[_c('strong',[_vm._v(_vm._s(f.name))]),_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":'?dl=' + f.file + '&id=' + a.id}},[_c('i',{staticClass:"icon-download"}),_vm._v(" Télécharger")]),(a.sendable)?_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerDeleteFile(f, a)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" Supprimer ce fichier")]):_vm._e()])}),0)])]),_c('div',{staticClass:"col-md-6"},[_c('section',[_vm._m(2,true),_vm._l((a.suivi),function(s){return _c('article',{staticClass:"follow"},[_c('figure',{staticClass:"avatar"},[_c('img',{attrs:{"src":'//www.gravatar.com/avatar/' + s.by.gravatar + '?s=64',"alt":""}})]),_c('div',{staticClass:"content"},[_c('small',{staticClass:"infos"},[_c('i',{staticClass:"icon-clock"}),_vm._v(" "+_vm._s(_vm._f("date")(s.datecreated))+" par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(" "+_vm._s(s.by.username))])]),_c('br'),_c('p',[_vm._v(_vm._s(s.description))])])])})],2)])]),(a.sendable)?_c('nav',[_c('a',{staticClass:"btn btn-default",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerEdit(a)}}},[_c('i',{staticClass:"icon-edit"}),_vm._v(" Modifier")]),_c('a',{staticClass:"btn btn-danger",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerDelete(a)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" Supprimer")]),_c('a',{staticClass:"btn btn-success",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerSend(a)}}},[_c('i',{staticClass:"icon-paper-plane"}),_vm._v(" Envoyer")])]):_vm._e()])}),0):_c('div',[_c('p',{staticClass:"alert alert-info"},[_vm._v(" Aucune demande ")])]),_c('hr'),(_vm.allowNew)?_c('button',{staticClass:"btn btn-primary",attrs:{"type":"button"},on:{"click":function($event){$event.preventDefault();return _vm.handlerNew.apply(null, arguments)}}},[_c('i',{staticClass:"icon-plus-circled"}),_vm._v(" Nouvelle Demande ")]):_vm._e(),(_vm.lockMessages.length)?_c('div',{staticClass:"alert alert-danger"},[_c('ul',_vm._l((_vm.lockMessages),function(m){return _c('li',[_vm._v(_vm._s(m))])}),0)]):_vm._e()],1)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:" icon-edit"}),_vm._v(" Informations")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-attach-outline"}),_vm._v(" Fichiers")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-signal"}),_vm._v(" Suivi")])}]


// CONCATENATED MODULE: ./src/ActivityRequest.vue?vue&type=template&id=32491513&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/JCKSelector.vue?vue&type=template&id=16a978b4&
var JCKSelectorvue_type_template_id_16a978b4_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"jck-selector"},[_c('div',{staticClass:"selected"},[_c('i',{staticClass:"icon-cog"}),_c('strong',[_vm._v(_vm._s(_vm.selectedLabel))])]),_c('div',{staticClass:"list"},_vm._l((_vm.choose),function(opt){return _c('div',{staticClass:"item",class:{ 'selected': (_vm.selected.indexOf(opt.id) > -1) },on:{"click":function($event){return _vm.toggleSelection(opt.id)}}},[_c('i',{staticClass:"icon-check"}),_c('i',{staticClass:"icon-check-empty"}),_vm._v(" "+_vm._s(opt.label)+" "),_c('small',[_vm._v(" ("+_vm._s(opt.description)+")")])])}),0)])}
var JCKSelectorvue_type_template_id_16a978b4_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/JCKSelector.vue?vue&type=template&id=16a978b4&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/JCKSelector.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var JCKSelectorvue_type_script_lang_js_ = ({
    props: {
        choose: {
            default: null
        },
        selected: {
            default: null
        }
    },

    computed: {
        selectedLabel(){
            if( this.selected.length ){
                let label = [];
                this.choose.forEach(item => {
                   if(this.selected.indexOf(item.id) > -1){
                       label.push(item.label);
                   }
                });
                return label.join(', ');
            }
            return "Aucune selection";
        }
    },

    methods: {
        toggleSelection(value){
            let selection = [];
            if( this.selected )
                selection = this.selected;

            let pos = selection.indexOf(value)
            if( pos > -1 ){
                selection.splice(pos, 1);
            } else {
                selection.push(value);
            }
            console.log(selection);
            this.$emit('change', selection);
        }
    }
});

// CONCATENATED MODULE: ./src/components/JCKSelector.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_JCKSelectorvue_type_script_lang_js_ = (JCKSelectorvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent (
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier, /* server only */
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = shadowMode
      ? function () {
        injectStyles.call(
          this,
          (options.functional ? this.parent : this).$root.$options.shadowRoot
        )
      }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functional component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}

// CONCATENATED MODULE: ./src/components/JCKSelector.vue





/* normalize component */

var component = normalizeComponent(
  components_JCKSelectorvue_type_script_lang_js_,
  JCKSelectorvue_type_template_id_16a978b4_render,
  JCKSelectorvue_type_template_id_16a978b4_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var JCKSelector = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityRequest.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

// node node_modules/.bin/vue-cli-service build --name ActivityRequest --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/ActivityRequest.vue



/* harmony default export */ var ActivityRequestvue_type_script_lang_js_ = ({
    data(){
        return {
            formData: null,
            addFile: false,
            sendData: null,
            addableFiles: null,
            file: null,
            loading: "",
            activityRequests: [],
            error: null,
            deleteData: null,
            allowNew : false,
            demandeur : "",
            demandeur_id : null,
            organisations : [],
            lockMessages : [],
            selectedStatus: [1, 2]
        }
    },

    props: {
        moment: {
            required: true
        },
        title: {
            required: true
        }
    },

    watch: {
        selectedStatus(){
            this.fetch();
        }
    },

    computed:{
        listStatus(){
            let status = [
                {id: 1, label: "Brouillon", description: "Demandes en cours de rédaction (non envoyées)" },
                {id: 2, label: "Envoyée", description: "Demandes envoyées mais pas encore traitées" },
                {id: 5, label: "Validée", description: "Demandes validées" },
                {id: 7, label: "Refusée", description: "Demandes refusées" }
            ];
            return status;
        }
    },

    components: {
        'jckselector': JCKSelector
    },

    methods:{
        processFile(evt){
            this.addableFiles = evt.target.files[0].name;
        },

        handlerAddFile(){
            this.addFile = true;
        },

        /**
         * Récupération des données.
         */
        fetch(){
            this.loading = "Chargement des Demandes";
            this.$http.get('?&status=' + this.selectedStatus.join(',')).then(
                ok => {
                    this.activityRequests = ok.body.activityRequests;
                    this.allowNew = ok.body.allowNew;
                        this.demandeur = ok.body.demandeur;
                        this.demandeur_id = ok.body.demandeur_id;
                        this.organisations = ok.body.organisations;
                        this.lockMessages = ok.body.lockMessages;
                },
                ko => {
                    this.error = "Impossible de charger les demandes : " + ko.body;
                }
            ).then( foo =>{
                this.loading = null;
            });
        },

        sendFile(id, evt){
            let upload = new FormData(evt.target);
            this.$http.post('?', upload).then(
                ok => {

            }).catch(
                err => {

            })
        },

        handlerSend(demande){
            this.sendData = demande;
        },

        performSend(){
            let demande = this.sendData;
            let form = new FormData();
            form.append('action', 'send');
            form.append('id', demande.id);

            this.$http.post('?', form)
                .then( ok => {
                    this.fetch();
                })
                .catch( err => {
                    this.error = err.body;
                })
                .then( foo => {
                    this.sendData = null;
                })
        },

        handlerNew(){
            this.formData = {
                id: null,
                label: "",
                description: "",
                dateStart: null,
                dateEnd: null,
                amount: 0.0,
                organization: this.organisations[0],
                files: []
            };
        },

        handlerEdit( demande ){

            this.formData = {
                id: demande.id,
                label: demande.label,
                description: demande.description,
                dateStart: demande.dateStart,
                dateEnd: demande.dateEnd,
                amount: demande.amount,
                organization: demande.organization,
                files: demande.files,
            };
            /****/
        },

        handlerDeleteFile(f, a){
            this.loading = "Suppression du fichier " + f.name;
            this.$http.get('?rdl=' + f.file + '&id=' + a.id).then(
                ok => {
                    this.fetch();
                }
            ).catch( err => {
                this.error = err.body;
            }).finally( foo => {
                this.loading = "";
            })
        },

        handlerSave( evt ){
            let upload = new FormData(evt.target);
            upload.append('dateStart', this.formData.dateStart);
            upload.append('dateEnd', this.formData.dateEnd);
            this.$http.post('?', upload).then(
                ok => {
                    this.fetch();
                    this.formData = null;
                }).catch(
                err => {
                    this.error = err.body;
                    this.formData = null;
                    console.log(err);
                })

        },

        handlerCancelForm(){
            this.formData = null;
        },


        handlerDelete( request ){
            this.deleteData = request;
        },

        performDelete(){
            this.loading = "Suppression de la demande " + this.deleteData.label;
            let request = this.deleteData;
            this.$http.delete('?id=' + request.id).then(
                ok => {
                    this.fetch();
                },
                ko => {
                    this.error = ko.body;
                }
            ).then( foo => this.deleteData = null );
            /****/
        }
    },
    mounted(){
        this.fetch();
    }
});

// CONCATENATED MODULE: ./src/ActivityRequest.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ActivityRequestvue_type_script_lang_js_ = (ActivityRequestvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/ActivityRequest.vue





/* normalize component */

var ActivityRequest_component = normalizeComponent(
  src_ActivityRequestvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ActivityRequest = (ActivityRequest_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (ActivityRequest);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=ActivityRequest.umd.js.map
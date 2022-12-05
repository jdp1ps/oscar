(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ActivityRequestAdmin"] = factory();
	else
		root["ActivityRequestAdmin"] = factory();
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

/***/ "8875":
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;// addapted from the document.currentScript polyfill by Adam Miller
// MIT license
// source: https://github.com/amiller-gh/currentScript-polyfill

// added support for Firefox https://bugzilla.mozilla.org/show_bug.cgi?id=1620505

(function (root, factory) {
  if (true) {
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else {}
}(typeof self !== 'undefined' ? self : this, function () {
  function getCurrentScript () {
    var descriptor = Object.getOwnPropertyDescriptor(document, 'currentScript')
    // for chrome
    if (!descriptor && 'currentScript' in document && document.currentScript) {
      return document.currentScript
    }

    // for other browsers with native support for currentScript
    if (descriptor && descriptor.get !== getCurrentScript && document.currentScript) {
      return document.currentScript
    }
  
    // IE 8-10 support script readyState
    // IE 11+ & Firefox support stack trace
    try {
      throw new Error();
    }
    catch (err) {
      // Find the second match for the "at" string to get file src url from stack.
      var ieStackRegExp = /.*at [^(]*\((.*):(.+):(.+)\)$/ig,
        ffStackRegExp = /@([^@]*):(\d+):(\d+)\s*$/ig,
        stackDetails = ieStackRegExp.exec(err.stack) || ffStackRegExp.exec(err.stack),
        scriptLocation = (stackDetails && stackDetails[1]) || false,
        line = (stackDetails && stackDetails[2]) || false,
        currentLocation = document.location.href.replace(document.location.hash, ''),
        pageSource,
        inlineScriptSourceRegExp,
        inlineScriptSource,
        scripts = document.getElementsByTagName('script'); // Live NodeList collection
  
      if (scriptLocation === currentLocation) {
        pageSource = document.documentElement.outerHTML;
        inlineScriptSourceRegExp = new RegExp('(?:[^\\n]+?\\n){0,' + (line - 2) + '}[^<]*<script>([\\d\\D]*?)<\\/script>[\\d\\D]*', 'i');
        inlineScriptSource = pageSource.replace(inlineScriptSourceRegExp, '$1').trim();
      }
  
      for (var i = 0; i < scripts.length; i++) {
        // If ready state is interactive, return the script tag
        if (scripts[i].readyState === 'interactive') {
          return scripts[i];
        }
  
        // If src matches, return the script tag
        if (scripts[i].src === scriptLocation) {
          return scripts[i];
        }
  
        // If inline source matches, return the script tag
        if (
          scriptLocation === currentLocation &&
          scripts[i].innerHTML &&
          scripts[i].innerHTML.trim() === inlineScriptSource
        ) {
          return scripts[i];
        }
      }
  
      // If no match, return null
      return null;
    }
  };

  return getCurrentScript
}));


/***/ }),

/***/ "fb15":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (true) {
    var getCurrentScript = __webpack_require__("8875")
    currentScript = getCurrentScript()

    // for backward compatibility, because previously we directly included the polyfill
    if (!('currentScript' in document)) {
      Object.defineProperty(document, 'currentScript', { get: getCurrentScript })
    }
  }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityRequestAdmin.vue?vue&type=template&id=1ce1095c&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',[_c('transition',{attrs:{"name":"fade"}},[(_vm.error)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"alert alert-danger overlay-content"},[_c('h3',[_vm._v("Erreur "),_c('a',{staticClass:"float-right",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.error =null}}},[_c('i',{staticClass:"icon-cancel-outline"})])]),_c('pre',[_vm._v(_vm._s(_vm.error))])])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.confirmProccess)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"alert alert-danger overlay-content"},[_c('h3',[_vm._v(_vm._s(_vm.confirmProccess.message))]),_c('transition',{attrs:{"name":"slide"}},[(_vm.confirmProccess.step == 2)?_c('div',[_c('button',{staticClass:"btn btn-danger",attrs:{"type":"reset"},on:{"click":function($event){$event.preventDefault();_vm.confirmProccess = null}}},[_c('i',{staticClass:"icon-cancel-outline"}),_vm._v(" Annuler ")]),_c('button',{staticClass:"btn btn-success",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();return _vm.confirmProccess.process()}}},[_c('i',{staticClass:"icon-ok-circled"}),_vm._v(" Confirmer ")])]):_c('div',[(_vm.confirmProccess.person)?_c('section',{staticClass:"row"},[_c('label',{staticClass:"col-md-8",attrs:{"for":"roleDelarer"}},[_vm._v(" Rôle de "+_vm._s(_vm.confirmProccess.person)),_c('br'),_c('small',[_vm._v("Selectionnez un rôle pour "),_c('strong',[_vm._v(_vm._s(_vm.confirmProccess.person))]),_vm._v(" dans l'activité de recherche. Vous pourrez modfifier cette information en éditant directement l'activité par le suite ")])]),_c('div',{staticClass:"col-md-4"},[_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.confirmProccess.personRole),expression:"confirmProccess.personRole"}],staticClass:"form-control",attrs:{"id":"roleDeclarer"},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.$set(_vm.confirmProccess, "personRole", $event.target.multiple ? $$selectedVal : $$selectedVal[0])}}},[_c('option',{attrs:{"value":"0"}},[_vm._v("Ne pas affecter à l'activité")]),_vm._l((_vm.rolesPerson),function(r,id){return _c('option',{domProps:{"value":id}},[_vm._v(_vm._s(r))])})],2)])]):_vm._e(),(_vm.confirmProccess.organization)?_c('section',{staticClass:"row"},[_c('label',{staticClass:"col-md-8",attrs:{"for":"roleOrg"}},[_vm._v("Rôle de "+_vm._s(_vm.confirmProccess.organization))]),_c('div',{staticClass:"col-md-4"},[_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.confirmProccess.organisationRole),expression:"confirmProccess.organisationRole"}],staticClass:"form-control",attrs:{"id":"roleOrg"},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.$set(_vm.confirmProccess, "organisationRole", $event.target.multiple ? $$selectedVal : $$selectedVal[0])}}},_vm._l((_vm.rolesOrganisation),function(r,id){return _c('option',{domProps:{"value":id}},[_vm._v(_vm._s(r))])}),0)])]):_vm._e(),_c('hr',{staticClass:"separator"}),_c('button',{staticClass:"btn btn-danger",attrs:{"type":"reset"},on:{"click":function($event){$event.preventDefault();_vm.confirmProccess = null}}},[_c('i',{staticClass:"icon-cancel-outline"}),_vm._v(" Annuler ")]),_c('button',{staticClass:"btn btn-default",attrs:{"type":"button"},on:{"click":function($event){_vm.confirmProccess.step = 2}}},[_vm._v(" Suivant "),_c('i',{staticClass:"icon-right-outline"})])])])],1)]):_vm._e()]),_c('header',{staticClass:"row"},[_c('h1',{staticClass:"col-md-9"},[_vm._v(_vm._s(_vm.title))]),_c('nav',{staticClass:"col-md-3"},[_vm._v("   "),_c('jckselector',{attrs:{"choose":_vm.listStatus,"selected":_vm.selectedStatus},on:{"change":function($event){_vm.selectedStatus = $event}}})],1)]),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.loading),expression:"loading"}],staticClass:"alert alert-info"},[_vm._v(_vm._s(_vm.loading))]),(_vm.activityRequests.length)?_c('section',_vm._l((_vm.activityRequests),function(a){return _c('article',{staticClass:"card"},[_c('h3',{staticClass:"card-title"},[_c('strong',[_c('i',{class:'icon-' + a.statutText}),_vm._v(" "+_vm._s(a.label))]),_c('strong',[_c('i',{staticClass:"icon-bank"}),_vm._v(" "+_vm._s(a.amount)+" ")]),_c('small',{staticClass:"right"},[_vm._v("par "),_c('strong',[_vm._v(_vm._s(a.requester))])])]),_c('div',{staticClass:"content row"},[_c('div',{staticClass:"col-md-6"},[_c('i',{staticClass:"icon-user"}),_vm._v(" Statut : "),_c('strong',[_vm._v(_vm._s(_vm._f("renderStatus")(a.statut)))]),_c('br'),_c('i',{staticClass:"icon-user"}),_vm._v(" Demandeur : "),_c('strong',[_vm._v(_vm._s(a.requester))]),_c('br'),_c('i',{staticClass:"icon-building-filled"}),_vm._v("Organisme : "),(a.organisation)?_c('strong',[_vm._v(" "+_vm._s(a.organisation))]):_c('em',[_vm._v("Aucun organisme identifié")]),_c('br'),_c('i',{staticClass:"icon-bank"}),_vm._v(" Budget : "),_c('strong',[_vm._v(_vm._s(_vm._f("montant")(a.amount)))]),_c('br'),_c('i',{staticClass:"icon-calendar"}),_vm._v(" du "),(a.dateStart)?_c('strong',[_vm._v(_vm._s(_vm._f("date")(a.dateStart)))]):_c('em',[_vm._v("non précisé")]),_vm._v(" au "),(a.dateEnd)?_c('strong',[_vm._v(_vm._s(_vm._f("date")(a.dateEnd)))]):_c('em',[_vm._v("non précisé")]),_c('br'),_vm._m(0,true),_vm._v(" "+_vm._s(a.description)+" ")]),_c('div',{staticClass:"col-md-6"},[_c('h3',[_vm._v("Suivi")]),_vm._l((a.suivi),function(s){return _c('article',{staticClass:"follow"},[_c('figure',{staticClass:"avatar"},[_c('img',{attrs:{"src":'//www.gravatar.com/avatar/' + s.by.gravatar + '?s=64',"alt":""}})]),_c('div',{staticClass:"content"},[_c('small',{staticClass:"infos"},[_c('i',{staticClass:"icon-clock"}),_vm._v(" "+_vm._s(_vm._f("date")(s.datecreated))+" par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(" "+_vm._s(s.by.username))])]),_c('br'),_c('p',[_vm._v(_vm._s(s.description))])])])})],2)]),(a.files.length)?_c('section',{staticClass:"liste-fichiers"},[_vm._m(1,true),_c('ul',_vm._l((a.files),function(f){return _c('li',[_c('strong',[_vm._v(_vm._s(f.name))]),_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":'?dl=' + f.file + '&id=' + a.id}},[_c('i',{staticClass:"icon-download"}),_vm._v(" Télécharger")])])}),0)]):_vm._e(),(a.statut == 2)?_c('nav',[_c('button',{staticClass:"btn btn-success",on:{"click":function($event){return _vm.handlerValid(a)}}},[_c('i',{staticClass:"icon-valid"}),_vm._v(" Valider la demande ")]),_c('button',{staticClass:"btn btn-danger",on:{"click":function($event){return _vm.handlerReject(a)}}},[_c('i',{staticClass:"icon-cancel-alt"}),_vm._v(" Rejeter la demande ")])]):_vm._e()])}),0):_c('div',[_c('p',{staticClass:"alert alert-info"},[_vm._v(" Aucune demande ")])])],1)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('strong',[_c('i',{staticClass:"icon-comment"}),_vm._v("Description : ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-file-excel"}),_vm._v(" Fichiers")])}]


// CONCATENATED MODULE: ./src/ActivityRequestAdmin.vue?vue&type=template&id=1ce1095c&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/JCKSelector.vue?vue&type=template&id=16a978b4&
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
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityRequestAdmin.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

// node node_modules/.bin/vue-cli-service build --name ActivityRequestAdmin --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/ActivityRequestAdmin.vue

    

    /* harmony default export */ var ActivityRequestAdminvue_type_script_lang_js_ = ({
        data(){
            return {
                formData: null,
                addFile: false,
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
                confirmProccess: null,
                history: false,
                selectedStatus: [2],
                roles: {
                    person: null,
                    organisation: null
                }
            }
        },

        components: {
            'jckselector': JCKSelector,
        },

        props: {
            moment: {
                required: true
            },
            rolesPerson: {
                required: true
            },
            rolesOrganisation: {
                required: true
            },
            asAdmin: {
                default: false
            },
            title: {
                required: true
            }
        },

        computed:{
            listStatus(){
                let status = [
                    {id: 2, label: "Envoyée", description: "Demandes envoyées" },
                    {id: 5, label: "Validée", description: "Demandes validées" },
                    {id: 7, label: "Refusée", description: "Demandes refusées" }
                ];
                if( this.asAdmin ){
                    status.push(
                    {id: 1, label: "Brouillon", description: "Demandes en cours de rédaction" }
                    )
                }
                return status;
            }
        },

        watch: {
            'history' : function(){
                this.fetch();
            },
            'selectedStatus' : function(){
                this.fetch();
            }
        },

        methods:{
            handlerValid(request){
                this.confirmProccess = {
                    step: 1,
                    message: "Confirmer la transformation de la demande en activité : "+ request.label + " par " + request.requester +" ?",
                    person: request.requester,
                    personRole: 0,
                    organization: request.organisation,
                    organizationRole: 0,
                    process: () => this.performValid(request)
                }
            },

            handlerReject(request){
                this.confirmProccess = {
                    step: 2,
                    message: "Confirmer le rejet de la demande : " + request.label + " par " + request.requester +" ?",
                    process: () => this.performReject(request)
                }
            },

            performValid(request){
              this.loading = "Transformation de la demande en activité...";
              let datas = new FormData();
              datas.append('id', request.id);
              datas.append('action', 'valid');

              if( this.confirmProccess.person ) {
                  datas.append('personRoleId', this.confirmProccess.personRole);
              }
              if( this.confirmProccess.organization ) {
                  datas.append('organisationRoleId', this.confirmProccess.organisationRole);
              }

              this.$http.post('?', datas).then(
                  ok => {
                      this.fetch();
                  },
                  ko => {
                      this.error = "Impossible de valider la demande : " +ko.body;
                  }
              ).then( foo => {
                  this.loading = "";
                  this.confirmProccess = null;
              })
            },

            performReject(request){
                this.loading = "Rejet de la demande en activité...";
                let datas = new FormData();
                datas.append('id', request.id);
                datas.append('action', 'reject');
                this.$http.post('?', datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = "Impossible de rejeter la demande : " +ko.body;
                    }
                ).then( foo => {
                    this.loading = "";
                    this.confirmProccess = null;
                })
            },

            /**
             * Récupération des données.
             */
            fetch(){
                this.loading = "Chargement des Demandes";
                this.$http.get('?' + (this.history ? '&history=1': '') +'&status=' +this.selectedStatus.join(',')).then(
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

// CONCATENATED MODULE: ./src/ActivityRequestAdmin.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ActivityRequestAdminvue_type_script_lang_js_ = (ActivityRequestAdminvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/ActivityRequestAdmin.vue





/* normalize component */

var ActivityRequestAdmin_component = normalizeComponent(
  src_ActivityRequestAdminvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ActivityRequestAdmin = (ActivityRequestAdmin_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (ActivityRequestAdmin);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=ActivityRequestAdmin.umd.js.map
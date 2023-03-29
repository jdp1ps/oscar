(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["AccountList"] = factory();
	else
		root["AccountList"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/AccountList.vue?vue&type=template&id=83efb9b4&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',{staticClass:"account-list admin"},[_c('transition',{attrs:{"name":"fade"}},[(_vm.editedAccount)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('h3',[_vm._v(" Modification de la masse budgétaire pour le compte "),_c('strong',[_vm._v(_vm._s(_vm.editedAccount.label))]),_c('span',{staticClass:"overlay-closer",on:{"click":function($event){_vm.editedAccount = null}}},[_vm._v("X")])]),_c('p',[_vm._v(" Code OSCAR : "),_c('strong',[_vm._v(_vm._s(_vm.editedAccount.code))]),_vm._v(" Code Comptable (SIFAC) : "),_c('strong',[_vm._v(_vm._s(_vm.editedAccount.codeFull))])]),_c('p',[_vm._v(" Choisissez une annexe budgétaire : "),_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.editedAccount.annexe),expression:"editedAccount.annexe"}],staticClass:"form-control",attrs:{"name":"","id":""},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.$set(_vm.editedAccount, "annexe", $event.target.multiple ? $$selectedVal : $$selectedVal[0])}}},[_c('option',{attrs:{"value":"0"}},[_vm._v("Ignorer")]),_c('option',{attrs:{"value":"1"}},[_vm._v("Traiter comme une recette")]),_vm._l((_vm.masses),function(text,masse){return _c('option',{domProps:{"value":masse}},[_vm._v(_vm._s(text))])})],2)]),_c('hr'),_c('button',{staticClass:"btn btn-danger",on:{"click":function($event){_vm.editedAccount = null}}},[_c('i',{staticClass:"icon-cancel-circled"}),_vm._v("Annuler")]),_c('button',{staticClass:"btn btn-success",on:{"click":_vm.handlerPerformEdit}},[_c('i',{staticClass:"icon-floppy"}),_vm._v("Enregistrer")])])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.error)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('h3',[_c('i',{staticClass:"icon-bug"}),_vm._v(" ERREUR")]),_c('pre',{staticClass:"alert-danger alert"},[_vm._v(_vm._s(_vm.error))]),_c('nav',{staticClass:"buttons text-center"},[_c('button',{staticClass:"btn btn-default",on:{"click":function($event){_vm.error = null}}},[_vm._v(" Fermer ")])])])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.pending)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('p',{staticClass:"text-center"},[_c('i',{staticClass:"animate-spin icon-spinner"}),_vm._v(" "+_vm._s(_vm.Pending)+" ")])])]):_vm._e()]),_vm._m(0),_vm._l((_vm.accounts),function(a){return _c('article',{staticClass:"card account-infos",class:{
              'missing': a.annexe == null,
              'ignored': a.annexe == 0,
              'input': a.annexe == 1
            }},[_c('h3',[_c('code',{attrs:{"title":"Code utilisé dans SIFAC"}},[_c('a',{staticStyle:{"color":"white"},attrs:{"href":'/activites-de-recherche/advancedsearch?q=&criteria[]=cb2%3B'+a.codeFull}},[_vm._v(" "+_vm._s(a.codeFull)+" ")])]),_c('strong',[_vm._v(" "+_vm._s(a.label)+" "),_c('small',{attrs:{"title":"Numéro dans OSCAR"}},[_vm._v("("+_vm._s(a.code)+")")]),_c('a',{staticClass:"btn btn-xs",class:{ 'btn-primary': a.annexe == null, 'btn-default': a.annexe != null },attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.handlerEdit(a)}}},[_c('i',{staticClass:"icon-edit"}),_vm._v(" Modifier l'annexe budgétaire")])]),(a.annexe == '0')?_c('em',{staticClass:"off"},[_vm._v("Ignorée")]):(a.annexe == '1')?_c('em',{staticClass:"plus"},[_vm._v("Recette")]):(a.annexe)?_c('em',{staticClass:"minus"},[_vm._v(_vm._s(_vm.masses[a.annexe]))]):_c('em',{staticClass:"value-missing"},[_vm._v("AUCUNE")])])])})],2)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('p',{staticClass:"alert alert-info"},[_c('i',{staticClass:"icon-info-outline"}),_vm._v(" Vous trouverez ci-dessous la liste des comptes utilisés dans la remontée des dépenses. Ceux apparaissant en rouge dans cette liste n'ont pas de masse attribués et seront affichés en rouge dans une catégorie "),_c('strong',[_vm._v("Hors-Masse")]),_vm._v(" dans la zone de synthèse des dépenses de la fiche activité. ")])}]


// CONCATENATED MODULE: ./src/AccountList.vue?vue&type=template&id=83efb9b4&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/AccountList.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/**
 node node_modules/.bin/vue-cli-service build --name AccountList --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/AccountList.vue
 */
  /* harmony default export */ var AccountListvue_type_script_lang_js_ = ({

    props: {
      url: { default: "" },
      manage: { default: "" }
    },

    data(){
      return {
        accounts: [],
        masses: [],
        editedAccount: null,
        error: "",
        pending: ""
      }
    },

    methods: {

      /**
       * Chargement des comptes utilisés dans OSCAR
       */
      fetch(){
        this.pending = "Chargement des comptes utilisés";
        this.$http.get(this.url).then( ok => {
          this.accounts = ok.data.accounts;
          this.masses = ok.data.masses;
        }, ko => {
          let message = "Erreur inconnue";
          try {
            message = ko.body;
          } catch (e) {
            message = "Erreur JS : " + e;
          }
          this.error = "Impossible de charger des comptes utilisés " + message;
        }).then( this.pending = null )
      },

      /**
       * Affichage de la fenêtre de modification des annexes budgétaires.
       *
       * @param account
       */
      handlerEdit(account){
        this.editedAccount = JSON.parse(JSON.stringify(account));
      },

      /**
       * Envoi des modifications.
       */
      handlerPerformEdit(){
        this.pending = "Enregistrement en cours";
        let accountId = this.editedAccount.id;
        let annexe = this.editedAccount.annexe;
        let data = new FormData();
        data.append('id', accountId);
        data.append('annexe', annexe);
        data.append('action', 'annexe');
        this.$http.post(this.manage, data).then(
          ok => {
            this.editedAccount = null;
            this.fetch();
          }, ko => {
              let message = "";
              console.log(ko.body);
              try {
                message = ko.body;
              } catch (e) {
                message = "Erreur JS : " + e;
              }
              console.log(message);
              this.error = "Impossible de modifier l'annexe budgétaire : " + message;
            }
        );
      }
    },

    mounted() {
      this.fetch();
    }
});

// CONCATENATED MODULE: ./src/AccountList.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_AccountListvue_type_script_lang_js_ = (AccountListvue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/AccountList.vue





/* normalize component */

var component = normalizeComponent(
  src_AccountListvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var AccountList = (component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (AccountList);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=AccountList.umd.js.map
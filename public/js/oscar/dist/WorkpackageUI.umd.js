(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["WorkpackageUI"] = factory();
	else
		root["WorkpackageUI"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/WorkpackageUI.vue?vue&type=template&id=53b388bb&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',[_c('transition',{attrs:{"name":"fade"}},[(_vm.errors.length)?_c('div',{staticClass:"vue-loader"},_vm._l((_vm.errors),function(error,i){return _c('div',{staticClass:"alert alert-danger"},[_vm._v(" "+_vm._s(error)+" "),_c('a',{attrs:{"href":""},on:{"click":function($event){$event.preventDefault();return _vm.errors.splice(i,1)}}},[_c('i',{staticClass:"icon-cancel-outline"})])])}),0):_vm._e()]),(_vm.loading)?_c('div',{staticClass:"vue-loader-component"},[_c('span',[_vm._v("Chargement")])]):_vm._e(),_c('nav',{staticClass:"buttons"},[(_vm.editable)?_c('a',{staticClass:"btn btn-primary",attrs:{"href":""},on:{"click":function($event){$event.preventDefault();return _vm.handlerWorkPackageNew.apply(null, arguments)}}},[_vm._v("Nouveau lot")]):_vm._e()]),_c('section',{staticClass:"workpackages"},_vm._l((_vm.workpackages),function(wp){return _c('workpackage',{key:wp.id,attrs:{"workpackage":wp,"persons":_vm.persons,"editable":_vm.editable,"is-validateur":_vm.isValidateur},on:{"addperson":_vm.addperson,"workpackageupdate":_vm.handlerWorkPackageUpdate,"workpackagepersonupdate":_vm.handlerUpdateWorkPackagePerson,"workpackagepersondelete":_vm.handlerWorkPackagePersonDelete,"workpackagedelete":_vm.handlerWorkPackageDelete,"workpackagecancelnew":_vm.handlerWorkPackageCancelNew}})}),1)],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/WorkpackageUI.vue?vue&type=template&id=53b388bb&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Workpackage.vue?vue&type=template&id=a82ffeb4&
var Workpackagevue_type_template_id_a82ffeb4_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('article',{staticClass:"workpackage"},[(_vm.mode == 'edit')?_c('form',{attrs:{"action":""},on:{"submit":function($event){$event.preventDefault();return _vm.handlerUpdateWorkPackage.apply(null, arguments)}}},[_c('h4',[(_vm.workpackage.id > 0)?_c('span',[_vm._v("Modification du lot")]):_c('span',[_vm._v("Nouveau lot")]),_vm._v(" "+_vm._s(_vm.formData.label)+" ")]),_c('div',{staticClass:"form-group"},[_c('label',{attrs:{"for":""}},[_vm._v("Code")]),_vm._m(0),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.formData.code),expression:"formData.code"}],staticClass:"form-control",attrs:{"type":"text","placeholder":"CODE"},domProps:{"value":(_vm.formData.code)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.formData, "code", $event.target.value)}}})]),_c('div',{staticClass:"form-group"},[_c('label',{attrs:{"for":""}},[_vm._v("Intitulé")]),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.formData.label),expression:"formData.label"}],staticClass:"form-control",attrs:{"type":"text","placeholder":"Intitulé"},domProps:{"value":(_vm.formData.label)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.formData, "label", $event.target.value)}}})]),_c('div',{staticClass:"form-group"},[_c('label',{attrs:{"for":""}},[_vm._v("Description")]),_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.formData.description),expression:"formData.description"}],staticClass:"form-control",attrs:{"type":"text","placeholder":"Description"},domProps:{"value":(_vm.formData.description)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.formData, "description", $event.target.value)}}})]),_c('nav',{staticClass:"buttons-bar"},[_c('button',{staticClass:"btn btn-default btn-save",class:{'disabled': !_vm.formData.code },attrs:{"type":"submit"}},[_c('i',{staticClass:"icon-floppy"}),_vm._v(" Enregistrer ")]),_c('button',{staticClass:"btn btn-default",attrs:{"type":"button"},on:{"click":_vm.handlerCancelEdit}},[_c('i',{staticClass:"icon-block"}),_vm._v(" Annuler ")])])]):_vm._e(),(_vm.mode == 'read')?_c('div',[_c('h3',[_vm._v("["+_vm._s(_vm.workpackage.code)+"] "+_vm._s(_vm.workpackage.label))]),_c('p',[_vm._v(_vm._s(_vm.workpackage.description))]),_c('section',{staticClass:"workpackage-persons"},[_vm._m(1),_vm._l((_vm.workpackage.persons),function(person){return _c('workpackageperson',{key:person.id,attrs:{"person":person,"editable":_vm.editable},on:{"workpackagepersondelete":_vm.handlerDelete,"workpackagepersonupdate":_vm.handlerUpdate}})})],2),(_vm.editable && _vm.persons.length)?_c('div',{staticClass:"buttons"},[_c('div',{staticClass:"btn-group"},[_vm._m(2),_c('ul',{staticClass:"dropdown-menu"},_vm._l((_vm.persons),function(person){return _c('li',[_c('a',{attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.$emit('addperson', person.id, _vm.workpackage.id)}}},[_vm._v(_vm._s(person.displayname))])])}),0)]),_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.handlerEditWorkPackage.apply(null, arguments)}}},[_c('i',{staticClass:"icon-pencil"}),_vm._v("Modifier")]),_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.handlerDeleteWorkPackage.apply(null, arguments)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v("Supprimer")])]):_vm._e(),(_vm.persons.length <= 0)?_c('div',{staticClass:"text-danger"},[_vm._v(" Vous n'avez pas encore ajouté de membre à cette activité. "),_c('strong',[_vm._v("Seul les membres d'une activité peuvent être identifiés comme déclarant")]),_vm._v(". ")]):_vm._e()]):_vm._e()])}
var Workpackagevue_type_template_id_a82ffeb4_staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('p',{staticClass:"text-danger"},[_vm._v("Le code est "),_c('strong',[_vm._v("utilisé pour l'affichage des créneaux")]),_vm._v(" simplifiés, utilisez un code de préférence entre 3 et 5 caractères.")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-group"}),_vm._v(" Déclarants ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('button',{staticClass:"btn btn-default  btn-xs dropdown-toggle",attrs:{"type":"button","data-toggle":"dropdown","aria-haspopup":"true","aria-expanded":"false"}},[_vm._v(" Ajouter un déclarant "),_c('span',{staticClass:"caret"})])}]


// CONCATENATED MODULE: ./src/components/Workpackage.vue?vue&type=template&id=a82ffeb4&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/WorkpackagePerson.vue?vue&type=template&id=2fe5cef0&
var WorkpackagePersonvue_type_template_id_2fe5cef0_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('article',{staticClass:"workpackage-person"},[_c('div',{staticClass:"displayname"},[_c('strong',[_vm._v(_vm._s(_vm.person.person.displayname))]),(_vm.editable && _vm.mode == 'read')?_c('a',{staticClass:"link",attrs:{"href":"#","title":"Supprimer ce déclarant"},on:{"click":function($event){$event.preventDefault();return _vm.handlerRemove(_vm.person)}}},[_c('i',{staticClass:"icon-trash"})]):_vm._e()]),_c('div',{staticClass:"tempsdeclare temps"},[(_vm.editable && _vm.mode == 'edit')?_c('div',[_vm._v(" Heures prévues : "),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.durationForm),expression:"durationForm"}],staticStyle:{"width":"5em"},attrs:{"type":"integer"},domProps:{"value":(_vm.durationForm)},on:{"keyup":function($event){if(!$event.type.indexOf('key')&&$event.keyCode!==13){ return null; }return _vm.handlerUpdate.apply(null, arguments)},"input":function($event){if($event.target.composing){ return; }_vm.durationForm=$event.target.value}}}),_c('a',{attrs:{"href":"#","title":"Appliquer la modification des heures prévues"},on:{"click":function($event){$event.preventDefault();return _vm.handlerUpdate.apply(null, arguments)}}},[_c('i',{staticClass:"icon-floppy"})]),_c('a',{attrs:{"href":"#","title":"Annuler la modification des heures prévues"},on:{"click":function($event){$event.preventDefault();return _vm.handlerCancel.apply(null, arguments)}}},[_c('i',{staticClass:"icon-cancel-outline"})])]):_c('span',[_c('strong',{staticClass:"wp-hours"},[_c('span',{staticClass:"wp-hour unsend",attrs:{"title":"Heure(s) saisie(s)"}},[_vm._v(_vm._s(_vm._f("heures")(_vm.person.unsend)))]),_c('span',{staticClass:"wp-hour validate",attrs:{"title":"Heure(s) validée(s)"}},[_vm._v(_vm._s(_vm._f("heures")(_vm.person.validate)))]),(_vm.person.validating > 0)?_c('span',{staticClass:"wp-hour validating",attrs:{"title":"Heure(s) en cours de validation"}},[_vm._v(_vm._s(_vm._f("heures")(_vm.person.validating)))]):_vm._e(),(_vm.person.conflicts > 0)?_c('span',{staticClass:"wp-hour conflicts",attrs:{"title":"Heure(s) en conflit"}},[_vm._v(_vm._s(_vm._f("heures")(_vm.person.conflicts)))]):_vm._e(),_c('span',{staticClass:"wp-hour duration",attrs:{"title":"Heure(s) à valider"}},[_vm._v(" / "+_vm._s(_vm._f("heures")(_vm.person.duration))+" "),(_vm.editable && _vm.mode == 'read')?_c('a',{attrs:{"href":"#","title":"Modifier les heures prévues"},on:{"click":function($event){$event.preventDefault();return _vm.handlerEdit.apply(null, arguments)}}},[_c('i',{staticClass:"icon-pencil"})]):_vm._e()])])])])])}
var WorkpackagePersonvue_type_template_id_2fe5cef0_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/WorkpackagePerson.vue?vue&type=template&id=2fe5cef0&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/WorkpackagePerson.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var WorkpackagePersonvue_type_script_lang_js_ = ({
    props: {
        'person': { default: function(){ return {} } },
        'editable': false
    },
    computed: {
        duration(){
            return this.person.duration;
        }
    },
    data(){
        return {
            'canSave': false,
            'mode' : 'read',
            'durationForm': 666
        }
    },
    methods: {
        handlerKeyUp(){
            console.log(arguments);
        },
        handlerUpdate(){
            this.$emit('workpackagepersonupdate', this.person, this.durationForm);
            this.mode = 'read';
        },
        handlerEdit(){
            this.mode = 'edit';
            this.durationForm = this.person.duration;
        },
        handlerCancel(){
            this.mode = 'read';
            this.durationForm = this.person.duration;
        },
        handlerRemove(){
            this.$emit('workpackagepersondelete', this.person);
        }
    }
});

// CONCATENATED MODULE: ./src/components/WorkpackagePerson.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_WorkpackagePersonvue_type_script_lang_js_ = (WorkpackagePersonvue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/components/WorkpackagePerson.vue





/* normalize component */

var component = normalizeComponent(
  components_WorkpackagePersonvue_type_script_lang_js_,
  WorkpackagePersonvue_type_template_id_2fe5cef0_render,
  WorkpackagePersonvue_type_template_id_2fe5cef0_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var WorkpackagePerson = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Workpackage.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/**********************************************************************************************************************/
/*
/* GESTION des LOTS de TRAVAIL (Workpackage) d'une activité pour les feuilles de temps
/* USAGE :
/*  - Fiche activité
/*
/**********************************************************************************************************************/



/* harmony default export */ var Workpackagevue_type_script_lang_js_ = ({
  components: {
    'workpackageperson': WorkpackagePerson
  },

  data() {
    return {
      mode: "read",
      canSave: false,
      formData: {
        id: -1,
        code: "",
        label: "",
        description: ""
      }
    }
  },
  created() {
    console.log("created", this.workpackage.id);
    if (this.workpackage.id < 0) {
      this.mode = "edit";
    }
  },
  props: {
    'workpackage': null,
    'persons': {
      default: function () {
        return []
      }
    },
    'editable': false,
    'isValidateur': false
  },

  watch: {
    'person.duration': function () {
      console.log('Modification de la durée')
    }
  },

  methods: {
    handlerEditWorkPackage() {
      this.formData = JSON.parse(JSON.stringify(this.workpackage));
      this.mode = 'edit';
    },

    handlerCancelEdit() {
      if (this.workpackage.id < 0) {
        this.$emit('workpackagecancelnew', this.workpackage);
      } else {
        this.mode = 'read';
      }
    },

    handlerDeleteWorkPackage() {
      this.$emit('workpackagedelete', this.workpackage);
    },

    handlerUpdateWorkPackage(e) {
      if (this.formData.code) {
        this.$emit('workpackageupdate', this.formData);
        this.mode = 'read';
      } else {
        e.stopPropagation();
        e.stopImmediatePropagation()
        return false;
      }
    },

    handlerUpdate(person, duration) {
      this.$emit('workpackagepersonupdate', person, duration);
    },

    handlerDelete(person) {
      this.$emit('workpackagepersondelete', person);
    },

    roles(person) {
      return person.roles.join(',');
    },

    tempsPrevu(person) {
      return 0;
    },

    tempsDeclare(person) {
      return 0;
    }

  }
});

// CONCATENATED MODULE: ./src/components/Workpackage.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_Workpackagevue_type_script_lang_js_ = (Workpackagevue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/Workpackage.vue





/* normalize component */

var Workpackage_component = normalizeComponent(
  components_Workpackagevue_type_script_lang_js_,
  Workpackagevue_type_template_id_a82ffeb4_render,
  Workpackagevue_type_template_id_a82ffeb4_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var Workpackage = (Workpackage_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/WorkpackageUI.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ var WorkpackageUIvue_type_script_lang_js_ = ({
    components: {
        'workpackage': Workpackage
    },

    data(){
        return {
            loading: false,
            errors: [],
            workpackages: [],
            persons: [],
            editable: false,
            isDeclarant: false,
            isValidateur: false,
            token: 'DEFAULT_TKN'
        }
    },

    props: {
        url: { required: true }, //'<?= $this->url('workpackage/rest', ['idactivity' => $entity->getId()]) ?>',
        token: { required: true }, // '<?= $tokenValue ?>',
        isValidateur:  { required: true }, //,
        editable:  { required: true }, //
        Bootbox: { required: true }
    },

    watch: {
    },
    computed: {
    },

    created () {
        this.fetch();
    },

    methods: {
        ////////////////////////////////////////////////////////////////////////
        // HANDLER
        handlerWorkPackageCancelNew(workpackage){
            this.workpackages.splice(this.workpackages.indexOf(workpackage), 1);
        },

        handlerWorkPackageNew(){
            this.workpackages.push({
                id: -1,
                code: "Nouveau Lot",
                label : "",
                persons: [],
                description: ""
            })
        },

        handlerWorkPackagePersonDelete(workpackageperson){
            this.Bootbox.confirm("Supprimer le déclarant ? ", (result) => {
                if( result ){
                    this.$http.delete(this.url+"?workpackagepersonid=" + workpackageperson.id).then(
                        (res) => {
                            this.fetch();
                        },
                        (err) => {
                            this.errors.push("Impossible de supprimer le déclarant : " + err.body);
                        }
                    );
                }
            });
        },

        handlerWorkPackageDelete(workpackage){
            this.Bootbox.confirm("Souhaitez-vous supprimer ce lot ?", (result) => {
                if( result ) {
                    this.$http.delete(this.url+"?workpackageid=" + workpackage.id).then(
                        (res) => {
                            this.fetch();
                        },
                        (err) => {
                            this.errors.push("Impossible de supprimer le lot : " + err.body);
                        }
                    );
                }
            });
        },

        handlerWorkPackageUpdate(workPackageData){
            var datas = new FormData();
            for( var key in workPackageData ){
                datas.append(key, workPackageData[key]);
            }
            if( workPackageData.id > 0 ){
                console.log("MAJ du LOT");
                // Mise à jour
                datas.append('workpackageid', workPackageData.id);
                this.$http.post(this.url, datas).then(
                    (res) => {
                        this.fetch();
                    },
                    (err) => {
                        this.errors.push("Impossible de mettre à jour le lot de travail : " + err.body);
                    }
                );
            } else {
                console.log("NOUVEAU du LOT");
                datas.append('workpackageid', -1);
                this.$http.put(this.url, datas).then(
                    (res) => {
                        this.fetch();
                    },
                    (err) => {
                        this.errors.push("Impossible de créer le lot de travail : " + err.body);
                    }
                ).then(foo=> this.fetch());
            }
        },

        handlerUpdateWorkPackagePerson(workpackageperson, duration){
            var datas = new FormData();
            datas.append('workpackagepersonid', workpackageperson.id);
            datas.append('duration', duration);
            this.$http.post(this.url, datas).then(
                (res) => {
                    workpackageperson.duration = duration;
                },
                (err) => {
                    this.errors.push("Impossible de mettre à jour les heures prévues : " + err.body);
                }
            );
        },

        addperson(personid, workpackageid){
            console.log(arguments);
            var data = new FormData();
            data.append('idworkpackage', workpackageid);
            data.append('idperson', personid);

            this.$http.put(this.url, data).then(
                (res) => {
                    this.fetch();
                },
                (err) => {
                    this.errors.push("Impossible d'ajouter le déclarant : " + err.body);
                }
            ).then(()=> this.loading = false );
        },

        fetch(){
            this.loading = true;
            console.log(this.url);
            this.$http.get(this.url).then(
                (res) => {
                    this.workpackages = res.body.workpackages;
                    this.persons = res.body.persons;
                    this.editable = res.body.editable;
                    this.isDeclarant = res.body.isDeclarant;
                    this.isValidateur = res.body.isValidateur;
                },
                (err) => {
                    this.errors.push("Impossible de charger les lots de travail : " + err.body);
                }
            ).then(()=> this.loading = false );

        }
    }
});

// CONCATENATED MODULE: ./src/WorkpackageUI.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_WorkpackageUIvue_type_script_lang_js_ = (WorkpackageUIvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/WorkpackageUI.vue





/* normalize component */

var WorkpackageUI_component = normalizeComponent(
  src_WorkpackageUIvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var WorkpackageUI = (WorkpackageUI_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (WorkpackageUI);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=WorkpackageUI.umd.js.map
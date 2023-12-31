(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ReplaceStrengthenPerson"] = factory();
	else
		root["ReplaceStrengthenPerson"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ReplaceStrengthenPerson.vue?vue&type=template&id=24406f82&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[(_vm.mode != '')?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content",style:(_vm.step == 1 ? 'overflow: visible;' : 'overflow: scroll;')},[_c('a',{staticClass:"overlay-closer",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.handlerCancel()}}},[_vm._v("X")]),_c('h1',[_vm._v(" "+_vm._s(_vm.mode)+" "),_c('strong',[_vm._v(_vm._s(_vm.person.displayname))])]),_c('div',{staticClass:"alert"},[_vm._v(" Cette écran va vous permettre de remplacer "),_c('strong',[_vm._v(_vm._s(_vm.person.displayname))]),_vm._v(" par une autre personne. Choisissez un remplaçant, un récapitulatif sera affiché avant confirmation. ")]),_c('div',{staticClass:"steps-bar"},[_c('div',{staticClass:"step",class:{'current': _vm.step == 1, 'done': _vm.step > 1}},[_vm._v(" Choisir une personne ")]),_c('div',{staticClass:"step",class:{'current': _vm.step == 2, 'done': _vm.step > 2, 'futur': _vm.step < 2}},[_vm._v(" Vérifier les affectations ")])]),_c('div',{staticClass:"step-content"},[(_vm.pendingMessage)?_c('div',{staticClass:"alert-info alert"},[_c('i',{staticClass:"animate-spin icon-spinner"}),_vm._v(" "+_vm._s(_vm.pendingMessage)+" ")]):_vm._e(),(_vm.errorMessage)?_c('div',{staticClass:"alert-danger alert"},[_c('i',{staticClass:"icon-attention-1"}),_c('strong',[_vm._v("Erreur")]),_vm._v(" : "+_vm._s(_vm.errorMessage)+" ")]):_vm._e(),(_vm.step == 1)?_c('div',[(!_vm.replacer_id)?_c('div',[_c('p',[_vm._v("Choisissez une personne :")]),_c('person-auto-completer',{staticStyle:{"z-index":"10"},on:{"change":_vm.handlerSelectPerson}})],1):_vm._e()]):_vm._e(),(_vm.step1info)?_c('div',{staticClass:"alert alert-info"},[_vm._v(" "+_vm._s(_vm.step1info)+" ")]):_vm._e(),(_vm.step == 1)?_c('nav',{staticClass:"buttons text-center"},[_c('button',{staticClass:"btn btn-success",class:{ 'disabled': !_vm.replacer_id },on:{"click":function($event){return _vm.handlerConfirmStep1()}}},[(_vm.pendingMessage)?_c('i',{staticClass:"icon-spinner animate-spin"}):_c('i',{staticClass:"icon-angle-right"}),_vm._v(" Vérifier les affectations ")])]):_vm._e(),_c('section',{},[(_vm.step == 2)?_c('section',{staticClass:"affectations"},[_c('section',[_vm._m(0),_c('section',{staticClass:"projects"},[_vm._m(1),(_vm.replacer_id)?_c('p',[_c('strong',[_vm._v(_vm._s(_vm.replacer))]),_vm._v(" va être ajouté dans le(s) "),_c('strong',[_vm._v(_vm._s(_vm.projects.length))]),_vm._v(" projet(s) suivant : ")]):_vm._e(),_c('div',{staticClass:"columns-3"},_vm._l((_vm.projects),function(p){return _c('div',{staticClass:"card xs applyable",class:{ 'apply': p.apply, 'active': p.active },on:{"click":function($event){return _vm.handlerSwitchApply(p)}}},[_c('div',{},[_c('strong',[_c('i',{staticClass:"icon-cubes"}),_vm._v(" "+_vm._s(p.label)+" ")])]),_vm._l((p.roles),function(role){return _c('span',{staticClass:"cartouche xs",class:{'past obsolete': !role.active, 'success': p.apply}},[_vm._v(" "+_vm._s(role.roleId)+" ")])})],2)}),0)]),_vm._m(2),_c('div',{staticClass:"columns-3"},_vm._l((_vm.activities),function(p){return _c('div',{staticClass:"card xs applyable",class:{ 'apply': p.apply, 'active': p.active },on:{"click":function($event){return _vm.handlerSwitchApply(p)}}},[_c('div',{},[_c('strong',[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(p.label)+" ")])]),_vm._l((p.roles),function(role){return _c('span',{staticClass:"cartouche xs",class:{'past obsolete': !role.active, 'success': p.apply}},[_vm._v(" "+_vm._s(role.roleId)+" ")])})],2)}),0)]),_c('section',[_vm._m(3),_vm._l((_vm.structures),function(p){return _c('div',{staticClass:"card xs applyable",class:{'apply': p.apply },on:{"click":function($event){return _vm.handlerSwitchApply(p)}}},[_c('strong',[_vm._v(_vm._s(p.label))]),_vm._l((p.roles),function(role){return _c('span',{staticClass:"cartouche xs",class:{'past obsolete': !role.active, 'success': p.apply}},[_vm._v(" "+_vm._s(role.roleId)+" ")])})],2)})],2),_c('section',[_vm._m(4),(_vm.validations.prj)?_c('div',[_vm._m(5),_vm._l((_vm.validations.prj),function(v){return _c('div',{staticClass:"card xs applyable",class:{'apply': v.apply },on:{"click":function($event){v.apply = !v.apply; _vm.console.log('OK')}}},[_c('strong',[_vm._v(_vm._s(v.acronym))]),_c('em',[_vm._v(_vm._s(v.label))])])})],2):_vm._e(),(_vm.validations.sci)?_c('div',[_vm._m(6),_vm._l((_vm.validations.sci),function(v){return _c('div',{staticClass:"card xs applyable",class:{'apply': v.apply },on:{"click":function($event){v.apply = !v.apply}}},[_c('strong',[_vm._v(_vm._s(v.acronym))]),_c('em',[_vm._v(_vm._s(v.label))])])})],2):_vm._e(),(_vm.validations.adm)?_c('div',[_vm._m(7),_vm._l((_vm.validations.adm),function(v){return _c('div',{staticClass:"card xs applyable",class:{'apply': v.apply },on:{"click":function($event){v.apply = !v.apply}}},[_c('strong',[_vm._v(_vm._s(v.acronym))]),_c('em',[_vm._v(_vm._s(v.label))])])})],2):_vm._e()]),_c('section',[_vm._m(8),_c('div',[_vm._m(9),_c('p',[_c('i',{staticClass:"icon-info-circled"}),_vm._v(" Personnes qui seront chargées de valider les déclaration "),_c('strong',[_vm._v("Hors-lot")]),_vm._v(" de "+_vm._s(_vm.person.displayname)+". ")]),_vm._l((_vm.subordinates),function(p){return _c('div',{staticClass:"card xs person-card",class:{'apply': p.apply}},[_c('div',{staticClass:"card-title"},[_c('img',{staticClass:"gravatar",attrs:{"src":'//www.gravatar.com/avatar/' +p.mailmd5 +'&s=20',"alt":"","width":"20"}}),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(" "+_vm._s(p.label)+" ")])]),_c('small',[_c('i',{staticClass:"icon-mail"}),_vm._v(" "+_vm._s(p.mail)+" ")])])})],2),_c('div',[_vm._m(10),_c('p',[_c('i',{staticClass:"icon-info-circled"}),_vm._v(" Personnes pour qui "),_c('strong',[_vm._v(_vm._s(_vm.person.displayname))]),_vm._v(" devra valider les déclarations "),_c('strong',[_vm._v("Hors-lot")])]),_vm._l((_vm.referents),function(p){return _c('div',{staticClass:"card xs person-card",class:{'apply': p.apply}},[_c('div',{staticClass:"card-title"},[_c('img',{staticClass:"gravatar",attrs:{"src":'//www.gravatar.com/avatar/' +p.mailmd5 +'&s=20',"alt":"","width":"20"}}),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(" "+_vm._s(p.label)+" ")])]),_c('small',[_c('i',{staticClass:"icon-mail"}),_vm._v(" "+_vm._s(p.mail)+" ")])])})],2)]),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-success",on:{"click":_vm.handlerConfirmReplace}},[_vm._v(" Terminé ")])])]):_vm._e()])])])]):_vm._e(),_c('div',{staticClass:"replace-strengthen-person actions text-center"},[_c('button',{staticClass:"btn btn-lg btn-info",on:{"click":_vm.handlerReplace}},[_c('i',{staticClass:"icon-rewind-outline"}),_vm._v(" Remplacer ")])])])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-beaker"}),_vm._v(" Activité/projet de recherche ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-cubes"}),_vm._v(" Projets")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-cube"}),_vm._v(" Activités")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-building-filled"}),_vm._v(" Structure ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Validation ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-cube"}),_vm._v("Validation projet")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-beaker"}),_vm._v("Validation scientifique")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-book"}),_vm._v("Validation administratif")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Hiérarchie ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-book"}),_vm._v("Reférent(s)")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h4',[_c('i',{staticClass:"icon-beaker"}),_vm._v("Subordonné(s)")])}]


// CONCATENATED MODULE: ./src/ReplaceStrengthenPerson.vue?vue&type=template&id=24406f82&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PersonAutoCompleter.vue?vue&type=template&id=595e3162&
var PersonAutoCompletervue_type_template_id_595e3162_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.expression),expression:"expression"}],attrs:{"type":"text"},domProps:{"value":(_vm.expression)},on:{"keyup":function($event){if(!$event.type.indexOf('key')&&_vm._k($event.keyCode,"enter",13,$event.key,"Enter")){ return null; }$event.preventDefault();return _vm.search.apply(null, arguments)},"input":function($event){if($event.target.composing){ return; }_vm.expression=$event.target.value}}}),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.loading),expression:"loading"}]},[_c('i',{staticClass:"icon-spinner animate-spin"})]),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.persons.length > 0 && _vm.showSelector),expression:"persons.length > 0 && showSelector"}],staticClass:"choose",staticStyle:{"position":"absolute","z-index":"3000","max-height":"400px","overflow":"hidden","overflow-y":"scroll"}},_vm._l((_vm.persons),function(c){return _c('div',{key:c.id,staticClass:"choice",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerSelectPerson(c)}}},[_c('div',{staticStyle:{"display":"block","width":"50px","height":"50px"}},[_c('img',{staticStyle:{"width":"100%"},attrs:{"src":'https://www.gravatar.com/avatar/'+c.mailMd5+'?s=50',"alt":c.displayname}})]),_c('div',{staticClass:"infos"},[_c('strong',{staticStyle:{"font-weight":"700","font-size":"1.1em","padding-left":"0"}},[_vm._v(_vm._s(c.displayname))]),_c('br'),_c('span',{staticStyle:{"font-weight":"100","font-size":".8em","padding-left":"0"}},[_c('i',{staticClass:"icon-location"}),_vm._v(" "+_vm._s(c.affectation)+" "),(c.ucbnSiteLocalisation)?_c('span',[_vm._v(" ~ "+_vm._s(c.ucbnSiteLocalisation))]):_vm._e()]),_c('br'),_c('em',{staticStyle:{"font-weight":"100","font-size":".8em"}},[_c('i',{staticClass:"icon-mail"}),_vm._v(_vm._s(c.email))])])])}),0),(_vm.error)?_c('div',{staticClass:"alert alert-danger"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" "+_vm._s(_vm.error)+" ")]):_vm._e()])}
var PersonAutoCompletervue_type_template_id_595e3162_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/PersonAutoCompleter.vue?vue&type=template&id=595e3162&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PersonAutoCompleter.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


let tempo;

/* harmony default export */ var PersonAutoCompletervue_type_script_lang_js_ = ({
  data() {
    return {
      url: "/person?l=m&q=",
      persons: [],
      expression: "",
      loading: false,
      selectedPerson: null,
      showSelector: true,
      request: null,
      error: ""
    }
  },
  watch: {
    expression(n, o) {

      if (n.length >= 2) {
        if (tempo) {
          clearTimeout(tempo);
        }
        tempo = setTimeout(() => {
          this.search();
        }, 500)

      }
    }
  },
  methods: {
    search() {
      this.loading = true;
      this.$http.get(this.url + this.expression, {
        before(r) {
          if (this.request) {
            this.request.abort();
          }
          this.request = r;
        }
      }).then(
          ok => {
            console.log(ok);
            this.persons = ok.body.datas;
            this.showSelector = true;
          },
          ko => {
            console.log(ko);
            if( ko.status == 403 ){
              this.error = "403 Unauthorized";
            }
            else if( ko.body ){
              this.error = ko.body;
            }
          }
      ).then(foo => {
        this.loading = false;
        this.request = null;
      });
    },
    handlerSelectPerson(data) {
      this.selectedPerson = data;
      this.showSelector = false;
      this.expression = "";
      this.$emit('change', data);
    }
  }
});

// CONCATENATED MODULE: ./src/components/PersonAutoCompleter.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_PersonAutoCompletervue_type_script_lang_js_ = (PersonAutoCompletervue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/components/PersonAutoCompleter.vue





/* normalize component */

var component = normalizeComponent(
  components_PersonAutoCompletervue_type_script_lang_js_,
  PersonAutoCompletervue_type_template_id_595e3162_render,
  PersonAutoCompletervue_type_template_id_595e3162_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var PersonAutoCompleter = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ReplaceStrengthenPerson.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

// MANUAL COMPILATION
// node node_modules/.bin/vue-cli-service build --name ReplaceStrengthenPerson --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/ReplaceStrengthenPerson.vue



/* harmony default export */ var ReplaceStrengthenPersonvue_type_script_lang_js_ = ({
  name: 'ReplaceStrengthenPerson',

  components: {
    PersonAutoCompleter: PersonAutoCompleter
  },

  props: {
    person: {
      required: true
    },
    urlReplace: {
      required: true
    },
    urlAffectation: {
      required: true
    },
    urlStrengthen: {
      required: true
    }
  },

  data() {
    return {
      mode: "",
      recap: null,
      recapPending: "",
      errorRecap: "",
      error: "",

      step: 1,

      pendingMessage: "",
      errorMessage: "",

      replacer: "",
      replacer_id: null,
      step1info: "",

      projects: {},
      activities: {},
      structures: {},
      validations: {},
      referents: {},
      subordinates: {}
    }
  },

  methods: {
    handlerReplace() {
      this.mode = 'Remplacer';
      this.loadingMessage = "Chargement de l'aperçu";
    },

    handlerSwitchApply(item) {
      console.log("handlerSwitchApply", item);
      item.apply = !item.apply;
    },

    /**
     * Récap des changements.
     *
     * @param e
     */
    handlerSelectPerson(e) {
      this.replacer = e.displayname;
      this.replacer_id = e.id;
      this.step1info = "Remplacer " + this.person.displayname + " par " + this.replacer;
    },

    handlerConfirmStep1(){
      this.pendingMessage = "Chargement des affectations...";
      this.$http.get(this.urlAffectation+"?person=" + this.replacer_id).then(
          ok => {
            this.step = 2;
            this.projects = ok.body.affectations.projects;
            this.activities = ok.body.affectations.activities;
            this.structures = ok.body.affectations.structures;
            this.validations = ok.body.affectations.validations;
            this.referents = ok.body.affectations.referents;
            this.subordinates = ok.body.affectations.subordinates;
          },
          ko => {
            this.errorMessage = ko.body;
          }

      ).then( () => {
        this.pendingMessage = "";
      });
    },

    handlerConfirmReplace() {
      let data = new FormData();
      let out = {
        'projects': this.projects,
        'activities': this.activities,
        'structures': this.structures,
        'validations': this.validations,
        'subordinates': this.subordinates,
        'referents': this.referents,
        'replacer_id': this.replacer_id
      };

      this.pendingMessage = "Remplacement...";
      this.step = 3;

      data.append("out", JSON.stringify(out));

      this.$http.post(this.urlAffectation, data).then(
          ok => {
            document.location.reload();
          }, ko => {
            if (ko.status == 403) {
              this.errorMessage = "Non-autorisé";
            } else {
              if (ko.body.error) {
                this.errorMessage = "Erreur : " + ko.body.error;
              } else {
                this.errorMessage = ko.body;
              }
            }
          }
      ).then( foo => {
        this.pendingMessage = "";
      })
    },

    handlerCancel() {
      this.step = 1;
      this.errorMessage = "";
      this.mode = "";
      this.replacer_id = null;
      this.replacer = "";
      this.step1info = "";
    },
  },


  // Lifecycle
  mounted() {
    // amazing void content
  }
});

// CONCATENATED MODULE: ./src/ReplaceStrengthenPerson.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ReplaceStrengthenPersonvue_type_script_lang_js_ = (ReplaceStrengthenPersonvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/ReplaceStrengthenPerson.vue





/* normalize component */

var ReplaceStrengthenPerson_component = normalizeComponent(
  src_ReplaceStrengthenPersonvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ReplaceStrengthenPerson = (ReplaceStrengthenPerson_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (ReplaceStrengthenPerson);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=ReplaceStrengthenPerson.umd.js.map
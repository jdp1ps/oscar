(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ActivityValidator"] = factory();
	else
		root["ActivityValidator"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityValidator.vue?vue&type=template&id=ef89602a&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"validators"},[(_vm.mode == 'select-person')?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content",staticStyle:{"overflow":"visible"}},[_c('div',{staticClass:"overlay-closer",on:{"click":function($event){_vm.mode = ''}}},[_vm._v(" x ")]),_c('h3',[_vm._v("Choisissez une personne : ")]),_c('personselector',{on:{"change":_vm.handlerPersonSelect}})],1)]):_vm._e(),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-8"},[_c('div',{staticClass:"validators"},[_vm._m(0),_vm._m(1),_c('section',{staticClass:"row"},[_c('div',{staticClass:"col-md-4"},[_vm._m(2),_c('validatorslist',{attrs:{"level":"prj","fixed":_vm.validatorsPrj,"inherits":_vm.validatorsPrjDefault},on:{"addperson":function($event){return _vm.handlerAddPerson($event)},"removeperson":function($event){return _vm.handlerRemove($event.person_id, $event.level)}}})],1),_c('div',{staticClass:"col-md-4"},[_vm._m(3),_c('validatorslist',{attrs:{"level":"sci","fixed":_vm.validatorsSci,"inherits":_vm.validatorsSciDefault},on:{"addperson":function($event){return _vm.handlerAddPerson($event)},"removeperson":function($event){return _vm.handlerRemove($event.person_id, $event.level)}}})],1),_c('div',{staticClass:"col-md-4"},[_vm._m(4),_c('validatorslist',{attrs:{"level":"adm","fixed":_vm.validatorsAdm,"inherits":_vm.validatorsAdmDefault},on:{"addperson":function($event){return _vm.handlerAddPerson($event)},"removeperson":function($event){return _vm.handlerRemove($event.person_id, $event.level)}}})],1)])])]),_c('div',{staticClass:"col-md-4"},[_c('section',{staticClass:"members"},[_vm._m(5),_vm._l((_vm.members),function(p){return _c('article',{staticClass:"personcard card"},[_c('h5',{staticClass:"personcard-header"},[_c('img',{staticClass:"personcard-gravatar",attrs:{"src":'//www.gravatar.com/avatar/' + p.mailMd5 +'?s=40',"alt":""}}),_c('div',{staticClass:"personcard-infos"},[_c('strong',[_vm._v(_vm._s(p.person))]),_c('br'),_c('small',[_c('i',{staticClass:"icon-mail"}),_vm._v(" "+_vm._s(p.mail)+" ")]),_c('br'),_vm._v(" Rôle(s) : "),_c('strong',[_vm._v(_vm._s(p.roles.join(', ')))])])])])})],2)])]),_c('section',{staticClass:"validations"},[_vm._m(6),_vm._l((_vm.validations),function(v){return _c('article',{staticClass:"card",class:'status-'+v.status},[_c('h5',[_c('i',{class:'icon-'+v.status}),_vm._v(" "+_vm._s(v.period)+" | "),_c('strong',[_vm._v(_vm._s(v.declarer))])]),_c('div',{staticClass:"row text-small"},[_c('div',{staticClass:"col-md-4"},[(v.validatedPrjBy)?_c('div',{staticClass:"cartouche success"},[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(v.validatedPrjBy)+" ")]):_c('div',{staticClass:"validators-todo"},[(v.status == 'send-prj')?_c('div',[_c('i',{staticClass:"text-info icon-cube"}),_vm._v(" A faire ")]):_c('div',{staticClass:"validators-todo"},[_c('i',{staticClass:"icon-hourglass-3"}),_vm._v(" En attente ")]),_vm._l((v.validatorsPrj),function(p){return _c('span',[_vm._v(_vm._s(p.person))])})],2)]),_c('div',{staticClass:"col-md-4"},[(v.validatedSciBy)?_c('div',{staticClass:"cartouche success"},[_c('i',{staticClass:"icon-beaker"}),_vm._v(" "+_vm._s(v.validatedPrjBy)+" ")]):_c('div',{staticClass:"validators-todo"},[(v.status == 'send-sci')?_c('div',[_c('i',{staticClass:"text-info icon-beaker"}),_vm._v(" A faire ")]):_c('div',{staticClass:"validators-todo"},[_c('i',{staticClass:"icon-hourglass-3"}),_vm._v(" En attente ")]),_vm._l((v.validatorsSci),function(p){return _c('span',[_vm._v(_vm._s(p.person))])})],2)]),_c('div',{staticClass:"col-md-4"},[(v.validatedAdmBy)?_c('div',{staticClass:"cartouche success"},[_c('i',{staticClass:"icon-book"}),_vm._v(" "+_vm._s(v.validatedPrjBy)+" ")]):_c('div',{staticClass:"validators-todo"},[(v.status == 'send-adm')?_c('div',[_c('i',{staticClass:"icon-book text-info"}),_vm._v(" A faire ")]):_c('div',{staticClass:"validators-todo"},[_c('i',{staticClass:"icon-hourglass-3"}),_vm._v(" En attente ")]),_vm._l((v.validatorsAdm),function(p){return _c('span',[_vm._v(_vm._s(p.person))])})],2)])])])})],2)])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-user-md"}),_vm._v(" Validateurs ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('p',{staticClass:"alert alert-info"},[_c('i',{staticClass:"icon-info-circled"}),_vm._v(" Les validateurs sont les personnes chargées de valider les créneaux "),_c('strong',[_vm._v("pour cette activité uniquement")]),_vm._v(". A noter que les déclarations "),_c('i',[_vm._v("Hors-Lots")]),_vm._v(" (Congès, enseignement) doivent être validées par le "),_c('strong',[_vm._v("N+1")]),_vm._v(" (visible depuis la fiche personne du déclarant). ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-cube"}),_vm._v(" Validation projet ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-beaker"}),_vm._v(" Validation scientifique ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-book"}),_vm._v(" Validation administrative ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-users"}),_vm._v(" Membres identifiés ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Validations ")])}]


// CONCATENATED MODULE: ./src/ActivityValidator.vue?vue&type=template&id=ef89602a&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PersonAutoCompleter.vue?vue&type=template&id=638ea58c&
var PersonAutoCompletervue_type_template_id_638ea58c_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.expression),expression:"expression"}],attrs:{"type":"text"},domProps:{"value":(_vm.expression)},on:{"keyup":function($event){if(!$event.type.indexOf('key')&&_vm._k($event.keyCode,"enter",13,$event.key,"Enter")){ return null; }$event.preventDefault();return _vm.search.apply(null, arguments)},"input":function($event){if($event.target.composing){ return; }_vm.expression=$event.target.value}}}),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.loading),expression:"loading"}]},[_c('i',{staticClass:"icon-spinner animate-spin"})]),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.persons.length > 0 && _vm.showSelector),expression:"persons.length > 0 && showSelector"}],staticClass:"choose",staticStyle:{"position":"absolute","z-index":"3000","max-height":"400px","overflow":"hidden","overflow-y":"scroll"}},_vm._l((_vm.persons),function(c){return _c('div',{key:c.id,staticClass:"choice",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerSelectPerson(c)}}},[_c('div',{staticStyle:{"display":"block","width":"50px","height":"50px"}},[_c('img',{staticStyle:{"width":"100%"},attrs:{"src":'https://www.gravatar.com/avatar/'+c.mailMd5+'?s=50',"alt":c.displayname}})]),_c('div',{staticClass:"infos"},[_c('strong',{staticStyle:{"font-weight":"700","font-size":"1.1em","padding-left":"0"}},[_vm._v(_vm._s(c.displayname))]),_c('br'),_c('span',{staticStyle:{"font-weight":"100","font-size":".8em","padding-left":"0"}},[_c('i',{staticClass:"icon-location"}),_vm._v(" "+_vm._s(c.affectation)+" "),(c.ucbnSiteLocalisation)?_c('span',[_vm._v(" ~ "+_vm._s(c.ucbnSiteLocalisation))]):_vm._e()]),_c('br'),_c('em',{staticStyle:{"font-weight":"100","font-size":".8em"}},[_c('i',{staticClass:"icon-mail"}),_vm._v(_vm._s(c.email))])])])}),0)])}
var PersonAutoCompletervue_type_template_id_638ea58c_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/PersonAutoCompleter.vue?vue&type=template&id=638ea58c&

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
      request: null
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
            this.persons = ok.body.datas;
            this.showSelector = true;
          },
          ko => {
            // OscarBus.message('Erreur de recherche sur la personne', 'error');
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
  PersonAutoCompletervue_type_template_id_638ea58c_render,
  PersonAutoCompletervue_type_template_id_638ea58c_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var PersonAutoCompleter = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/ValidatorsList.vue?vue&type=template&id=7bbac429&
var ValidatorsListvue_type_template_id_7bbac429_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',{staticClass:"validators-list"},[(_vm.fixed.length == 0)?_c('div',{staticClass:"alert alert-warning"},[(_vm.inherits.length == 0)?_c('div',{staticClass:"alert alert-danger"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Personne n'a été trouvé pour la validation, les déclarations ne seront jamais validées ")]):_c('div',[_vm._m(0),_c('ul',_vm._l((_vm.inherits),function(v){return _c('li',[_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(" "+_vm._s(v.person)+" ")])])}),0)])]):_c('section',{staticClass:"persons"},_vm._l((_vm.fixed),function(p){return _c('article',{staticClass:"personcard card"},[_c('h5',{staticClass:"personcard-header"},[_c('img',{staticClass:"personcard-gravatar",attrs:{"src":'//www.gravatar.com/avatar/' + p.mailMd5 +'?s=40',"alt":""}}),_c('div',{staticClass:"personcard-infos"},[_c('strong',[_vm._v(_vm._s(p.person))]),_c('br'),_c('small',[_c('i',{staticClass:"icon-mail"}),_vm._v(" "+_vm._s(p.mail)+" ")])])]),_c('nav',{staticClass:"buttons text-center"},[_c('button',{staticClass:"btn btn-danger btn-xs xs",on:{"click":function($event){return _vm.$emit('removeperson', { person_id: p.person_id, level: _vm.level })}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" Supprimer ")])])])}),0),_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){return _vm.$emit('addperson', _vm.level)}}},[_c('i',{staticClass:"icon-user"}),_vm._v(" Ajouter ")])])}
var ValidatorsListvue_type_template_id_7bbac429_staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('p',[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Aucun validateur désignés. Les personnes suivantes seront sollicitées automatiquement : ")])}]


// CONCATENATED MODULE: ./src/components/ValidatorsList.vue?vue&type=template&id=7bbac429&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/ValidatorsList.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var ValidatorsListvue_type_script_lang_js_ = ({
  props: {
    fixed: {
      type: Array,
      required: true
    },
    inherits: {
      type: Array,
      required: true
    },
    level: {
      type: String,
      required: true
    }
  }
});

// CONCATENATED MODULE: ./src/components/ValidatorsList.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_ValidatorsListvue_type_script_lang_js_ = (ValidatorsListvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/ValidatorsList.vue





/* normalize component */

var ValidatorsList_component = normalizeComponent(
  components_ValidatorsListvue_type_script_lang_js_,
  ValidatorsListvue_type_template_id_7bbac429_render,
  ValidatorsListvue_type_template_id_7bbac429_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ValidatorsList = (ValidatorsList_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityValidator.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_modules/.bin/gulp activityValidatorWatch

Pour compiler :
node node_modules/.bin/gulp activityValidator

 */

const WHERE_PRJ = 'prj';
const WHERE_SCI = 'sci';
const WHERE_ADM = 'adm';




/* harmony default export */ var ActivityValidatorvue_type_script_lang_js_ = ({

  components: {
    'personselector': PersonAutoCompleter,
    'validatorslist': ValidatorsList
  },

  props: {
    url: {required: true, default: ''},
    documentTypes: {required: true},
    urlDocumentType: {required: true}
  },

  data() {
    return {
      validatorsPrj: [],
      validatorsPrjDefault: [],
      validatorsSci: [],
      validatorsSciDefault: [],
      validatorsAdm: [],
      validatorsAdmDefault: [],
      workpackages: [],
      declarers: [],
      validations: [],
      members: [],
      where: null,
      mode: ""
    }
  },

  methods: {

    handlerAddWorkpackage() {
      console.log("Ajout d'un lot de travail");
    },

    handlerSuccess() {
      console.log('handlerSuccess', arguments);
    },

    handlerPersonSelect(person) {
      console.log('handlerPersonSelect', arguments);
      this.addPerson(person, this.where);
      this.mode = "";
      this.where = null;
    },

    handlerAddPerson(where) {
      console.log("handlerAddPerson(",where,")");
      this.where = where;
      this.mode = 'select-person';
    },

    handlerRemove(personId, where) {
      this.$http.delete(this.url + '?a=d&p=' + personId + '&w=' + where).then(
          ok => {
            this.fetch();
          },
          ko => {
            console.log(ko);
          }
      )
    },

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    addPerson(person, where) {
      let send = new FormData();
      send.append('person_id', person.id);
      send.append('where', where);
      this.$http.post(this.url, send).then(
          ok => {
            this.fetch();
          },
          ko => {
            console.log(ko);
          }
      )
    },

    fetch() {
      this.$http.get(this.url).then(
          ok => {
            this.validatorsPrj = ok.data.validators.validators_prj;
            this.validatorsPrjDefault = ok.data.validators.validators_prj_default;
            this.validatorsSci = ok.data.validators.validators_sci;
            this.validatorsSciDefault = ok.data.validators.validators_sci_default;
            this.validatorsAdm = ok.data.validators.validators_adm;
            this.validatorsAdmDefault = ok.data.validators.validators_adm_default;
            this.workpackages = ok.data.workpackages;
            this.members = ok.data.members;
            this.validations = ok.data.validations;
          },
          ko => {
            console.log(ko);
          }
      )
    }
  },

  mounted() {
    this.fetch();
  }

});

// CONCATENATED MODULE: ./src/ActivityValidator.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ActivityValidatorvue_type_script_lang_js_ = (ActivityValidatorvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/ActivityValidator.vue





/* normalize component */

var ActivityValidator_component = normalizeComponent(
  src_ActivityValidatorvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ActivityValidator = (ActivityValidator_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (ActivityValidator);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=ActivityValidator.umd.js.map
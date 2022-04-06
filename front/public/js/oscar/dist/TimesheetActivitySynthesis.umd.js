(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["TimesheetActivitySynthesis"] = factory();
	else
		root["TimesheetActivitySynthesis"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetActivitySynthesis.vue?vue&type=template&id=b4f97e9e&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',{staticClass:"validations-admin"},[_c('transition',{attrs:{"name":"fade"}},[(_vm.loading)?_c('div',{staticClass:"pending overlay"},[_c('div',{staticClass:"overlay-content"},[_c('i',{staticClass:"icon-spinner animate-spin"}),_vm._v(" "+_vm._s(_vm.loading)+" ")])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.error)?_c('div',{staticClass:"pending overlay"},[_c('div',{staticClass:"overlay-content"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" "+_vm._s(_vm.error)+" ")])]):_vm._e()]),_c('nav',[_vm._v(" Période ")]),_c('section',{staticClass:"synthesis heading"},[_c('div',{staticClass:"label-line"},[_c('span',{staticStyle:{"cursor":"pointer"},on:{"click":function($event){_vm.state = (_vm.state == 'period' ? 'person' : 'period')}}},[_c('i',{staticClass:"icon-angle-left",class:{'disabled': _vm.state == 'period' }}),(_vm.state == 'person')?_c('span',[_vm._v("Par Période")]):_vm._e(),(_vm.state == 'period')?_c('span',[_vm._v("Par Période")]):_vm._e(),_c('i',{staticClass:"icon-angle-right",class:{'disabled': _vm.state == 'person' }})])]),_vm._l((_vm.synthesis.headings.current.workpackages),function(wp){return _c('div',{staticClass:"main research"},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(wp.label))])])}),_vm._m(0),_vm._l((_vm.synthesis.headings.prjs.prjs),function(prj){return _c('div',{staticClass:"research",attrs:{"title":prj.label}},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(prj.label))])])}),_vm._l((_vm.synthesis.headings.others),function(other){return _c('div',{class:other.group,attrs:{"title":other.label}},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(other.label))])])}),_vm._m(1)],2),_vm._l((_vm.facet),function(entry,key){return _c('section',{staticClass:"synthesis"},[_c('div',{staticClass:"label-line"},[_vm._v(" "+_vm._s(entry.label)+" "),_c('a',{attrs:{"href":'/feuille-de-temps/synthesisactivity?activity_id=' +_vm.synthesis.activity_id +'&format=pdf&period=' + key}},[_c('i',{staticClass:"icon-file-pdf"}),_vm._v(" Télécharger ")])]),_vm._l((entry.datas.current.workpackages),function(wp){return _c('div',{staticClass:"main research",attrs:{"title":wp.code +' - ' +wp.label}},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(wp.total)))])])}),_c('div',{staticClass:"main research total"},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(entry.datas.current.total)))])]),_vm._l((entry.datas.prjs),function(prj){return _c('div',{staticClass:"research",attrs:{"title":prj.label}},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(prj.total)))])])}),_vm._l((entry.datas.others),function(other){return _c('div',{class:other.group,attrs:{"title":other.label}},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(other.total)))])])}),_c('div',{staticClass:"total"},[_vm._v(" "+_vm._s(_vm._f("duration")(entry.total))+" ")])],2)}),_c('section',{staticClass:"synthesis heading sum"},[_c('div',{staticClass:"label-line"},[_vm._v(" Total ")]),_vm._l((_vm.synthesis.headings.current.workpackages),function(wp){return _c('div',{staticClass:"main research"},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(wp.total)))])])}),_c('div',{staticClass:"main research total"},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(_vm.synthesis.headings.current.total)))])]),_vm._l((_vm.synthesis.headings.prjs.prjs),function(prj){return _c('div',{staticClass:"research",attrs:{"title":prj.label}},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(prj.total)))])])}),_vm._l((_vm.synthesis.headings.others),function(other){return _c('div',{class:other.group,attrs:{"title":other.label}},[_c('span',{staticClass:"value hours"},[_vm._v(_vm._s(_vm._f("duration")(other.total)))])])}),_c('div',{staticClass:"total"},[_c('span',{staticClass:"value"},[_vm._v(" "+_vm._s(_vm._f("duration")(_vm.synthesis.headings.total))+" ")])])],2)],2)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"main research total"},[_c('span',{staticClass:"value hours"},[_vm._v("Total")])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"total"},[_c('span',{staticClass:"value"},[_vm._v(" Total ")])])}]


// CONCATENATED MODULE: ./src/TimesheetActivitySynthesis.vue?vue&type=template&id=b4f97e9e&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


// node node_modules/.bin/vue-cli-service build --name TimesheetActivitySynthesis --dest public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/TimesheetActivitySynthesis.vue

const STATE_PERIOD = "period";
const STATE_PERSON = "person";

/* harmony default export */ var TimesheetActivitySynthesisvue_type_script_lang_js_ = ({
    name: 'TimesheetActivitySynthesis',

    props: {
      initialdata: {
        default: null,
        required: true
      }
    },

    components: {

    },

    data() {
        return {
            loading: null,
            state: STATE_PERSON
        }
    },

    computed: {
      synthesis(){
        return this.initialdata;
      },
      facet(){
        if( this.state == STATE_PERIOD )
          return this.synthesis.by_periods;
        else
          return this.synthesis.by_persons;
      }
    },

    filters: {
      duration(v){
        let h = Math.floor(v);
        let m = Math.round(60 * (v - h));
        return h + "h" +m;
      }
    },

    methods: {

        fetch(clear = true) {
            // this.loading = "Chargement des données";
            //
            // this.$http.get('').then(
            //     ok => {
            //         for( let item in ok.body.periods ){
            //             ok.body.periods[item].open = false;
            //         }
            //         this.declarations = ok.body.periods;
            //         this.declarers = ok.body.declarants;
            //     },
            //     ko => {
            //         this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
            //     }
            // ).then(foo => {
            //     this.loading = false
            // });
        }
    },

    mounted() {
      console.log('INITIALDATA', this.initialdata);
        this.fetch(true)
    }
});

// CONCATENATED MODULE: ./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_TimesheetActivitySynthesisvue_type_script_lang_js_ = (TimesheetActivitySynthesisvue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/TimesheetActivitySynthesis.vue





/* normalize component */

var component = normalizeComponent(
  src_TimesheetActivitySynthesisvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var TimesheetActivitySynthesis = (component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (TimesheetActivitySynthesis);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=TimesheetActivitySynthesis.umd.js.map
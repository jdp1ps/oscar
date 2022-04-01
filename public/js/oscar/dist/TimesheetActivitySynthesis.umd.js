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
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@soda/get-current-script/index.js":
/*!********************************************************!*\
  !*** ./node_modules/@soda/get-current-script/index.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;// addapted from the document.currentScript polyfill by Adam Miller\n// MIT license\n// source: https://github.com/amiller-gh/currentScript-polyfill\n\n// added support for Firefox https://bugzilla.mozilla.org/show_bug.cgi?id=1620505\n\n(function (root, factory) {\n  if (true) {\n    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),\n\t\t\t\t__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?\n\t\t\t\t(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),\n\t\t\t\t__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));\n  } else {}\n}(typeof self !== 'undefined' ? self : this, function () {\n  function getCurrentScript () {\n    var descriptor = Object.getOwnPropertyDescriptor(document, 'currentScript')\n    // for chrome\n    if (!descriptor && 'currentScript' in document && document.currentScript) {\n      return document.currentScript\n    }\n\n    // for other browsers with native support for currentScript\n    if (descriptor && descriptor.get !== getCurrentScript && document.currentScript) {\n      return document.currentScript\n    }\n  \n    // IE 8-10 support script readyState\n    // IE 11+ & Firefox support stack trace\n    try {\n      throw new Error();\n    }\n    catch (err) {\n      // Find the second match for the \"at\" string to get file src url from stack.\n      var ieStackRegExp = /.*at [^(]*\\((.*):(.+):(.+)\\)$/ig,\n        ffStackRegExp = /@([^@]*):(\\d+):(\\d+)\\s*$/ig,\n        stackDetails = ieStackRegExp.exec(err.stack) || ffStackRegExp.exec(err.stack),\n        scriptLocation = (stackDetails && stackDetails[1]) || false,\n        line = (stackDetails && stackDetails[2]) || false,\n        currentLocation = document.location.href.replace(document.location.hash, ''),\n        pageSource,\n        inlineScriptSourceRegExp,\n        inlineScriptSource,\n        scripts = document.getElementsByTagName('script'); // Live NodeList collection\n  \n      if (scriptLocation === currentLocation) {\n        pageSource = document.documentElement.outerHTML;\n        inlineScriptSourceRegExp = new RegExp('(?:[^\\\\n]+?\\\\n){0,' + (line - 2) + '}[^<]*<script>([\\\\d\\\\D]*?)<\\\\/script>[\\\\d\\\\D]*', 'i');\n        inlineScriptSource = pageSource.replace(inlineScriptSourceRegExp, '$1').trim();\n      }\n  \n      for (var i = 0; i < scripts.length; i++) {\n        // If ready state is interactive, return the script tag\n        if (scripts[i].readyState === 'interactive') {\n          return scripts[i];\n        }\n  \n        // If src matches, return the script tag\n        if (scripts[i].src === scriptLocation) {\n          return scripts[i];\n        }\n  \n        // If inline source matches, return the script tag\n        if (\n          scriptLocation === currentLocation &&\n          scripts[i].innerHTML &&\n          scripts[i].innerHTML.trim() === inlineScriptSource\n        ) {\n          return scripts[i];\n        }\n      }\n  \n      // If no match, return null\n      return null;\n    }\n  };\n\n  return getCurrentScript\n}));\n\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./node_modules/@soda/get-current-script/index.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js":
/*!***********************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js ***!
  \***********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./src/TimesheetActivitySynthesis.vue\");\n/* empty/unused harmony star reexport */\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_entry__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (true) {\n    var getCurrentScript = __webpack_require__(/*! @soda/get-current-script */ \"./node_modules/@soda/get-current-script/index.js\")\n    currentScript = getCurrentScript()\n\n    // for backward compatibility, because previously we directly included the polyfill\n    if (!('currentScript' in document)) {\n      Object.defineProperty(document, 'currentScript', { get: getCurrentScript })\n    }\n  }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n// node node_modules/.bin/vue-cli-service build --name TimesheetActivitySynthesis --dest public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/TimesheetActivitySynthesis.vue\n\nconst STATE_PERIOD = \"period\";\nconst STATE_PERSON = \"person\";\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n    name: 'TimesheetActivitySynthesis',\n\n    props: {\n      initialdata: {\n        default: null,\n        required: true\n      }\n    },\n\n    components: {\n\n    },\n\n    data() {\n        return {\n            loading: null,\n            state: STATE_PERIOD\n        }\n    },\n\n    computed: {\n      synthesis(){\n        return this.initialdata;\n      },\n      facet(){\n        if( this.state == STATE_PERIOD )\n          return this.synthesis.by_periods;\n        else\n          return this.synthesis.by_persons;\n      }\n    },\n\n    filters: {\n      duration(v){\n        let h = Math.floor(v);\n        let m = Math.round(60 * (v - h));\n        return h + \"h\" +m;\n      }\n    },\n\n    methods: {\n\n        fetch(clear = true) {\n            // this.loading = \"Chargement des données\";\n            //\n            // this.$http.get('').then(\n            //     ok => {\n            //         for( let item in ok.body.periods ){\n            //             ok.body.periods[item].open = false;\n            //         }\n            //         this.declarations = ok.body.periods;\n            //         this.declarers = ok.body.declarants;\n            //     },\n            //     ko => {\n            //         this.error = AjaxResolve.resolve('Impossible de charger les données', ko);\n            //     }\n            // ).then(foo => {\n            //     this.loading = false\n            // });\n        }\n    },\n\n    mounted() {\n      console.log('INITIALDATA', this.initialdata);\n        this.fetch(true)\n    }\n});\n\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./src/TimesheetActivitySynthesis.vue?./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"4773b33c-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec&":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"4773b33c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec& ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"section\",\n    { staticClass: \"validations-admin\" },\n    [\n      _c(\"transition\", { attrs: { name: \"fade\" } }, [\n        _vm.loading\n          ? _c(\"div\", { staticClass: \"pending overlay\" }, [\n              _c(\"div\", { staticClass: \"overlay-content\" }, [\n                _c(\"i\", { staticClass: \"icon-spinner animate-spin\" }),\n                _vm._v(\" \" + _vm._s(_vm.loading) + \" \"),\n              ]),\n            ])\n          : _vm._e(),\n      ]),\n      _c(\"transition\", { attrs: { name: \"fade\" } }, [\n        _vm.error\n          ? _c(\"div\", { staticClass: \"pending overlay\" }, [\n              _c(\"div\", { staticClass: \"overlay-content\" }, [\n                _c(\"i\", { staticClass: \"icon-attention-1\" }),\n                _vm._v(\" \" + _vm._s(_vm.error) + \" \"),\n              ]),\n            ])\n          : _vm._e(),\n      ]),\n      _c(\n        \"section\",\n        { staticClass: \"synthesis heading\" },\n        [\n          _c(\"div\", { staticClass: \"label-line\" }, [\n            _c(\n              \"span\",\n              {\n                staticStyle: { cursor: \"pointer\" },\n                on: {\n                  click: function ($event) {\n                    _vm.state = _vm.state == \"period\" ? \"person\" : \"period\"\n                  },\n                },\n              },\n              [\n                _c(\"i\", {\n                  staticClass: \"icon-angle-left\",\n                  class: { disabled: _vm.state == \"period\" },\n                }),\n                _vm.state == \"person\"\n                  ? _c(\"span\", [_vm._v(\"Par Période\")])\n                  : _vm._e(),\n                _vm.state == \"period\"\n                  ? _c(\"span\", [_vm._v(\"Par Période\")])\n                  : _vm._e(),\n                _c(\"i\", {\n                  staticClass: \"icon-angle-right\",\n                  class: { disabled: _vm.state == \"person\" },\n                }),\n              ]\n            ),\n          ]),\n          _vm._l(_vm.synthesis.headings.current.workpackages, function (wp) {\n            return _c(\"div\", { staticClass: \"main research\" }, [\n              _c(\"span\", { staticClass: \"value hours\" }, [\n                _vm._v(_vm._s(wp.label)),\n              ]),\n            ])\n          }),\n          _vm._m(0),\n          _vm._l(_vm.synthesis.headings.prjs.prjs, function (prj) {\n            return _c(\n              \"div\",\n              { staticClass: \"research\", attrs: { title: prj.label } },\n              [\n                _c(\"span\", { staticClass: \"value hours\" }, [\n                  _vm._v(_vm._s(prj.label)),\n                ]),\n              ]\n            )\n          }),\n          _vm._l(_vm.synthesis.headings.others, function (other) {\n            return _c(\n              \"div\",\n              { class: other.group, attrs: { title: other.label } },\n              [\n                _c(\"span\", { staticClass: \"value hours\" }, [\n                  _vm._v(_vm._s(other.label)),\n                ]),\n              ]\n            )\n          }),\n          _vm._m(1),\n        ],\n        2\n      ),\n      _vm._l(_vm.facet, function (entry, key) {\n        return _c(\n          \"section\",\n          { staticClass: \"synthesis\" },\n          [\n            _c(\"div\", { staticClass: \"label-line\" }, [\n              _vm._v(\" \" + _vm._s(entry.label) + \" \"),\n              _c(\n                \"a\",\n                {\n                  attrs: {\n                    href:\n                      \"/feuille-de-temps/synthesisactivity?activity_id=\" +\n                      _vm.synthesis.activity_id +\n                      \"&format=pdf&period=\" +\n                      key,\n                  },\n                },\n                [\n                  _c(\"i\", { staticClass: \"icon-file-pdf\" }),\n                  _vm._v(\" Télécharger \"),\n                ]\n              ),\n            ]),\n            _vm._l(entry.datas.current.workpackages, function (wp) {\n              return _c(\n                \"div\",\n                {\n                  staticClass: \"main research\",\n                  attrs: { title: wp.code + \" - \" + wp.label },\n                },\n                [\n                  _c(\"span\", { staticClass: \"value hours\" }, [\n                    _vm._v(_vm._s(_vm._f(\"duration\")(wp.total))),\n                  ]),\n                ]\n              )\n            }),\n            _c(\"div\", { staticClass: \"main research total\" }, [\n              _c(\"span\", { staticClass: \"value hours\" }, [\n                _vm._v(_vm._s(_vm._f(\"duration\")(entry.datas.current.total))),\n              ]),\n            ]),\n            _vm._l(entry.datas.prjs, function (prj) {\n              return _c(\n                \"div\",\n                { staticClass: \"research\", attrs: { title: prj.label } },\n                [\n                  _c(\"span\", { staticClass: \"value hours\" }, [\n                    _vm._v(_vm._s(_vm._f(\"duration\")(prj.total))),\n                  ]),\n                ]\n              )\n            }),\n            _vm._l(entry.datas.others, function (other) {\n              return _c(\n                \"div\",\n                { class: other.group, attrs: { title: other.label } },\n                [\n                  _c(\"span\", { staticClass: \"value hours\" }, [\n                    _vm._v(_vm._s(_vm._f(\"duration\")(other.total))),\n                  ]),\n                ]\n              )\n            }),\n            _c(\"div\", { staticClass: \"total\" }, [\n              _vm._v(\" \" + _vm._s(_vm._f(\"duration\")(entry.total)) + \" \"),\n            ]),\n          ],\n          2\n        )\n      }),\n      _c(\n        \"section\",\n        { staticClass: \"synthesis heading sum\" },\n        [\n          _c(\"div\", { staticClass: \"label-line\" }, [_vm._v(\" Total \")]),\n          _vm._l(_vm.synthesis.headings.current.workpackages, function (wp) {\n            return _c(\"div\", { staticClass: \"main research\" }, [\n              _c(\"span\", { staticClass: \"value hours\" }, [\n                _vm._v(_vm._s(_vm._f(\"duration\")(wp.total))),\n              ]),\n            ])\n          }),\n          _c(\"div\", { staticClass: \"main research total\" }, [\n            _c(\"span\", { staticClass: \"value hours\" }, [\n              _vm._v(\n                _vm._s(_vm._f(\"duration\")(_vm.synthesis.headings.current.total))\n              ),\n            ]),\n          ]),\n          _vm._l(_vm.synthesis.headings.prjs.prjs, function (prj) {\n            return _c(\n              \"div\",\n              { staticClass: \"research\", attrs: { title: prj.label } },\n              [\n                _c(\"span\", { staticClass: \"value hours\" }, [\n                  _vm._v(_vm._s(_vm._f(\"duration\")(prj.total))),\n                ]),\n              ]\n            )\n          }),\n          _vm._l(_vm.synthesis.headings.others, function (other) {\n            return _c(\n              \"div\",\n              { class: other.group, attrs: { title: other.label } },\n              [\n                _c(\"span\", { staticClass: \"value hours\" }, [\n                  _vm._v(_vm._s(_vm._f(\"duration\")(other.total))),\n                ]),\n              ]\n            )\n          }),\n          _c(\"div\", { staticClass: \"total\" }, [\n            _c(\"span\", { staticClass: \"value\" }, [\n              _vm._v(\n                \" \" +\n                  _vm._s(_vm._f(\"duration\")(_vm.synthesis.headings.total)) +\n                  \" \"\n              ),\n            ]),\n          ]),\n        ],\n        2\n      ),\n    ],\n    2\n  )\n}\nvar staticRenderFns = [\n  function () {\n    var _vm = this\n    var _h = _vm.$createElement\n    var _c = _vm._self._c || _h\n    return _c(\"div\", { staticClass: \"main research total\" }, [\n      _c(\"span\", { staticClass: \"value hours\" }, [_vm._v(\"Total\")]),\n    ])\n  },\n  function () {\n    var _vm = this\n    var _h = _vm.$createElement\n    var _c = _vm._self._c || _h\n    return _c(\"div\", { staticClass: \"total\" }, [\n      _c(\"span\", { staticClass: \"value\" }, [_vm._v(\" Total \")]),\n    ])\n  },\n]\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./src/TimesheetActivitySynthesis.vue?./node_modules/cache-loader/dist/cjs.js?%7B%22cacheDirectory%22:%22node_modules/.cache/vue-loader%22,%22cacheIdentifier%22:%224773b33c-vue-loader-template%22%7D!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return normalizeComponent; });\n/* globals __VUE_SSR_CONTEXT__ */\n\n// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).\n// This module is a runtime utility for cleaner component module output and will\n// be included in the final webpack user bundle.\n\nfunction normalizeComponent (\n  scriptExports,\n  render,\n  staticRenderFns,\n  functionalTemplate,\n  injectStyles,\n  scopeId,\n  moduleIdentifier, /* server only */\n  shadowMode /* vue-cli only */\n) {\n  // Vue.extend constructor export interop\n  var options = typeof scriptExports === 'function'\n    ? scriptExports.options\n    : scriptExports\n\n  // render functions\n  if (render) {\n    options.render = render\n    options.staticRenderFns = staticRenderFns\n    options._compiled = true\n  }\n\n  // functional template\n  if (functionalTemplate) {\n    options.functional = true\n  }\n\n  // scopedId\n  if (scopeId) {\n    options._scopeId = 'data-v-' + scopeId\n  }\n\n  var hook\n  if (moduleIdentifier) { // server build\n    hook = function (context) {\n      // 2.3 injection\n      context =\n        context || // cached call\n        (this.$vnode && this.$vnode.ssrContext) || // stateful\n        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional\n      // 2.2 with runInNewContext: true\n      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {\n        context = __VUE_SSR_CONTEXT__\n      }\n      // inject component styles\n      if (injectStyles) {\n        injectStyles.call(this, context)\n      }\n      // register component module identifier for async chunk inferrence\n      if (context && context._registeredComponents) {\n        context._registeredComponents.add(moduleIdentifier)\n      }\n    }\n    // used by ssr in case component is cached and beforeCreate\n    // never gets called\n    options._ssrRegister = hook\n  } else if (injectStyles) {\n    hook = shadowMode\n      ? function () {\n        injectStyles.call(\n          this,\n          (options.functional ? this.parent : this).$root.$options.shadowRoot\n        )\n      }\n      : injectStyles\n  }\n\n  if (hook) {\n    if (options.functional) {\n      // for template-only hot-reload because in that case the render fn doesn't\n      // go through the normalizer\n      options._injectStyles = hook\n      // register for functional component in vue file\n      var originalRender = options.render\n      options.render = function renderWithStyleInjection (h, context) {\n        hook.call(context)\n        return originalRender(h, context)\n      }\n    } else {\n      // inject component registration as beforeCreate hook\n      var existing = options.beforeCreate\n      options.beforeCreate = existing\n        ? [].concat(existing, hook)\n        : [hook]\n    }\n  }\n\n  return {\n    exports: scriptExports,\n    options: options\n  }\n}\n\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./node_modules/vue-loader/lib/runtime/componentNormalizer.js?");

/***/ }),

/***/ "./src/TimesheetActivitySynthesis.vue":
/*!********************************************!*\
  !*** ./src/TimesheetActivitySynthesis.vue ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _TimesheetActivitySynthesis_vue_vue_type_template_id_72360dec___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec& */ \"./src/TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec&\");\n/* harmony import */ var _TimesheetActivitySynthesis_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TimesheetActivitySynthesis.vue?vue&type=script&lang=js& */ \"./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _TimesheetActivitySynthesis_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _TimesheetActivitySynthesis_vue_vue_type_template_id_72360dec___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _TimesheetActivitySynthesis_vue_vue_type_template_id_72360dec___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"src/TimesheetActivitySynthesis.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./src/TimesheetActivitySynthesis.vue?");

/***/ }),

/***/ "./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js&":
/*!*********************************************************************!*\
  !*** ./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js& ***!
  \*********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TimesheetActivitySynthesis_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../node_modules/cache-loader/dist/cjs.js??ref--1-0!../node_modules/vue-loader/lib??vue-loader-options!./TimesheetActivitySynthesis.vue?vue&type=script&lang=js& */ \"./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TimesheetActivitySynthesis.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TimesheetActivitySynthesis_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./src/TimesheetActivitySynthesis.vue?");

/***/ }),

/***/ "./src/TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec&":
/*!***************************************************************************!*\
  !*** ./src/TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec& ***!
  \***************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_4773b33c_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TimesheetActivitySynthesis_vue_vue_type_template_id_72360dec___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"4773b33c-vue-loader-template\"}!../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../node_modules/cache-loader/dist/cjs.js??ref--1-0!../node_modules/vue-loader/lib??vue-loader-options!./TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec& */ \"./node_modules/cache-loader/dist/cjs.js?{\\\"cacheDirectory\\\":\\\"node_modules/.cache/vue-loader\\\",\\\"cacheIdentifier\\\":\\\"4773b33c-vue-loader-template\\\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TimesheetActivitySynthesis.vue?vue&type=template&id=72360dec&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_4773b33c_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TimesheetActivitySynthesis_vue_vue_type_template_id_72360dec___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_4773b33c_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TimesheetActivitySynthesis_vue_vue_type_template_id_72360dec___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack://TimesheetActivitySynthesis/./src/TimesheetActivitySynthesis.vue?");

/***/ })

/******/ })["default"];
});
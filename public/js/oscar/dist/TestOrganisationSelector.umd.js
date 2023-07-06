(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["TestOrganisationSelector"] = factory();
	else
		root["TestOrganisationSelector"] = factory();
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

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js":
/*!***********************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js ***!
  \***********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./src/TestOrganisationSelector.vue\");\n/* empty/unused harmony star reexport */\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_entry__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TestOrganisationSelector.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TestOrganisationSelector.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _components_OrganizationAutoCompleter2__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/OrganizationAutoCompleter2 */ \"./src/components/OrganizationAutoCompleter2.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n/**\nnode node_modules/.bin/vue-cli-service build --name TestOrganisationSelector --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/TestOrganisationSelector.vue\n**/\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  components: {\n    organizationselector: _components_OrganizationAutoCompleter2__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n  },\n\n  props: {\n\n  },\n\n  data() {\n    return {\n      selected: null,\n    };\n  },\n\n  computed: {\n\n  },\n\n  methods: {\n\n    handlerEnrolledSelected(data) {\n      console.log(data);\n    }\n  },\n\n  mounted() {\n\n  }\n});\n\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/TestOrganisationSelector.vue?./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/OrganizationAutoCompleter2.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/OrganizationAutoCompleter2.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n\n  props: {\n    value: {default: null}\n  },\n  emits: ['update:value'],\n\n  components: {\n\n  },\n\n  data() {\n    return {\n      // Liste des structures\n      options: [],\n\n      lastUpdatedSearch: 0,\n      delay: null,\n      preloadedValue: false,\n      selectedValue: null,\n      selectedLabel : \"\",\n      displayClosed: false,\n      showSelector: false,\n      searchFor: \"\",\n      latency: null,\n      highlightedIndex: null,\n      loading: false,\n      inside: false,\n      displayValue: false,\n      error: \"\"\n    }\n  },\n\n  computed: {\n    filteredOptions(){\n      let opts = [];\n      if( this.displayClosed ){\n        return this.options;\n      } else {\n        this.options.forEach(item => {\n          if( !item.closed ){\n            opts.push(item);\n          }\n        });\n        return opts;\n      }\n\n    }\n  },\n\n  mounted() {\n    // Détection d'un valeur initiale\n\n    document.addEventListener('click', this.handlerGlobalClick, true);\n\n    if (this.value) {\n      //   this.selectedValue = this.value;\n      //   this.options.push({\n      //     id: this.value,\n      //     label: \"Waiting for data\"\n      //   })\n      //   this.value = null;\n      //   this.searchOrganization(null, 'id:' + this.selectedValue);\n      // } else {\n      //   this.preloadedValue = true;\n    }\n  },\n\n  // todo : unmount\n\n  methods: {\n    handlerGlobalClick(evt){\n      if( !this.inside ){\n        this.showSelector = false;\n        this.displayValue = true;\n      } else {\n        this.displayValue = false;\n        this.showSelector = true;\n      }\n    },\n\n    handlerMouseLeave(){\n      console.log(\"LEAVE\");\n      this.inside = false;\n    },\n\n    handlerMouseEnter(){\n      console.log(\"ENTER\");\n      this.inside = true;\n    },\n\n    handlerClick(e){\n      console.log(\"CLICK\");\n      this.displayValue = false;\n    },\n\n    handlerUnselect(){\n      this.selectedValue = null;\n      this.selectedLabel = \"\";\n      this.showSelector = false;\n      this.displayValue = false;\n    },\n\n    handlerSelectIndex(index){\n      console.log(\"SELECT\");\n      this.selectedValue = this.options[index].id;\n      this.selectedLabel = this.options[index].label;\n      this.showSelector = false;\n      this.displayValue = true;\n      this.$emit('change', this.selectedValue);\n      this.$emit('update:value', this.selectedValue);\n      this.$emit('input', this.selectedValue);\n    },\n\n    handlerSelectPrev( scroll = false ){\n      if( this.highlightedIndex == 0 ){\n        this.showSelector = false;\n      }\n      if( this.highlightedIndex > 0 ){\n        if(!this.showSelector) this.showSelector = true;\n        this.highlightedIndex--;\n      }\n      if( scroll == true ){\n        let itemId = '#item_' + this.highlightedIndex;\n        let item = this.$el.querySelector(itemId);\n        let el = this.$el.querySelectorAll('.options')[0];\n        el.scrollTop = item.offsetTop;\n        console.log(\"SCROLL\", el.scrollTop);\n        console.log(\"ITEM\", itemId, item);\n      }\n    },\n\n    handlerSelectNext( scroll = false ){\n      if(!this.showSelector) {\n        this.showSelector = true;\n      } else {\n        if( this.highlightedIndex < this.options.length - 1 ){\n          this.highlightedIndex++;\n        }\n      }\n      if( scroll == true ){\n        let itemId = '#item_' + this.highlightedIndex;\n        let item = this.$el.querySelector(itemId);\n        let el = this.$el.querySelectorAll('.options')[0];\n        el.scrollTop = item.offsetTop;\n        console.log(\"SCROLL\", el.scrollTop);\n        console.log(\"ITEM\", itemId, item);\n      }\n    },\n\n    handlerKeyUp(e){\n      switch(e.code){\n        case \"ArrowUp\":\n          this.handlerSelectPrev(true);\n          console.log(\"ArrowUp\");\n          break;\n        case \"ArrowDown\":\n          this.handlerSelectNext(true);\n          console.log(\"ArrowDown\");\n          break;\n        case \"ArrowLeft\":\n        case \"ArrowRight\":\n          break;\n\n        case \"Enter\":\n          e.preventDefault();\n          this.handlerSelectIndex(this.highlightedIndex);\n          break;\n\n        default:\n          console.log(e.code);\n          if( this.searchFor && this.searchFor.length > 1 ){\n            this.handlerChange();\n          }\n      }\n    },\n\n    handlerChange(e){\n      console.log(\"Changement \", this.searchFor);\n      // Système de retardement Eco+\n      if (this.latency != null) {\n        clearTimeout(this.latency);\n      }\n      let delayFunction = function () {\n        console.log(\"delayFunction\");\n        this.search();\n        clearTimeout(this.latency);\n      }.bind(this);\n      this.latency = setTimeout(delayFunction, 1000);\n    },\n\n    search(){\n      console.log(\"Recherche lancée\")\n      this.loading = true;\n      this.showSelector = false;\n      this.$http.get('/organization?l=m&q=' + encodeURI(this.searchFor)).then(\n          ok => {\n              this.options = ok.data.datas;\n              if( this.options.length > 0 ){\n                this.highlightedIndex = 0;\n                this.showSelector = true;\n              }\n          },\n          ko => {\n            console.log(ko);\n            switch(ko.status){\n              case 401:\n              case 403:\n                this.error = \"Vous avez été déconnecté (actualisé votre page pour vous reconnecter)\";\n                break;\n              case 500:\n                this.error = \"La recherche a provoqué une erreur\";\n                break;\n              default:\n                this.error = \"Un problème inconnu est survenu\";\n            }\n          }\n      ).then(foo => {\n        this.loading = false;\n      })\n    },\n\n    /**\n     * Selection d'une option.\n     *\n     * @param selected\n     */\n    setSelected(selected) {\n      this.value = selected;\n      this.$emit('change', this.value);\n      this.$emit('input', this.value);\n    },\n\n    /**\n     * Déclenchement de la recherche (à la saisie).\n     *\n     * @param search\n     * @param loading\n     */\n    handlerSearchOrganisation(search, loading) {\n      if (search.length) {\n        loading(true);\n        // Système de retardement Eco+\n        let delayFunction = function () {\n          this.searchOrganization(loading, search, this);\n          this.delay = null;\n        }.bind(this);\n        if (this.delay != null) {\n          clearTimeout(this.delay);\n        }\n        this.delay = setTimeout(delayFunction, 1000);\n      }\n    }\n  }\n});\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/components/OrganizationAutoCompleter2.vue?./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"21d02cb8-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TestOrganisationSelector.vue?vue&type=template&id=0a174804&":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"21d02cb8-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TestOrganisationSelector.vue?vue&type=template&id=0a174804& ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    [\n      _c(\"pre\", [_vm._v(\"    SELECTED : \" + _vm._s(_vm.selected) + \"\\n  \")]),\n      _c(\"organizationselector\", {\n        model: {\n          value: _vm.selected,\n          callback: function ($$v) {\n            _vm.selected = $$v\n          },\n          expression: \"selected\",\n        },\n      }),\n    ],\n    1\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/TestOrganisationSelector.vue?./node_modules/cache-loader/dist/cjs.js?%7B%22cacheDirectory%22:%22node_modules/.cache/vue-loader%22,%22cacheIdentifier%22:%2221d02cb8-vue-loader-template%22%7D!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"21d02cb8-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a&":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"21d02cb8-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a& ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    {\n      staticClass: \"oscar-selector organization-selector\",\n      on: {\n        click: _vm.handlerClick,\n        mouseleave: _vm.handlerMouseLeave,\n        mouseenter: _vm.handlerMouseEnter,\n      },\n    },\n    [\n      _c(\n        \"div\",\n        { staticClass: \"input-group\", staticStyle: { position: \"relative\" } },\n        [\n          _vm.error\n            ? _c(\"div\", { staticClass: \"displayed-value text-danger\" }, [\n                _c(\"i\", { staticClass: \"icon-attention-1\" }),\n                _vm._v(\" \" + _vm._s(_vm.error) + \" \"),\n                _c(\"i\", {\n                  staticClass:\n                    \"icon-cancel-circled-outline button-cancel-value\",\n                  on: {\n                    click: function ($event) {\n                      _vm.error = \"\"\n                    },\n                  },\n                }),\n              ])\n            : _vm._e(),\n          _vm.displayValue\n            ? _c(\"div\", { staticClass: \"displayed-value\" }, [\n                _vm._v(\" \" + _vm._s(_vm.selectedLabel) + \" \"),\n                _vm.selectedValue\n                  ? _c(\"i\", {\n                      staticClass:\n                        \"icon-cancel-circled-outline button-cancel-value\",\n                      on: { click: _vm.handlerUnselect },\n                    })\n                  : _vm._e(),\n              ])\n            : _vm._e(),\n          _c(\"span\", { staticClass: \"input-group-addon\" }, [\n            _c(\"i\", {\n              directives: [\n                {\n                  name: \"show\",\n                  rawName: \"v-show\",\n                  value: _vm.loading,\n                  expression: \"loading\",\n                },\n              ],\n              staticClass: \"icon-spinner animate-spin\",\n            }),\n            _c(\"i\", {\n              directives: [\n                {\n                  name: \"show\",\n                  rawName: \"v-show\",\n                  value: !_vm.loading,\n                  expression: \"!loading\",\n                },\n              ],\n              staticClass: \"icon-building-filled\",\n            }),\n          ]),\n          _c(\"input\", {\n            directives: [\n              {\n                name: \"model\",\n                rawName: \"v-model\",\n                value: _vm.searchFor,\n                expression: \"searchFor\",\n              },\n            ],\n            staticClass: \"form-control\",\n            attrs: {\n              type: \"text\",\n              placeholder: \"Rechercher une organisation...\",\n            },\n            domProps: { value: _vm.searchFor },\n            on: {\n              keyup: _vm.handlerKeyUp,\n              input: function ($event) {\n                if ($event.target.composing) {\n                  return\n                }\n                _vm.searchFor = $event.target.value\n              },\n            },\n          }),\n        ]\n      ),\n      _c(\n        \"div\",\n        {\n          directives: [\n            {\n              name: \"show\",\n              rawName: \"v-show\",\n              value: _vm.showSelector && _vm.options.length,\n              expression: \"showSelector && options.length\",\n            },\n          ],\n          staticClass: \"options\",\n        },\n        [\n          _c(\"header\", [_vm._v(\"Résultat(s) : \" + _vm._s(_vm.options.length))]),\n          _vm._l(_vm.options, function (o, i) {\n            return _c(\n              \"div\",\n              {\n                staticClass: \"option\",\n                class: {\n                  active: i == _vm.highlightedIndex,\n                  selected: o.id == _vm.selectedValue,\n                },\n                attrs: { id: \"item_\" + i },\n                on: {\n                  mouseover: function ($event) {\n                    _vm.highlightedIndex = i\n                  },\n                  click: function ($event) {\n                    $event.preventDefault()\n                    $event.stopPropagation()\n                    return _vm.handlerSelectIndex(i)\n                  },\n                },\n              },\n              [\n                _c(\"div\", { staticClass: \"option-title\" }, [\n                  _c(\"code\", [\n                    _vm._v(\" [\" + _vm._s(i) + \"]\" + _vm._s(o.code) + \" \"),\n                  ]),\n                  _c(\"strong\", [_vm._v(\" \" + _vm._s(o.shortname) + \" \")]),\n                  _c(\"em\", [_vm._v(\" \" + _vm._s(o.longname) + \" \")]),\n                ]),\n                _c(\"div\", { staticClass: \"option-infos\" }, [\n                  _c(\"i\", { staticClass: \"icon-location\" }),\n                  _vm._v(\n                    \" \" + _vm._s(o.city) + \" - \" + _vm._s(o.country) + \" \"\n                  ),\n                ]),\n              ]\n            )\n          }),\n        ],\n        2\n      ),\n    ]\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/components/OrganizationAutoCompleter2.vue?./node_modules/cache-loader/dist/cjs.js?%7B%22cacheDirectory%22:%22node_modules/.cache/vue-loader%22,%22cacheIdentifier%22:%2221d02cb8-vue-loader-template%22%7D!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return normalizeComponent; });\n/* globals __VUE_SSR_CONTEXT__ */\n\n// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).\n// This module is a runtime utility for cleaner component module output and will\n// be included in the final webpack user bundle.\n\nfunction normalizeComponent (\n  scriptExports,\n  render,\n  staticRenderFns,\n  functionalTemplate,\n  injectStyles,\n  scopeId,\n  moduleIdentifier, /* server only */\n  shadowMode /* vue-cli only */\n) {\n  // Vue.extend constructor export interop\n  var options = typeof scriptExports === 'function'\n    ? scriptExports.options\n    : scriptExports\n\n  // render functions\n  if (render) {\n    options.render = render\n    options.staticRenderFns = staticRenderFns\n    options._compiled = true\n  }\n\n  // functional template\n  if (functionalTemplate) {\n    options.functional = true\n  }\n\n  // scopedId\n  if (scopeId) {\n    options._scopeId = 'data-v-' + scopeId\n  }\n\n  var hook\n  if (moduleIdentifier) { // server build\n    hook = function (context) {\n      // 2.3 injection\n      context =\n        context || // cached call\n        (this.$vnode && this.$vnode.ssrContext) || // stateful\n        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional\n      // 2.2 with runInNewContext: true\n      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {\n        context = __VUE_SSR_CONTEXT__\n      }\n      // inject component styles\n      if (injectStyles) {\n        injectStyles.call(this, context)\n      }\n      // register component module identifier for async chunk inferrence\n      if (context && context._registeredComponents) {\n        context._registeredComponents.add(moduleIdentifier)\n      }\n    }\n    // used by ssr in case component is cached and beforeCreate\n    // never gets called\n    options._ssrRegister = hook\n  } else if (injectStyles) {\n    hook = shadowMode\n      ? function () {\n        injectStyles.call(\n          this,\n          (options.functional ? this.parent : this).$root.$options.shadowRoot\n        )\n      }\n      : injectStyles\n  }\n\n  if (hook) {\n    if (options.functional) {\n      // for template-only hot-reload because in that case the render fn doesn't\n      // go through the normalizer\n      options._injectStyles = hook\n      // register for functional component in vue file\n      var originalRender = options.render\n      options.render = function renderWithStyleInjection (h, context) {\n        hook.call(context)\n        return originalRender(h, context)\n      }\n    } else {\n      // inject component registration as beforeCreate hook\n      var existing = options.beforeCreate\n      options.beforeCreate = existing\n        ? [].concat(existing, hook)\n        : [hook]\n    }\n  }\n\n  return {\n    exports: scriptExports,\n    options: options\n  }\n}\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./node_modules/vue-loader/lib/runtime/componentNormalizer.js?");

/***/ }),

/***/ "./src/TestOrganisationSelector.vue":
/*!******************************************!*\
  !*** ./src/TestOrganisationSelector.vue ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _TestOrganisationSelector_vue_vue_type_template_id_0a174804___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TestOrganisationSelector.vue?vue&type=template&id=0a174804& */ \"./src/TestOrganisationSelector.vue?vue&type=template&id=0a174804&\");\n/* harmony import */ var _TestOrganisationSelector_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TestOrganisationSelector.vue?vue&type=script&lang=js& */ \"./src/TestOrganisationSelector.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _TestOrganisationSelector_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _TestOrganisationSelector_vue_vue_type_template_id_0a174804___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _TestOrganisationSelector_vue_vue_type_template_id_0a174804___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"src/TestOrganisationSelector.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/TestOrganisationSelector.vue?");

/***/ }),

/***/ "./src/TestOrganisationSelector.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./src/TestOrganisationSelector.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TestOrganisationSelector_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../node_modules/cache-loader/dist/cjs.js??ref--1-0!../node_modules/vue-loader/lib??vue-loader-options!./TestOrganisationSelector.vue?vue&type=script&lang=js& */ \"./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TestOrganisationSelector.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TestOrganisationSelector_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://TestOrganisationSelector/./src/TestOrganisationSelector.vue?");

/***/ }),

/***/ "./src/TestOrganisationSelector.vue?vue&type=template&id=0a174804&":
/*!*************************************************************************!*\
  !*** ./src/TestOrganisationSelector.vue?vue&type=template&id=0a174804& ***!
  \*************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_21d02cb8_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TestOrganisationSelector_vue_vue_type_template_id_0a174804___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"21d02cb8-vue-loader-template\"}!../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../node_modules/cache-loader/dist/cjs.js??ref--1-0!../node_modules/vue-loader/lib??vue-loader-options!./TestOrganisationSelector.vue?vue&type=template&id=0a174804& */ \"./node_modules/cache-loader/dist/cjs.js?{\\\"cacheDirectory\\\":\\\"node_modules/.cache/vue-loader\\\",\\\"cacheIdentifier\\\":\\\"21d02cb8-vue-loader-template\\\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/TestOrganisationSelector.vue?vue&type=template&id=0a174804&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_21d02cb8_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TestOrganisationSelector_vue_vue_type_template_id_0a174804___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_21d02cb8_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TestOrganisationSelector_vue_vue_type_template_id_0a174804___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/TestOrganisationSelector.vue?");

/***/ }),

/***/ "./src/components/OrganizationAutoCompleter2.vue":
/*!*******************************************************!*\
  !*** ./src/components/OrganizationAutoCompleter2.vue ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _OrganizationAutoCompleter2_vue_vue_type_template_id_d263ee6a___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a& */ \"./src/components/OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a&\");\n/* harmony import */ var _OrganizationAutoCompleter2_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./OrganizationAutoCompleter2.vue?vue&type=script&lang=js& */ \"./src/components/OrganizationAutoCompleter2.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _OrganizationAutoCompleter2_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _OrganizationAutoCompleter2_vue_vue_type_template_id_d263ee6a___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _OrganizationAutoCompleter2_vue_vue_type_template_id_d263ee6a___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"src/components/OrganizationAutoCompleter2.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/components/OrganizationAutoCompleter2.vue?");

/***/ }),

/***/ "./src/components/OrganizationAutoCompleter2.vue?vue&type=script&lang=js&":
/*!********************************************************************************!*\
  !*** ./src/components/OrganizationAutoCompleter2.vue?vue&type=script&lang=js& ***!
  \********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_OrganizationAutoCompleter2_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js??ref--1-0!../../node_modules/vue-loader/lib??vue-loader-options!./OrganizationAutoCompleter2.vue?vue&type=script&lang=js& */ \"./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/OrganizationAutoCompleter2.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_OrganizationAutoCompleter2_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://TestOrganisationSelector/./src/components/OrganizationAutoCompleter2.vue?");

/***/ }),

/***/ "./src/components/OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a&":
/*!**************************************************************************************!*\
  !*** ./src/components/OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a& ***!
  \**************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_21d02cb8_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_OrganizationAutoCompleter2_vue_vue_type_template_id_d263ee6a___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"21d02cb8-vue-loader-template\"}!../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../node_modules/cache-loader/dist/cjs.js??ref--1-0!../../node_modules/vue-loader/lib??vue-loader-options!./OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a& */ \"./node_modules/cache-loader/dist/cjs.js?{\\\"cacheDirectory\\\":\\\"node_modules/.cache/vue-loader\\\",\\\"cacheIdentifier\\\":\\\"21d02cb8-vue-loader-template\\\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/OrganizationAutoCompleter2.vue?vue&type=template&id=d263ee6a&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_21d02cb8_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_OrganizationAutoCompleter2_vue_vue_type_template_id_d263ee6a___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_cache_loader_dist_cjs_js_cacheDirectory_node_modules_cache_vue_loader_cacheIdentifier_21d02cb8_vue_loader_template_node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_OrganizationAutoCompleter2_vue_vue_type_template_id_d263ee6a___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack://TestOrganisationSelector/./src/components/OrganizationAutoCompleter2.vue?");

/***/ })

/******/ })["default"];
});
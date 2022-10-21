(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["TimesheetMonth"] = factory();
	else
		root["TimesheetMonth"] = factory();
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

/***/ "2877":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return normalizeComponent; });
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


/***/ }),

/***/ "6600":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/UITimeChooser.vue?vue&type=template&id=a2845ab8&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"ui-timechooser"},[_c('div',{staticClass:"percents"},[(_vm.fill > 0)?_c('span',{on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.applyDuration(_vm.fill)}}},[_vm._v("Remplir")]):_vm._e(),_c('span',{class:_vm.displayPercent == '100' ? 'selected' : '',on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.applyPercent(100)}}},[_vm._v("100%")]),_c('span',{class:_vm.displayPercent == '75' ? 'selected' : '',on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.applyPercent(75)}}},[_vm._v("75%")]),_c('span',{class:_vm.displayPercent == '50' ? 'selected' : '',on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.applyPercent(50)}}},[_vm._v("50%")]),_c('span',{class:_vm.displayPercent == '25' ? 'selected' : '',on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.applyPercent(25)}}},[_vm._v("25%")])]),(_vm.declarationInHours)?_c('div',{staticClass:"hours"},[_c('span',{staticClass:"hour sel"},[_c('span',{on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.moreHours()}}},[_c('i',{staticClass:"icon-angle-up"})]),_vm._v(" "+_vm._s(_vm.displayHours)+" "),_c('span',{on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.lessHours()}}},[_c('i',{staticClass:"icon-angle-down"})])]),_c('span',{staticClass:"separator"},[_vm._v(":")]),_c('span',{staticClass:"minutes sel"},[_c('span',{on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.moreMinutes()}}},[_c('i',{staticClass:"icon-angle-up"})]),_vm._v(" "+_vm._s(_vm.displayMinutes)+" "),_c('span',{on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.lessMinutes()}}},[_c('i',{staticClass:"icon-angle-down"})])])]):_vm._e()])}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/UITimeChooser.vue?vue&type=template&id=a2845ab8&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/UITimeChooser.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var UITimeChooservue_type_script_lang_js_ = ({
    props: {
        duration: { default: 0 },
        baseTime: { default: 7.5 },
        declarationInHours: { required: true },
        // PAS en minutes
        pas: { default: 10 },
        fill: { default: 0 },
    },
    data(){
        return {
            hours: 0.0,
            minutes: 0.0
        }
    },

    computed: {
        displayHours(){
            return Math.floor(this.duration);
        },

        displayMinutes(){
            return Math.round(((this.duration - this.displayHours)*60));
        },

        displayPercent(){
            return Math.round(100 / this.baseTime * this.duration);
        }
    },

    methods: {
        /**
         * Uniformisation de la valeur.
         *
         * @param durationMinutes
         * @returns {number}
         */
        standardizeDuration(durationMinutes){
            let standardized = (Math.round(durationMinutes/this.pas) * this.pas)/60;
            return standardized;
        },

        moreMinutes(){
            this.duration = this.standardizeDuration(this.duration*60 + this.pas);
            this.emitUpdate();
        },

        lessMinutes(){
            this.duration = this.standardizeDuration(this.duration*60 - this.pas);
            this.emitUpdate();
        },

        moreHours(){
            this.duration += 1;
            this.emitUpdate();
        },

        lessHours(){
            this.duration -= 1;
            if( this.duration < 0.0 )
                this.duration = 0.0;
            this.emitUpdate();
        },

        applyDuration(fill){
            this.duration = fill;
            this.emitUpdate();
        },

        roundMinutes(minutes){
            return Math.round(minutes/this.pas) * this.pas;
        },

        applyPercent(percent){
            this.duration = this.baseTime * percent / 100;
            this.emitUpdate();
        },

        emitUpdate(){
            let hours = Math.floor(this.duration);
            let minutes = this.duration - hours;
            console.log(this.duration, hours, minutes);
            this.$emit('timeupdate', { h: hours, m: minutes })
        }
    }
});

// CONCATENATED MODULE: ./src/UITimeChooser.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_UITimeChooservue_type_script_lang_js_ = (UITimeChooservue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/UITimeChooser.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_UITimeChooservue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var UITimeChooser = __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "a76e":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthWorkPackageSelector.vue?vue&type=template&id=82ea08e2&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[(_vm.showSelector)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('p',[_vm._v("Choississez un type de créneau : ")]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-6"},[_vm._m(0),_vm._l((_vm.workpackages),function(w){return _c('article',{staticClass:"timesheet-item",class:{ 'selected' : _vm.selection && _vm.selection.id == w.id, 'disabled': !w.validation_up },on:{"click":function($event){$event.preventDefault();return _vm.handlerSelectWP(w)}}},[_c('abbr',{staticClass:"project-acronym",attrs:{"title":_vm.project}},[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(w.acronym))]),_c('span',{staticClass:"activity-label",attrs:{"title":w.activity}},[_vm._v(_vm._s(w.activity.substring(1,13)))]),_c('strong',{staticClass:"workpackage-infos"},[_c('span',{staticClass:"code",attrs:{"title":w.label}},[_vm._v(_vm._s(w.code))]),_c('small',{staticClass:"workpackage-label"},[_vm._v(_vm._s(w.label))])])])})],2),_c('div',{staticClass:"col-md-6"},[_c('h3',[_vm._v("Hors activité (Hors-lot)")]),_vm._l((_vm.others),function(w){return _c('article',{staticClass:"timesheet-item horslots-item",class:{ 'selected' : _vm.selection == w, 'disabled': !w.validation_up },on:{"click":function($event){$event.preventDefault();return _vm.handlerSelectOther(w)}}},[_c('span',{staticClass:"project-acronym"},[_c('i',{class:'icon-'+w.code}),_vm._v(" "+_vm._s(w.label))]),_c('small',{staticClass:"workpackage-infos"},[_vm._v(_vm._s(w.description))])])})],2)]),_c('nav',[_c('button',{staticClass:"btn btn-default",on:{"click":function($event){_vm.showSelector = false}}},[_vm._v("Annuler")]),(_vm.usevalidation)?_c('button',{staticClass:"btn btn-primary",class:_vm.selection ? '' : 'disabled',on:{"click":function($event){return _vm.handlerValidSelection()}}},[_vm._v("Valider")]):_vm._e()])])]):_vm._e(),_c('div',{staticClass:"dropdown"},[_c('button',{staticClass:"btn-lg btn btn-default dropdown-toggle",attrs:{"type":"button"},on:{"click":function($event){$event.preventDefault();_vm.showSelector = true}}},[(_vm.hasSelected)?_c('span',{staticClass:"info"},[_c('i',{class:_vm.selectedIcon ? 'icon-' +_vm.selectedIcon : 'icon-archive'}),_c('strong',[_vm._v(_vm._s(_vm.selectedCode))]),_vm._v(" "),_c('em',[_vm._v(_vm._s(_vm.selectedLabel))]),_c('br'),_c('small',{staticClass:"text-light"},[_vm._v(_vm._s(_vm.selectedDescription))])]):_c('em',{staticClass:"info"},[_vm._v("Lot de travail/Activité...")]),_c('span',{staticClass:"caret"})])])])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-cube"}),_vm._v(" Activités")])}]


// CONCATENATED MODULE: ./src/TimesheetMonthWorkPackageSelector.vue?vue&type=template&id=82ea08e2&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthWorkPackageSelector.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var TimesheetMonthWorkPackageSelectorvue_type_script_lang_js_ = ({
    props: {
        workpackages: { default: [] },
        selection: { required:true },
        others: { required: true },
        usevalidation: { default: false }
    },

    data(){
        return {
            showSelector: false
        }
    },

    computed: {
        hasSelected(){
            return this.selection != null;
        },
        selectedCode(){
            return this.selection ? this.selection.code : '';
        },
        selectedLabel(){
            return this.selection ? this.selection.label : '';
        },
        selectedIcon(){
            return this.selection && this.selection.icon ? this.selection.code : '';
        },
        selectedDescription(){
            return this.selection ? this.selection.description : '';
        }
    },

    methods: {
        handlerSelectWP(wp){
            console.log(wp);
            this.selection = wp;
            if( !this.usevalidation ){
                this.handlerValidSelection();
            }
        },

        handlerValidSelection(){
            if( this.selection ) {
                this.showSelector = false;
                this.$emit('select', this.selection);
            }
        },

        handlerSelectOther(wp){
            this.selection = wp;
            if( !this.usevalidation ){
                this.handlerValidSelection();
            }
        }
    }
});

// CONCATENATED MODULE: ./src/TimesheetMonthWorkPackageSelector.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_TimesheetMonthWorkPackageSelectorvue_type_script_lang_js_ = (TimesheetMonthWorkPackageSelectorvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/TimesheetMonthWorkPackageSelector.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_TimesheetMonthWorkPackageSelectorvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var TimesheetMonthWorkPackageSelector = __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "c8e1":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthDay.vue?vue&type=template&id=70b5d86c&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"day",class:{'locked': _vm.day.locked, 'error': (_vm.day.total > _vm.day.maxLength)},on:{"click":_vm.handlerClick}},[_c('span',{staticClass:"label"},[_vm._v(" "+_vm._s(_vm.day.i)+" ")]),(_vm.day.total > _vm.day.maxLength)?_c('span',{staticClass:"text-danger"},[_c('i',{staticClass:"icon-attention"}),_vm._v(" Erreur ")]):_vm._e(),_vm._l((_vm.groupProject),function(d){return _c('span',{staticClass:"cartouche wp xs",style:({ 'background-color': d.color }),attrs:{"title":d.label}},[(d.status_id == null)?_c('i',{staticClass:"icon-draft"}):_c('i',{class:'icon-' + d.status_id}),_vm._v(" "+_vm._s(d.acronym)+" "),_c('span',{staticClass:"addon"},[_vm._v(" "+_vm._s(_vm._f("duration2")(d.duration,_vm.day.dayLength))+" ")])])}),_c('span',_vm._l((_vm.day.othersWP),function(other){return _c('span',{staticClass:"cartouche xs",class:other.code},[(other.validations == null)?_c('i',{staticClass:"icon-draft"}):_c('i',{class:'icon-' + other.status_id}),_vm._v(" "+_vm._s(other.label)+" "),_c('span',{staticClass:"addon"},[_vm._v(" "+_vm._s(_vm._f("duration2")(other.duration,_vm.day.dayLength))+" ")])])}),0),(_vm.day.closed)?_c('span',{staticStyle:{"font-size":".7em"},attrs:{"title":_vm.day.lockedReason}},[_c('i',{staticClass:"icon-minus-circled"}),_vm._v(" Fermé ")]):(_vm.day.locked)?_c('span',{staticStyle:{"font-size":".7em"},attrs:{"title":_vm.day.lockedReason}},[_c('i',{staticClass:"icon-lock"}),_vm._v(" Verrouillé ")]):_vm._e(),_vm._v("   ")],2)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/TimesheetMonthDay.vue?vue&type=template&id=70b5d86c&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthDay.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


    /* harmony default export */ var TimesheetMonthDayvue_type_script_lang_js_ = ({
        name: 'TimesheetMonthDay',

        props: {
            others: { required: true },
            day: { required: true },
            projectscolors: { required: true, default: null }
        },

        data(){
            return {
                colors: ["#093b8c",
                    "#098c29",
                    "#8c2109",
                    "#4c098c",
                    "#8c0971",
                    "#8c6f09"]
            }
        },

        filters: {
            duration(v){
                let h = Math.floor(v);
                let m = Math.round((v - h)*60);
                if( m < 10 ) m = '0'+m;
                return h +':' +m;
            }
        },

        computed: {
           groupProject(){
               let groups = {};
               if( this.day.declarations ) {
                   this.day.declarations.forEach(d => {
                       if (!groups.hasOwnProperty(d.acronym)) {
                           groups[d.acronym] = {
                               label: d.label,
                               acronym: d.acronym,
                               duration: 0.0,
                               status_id: d.status_id,
                               color: this.getProjectColor(d.acronym)
                           }
                       }

                       //groups[d.acronym].status_id += d.duration;
                       groups[d.acronym].duration += d.duration;
                   });
               }
               return groups;
           }
        },

        methods: {
            /**
             *
             * @param acronym
             */
            getProjectColor(acronym){
                if( this.projectscolors && this.projectscolors.hasOwnProperty(acronym) ){
                    return this.projectscolors[acronym];
                }
                else {
//                    let rand = Math.floor(Math.random()*this.colors.length);
                    return '#8c0971'
                }
            },

            totalOther(code){
                let t = 0.0;
                this.day[code].forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            handlerClick(){
                this.$emit('selectDay', this.day);
            },

            handlerRightClick(e){
                this.$emit('daymenu', e, this.day);
            }
        }

    });

// CONCATENATED MODULE: ./src/TimesheetMonthDay.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_TimesheetMonthDayvue_type_script_lang_js_ = (TimesheetMonthDayvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/TimesheetMonthDay.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_TimesheetMonthDayvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var TimesheetMonthDay = __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "ea58":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthDayDetails.vue?vue&type=template&id=abba71e2&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"day-details",class:{'locked': _vm.day.locked}},[_c('h3',{staticClass:"title-with-menu",on:{"click":function($event){$event.stopPropagation();$event.preventDefault();if(!$event.shiftKey){ return null; }return _vm.$emit('debug', _vm.day)}}},[_c('div',{staticClass:"text"},[_c('i',{staticClass:"icon-calendar"}),_vm._v(" "),_c('strong',[_vm._v(_vm._s(_vm.label))])]),_c('nav',{staticClass:"right-menu"},[_c('a',{directives:[{name:"show",rawName:"v-show",value:(_vm.day.othersWP || _vm.day.declarations.length),expression:"day.othersWP || day.declarations.length"}],attrs:{"href":"#","title":"Copier les créneaux"},on:{"click":function($event){$event.preventDefault();return _vm.$emit('copy', _vm.day)}}},[_c('i',{staticClass:"icon-docs"})]),_c('a',{directives:[{name:"show",rawName:"v-show",value:(_vm.copiable),expression:"copiable"}],attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.$emit('paste', _vm.day)}}},[_c('i',{staticClass:"icon-paste"})])])]),_c('a',{staticClass:"btn btn-xs btn-default",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.$emit('cancel')}}},[_c('i',{staticClass:"icon-angle-left"}),_vm._v(" Retour ")]),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.day.locked),expression:"day.locked"}],staticClass:"alert alert-danger"},[_c('i',{staticClass:"icon-attention"}),_vm._v(" Cette journée est verrouillé "),_c('strong',[_vm._v(_vm._s(_vm.day.lockedReason))])]),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.day.total > _vm.day.maxLength),expression:"day.total > day.maxLength"}],staticClass:"alert alert-danger"},[_c('i',{staticClass:"icon-attention"}),_vm._v(" Le temps déclaré "),_c('strong',[_vm._v("excède la durée autorisée")]),_vm._v(". Vous ne pourrez pas soumettre votre feuille de temps. ")]),(_vm.editable)?_c('div',[_vm._v(" Compléter avec : "),_c('wpselector',{attrs:{"others":_vm.others,"workpackages":_vm.workPackages,"selection":_vm.selection},on:{"select":_vm.addToWorkpackage}})],1):_vm._e(),_c('section',[(_vm.day.declarations.length)?[_vm._m(0),_vm._l((_vm.day.declarations),function(d){return _c('day',{key:d.id,attrs:{"d":d,"day-length":_vm.day.dayLength},on:{"debug":function($event){return _vm.$emit('debug', $event)},"removetimesheet":function($event){return _vm.$emit('removetimesheet', $event)},"edittimesheet":function($event){return _vm.$emit('edittimesheet', $event, _vm.day)}}})}),_c('article',{staticClass:"wp-duration card xs"},[_vm._m(1),_c('div',{staticClass:"total"},[_c('span',{staticClass:"text-large text-xl"},[_vm._v(_vm._s(_vm._f("duration2")(_vm.totalWP,_vm.day.dayLength)))])]),_c('div',{staticClass:"left"})]),_c('hr')]:_vm._e(),(_vm.day.othersWP)?_c('section',{staticClass:"othersWP"},[_vm._m(2),_vm._l((_vm.day.othersWP),function(t){return _c('article',{staticClass:"wp-duration card xs"},[_c('strong',[_c('i',{class:'icon-'+t.code}),_vm._v(" "+_vm._s(_vm.others[t.label] ? _vm.others[t.label].label : t.label)),_c('br'),_c('small',[_vm._v(_vm._s(t.description))])]),_c('div',{staticClass:"total"},[_vm._v(_vm._s(_vm._f("duration2")(t.duration,_vm.day.dayLength)))]),_c('div',{staticClass:"left buttons-icon"},[_c('i',{staticClass:"icon-trash",class:_vm.day.editable != true ? 'disabled':'',on:{"click":function($event){return _vm.$emit('removetimesheet', t)}}}),_c('i',{staticClass:"icon-edit",class:_vm.day.editable != true ? 'disabled':'',on:{"click":function($event){return _vm.$emit('edittimesheet', t, _vm.day)}}})])])}),_c('hr')],2):_vm._e(),_c('article',{staticClass:"wp-duration card xl"},[_vm._m(3),_c('div',{staticClass:"total"},[_vm._v(_vm._s(_vm._f("duration2")(_vm.totalJour,_vm.day.dayLength)))])])],2)])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-archive"}),_vm._v(" Heures identifiées sur des lots")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',[_c('strong',[_c('i',{staticClass:"icon-archive"}),_vm._v(" Total"),_c('br'),_c('small',[_vm._v("sur les lot de travail")])])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-tags"}),_vm._v(" Heures Hors-Lots")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('strong',[_vm._v(" Total de la journée"),_c('br')])}]


// CONCATENATED MODULE: ./src/TimesheetMonthDayDetails.vue?vue&type=template&id=abba71e2&

// EXTERNAL MODULE: ./src/TimesheetMonthWorkPackageSelector.vue + 4 modules
var TimesheetMonthWorkPackageSelector = __webpack_require__("a76e");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthDeclarationItem.vue?vue&type=template&id=8174df4a&
var TimesheetMonthDeclarationItemvue_type_template_id_8174df4a_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('article',{staticClass:"card card-xs xs wp-duration",class:'status-' + _vm.d.status_id},[_c('span',{staticClass:"infos"},[_c('strong',[_c('i',{staticClass:"icon-archive"}),_c('abbr',{attrs:{"title":_vm.d.project}},[_vm._v(_vm._s(_vm.d.acronym))]),_c('i',{staticClass:"icon-angle-right"}),_vm._v(" "+_vm._s(_vm.d.wpCode)+" "),_c('i',{staticClass:"icon-comment",class:_vm.d.comment ? 'with-comment' : '',attrs:{"title":_vm.d.comment}})]),_c('br'),_c('small',[_c('i',{staticClass:"icon-cubes"}),_vm._v(" "+_vm._s(_vm.d.label))]),_c('div',{staticClass:"status"},[_c('i',{class:'icon-'+_vm.d.status_id}),_vm._v(" "+_vm._s(_vm._f("status")(_vm.d.status_id))+" "),(_vm.d.validations.conflict)?_c('span',{staticClass:"text-danger"},[_vm._v(" "+_vm._s(_vm.d.validations.conflict)+" ")]):_vm._e()])]),_c('div',{staticClass:"total"},[_vm._v(" "+_vm._s(_vm._f("duration2")(_vm.d.duration,_vm.dayLength))+" ")]),_c('div',{staticClass:"left buttons-icon"},[_c('i',{staticClass:"icon-trash",class:_vm.d.credentials.deletable != true ? 'disabled':'',on:{"click":function($event){return _vm.$emit('removetimesheet', _vm.d)}}}),_c('i',{staticClass:"icon-edit",class:_vm.d.credentials.editable != true ? 'disabled':'',on:{"click":function($event){return _vm.$emit('edittimesheet', _vm.d)}}})])])}
var TimesheetMonthDeclarationItemvue_type_template_id_8174df4a_staticRenderFns = []


// CONCATENATED MODULE: ./src/TimesheetMonthDeclarationItem.vue?vue&type=template&id=8174df4a&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthDeclarationItem.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var TimesheetMonthDeclarationItemvue_type_script_lang_js_ = ({
    name: 'TimesheetMonthDeclarationItem',
    props: {
        'd': { required: true },
        'dayLength': { required: true }
    },
    filters: {
        heures(v){
            let heures = Math.floor(v);
            let minutes = Math.round((v - heures)*60);
            if( minutes < 10 ) minutes = '0'+minutes;
            console.log(v, ' => ',heures,'h',minutes);
            return heures+":"+minutes;
        }
    }
});

// CONCATENATED MODULE: ./src/TimesheetMonthDeclarationItem.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_TimesheetMonthDeclarationItemvue_type_script_lang_js_ = (TimesheetMonthDeclarationItemvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/TimesheetMonthDeclarationItem.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_TimesheetMonthDeclarationItemvue_type_script_lang_js_,
  TimesheetMonthDeclarationItemvue_type_template_id_8174df4a_render,
  TimesheetMonthDeclarationItemvue_type_template_id_8174df4a_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var TimesheetMonthDeclarationItem = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonthDayDetails.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var TimesheetMonthDayDetailsvue_type_script_lang_js_ = ({
    name: 'TimesheetMonthDayDetails',

    components: {
        wpselector: TimesheetMonthWorkPackageSelector["default"],
        day: TimesheetMonthDeclarationItem
    },

    props: {
        workPackages: {
            require: true
        },
        others: {
            require: true
        },
        editable: {
            required: true,
        },
        day: {
           require: true
        },
        selection: {
            require: true
        },
        label: {
            require: true
        },
        dayExcess: {
            require: true
        },
        copiable: {
            default: null
        }
    },

    data(){
        return {
            formAdd: false,
            debug: false
        }
    },

    filters: {
        heures(v){
            let heures = Math.floor(v);
            let minutes = Math.round((v - heures)*60);
            if( minutes < 10 ) minutes = '0'+minutes;
            console.log(v, ' => ',heures,'h',minutes);
            return heures+":"+minutes;
        }
    },

    computed: {

        isExceed(){
            return this.total > this.day.dayLength;
        },

        totalWP(){
            let t = 0.0;
            this.day.declarations.forEach( d => {
                t += d.duration;
            })
            return t;
        },

        totalHWP(){
            let t = 0.0;
            if( this.day.othersWP ) {
                this.day.othersWP.forEach(d => {
                    t += d.duration;
                })
            }
            return t;

        },

        totalJour(){
          return this.totalWP + this.totalHWP;

        },

        enseignements(){
            let t = 0.0;
            this.day.teaching.forEach( ts => {
                t += ts.duration;
            })
            return t;
        },
        abs(){
            let t = 0.0;
            this.day.vacations.forEach( ts => {
                t += ts.duration;
            })
            return t;
        },
        learn(){
            let t = 0.0;
            this.day.training.forEach( ts => {
                t += ts.duration;
            })
            return t;
        },
        other(){
            let t= 0.0;
            this.day.infos.forEach( ts => {
               t += ts.duration
            });
            return t;
        },
        sickleave(){
            let t= 0.0;
            this.day.sickleave.forEach( ts => {
                t += ts.sickleave
            });
            return t;
        },
        research(){
            let t= 0.0;
            this.day.research.forEach( ts => {
                t += ts.research
            });
            return t;
        }
    },

    methods: {
        addToWorkpackage( wp){
            this.$emit('addtowp', wp);
        },

        hasDeclarationHWP(code){
            return this.day[code] && this.day[code].length;
        }
    }
});

// CONCATENATED MODULE: ./src/TimesheetMonthDayDetails.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_TimesheetMonthDayDetailsvue_type_script_lang_js_ = (TimesheetMonthDayDetailsvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/TimesheetMonthDayDetails.vue





/* normalize component */

var TimesheetMonthDayDetails_component = Object(componentNormalizer["a" /* default */])(
  src_TimesheetMonthDayDetailsvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var TimesheetMonthDayDetails = __webpack_exports__["default"] = (TimesheetMonthDayDetails_component.exports);

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
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonth.vue?vue&type=template&id=3d0f00f6&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',{on:{"click":_vm.handlerClick,"keyup":_vm.handlerKeyDown}},[_c('transition',{attrs:{"name":"fade"}},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.loading),expression:"loading"}],staticClass:"loading-message"},[_c('i',{staticClass:"icon-spinner animate-spin"}),_vm._v(" "+_vm._s(_vm.loading)+" ")])]),_c('transition',{attrs:{"name":"fade"}},[(_vm.commentEdited)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('h2',[_c('i',{staticClass:"icon-comment"}),_vm._v(" Commentaire pour "),_c('strong',[_vm._v(_vm._s(_vm.commentEditedLabel))]),_vm._v(" pour "),_c('em',[_vm._v(_vm._s(_vm.mois))])]),_c('div',{staticClass:"alert alert-info"},[_vm._v(" Le commentaire saisi sera repris dans le feuille de temps ")]),_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.commentEditedContent),expression:"commentEditedContent"}],staticClass:"form-control",class:{'disabled': !_vm.ts.editable },attrs:{"name":"comment","id":"","cols":"30","rows":"10"},domProps:{"value":(_vm.commentEditedContent)},on:{"input":function($event){if($event.target.composing){ return; }_vm.commentEditedContent=$event.target.value}}}),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",class:{'disabled': !_vm.ts.editable },on:{"click":_vm.handlerSendComment}},[_c('i',{staticClass:"icon-floppy"}),_vm._v(" Enregistrer le commentaire")]),_c('button',{staticClass:"btn btn-default",on:{"click":function($event){_vm.commentEdited = null}}},[_c('i',{staticClass:"icon-cancel-outline"}),_vm._v("Annuler")])])])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.screensend)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('h2',[_c('i',{staticClass:"icon-paper-plane"}),_vm._v(" Soumettre la déclaration pour "),_c('strong',[_vm._v(_vm._s(_vm.mois))])]),_c('table',{staticClass:"table table-bordered table-recap"},[_c('thead',[_c('th',{attrs:{"colspan":"2"}},[_vm._v(" ")]),_vm._l((_vm.ts.days),function(d){return _c('th',[_c('small',[_vm._v(_vm._s(d.label))]),_c('br'),_c('strong',[_vm._v(_vm._s(d.i))])])}),_c('th',[_vm._v(" Total ")])],2),_vm._l((_vm.recapsend.lot),function(project){return _c('tbody',[_vm._l((project.activities),function(activity){return [_c('tr',{staticClass:"activity-line"},[_c('th',{attrs:{"colspan":_vm.ts.dayNbr + 3}},[_c('h3',[_c('strong',[_c('i',{staticClass:"icon-cube"}),_vm._v(" ["+_vm._s(activity.acronym)+"] "+_vm._s(activity.label))])])])]),_c('tr',[_c('th',{attrs:{"colspan":"2"}},[_vm._v(" ")]),_c('td',{attrs:{"colspan":_vm.ts.dayNbr}},[_vm._v("   ")]),_c('td',[_vm._v(" ")])]),_vm._l((activity.workpackages),function(wp){return _c('tr',{staticClass:"workpackage-line"},[_c('th',{attrs:{"colspan":"2"}},[_c('i',{staticClass:"icon-archive"}),_vm._v(" "+_vm._s(wp.label)+" ")]),_vm._l((_vm.ts.days),function(d){return _c('td',[(wp.days[d.i])?_c('strong',[_vm._v(_vm._s(_vm._f("duration2")(wp.days[d.i],d.dayLength)))]):_c('small',[_vm._v("-")])])}),_c('th',{staticClass:"total"},[_vm._v(" "+_vm._s(_vm._f("duration2")(wp.total,_vm.monthLength))+" ")])],2)}),_c('tr',{staticClass:"activity-line-total"},[_c('th',{attrs:{"colspan":"2"}},[_vm._v("Total")]),_vm._l((_vm.ts.days),function(d){return _c('td',[(activity.days[d.i])?_c('strong',[_vm._v(_vm._s(_vm._f("duration2")(activity.days[d.i],d.dayLength)))]):_c('small',[_vm._v("-")])])}),_c('th',{staticClass:"total"},[_vm._v(_vm._s(_vm._f("duration2")(activity.total,_vm.monthLength)))])],2)]})],2)}),_c('tbody',[_c('tr',[_c('th',{attrs:{"colspan":_vm.ts.dayNbr + 3}},[_c('h3',[_c('i',{staticClass:"icon-tags"}),_c('strong',[_vm._v("Hors-Lot")])])])]),_vm._l((_vm.recapsend.hl),function(hl){return _c('tr',{staticClass:"workpackage-line"},[_c('th',[_c('i',{class:'icon-' + hl.code}),_vm._v(" "+_vm._s(hl.label)+" ")]),_c('td',[_vm._v("   ")]),_vm._l((_vm.ts.days),function(d){return _c('td',[(hl.days[d.i])?_c('strong',[_vm._v(_vm._s(_vm._f("duration2")(hl.days[d.i],d.dayLength)))]):_c('small',[_vm._v("-")])])}),_c('th',{staticClass:"total"},[_vm._v(" "+_vm._s(_vm._f("duration2")(hl.total,_vm.monthLength))+" ")])],2)})],2),_c('tbody',[_c('tr',[_c('th',{attrs:{"colspan":_vm.ts.dayNbr + 3}},[_c('h3',[_c('strong',[_vm._v("Total")])])])]),_c('tr',{staticClass:"total-line"},[_c('th',{attrs:{"colspan":"2"}},[_vm._v(" = ")]),_vm._l((_vm.ts.days),function(d){return _c('td',[(d.total)?_c('strong',[_vm._v(_vm._s(_vm._f("duration2")(d.total,d.dayLength)))]):_c('small',[_vm._v("-")])])}),_c('th',{staticClass:"total"},[_vm._v(" "+_vm._s(_vm._f("duration2")(_vm.ts.total,_vm.monthLength))+" ")])],2)])],2),_vm._l((_vm.recapsend.lot),function(project){return _c('div',_vm._l((project.activities),function(activity){return _c('div',[_c('h5',[_c('strong',[_c('i',{staticClass:"icon-cube"}),_vm._v(" ["+_vm._s(activity.acronym)+"] "+_vm._s(activity.label))])]),_c('strong',[_vm._v("Commentaires : ")]),_c('br'),_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.screensend[activity.id]),expression:"screensend[activity.id]"}],staticClass:"form-control",staticStyle:{"max-width":"100%"},domProps:{"value":(_vm.screensend[activity.id])},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.screensend, activity.id, $event.target.value)}}})])}),0)}),_vm._l((_vm.recapsend.hl),function(hl){return _c('div',[_c('h5',[_c('strong',[_c('i',{staticClass:"icon",class:'icon-' +hl.code}),_vm._v(" "+_vm._s(hl.label))])]),_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.screensend[hl.code]),expression:"screensend[hl.code]"}],staticClass:"form-control",staticStyle:{"max-width":"100%"},domProps:{"value":(_vm.screensend[hl.code])},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.screensend, hl.code, $event.target.value)}}})])}),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",on:{"click":_vm.sendMonthProceed}},[_vm._v("Envoyer la déclaration")]),_c('button',{staticClass:"btn btn-default",on:{"click":function($event){_vm.screensend = null}}},[_vm._v("Annuler")])])],2)]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.configureColor)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('h2',[_c('i',{staticClass:"icon-tags"}),_vm._v(" Couleurs des activités ")]),_c('p',[_vm._v("Les options ajustables ici pemettent uniquement")]),_vm._l((_vm.ts.activities),function(a){return _c('article',[_c('strong',{staticClass:"cartouche ",style:({ 'background-color': _vm.getAcronymColor(a.acronym) })},[_vm._v(_vm._s(a.acronym)+" "),_c('em',{staticClass:"addon"},[_vm._v(_vm._s(a.label))])]),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm._colorsProjects[a.acronym]),expression:"_colorsProjects[a.acronym]"}],attrs:{"type":"color"},domProps:{"value":(_vm._colorsProjects[a.acronym])},on:{"change":function($event){return _vm.handlerChangeColor(a.acronym, $event)},"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm._colorsProjects, a.acronym, $event.target.value)}}})])}),_c('hr'),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){_vm.configureColor = false}}},[_c('i',{staticClass:"icon-cancel-alt"}),_vm._v(" Terminé")])])],2)]):_vm._e()]),(_vm.error)?_c('div',{staticClass:"overlay",staticStyle:{"z-index":"2002"}},[_c('div',{staticClass:"content container overlay-content"},[_vm._m(0),_c('pre',{staticClass:"alert alert-danger"},[_vm._v(_vm._s(_vm.error))]),_c('p',{staticClass:"text-danger"},[_vm._v(" Si ce message ne vous aide pas, transmettez le à l'administrateur Oscar. ")]),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){_vm.error = ''}}},[_vm._v("Fermer")])])])]):_vm._e(),(_vm.rejectPeriod)?_c('div',{staticClass:"overlay",staticStyle:{"z-index":"2002"}},[_c('div',{staticClass:"content container overlay-content"},[_vm._m(1),(_vm.rejectPeriod.rejectadmin_at)?_c('div',[_c('p',[_vm._v("Déclaration rejetée administrativement par "),_c('strong',[_vm._v(_vm._s(_vm.rejectPeriod.rejectadmin_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm.rejectPeriod.rejectadmin_at))])]),_c('pre',[_c('strong',[_vm._v("Motif : ")]),_vm._v(_vm._s(_vm.rejectPeriod.rejectadmin_message))])]):(_vm.rejectPeriod.rejectsci_at)?_c('div',[_c('p',[_vm._v("Déclaration rejetée scientifiquement par "),_c('strong',[_vm._v(_vm._s(_vm.rejectPeriod.rejectsci_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm.rejectPeriod.rejectsci_at))])]),_c('pre',[_c('strong',[_vm._v("Motif : ")]),_vm._v(_vm._s(_vm.rejectPeriod.rejectsci_message))])]):(_vm.rejectPeriod.rejectactivity_at)?_c('div',[_c('p',[_vm._v("Déclaration rejetée par "),_c('strong',[_vm._v(_vm._s(_vm.rejectPeriod.rejectactivity_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm.rejectPeriod.rejectactivity_at))])]),_c('pre',[_c('strong',[_vm._v("Motif : ")]),_vm._v(_vm._s(_vm.rejectPeriod.rejectactivity_message))])]):_vm._e(),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){_vm.rejectPeriod = null}}},[_vm._v("Fermer")])])])]):_vm._e(),(_vm.popup)?_c('div',{staticClass:"overlay",staticStyle:{"z-index":"2001"}},[_c('div',{staticClass:"content container overlay-content"},[_c('h2',[_vm._v("Historique")]),_c('pre',{staticClass:"alert alert-info"},[_vm._v(_vm._s(_vm.popup))]),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){_vm.popup = ''}}},[_vm._v("Fermer")])])])]):_vm._e(),(_vm.help)?_c('div',{staticClass:"overlay",staticStyle:{"z-index":"2002"}},[_c('div',{staticClass:"content container overlay-content"},[_vm._m(2),_vm._m(3),(_vm.ts)?_c('ul',[_c('li',[_vm._v("Durée "),_c('em',[_vm._v("normal")]),_vm._v(" d'une journée : "),_c('strong',[_vm._v(_vm._s(_vm._f("duration")(_vm.ts.daylength)))])]),_c('li',[_vm._v("Durée "),_c('strong',[_vm._v("maximum légale")]),_vm._v(" d'une journée : "),_c('strong',[_vm._v(_vm._s(_vm._f("duration")(_vm.ts.dayExcess)))])]),_c('li',[_vm._v("Durée "),_c('strong',[_vm._v("maximum légale")]),_vm._v(" d'une semaine : "),_c('strong',[_vm._v(_vm._s(_vm._f("duration")(_vm.ts.weekExcess)))])]),_c('li',[_vm._v("Durée "),_c('strong',[_vm._v("maximum légale")]),_vm._v(" d'un mois : "),_c('strong',[_vm._v(_vm._s(_vm._f("duration")(_vm.ts.monthExcess)))])])]):_vm._e(),_vm._m(4),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){_vm.help = ''}}},[_vm._v("Fermer")])])])]):_vm._e(),(_vm.debug)?_c('div',{staticClass:"overlay",staticStyle:{"z-index":"2002"}},[_c('div',{staticClass:"content container overlay-content"},[_vm._m(5),_c('pre',{staticClass:"alert alert-info",staticStyle:{"white-space":"pre","font-size":"12px"}},[_vm._v(_vm._s(_vm.debug))]),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){_vm.debug = ''}}},[_vm._v("Fermer")])])])]):_vm._e(),(_vm.selectedDay && _vm.selectionWP && _vm.selectionWP.code )?_c('div',{staticClass:"overlay",staticStyle:{"z-index":"2001"}},[_c('div',{staticClass:"content container overlay-content"},[_c('section',[(_vm.selectionWP.id)?_c('h3',[_c('small',[_vm._v("Déclaration pour le lot")]),_c('strong',[_c('i',{staticClass:"icon-archive"}),_c('abbr',[_vm._v(_vm._s(_vm.selectionWP.code))]),_vm._v(" "+_vm._s(_vm.selectionWP.label)+" ")])]):_c('h3',[_c('small',[_vm._v("Déclaration hors-lot pour")]),_c('strong',[_c('i',{class:'icon-' + _vm.selectionWP.code}),_vm._v(" "+_vm._s(_vm.selectionWP.label)+" ")])])]),(_vm.selectionWP.validation_up != true)?_c('div',{staticClass:"alert alert-danger"},[_vm._v(" Vous ne pouvez plus ajouter de créneaux pour ce lot sur cette période ")]):_c('div',[_c('p',[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Journée : "),_c('strong',[_vm._v(_vm._s(_vm._f("datefull")(_vm.selectedDay.date)))]),_c('br')]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-6"},[_c('h4',[_vm._v("Temps")]),_c('pre',[_vm._v(_vm._s(_vm.selection))]),_c('timechooser',{attrs:{"declarationInHours":_vm.declarationInHours,"baseTime":_vm.selectedDay.dayLength,"fill":_vm.fillDayValue,"duration":_vm.editedTimesheet ? _vm.editedTimesheet.duration : 0},on:{"timeupdate":_vm.handlerDayUpdated}})],1),_c('div',{staticClass:"col-md-6"},[_c('h4',[_vm._v("Commentaire")]),_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.commentaire),expression:"commentaire"}],staticClass:"form-control textarea",domProps:{"value":(_vm.commentaire)},on:{"input":function($event){if($event.target.composing){ return; }_vm.commentaire=$event.target.value}}})])])]),_c('nav',{staticClass:"buttons"},[_c('button',{staticClass:"btn btn-default",on:{"click":function($event){_vm.selectionWP = null}}},[_c('i',{staticClass:"icon-block"}),_vm._v(" Annuler ")]),(_vm.selectionWP.validation_up == true)?_c('button',{staticClass:"btn btn-primary",on:{"click":_vm.handlerSaveMenuTime}},[_c('i',{staticClass:"icon-floppy"}),_vm._v(" Valider ")]):_vm._e()])])]):_vm._e(),(_vm.ts)?_c('section',{staticClass:"container-fluid",staticStyle:{"margin-bottom":"5em"}},[_c('div',{staticClass:"month col-lg-8"},[_c('h2',[_vm._v(" Déclarations de temps pour "),_c('strong',[_vm._v(_vm._s(_vm.ts.person))]),_c('small',{staticStyle:{"position":"absolute","right":"0","font-size":"14px","cursor":"pointer"},on:{"click":function($event){_vm.configureColor = true}}},[_vm._v(" Options "),_c('i',{staticClass:"icon-cog"})])]),_c('h3',{staticClass:"periode"},[_vm._v("Période "),_c('a',{attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.prevMonth.apply(null, arguments)}}},[_c('i',{staticClass:"icon-angle-left"})]),_c('strong',{on:{"click":function($event){if(!$event.shiftKey){ return null; }_vm.debug = _vm.ts}}},[_vm._v(_vm._s(_vm.mois))]),_c('a',{attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.nextMonth.apply(null, arguments)}}},[_c('i',{staticClass:"icon-angle-right"})]),(_vm.urlimport)?_c('a',{staticClass:"btn btn-default",class:{ 'disabled': !_vm.ts.submitable },attrs:{"href":_vm.urlimport+'&period=' + _vm.periodCode,"title":!_vm.ts.submitable ? 'Vous ne pouvez pas importer pour cette période' : ''}},[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Importer un calendrier"),_c('br'),(!_vm.ts.submitable)?_c('small',[_vm._v(" Vous ne pouvez pas importer pour une période en cours/déjà envoyée ")]):_vm._e()]):_vm._e()]),_c('div',{staticClass:"month"},[_vm._m(6),_c('div',{staticClass:"weeks"},_vm._l((_vm.weeks),function(week){return (_vm.ts)?_c('section',{staticClass:"week",class:_vm.selectedWeek == week ? 'selected' : ''},[_c('header',{staticClass:"week-header",on:{"click":function($event){return _vm.selectWeek(week)}}},[_c('span',[_vm._v("Semaine "+_vm._s(week.label))]),_c('small',[_c('em',[_vm._v("Cumul des heures : ")]),_c('strong',{class:(week.total > week.weekExcess)?'has-titled-error':'',attrs:{"title":(week.total > week.weekExcess)?
                                            'Les heures excédentaires risques d\'être ignorées lors d\'une justification financière dans le cadre des projets soumis aux feuilles de temps'
                                            :''}},[(week.total > week.weekExcess)?_c('i',{staticClass:"icon-attention-1"}):_vm._e(),_vm._v(_vm._s(_vm._f("duration2")(week.total,week.weekLength)))])])]),_c('div',{staticClass:"days"},_vm._l((week.days),function(day){return _c('timesheetmonthday',{key:day.date,class:_vm.selectedDay == day ? 'selected':'',attrs:{"others":_vm.ts.otherWP,"projectscolors":_vm._colorsProjects,"day":day},on:{"selectDay":function($event){return _vm.handlerSelectData(day)},"daymenu":_vm.handlerDayMenu,"debug":function($event){_vm.debug = $event}}})}),1)]):_vm._e()}),0)])]),_c('section',{staticClass:"col-lg-4"},[(_vm.selectedDay)?_c('timesheetmonthdaydetails',{attrs:{"day":_vm.selectedDay,"workPackages":_vm.ts.workpackages,"others":_vm.ts.otherWP,"selection":_vm.selectionWP,"editable":_vm.ts.editable,"label":_vm.dayLabel,"day-excess":_vm.ts.dayExcess,"copiable":_vm.clipboardDataDay},on:{"debug":function($event){_vm.debug = $event},"copy":_vm.handlerCopyDay,"paste":_vm.handlerPasteDay,"cancel":function($event){_vm.selectedDay = null},"removetimesheet":_vm.deleteTimesheet,"edittimesheet":_vm.editTimesheet,"addtowp":function($event){return _vm.handlerWpFromDetails($event)}}}):(_vm.selectedWeek)?_c('div',[_c('h3',{staticClass:"title-with-menu",on:{"click":function($event){if(!$event.shiftKey){ return null; }_vm.debug = _vm.selectedWeek}}},[_c('div',{staticClass:"text"},[_c('i',{staticClass:"icon-calendar"}),_c('strong',[_vm._v("Semaine "+_vm._s(_vm.selectedWeek.label))])]),(_vm.ts.editable)?_c('nav',{staticClass:"right-menu"},[_c('a',{attrs:{"href":"#","title":"Copier les créneaux de la semaine"},on:{"click":function($event){return _vm.handlerCopyWeek(_vm.selectedWeek)}}},[_c('i',{staticClass:"icon-docs"})]),_c('a',{directives:[{name:"show",rawName:"v-show",value:(_vm.clipboardData),expression:"clipboardData"}],attrs:{"href":"#","title":"Coller les créneaux"},on:{"click":function($event){return _vm.handlerPasteWeek(_vm.selectedWeek)}}},[_c('i',{staticClass:"icon-paste"})])]):_vm._e()]),_c('a',{staticClass:"btn btn-default",on:{"click":function($event){_vm.selectedWeek = null}}},[_c('i',{staticClass:"icon-angle-left"}),_vm._v(" Revenir au mois ")]),_c('h4',[_vm._v("Jours : ")]),_vm._l((_vm.selectedWeek.days),function(d){return _c('article',{staticClass:"card xs total repport-item",class:{ 'locked': d.locked, 'closed': d.closed, 'excess': d.duration > _vm.ts.dayExcess },on:{"click":function($event){return _vm.handlerSelectData(d)}}},[_c('div',{staticClass:"week-header",class:{ 'text-thin' : d.closed || d.locked }},[_c('span',{},[_c('i',{staticClass:"icon-calendar"}),(d.closed)?_c('i',{staticClass:"icon-minus-circled"}):(d.locked)?_c('i',{staticClass:"icon-lock"}):(d.total > d.amplitudemin && d.total < d.amplitudemax )?_c('i',{staticClass:"icon-ok-circled",staticStyle:{"color":"#2d7800"}}):_c('i',{staticClass:"icon-help-circled",staticStyle:{"color":"#777777"}}),_vm._v(" "+_vm._s(_vm._f("datefull")(d.data))+" "),_c('i',{staticClass:"icon-attention-circled",staticStyle:{"color":"red"},attrs:{"title":"Les heures déclarées dépassent la limite légales"}})]),_c('small',[_c('strong',{staticClass:"text-large"},[_vm._v(_vm._s(_vm._f("duration2")(d.duration,d.dayLength)))])])])])}),_c('article',{staticClass:"card xs total"},[_c('div',{staticClass:"week-header"},[_c('span',{},[_c('i',{staticClass:"icon-clock"}),_vm._v(" Heures déclarées "),_c('br'),(_vm.selectedWeek.totalOpen < _vm.selectedWeek.weekLength)?_c('small',{staticClass:"text-thin"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Cette semaine n'est pas encore terminée ")]):_vm._e()]),_c('small',{staticClass:"text-big"},[_c('strong',[_vm._v(_vm._s(_vm._f("duration2")(_vm.selectedWeek.total,_vm.selectedWeek.weekLength)))])])])]),(_vm.selectedWeek.total > _vm.ts.weekExcess)?_c('div',{staticClass:"alert alert-danger"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Vos déclarations pour cette semaine dépasse la limite légale fixée à "),_c('strong',[_vm._v(_vm._s(_vm._f("duration")(_vm.ts.weekExcess)))]),_vm._v(" heures. ")]):_vm._e(),(_vm.selectedWeek.total > 0)?_c('nav',{staticClass:"buttons-bar"},[(_vm.ts.editable)?_c('button',{staticClass:"btn btn-danger btn-xs",on:{"click":function($event){return _vm.deleteWeek(_vm.selectedWeek)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" Supprimer les déclarations non-envoyées ")]):_vm._e()]):_vm._e(),(_vm.selectedWeek.total < _vm.selectedWeek.totalOpen && _vm.ts.editable)?_c('section',[_c('p',[_c('i',{staticClass:"icon-help-circled"}),_vm._v(" Vous pouvez compléter automatiquement cette semaine en affectant les "),_c('strong',[_vm._v(_vm._s(_vm._f("duration")((_vm.selectedWeek.totalOpen - _vm.selectedWeek.total)))+" heure(s)")]),_vm._v(" avec une des activités ci-dessous : ")]),_c('wpselector',{attrs:{"others":_vm.ts.otherWP,"workpackages":_vm.ts.workpackages,"selection":_vm.fillSelectedWP,"usevalidation":true},on:{"select":function($event){_vm.fillSelectedWP = $event; _vm.fillWeek(_vm.selectedWeek, _vm.fillSelectedWP);}}})],1):_vm._e()],2):_c('div',[_c('h3',{on:{"click":function($event){$event.preventDefault();if(!$event.shiftKey){ return null; }$event.stopPropagation();_vm.debug = _vm.ts}}},[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Mois de "),_c('strong',[_vm._v(_vm._s(_vm.mois))])]),(_vm.monthRest > 0 && _vm.ts.periodFinished)?_c('section',[_c('p',[_c('i',{staticClass:"icon-help-circled"}),_vm._v(" Vous pouvez compléter automatiquement ce mois avec : ")]),_c('wpselector',{attrs:{"others":_vm.ts.otherWP,"workpackages":_vm.ts.workpackages,"selection":_vm.fillMonthWP,"usevalidation":true},on:{"select":function($event){_vm.fillMonthWP = $event; _vm.handlerFillMonth(_vm.fillMonthWP)}}})],1):_vm._e(),_c('hr'),_vm._l((_vm.weeks),function(week){return (_vm.ts)?_c('section',{staticClass:"card xs"},[_c('header',{staticClass:"week-header",on:{"click":function($event){return _vm.selectWeek(week)}}},[_c('span',[_vm._v(" Semaine "+_vm._s(week.label)+" "),(week.total < week.weekLength)?_c('i',{staticClass:"icon-ok-circled",staticStyle:{"color":"#999"}}):(week.total > week.weekExcess)?_c('i',{staticClass:"icon-attention-circled",staticStyle:{"color":"#993d00"},attrs:{"title":"La déclaration est incomplète pour cette période"}}):_c('i',{staticClass:"icon-ok-circled",staticStyle:{"color":"#2d7800"}})]),_c('small',[_c('strong',{class:(week.total > week.weekExcess)?'has-titled-error':'',attrs:{"title":(week.total > week.weekExcess)?
                                            'Les décalarations dépassent la limite légales et risques d\'être ignorées lors d\'une justification financière dans le cadre des projets soumis aux feuilles de temps'
                                            :''}},[(week.total > week.weekExcess)?_c('i',{staticClass:"icon-attention-1"}):_vm._e(),_vm._v(_vm._s(_vm._f("duration2")(week.total,week.weekLength)))])])])]):_vm._e()}),_c('section',{staticClass:"card xs total interaction-off"},[_c('div',{staticClass:"week-header"},[_c('span',{staticClass:"text-big text-xxl"},[_vm._v("Total")]),_c('small',[_c('strong',{staticClass:"text-large"},[_vm._v(_vm._s(_vm._f("duration2")(_vm.ts.total,_vm.monthLength)))])])])]),(_vm.ts.total > _vm.ts.monthExcess)?_c('div',{staticClass:"alert alert-danger"},[_c('i',{staticClass:"icon-attention-circled"}),_vm._v(" Les heures mensuelles dépassent le cadre légale fixé à "),_c('strong',[_vm._v(_vm._s(_vm._f("duration")(_vm.ts.monthExcess)))]),_vm._v(" heures. ")]):_vm._e(),_c('hr'),_c('h4',[_c('i',{staticClass:"icon-tags"}),_vm._v(" Hors-lot")]),_vm._l((_vm.ts.otherWP),function(a){return (a.total > 0)?_c('section',{staticClass:"card xs"},[_c('div',{staticClass:"week-header"},[_c('span',[_c('i',{class:'icon-'+a.code}),_vm._v(" "+_vm._s(a.label)+" "),(a.validation_state == null)?_c('i'):(a.validation_state.status == 'send-prj')?_c('i',{staticClass:"icon-cube",attrs:{"title":"Validation projet en attente"}}):(a.validation_state.status == 'send-sci')?_c('i',{staticClass:"icon-beaker",attrs:{"title":"Validation scientifique en attente"}}):(a.validation_state.status == 'send-adm')?_c('i',{staticClass:"icon-hammer",attrs:{"title":"Validation administrative en attente"}}):(a.validation_state.status == 'conflict')?_c('i',{staticClass:"icon-minus-circled",attrs:{"title":"Il y'a un problème dans la déclaration"}}):(a.validation_state.status == 'valid')?_c('i',{staticClass:"icon-ok-circled",attrs:{"title":"Cette déclaration est valide"}}):_vm._e(),_c('br'),_c('em',{staticClass:"text-thin"},[_vm._v(_vm._s(a.description))]),_c('button',{staticClass:"btn btn-default btn-xs",on:{"click":function($event){if(!$event.type.indexOf('key')&&_vm._k($event.keyCode,"default",undefined,$event.key,undefined)){ return null; }$event.preventDefault();return _vm.handledEditComment('hl', a)}}},[_c('i',{staticClass:"icon-chat-alt"}),_vm._v(" Commentaire ")])]),_c('small',[_c('strong',{staticClass:"text-large"},[_vm._v(_vm._s(_vm._f("duration2")(a.total,_vm.monthLength)))])])])]):_vm._e()}),_c('section',{staticClass:"card xs total interaction-off"},[_c('div',{staticClass:"week-header"},[_c('span',{staticClass:"text-big text-xxl"},[_vm._v("Total")]),_c('small',[_c('strong',{staticClass:"text-large"},[_vm._v(_vm._s(_vm._f("duration2")(_vm.totalWP,_vm.monthLength)))])])])]),_c('h4',[_c('i',{staticClass:"icon-cubes"}),_vm._v(" Activités pour cette période")]),(_vm.ts.activities.length == 0)?_c('p',{staticClass:"alert alert-info"},[_vm._v(" Vous n'être identifié comme déclarant sur aucune activité pour cette période. Si cette situation vous semble anormale, prenez contact avec votre responsable scientifique. ")]):_vm._l((_vm.ts.activities),function(a){return _c('section',{staticClass:"card xs"},[_c('div',{staticClass:"week-header"},[_c('span',[_c('strong',[_vm._v(_vm._s(a.acronym))]),_c('span',{staticClass:"icon-tag",style:({'color': _vm._colorsProjects[a.acronym] }),on:{"click":function($event){_vm.configureColor = true}}},[_vm._v(" ")]),(a.validation_state == null)?_c('i'):(a.validation_state.status == 'send-prj')?_c('i',{staticClass:"icon-cube",attrs:{"title":"Validation projet en attente"}}):(a.validation_state.status == 'send-sci')?_c('i',{staticClass:"icon-beaker",attrs:{"title":"Validation scientifique en attente"}}):(a.validation_state.status == 'send-adm')?_c('i',{staticClass:"icon-hammer",attrs:{"title":"Validation administrative en attente"}}):(a.validation_state.status == 'conflict')?_c('i',{staticClass:"icon-minus-circled",attrs:{"title":"Il y'a un problème dans la déclaration"}}):(a.validation_state.status == 'valid')?_c('i',{staticClass:"icon-ok-circled",attrs:{"title":"Cette déclaration est valide"}}):_vm._e(),_c('br'),_c('em',{staticClass:"text-thin"},[_vm._v(_vm._s(a.label))]),_c('button',{staticClass:"btn btn-default btn-xs",on:{"click":function($event){if(!$event.type.indexOf('key')&&_vm._k($event.keyCode,"default",undefined,$event.key,undefined)){ return null; }$event.preventDefault();return _vm.handledEditComment('prj', a)}}},[_c('i',{staticClass:"icon-chat-alt"}),_vm._v(" Commentaire ")])]),_c('small',{staticClass:"subtotal"},[_c('strong',{staticClass:"text-large"},[_vm._v(_vm._s(_vm._f("duration2")(a.total,_vm.monthLength)))])])])])}),_c('section',{staticClass:"card xs total interaction-off"},[_c('div',{staticClass:"week-header"},[_c('span',[_c('strong',{staticClass:"text-big text-xxl"},[_vm._v("Total")]),_c('br'),_c('small',[_vm._v("Pour les activités soumises aux déclarations")])]),_c('small',[_c('strong',{staticClass:"text-large"},[_vm._v(_vm._s(_vm._f("duration2")(_vm.ts.periodDeclarations,_vm.monthLength)))])])])]),(_vm.ts.periodsValidations.length)?_c('div',[_c('h3',[_vm._v("Procédures de validation pour cette période")]),_vm._l((_vm.ts.periodsValidations),function(periodValidation){return _c('section',{staticClass:"card card-xs"},[(periodValidation.status == 'valid')?_c('i',{staticClass:"icon-ok-circled"}):(periodValidation.status == 'conflict')?_c('i',{staticClass:"icon-minus-circled"}):_c('i',{staticClass:"icon-history"}),_vm._v(" "+_vm._s(periodValidation.label)+" "),_c('a',{attrs:{"href":"#"},on:{"click":function($event){_vm.popup = periodValidation.log}}},[_vm._v("Historique")]),(periodValidation.status == 'conflict'
                           && (periodValidation.rejectadmin_message || periodValidation.rejectsci_message || periodValidation.rejectactivity_message ))?_c('a',{attrs:{"href":"#"},on:{"click":function($event){_vm.rejectPeriod = periodValidation}}},[_vm._v("Détails sur le rejet")]):_vm._e()])})],2):_vm._e()],2),_c('nav',{staticClass:"buttons-bar"},[(_vm.ts.submitable)?_c('button',{staticClass:"btn btn-primary",class:{ 'disabled': !_vm.ts.submitable, 'enabled': _vm.ts.submitable && !_vm.loading},staticStyle:{"margin-left":"auto"},on:{"click":function($event){return _vm.validateMonth()}}},[(_vm.loading)?_c('i',{staticClass:"icon-spinner animate-spin"}):_c('i',{staticClass:"icon-upload"}),(_vm.ts.hasConflict)?_c('span',[_vm._v("Réenvoyer")]):_c('span',[_vm._v("Soumettre mes déclarations")])]):_c('span',{staticClass:"alert",class:'alert-' +_vm.ts.submitableClass},[_vm._v(" Vous ne pouvez pas soumettre cette période"),_c('br'),_c('small',[_vm._v(_vm._s(_vm.ts.submitableInfos))])])])],1)]):_vm._e()],1)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Oups !")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Déclaration rejetée !")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-help-circled"}),_vm._v(" Informations légales")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('p',[_vm._v(" Dans le cadre des projets soumis aux feuilles de temps, l'organisme financeur impose la justification des heures, "),_c('strong',[_vm._v("incluant les activités hors-projets")]),_vm._v(". Le culum des heures déclarées doit respecter le cadre légale : "),_c('br')])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('p',[_vm._v(" Selon les modalités de financement, les dépacements (même en éxcédent) peuvent être considérés comme des "),_c('em',[_vm._v("irrégularité")]),_vm._v(" pouvant déclencher la suspension ou le remboursement des financements engagés ou à venir. ")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-bug"}),_vm._v(" Debug")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('header',{staticClass:"month-header"},[_c('strong',[_vm._v("Lundi")]),_c('strong',[_vm._v("Mardi")]),_c('strong',[_vm._v("Mercredi")]),_c('strong',[_vm._v("Jeudi")]),_c('strong',[_vm._v("Vendredi")]),_c('strong',[_vm._v("Samedi")]),_c('strong',[_vm._v("Dimanche")])])}]


// CONCATENATED MODULE: ./src/TimesheetMonth.vue?vue&type=template&id=3d0f00f6&

// CONCATENATED MODULE: ./src/components/AjaxResolve.js
/* harmony default export */ var AjaxResolve = ({
    resolve( message, ajaxResponse ){
        let serverMsg = "Erreur inconnue";
        if( ajaxResponse ){
            serverMsg = ajaxResponse.body;

            if( ajaxResponse.status == 403 ){
                serverMsg = "Vous avez été déconnectez de l'application";
            }
        }
        return message + " (Réponse : " + serverMsg +")";
    }
});
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetMonth.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


// node node_modules/.bin/vue-cli-service build --name TimesheetMonth --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/TimesheetMonth.vue

    


    let defaultDate = new Date();
    let moment = function () {
    };

    /* harmony default export */ var TimesheetMonthvue_type_script_lang_js_ = ({
        name: 'TimesheetMonth',

        props: {
            moment: {required: true},
            bootbox: {required: true},
            declarationInHours: {required: true},
            defaultMonth: {default: defaultDate.getMonth() + 1},
            defaultYear: {default: defaultDate.getFullYear()},
            defaultDayLength: {default: 8.0},
            urlimport: {default: null},
            urlValidation: { required: true },
            url:{ required: true }
        },

        components: {
            timesheetmonthday: __webpack_require__("c8e1").default,
            timesheetmonthdaydetails: __webpack_require__("ea58").default,
            timechooser: __webpack_require__("6600").default,
            wpselector: __webpack_require__("a76e").default
        },

        data() {
            return {
                // Gestion de l'affichage de la fenêtre
                // d'édition/ajout de créneaux
                editWindow: {
                    display: false,
                    wp: null,
                    type: 'infos',
                },

                commentEdited: null,
                commentEditedLabel: "",
                commentEditedContent: "",

                configureColor: false,

                _colorsProjects: null,
                colors: [
                    "#0b098c",
                    "#09518c",
                    "#09858c",
                    "#098c66",
                    "#098c27",
                    "#818c09",
                    "#8c6d09",
                    "#8c4e09",
                    "#8c1109",
                    "#8c0971",
                    "#8c6f09"],

                copyClipboard: null,
                clipboardDataDay: null,

                showHours: true,

                loading: false,
                debug: null,
                help: false,
                popup: "",
                screensend: null,
                sendaction: null,

                //
                error: '',
                commentaire: '',

                fillSelectedWP: null,

                // Données reçues
                ts: null,
                month: null,
                year: null,
                dayLength: null,
                selectedWeek: null,

                rejectPeriod: null,

                selectedDay: null,
                dayMenuLeft: 50,
                dayMenuTop: 50,
                dayMenu: 'none',
                selectedWP: null,
                selectionWP: null,
                selectedTime: null,
                dayMenuSelected: null,
                dayMenuTime: 0.0,
                editedTimesheet: null,
                fillMonthWP: null
            }
        },

        filters: {

            date(value, format = "ddd DD MMMM  YYYY") {
                var m = moment(value);
                return m.format(format);
            },
            datefull(value, format = "ddd DD MMMM  YYYY") {
                var m = moment(value);
                return m.format(format);
            },
            day(value, format = "ddd DD") {
                var m = moment(value);
                return m.format(format);
            }
        },

        computed: {

            projectsColors(){
                return this._colorsProjects;
            },

            recapsend() {
                let recap = {}, hl = {}, comments = {};

                Object.keys(this.ts.otherWP).forEach( code => {
                    let hlDef = this.ts.otherWP[code];

                    hl[hlDef.code] = {
                        id: hlDef.code,
                        code: hlDef.code,
                        label: hlDef.label,
                        days: {},
                        total: hlDef.total,
                        comment: hlDef.comment
                    };

                    comments[hlDef.code] = hlDef.comment;
                });

                Object.keys(this.ts.activities).forEach(a => {

                    let activity = this.ts.activities[a];
                    let project_id = activity.project_id;
                    let project = activity.project;
                    let com = activity.comment;

                    comments[activity.id] = com;

                    if (this.screensend && this.screensend.hasOwnProperty(a))
                        com = this.screensend[a];

                    if (!recap.hasOwnProperty(project_id)) {

                        recap[project_id] = {
                            label: project,
                            id: project_id,
                            activities: {}
                        }
                    }

                    if (!recap[project_id].activities.hasOwnProperty(activity.id)) {
                        recap[project_id].activities[activity.id] = {
                            id: activity.id,
                            label: activity.label,
                            acronym: activity.acronym,
                            total: activity.total,
                            days: {},
                            workpackages: {},
                            comment: com
                        }
                    }
                });

                Object.keys(this.ts.workpackages).forEach(wp => {
                    let workpackage = this.ts.workpackages[wp];
                    let project_id = workpackage.project_id;
                    let activity_id = workpackage.activity_id;
                    if (recap[project_id]) {
                        if (recap[project_id].activities[activity_id]) {
                            recap[project_id].activities[activity_id].workpackages[workpackage.id] = {
                                label: workpackage.code,
                                description: workpackage.label,
                                total: workpackage.total,
                                days: {}
                            }
                        }
                    }
                });

                Object.keys(this.ts.days).forEach(d => {
                    let day = this.ts.days[d];
                    day.declarations.forEach(dec => {
                        let activity_id = dec.activity_id,
                            project_id = dec.project_id,
                            wp_id = dec.wp_id;

                        if( !recap[project_id].activities[activity_id].days.hasOwnProperty(d) )
                            recap[project_id].activities[activity_id].days[d] = 0.0;

                        if( !recap[project_id].activities[activity_id].workpackages[wp_id].days.hasOwnProperty(d) )
                            recap[project_id].activities[activity_id].workpackages[wp_id].days[d] = 0.0;

                        recap[project_id].activities[activity_id].days[d] += dec.duration;
                        recap[project_id].activities[activity_id].workpackages[wp_id].days[d] += dec.duration;
                    });

                    if( !day.othersWP ) return;

                    day.othersWP.forEach(dec => {
                        let code = dec.code;

                        if( !hl.hasOwnProperty(code) ) {
                            hl[code] = {
                                days: {},
                                total: 0.0
                           };
                        }

                        if( !hl[code].days.hasOwnProperty(d) )
                            hl[code].days[d] = 0.0;

                        hl[code].days[d] += dec.duration;
                        hl[code].total += dec.duration;
                    });
                });


                return {
                    lot: recap,
                    comments: comments,
                    hl: hl
                };
            },

            monthRest(){
                return this.monthLength - this.ts.total;
            },

            monthLength(){
                let t = 0.0;
                for( let day in this.ts.days ){
                    t += this.ts.days[day].dayLength;
                }
                return t;
            },

            dayLabel() {
                if (this.selectedDay)
                    return moment(this.selectedDay.data).format('dddd DD MMMM YYYY');
                else
                    return "";
            },

            /**
             * Retourne la durée de remplissage d'une journée.
             */
            fillDayValue() {
                let reste = this.selectedDay.dayLength - this.selectedDay.duration;
                if (reste < 0) {
                    reste = 0;
                }
                return reste;
            },

            mois() {
                return moment(this.ts.from).format('MMMM YYYY');
            },

            periodCode() {
                return this.ts.from.substr(0, 7);
            },

            cssDayMenu() {
                return {
                    display: this.dayMenu,
                    top: this.dayMenuTop + 'px',
                    left: this.dayMenuLeft + 'px'
                }
            },

            totalWP(){
              let t = 0.0;
              for( let other in this.ts.otherWP ) {
                  if( this.ts.otherWP[other].total )
                      t += this.ts.otherWP[other].total;
              }
              return t;
            },

            /**
             * Retourne les informations par semaine.
             *
             * @returns {Array}
             */
            weeks() {
                let weeks = [];
                if (this.ts && this.ts.days) {

                    let firstDay = this.ts.days[1];
                    let currentWeekNum = firstDay.week;

                    let currentWWeek = {
                        label: currentWeekNum,
                        days: [],
                        total: 0.0,
                        totalOpen: 0.0,
                        weekLength: 0.0,
                        editable: this.ts.editable,
                        drafts: 0,
                        weekExcess: this.ts.weekExcess
                    };

                    for( let d in this.ts.days ){

                        let currentDay = this.ts.days[d];

                        if (currentWeekNum != currentDay.week) {
                            weeks.push(currentWWeek);
                            currentWWeek = {
                                label: currentDay.week,
                                days: [],
                                total: 0.0,
                                totalOpen: 0.0,
                                weekLength: 0.0,
                                drafts: 0,
                                weekExcess: this.ts.weekExcess
                            };
                        }

                        currentWeekNum = currentDay.week;
                        currentWWeek.total += currentDay.duration;

                        if (!(currentDay.locked || currentDay.closed)) {
                            currentWWeek.totalOpen += currentDay.dayLength;
                        }

                        if( currentDay.declarations ) {
                            currentDay.declarations.forEach(d => {
                                if (d.status_id == 2) {
                                    currentWWeek.drafts++;
                                }
                            });
                        }

                        if (!currentDay.closed)
                            currentWWeek.weekLength += currentDay.dayLength;

                        currentWWeek.days.push(currentDay);
                    }
                    if (currentWWeek.days.length)
                        weeks.push(currentWWeek);
                }
                return weeks;
            }
        },

        methods: {
            handledEditComment(type, data){
                console.log("MODIFICATION COMMENTAIRE ", type, data);

                this.commentEditedLabel = data.label;
                this.commentEdited = data;
                this.commentEditedContent = data.comment;

            },

            handlerSendComment(){
                if( this.ts.editable ) {
                    var type, id, code;
                    if (this.commentEdited.id) {
                        type = 'wp';
                        id = this.commentEdited.id;
                        code = "";
                    } else {
                        type = 'hl';
                        code = this.commentEdited.code;
                        id = "";
                    }
                    var formData = new FormData();
                    formData.append('action', 'comment');
                    formData.append('period', this.ts.period);
                    formData.append('type', type);
                    formData.append('id', id);
                    formData.append('code', code);
                    formData.append('content', this.commentEditedContent);

                    console.log("Envoi du commentaire");

                    this.$http.post('', formData).then(
                        ok => {
                            this.fetch();
                        },
                        ko => {
                            this.error = AjaxResolve.resolve("Impossible d'enregistrer le commentaire", ko);
                        }
                    ).then(foo => {
                        this.selectedWeek = null;
                        this.screensend = null;
                        this.loading = false;
                        this.commentEdited = null;
                    });
                }
            },

            getAcronymColor(acronym){
                if( this._colorsProjects.hasOwnProperty(acronym) ){
                    return this._colorsProjects[acronym];
                }
                return "#333333";
            },

            /**
             * Applique la configuration de la couleur pour l'acronyme donné.
             */
            handlerChangeColor(acronym, event){
                 let old = JSON.parse(JSON.stringify(this._colorsProjects));
                old[acronym] = event.target.value;
                this._colorsProjects = old;
                if( window.localStorage ){
                    window.localStorage.setItem('colorsprojects', JSON.stringify(this._colorsProjects));
                }
                this.$forceUpdate();
            },

            handlerFillMonth(withWP){

                let data = [];

                Object.keys(this.ts.days).forEach(date => {
                    let d = this.ts.days[date];
                    if (!(d.closed || d.locked || d.duration >= d.dayLength)) {
                        data.push({
                            'day': d.date,
                            'wpId': withWP.id,
                            'code': withWP.code,
                            'commentaire': '',
                            'duration': (d.dayLength - d.duration) * 60
                        });
                    }
                });
                this.performAddDays(data);
            },

            handlerPasteDay( day ){
                let datasSendable = [];

                this.clipboardDataDay.forEach(item => {
                    let data = JSON.parse(JSON.stringify(item));
                    data.day = day.datefull;
                    datasSendable.push(data);
                });

                this.performAddDays(datasSendable);
            },

            handlerCopyDay(day){
                let datasCopy = [];

                if( day.declarations ){
                    day.declarations.forEach(timesheet => {
                        datasCopy.push({
                            code: timesheet.wpCode,
                            comment: timesheet.comment,
                            duration: timesheet.duration * 60,
                            wpId: timesheet.wp_id,
                        });
                    });
                }
                if( day.othersWP ) {
                    day.othersWP.forEach(timesheet => {
                        datasCopy.push({
                            code: timesheet.code,
                            comment: "",
                            duration: timesheet.duration * 60,
                            wpId: null,
                        });
                    });
                }

                this.clipboardDataDay = datasCopy;
            },

            editTimesheet(timesheet, day) {
                this.editedTimesheet = timesheet;
                this.commentaire = timesheet.comment;
                this.selectedDay = day;
                this.dayMenuTime = timesheet.duration;
                if (timesheet.wp_id) {
                    this.selectionWP = this.getWorkpackageById(timesheet.wp_id);
                }
                else if (timesheet.code) {
                    this.selectionWP = this.getHorsLotByCode(timesheet.code);
                }
            },

            getHorsLotByCode(code){
                return this.ts.otherWP[code];
            },

            getWorkpackageById(id) {
                return this.ts.workpackages[id];
            },

            reSendPeriod(periodValidation) {
                this.validateMonth("resend");
            },

            /**
             * Déclenchement de la validation côté serveur pour vérifier si les créneaux saisis sont conformes.
             */
            validateMonth(action="sendmonth"){
                this.loading = true;
                this.$http.get(this.urlValidation +'?year=' + this.ts.year + '&month=' + this.ts.month).then(
                    ok => {
                        console.log("OK");
                        this.sendMonth(action);
                    },
                    ko => {
                        console.log(ko);
                        this.error = ko.body;
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                    this.loading = false;
                });
            },


            /**
             * Dans cette méthode, les données pour constituer les comentaires sont générées.
             */
            sendMonth(action="sendmonth") {

                this.sendaction = action;
                if ( !(this.ts.submitable == true || this.ts.hasConflict == true) ) {
                    this.error = 'Vous ne pouvez pas soumettre vos déclarations pour cette période : ' + this.ts.submitableInfos;
                    return;
                }
                let aggregatProjet = {};

                // Aggrégation des commentaires des activités
                Object.keys(this.ts.activities).forEach(a => {
                    if( this.ts.activities[a].comment ){
                        aggregatProjet[a] = [this.ts.activities[a].comment];
                    } else {
                        Object.keys(this.ts.days).forEach(d => {
                            let day = this.ts.days[d];
                            day.declarations.forEach(timesheet => {
                                if( timesheet.activity_id == a ){
                                    let key = timesheet.activity_id;

                                    if( !aggregatProjet.hasOwnProperty(key) ){
                                        aggregatProjet[key] = [];
                                    }

                                    if( timesheet.comment && aggregatProjet[key].indexOf(timesheet.comment) < 0 ){
                                        aggregatProjet[key].push(timesheet.comment);
                                    }
                                }
                            });
                        });
                    }
                });

                // Aggrégation des commentaires des Hors-Lot
                Object.keys(this.ts.otherWP).forEach(a => {
                    if( this.ts.otherWP[a].comment ){
                        aggregatProjet[a] = [this.ts.otherWP[a].comment];
                    } else {
                        Object.keys(this.ts.days).forEach(d => {
                            let day = this.ts.days[d];
                            day.declarations.forEach(timesheet => {
                                if( timesheet.activity_id == a ){
                                    let key = timesheet.code;

                                    if( !aggregatProjet.hasOwnProperty(key) ){
                                        aggregatProjet[key] = [];
                                    }

                                    if( timesheet.comment && aggregatProjet[key].indexOf(timesheet.comment) < 0 ){
                                        aggregatProjet[key].push(timesheet.comment);
                                    }
                                }
                            });
                        });
                    }
                });

                Object.keys(aggregatProjet).forEach(id => {
                    aggregatProjet[id] = " - " +aggregatProjet[id].join("\n - ")
                });

                this.screensend = aggregatProjet;
            },

            sendMonthProceed(){

                // Données à envoyer
                var datas = new FormData();
                datas.append('action', this.sendaction);
                datas.append('comments', JSON.stringify(this.screensend));
                datas.append('datas', JSON.stringify({
                    from: this.ts.from,
                    to: this.ts.to
                }));

                this.loading = true;

                this.$http.post('', datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible d\'envoyer la période', ko);
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                    this.screensend = null;
                    this.loading = false;
                });
            },

            fillWeek(week, wp) {
                let data = [];
                week.days.forEach(d => {
                    if (!(d.closed || d.locked || d.duration >= d.dayLength)) {
                        data.push({
                            'day': d.date,
                            'wpId': wp.id,
                            'code': wp.code,
                            'commentaire': this.commentaire,
                            'duration': (d.dayLength - d.duration) * 60
                        });
                    }
                });
                this.performAddDays(data);
            },

            fillDay() {

            },

            selectWeek(week) {
                this.selectedDay = null;
                this.selectedWeek = week;
            },

            deleteWeek(week) {
                let ids = [];
                week.days.forEach(d => {
                    d.declarations.forEach(t => {
                        ids.push(t.id);
                    })
                    if( d.othersWP ){
                        d.othersWP.forEach(t => {
                            ids.push(t.id);
                        })
                    }
                })

                this.performDelete(ids);
            },

            deleteTimesheet(timesheet) {
                this.performDelete([timesheet.id]);
            },

            handlerPasteWeek( week ){
                let datasSendable = [];
                week.days.forEach(day => {
                    this.clipboardData.forEach(item => {
                        if( item.day == day.day ){
                            let data = JSON.parse(JSON.stringify(item));
                            data.day = day.datefull;
                            datasSendable.push(data);
                        }
                    })
                });
                this.performAddDays(datasSendable);
            },

            handlerCopyWeek(week){
                let datasCopy = [];
                week.days.forEach(day => {
                    if( day.declarations ){
                        day.declarations.forEach(timesheet => {
                            datasCopy.push({
                               code: timesheet.wpCode,
                               comment: timesheet.comment,
                               duration: timesheet.duration * 60,
                               day: day.day,
                               wpId: timesheet.wp_id,
                            });
                        });
                    }
                    if( day.othersWP ) {
                        day.othersWP.forEach(timesheet => {
                            datasCopy.push({
                                code: timesheet.code,
                                comment: "",
                                duration: timesheet.duration * 60,
                                day: day.day,
                                wpId: null,
                            });
                        });
                    }
                });
                this.clipboardData = datasCopy;
            },

            handlerSaveMenuTime() {
                let data = [{
                    'id': this.editedTimesheet ? this.editedTimesheet.id : null,
                    'day': this.selectedDay.date,
                    'wpId': this.selectionWP.id,
                    'duration': this.dayMenuTime * 60,
                    'comment': this.commentaire,
                    'code': this.selectionWP.code
                }];

                this.performAddDays(data);
            },

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // TRAITEMENT DES CRENEAUX

            /**
             * Déclenchement de l'envoi des créneaux à l'API.
             */
            performAddDays(datas) {



                let formData = new FormData();
                formData.append('timesheets', JSON.stringify(datas));
                formData.append('action', "add");

                this.loading = "Enregistrement des créneaux";

                this.$http.post(this.url, formData).then(
                    ok => {
                        this.fetch(false);
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible d\'enregistrer les créneaux', ko);
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                    this.selectionWP = null;
                    this.loading = false;
                    this.commentaire = "";
                    this.editedTimesheet = null;
                });
                ;
            },

            performDelete(ids) {
                this.loading = "Suppression des créneaux";
                this.$http.delete(this.url + '&id=' + ids.join(',')).then(
                    ok => {
                        this.fetch(false);
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de supprimer le créneau', ko);
                    }
                ).then(foo => {
                    this.selectedWeek = null;
                    this.loading = false;
                });
            },

            handlerDayUpdated() {
                let t = arguments[0];
                this.dayMenuTime = (t.h + t.m);
            },

            handlerSelectWP(w) {
                this.selectedWP = w;
                this.selectionWP = w;
                this.dayMenu = 'none';
            },

            hideWpSelector() {
                this.selectedWP = null;
                this.selectedTime = null;
                this.dayMenu = 'none';
            },

            handlerKeyDown(event){

            },

            handlerClick() {
                this.hideWpSelector();
            },

            handlerWpFromDetails(wp) {
                this.handlerSelectWP(wp);
            },

            handlerDayMenu(event, day) {
                this.dayMenuLeft = event.clientX;
                this.dayMenuTop = event.clientY;
                this.dayMenu = 'block';
                this.selectedDay = day;
            },

            handlerSelectData(day) {
                this.selectedDay = day;
            },

            /**
             * Chargement du mois suivant
             */
            nextYear() {
                this.year += 1;
                this.fetch(true);
            },

            /**
             * Chargement du mois suivant
             */
            nextMonth() {
                this.month += 1;
                if (this.month > 12) {
                    this.month = 1;
                    this.nextYear();
                } else {
                    this.fetch(true);
                }
            },

            /**
             * Charement de l'année précédente.
             */
            prevYear() {
                this.year -= 1;
                this.fetch(true);
            },

            prevMonth() {
                this.month -= 1;
                if (this.month < 1) {
                    this.month = 12;
                    this.prevYear();
                } else {
                    this.fetch(true);
                }
            },

            fetch(clear = true) {

                this.loading = "Chargement de la période";

                if (clear) {
                    this.selectedDay = null;
                    this.selectedWeek = null;
                }

                let daySelected;

                if (this.selectedDay)
                    daySelected = this.selectedDay.i;

                this.$http.get(this.url + '&month=' + this.month + '&year=' + this.year).then(
                    ok => {
                        this.dayLength = ok.body.dayLength;
                        this.ts = ok.body
                        if (daySelected) {
                            this.selectedDay = this.ts.days[daySelected];
                        }
                        this.selectedWP = null;
                        this.selectionWP = null;
                        this.fillSelectedWP = null;
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de charger cette période', ko);
                    }
                ).then(foo => {
                    this.loading = false
                });
            }
        },


        mounted() {
            moment = this.moment;
            this.month = this.defaultMonth;
            this.year = this.defaultYear;
            this.dayLength = this.defaultDayLength;


            if( window.localStorage ){
                if( window.localStorage.getItem("colorsprojects") ){
                    this._colorsProjects = JSON.parse(window.localStorage.getItem("colorsprojects"));
                } else {
                    this._colorsProjects = {};
                }
            }
            this.fetch(true)
        }
    });

// CONCATENATED MODULE: ./src/TimesheetMonth.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_TimesheetMonthvue_type_script_lang_js_ = (TimesheetMonthvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/TimesheetMonth.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_TimesheetMonthvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var TimesheetMonth = (component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (TimesheetMonth);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=TimesheetMonth.umd.js.map
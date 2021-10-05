(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ActivityTimesheetSynthesisMenu"] = factory();
	else
		root["ActivityTimesheetSynthesisMenu"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"a257e54e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityTimesheetSynthesisMenu.vue?vue&type=template&id=e7973db4&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('button',{staticClass:"btn btn-primary",class:{'disabled': _vm.loading},on:{"click":function($event){return _vm.handlerOpenSynthesis()}}},[(_vm.loading)?_c('i',{staticClass:"icon-spinner animate-spin"}):_c('i',{staticClass:"icon-calendar"}),_vm._v(" Synthèse des déclarations ")]),_c('div',{staticClass:"overlay-content"},[_c('a',{staticClass:"overlay-closer",attrs:{"href":"#"},on:{"click":function($event){return _vm.handlerCloseSynthesis()}}},[_vm._v("Close")]),(_vm.synthese)?_c('div',{staticClass:"activity-timesheet-synthesis"},[_c('nav',{staticClass:"text-center"},_vm._l((_vm.persons),function(p,person_id){return _c('button',{staticClass:"btn",class:_vm.filterPersons.indexOf(parseInt(person_id)) < 0 ? 'btn-primary' : 'btn-default',on:{"click":function($event){return _vm.handlerTooglePerson(person_id)}}},[_vm._v(" "+_vm._s(p)+" ")])}),0),_vm._l((_vm.sortedDatas.years),function(y){return _c('section',{staticClass:"year",class:{'open': y.open }},[_c('h3',{staticClass:"line",on:{"click":function($event){return _vm.handlerOpenYear(y)}}},[_c('strong',[_c('i',{directives:[{name:"show",rawName:"v-show",value:(!y.open),expression:"!y.open"}],staticClass:"icon-angle-right"}),_c('i',{directives:[{name:"show",rawName:"v-show",value:(y.open),expression:"y.open"}],staticClass:"icon-angle-down"}),_vm._v(" "+_vm._s(y.label)+" ")]),_c('em',{directives:[{name:"show",rawName:"v-show",value:(!y.open),expression:"!y.open"}],staticClass:"total"},[_vm._v(_vm._s(_vm._f("hours")(y.totalmain)))])]),_vm._l((y.periods),function(p){return _c('section',{directives:[{name:"show",rawName:"v-show",value:(y.open),expression:"y.open"}],staticClass:"month",class:{'open': y.open }},[_c('h4',{staticClass:"line",on:{"click":function($event){return _vm.handlerOpenPeriod(p)}}},[_c('strong',[_c('i',{directives:[{name:"show",rawName:"v-show",value:(!p.open),expression:"!p.open"}],staticClass:"icon-angle-right"}),_c('i',{directives:[{name:"show",rawName:"v-show",value:(p.open),expression:"p.open"}],staticClass:"icon-angle-down"}),_vm._v(" "+_vm._s(p.label)+" ")]),_c('em',{directives:[{name:"show",rawName:"v-show",value:(!p.open),expression:"!p.open"}],staticClass:"total"},[_vm._v(_vm._s(_vm._f("hours")(p.totalmain)))])]),_vm._l((p.persons),function(pers){return _c('section',{directives:[{name:"show",rawName:"v-show",value:(p.open),expression:"p.open"}],staticClass:"person",class:{'open': p.open },on:{"click":function($event){return _vm.handlerOpenPerson(pers)}}},[_c('h5',{staticClass:"line"},[_c('strong',[_vm._v(_vm._s(pers.label))]),_c('em',{staticClass:"total"},[_vm._v(_vm._s(_vm._f("hours")(pers.totalmain)))])]),(pers.open)?_c('section',{staticClass:"details-person"},[_c('article',{staticClass:"line"},[_c('strong',[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(_vm.mainContext)+" ")]),_c('em',[_vm._v(" "+_vm._s(_vm._f("hours")(pers.totalmain))+" ")])]),(pers.othersprojects.total > 0)?_c('section',{staticClass:"othersprojects"},_vm._l((pers.othersprojects.projects),function(projectDuration,projectName){return _c('article',{staticClass:"line"},[_c('strong',[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(projectName)+" ")]),_c('em',{staticStyle:{"font-weight":"800"}},[_vm._v(" "+_vm._s(_vm._f("hours")(projectDuration))+" ")])])}),0):_vm._e(),(pers.others.total > 0)?_c('section',{staticClass:"othersprojects"},_vm._l((pers.others.context),function(otherDuration,otherName){return _c('article',{staticClass:"line"},[_c('strong',[_vm._v(" "+_vm._s(_vm._f("contextLabel")(otherName))+" ")]),_c('em',[_vm._v(" "+_vm._s(_vm._f("hours")(otherDuration))+" ")])])}),0):_vm._e(),_c('article',{staticClass:"line totalline"},[_c('strong',[_vm._v(" Total pour cette période : ")]),_c('em',[_vm._v(" "+_vm._s(_vm._f("hours")(pers.total))+" ")])])]):_vm._e()])}),_c('section',{directives:[{name:"show",rawName:"v-show",value:(p.open),expression:"p.open"}],staticClass:"person"},[_c('h5',{staticClass:"line totalline"},[_c('strong',[_vm._v("Total pour la période "+_vm._s(p.label))]),_c('em',{staticClass:"total"},[_vm._v(_vm._s(_vm._f("hours")(p.totalmain)))])])])],2)}),_c('section',{directives:[{name:"show",rawName:"v-show",value:(y.open),expression:"y.open"}],staticClass:"person"},[_c('h5',{staticClass:"line totalline"},[_c('strong',[_vm._v("Total pour "+_vm._s(y.label))]),_c('em',{staticClass:"total"},[_vm._v(_vm._s(_vm._f("hours")(y.totalmain)))])])])],2)})],2):_vm._e()]),(_vm.open)?_c('div',{staticClass:"overlay"}):_vm._e()])}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/ActivityTimesheetSynthesisMenu.vue?vue&type=template&id=e7973db4&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityTimesheetSynthesisMenu.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
node node_modules/.bin/gulp activityTimesheetSynthesisMenuWatch

Pour compiler :
node node_modules/.bin/gulp activityTimesheetSynthesisMenu

 */

let periodStr = function (year, month) {
  return year + '-' + (month < 10 ? '0' + month : month);
};

let months = ["", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre"];

let periodFullStr = function (year, month) {
  return months[month] + ' ' + year;
};

let contextsLabels = {
  'research' : "Autres recherches",
      'conges' : "Congès",
      'training' : "Formation",
      'sickleave' : "Arrêt maladie",
      'other' : "Autre activité"
};

/* harmony default export */ var ActivityTimesheetSynthesisMenuvue_type_script_lang_js_ = ({

  props: {
    url: {require: true}
  },

  data() {
    return {
      configuration: null,
      open: false,
      // Donnèes brutes
      synthese: null,
      structuredDatas: {},

      // Filtres
      filterPersons: [],

      // Donnèes calculées au fetch
      mainContext: null, // Projet référent
      mainContextId: null, // Projet référent
      contexts: null, // Autres contextes
      periods: null, // Liste des périodes
      persons: null, // Liste des déclarants
      years: null, // Années

      //
      openYear: [],
      openPeriod: [],
      openPerson: [],
    }
  },

  filters: {
    hours(str) {
      let t = parseFloat(str);
      let rnd = Math.round(t * 100);
      return (rnd / 100).toFixed(2);
    },
    contextLabel(contextKey){
      if( contextsLabels.hasOwnProperty(contextKey) ){
        return contextsLabels[contextKey];
      }
      return contextKey+"*";
    }
  },

  computed: {
    byDate() {
      let bydate = {};
    },

    sortedDatas() {

      // Structure racine
      let out = {
        "totaux": {
          "all": 0.0,
          "totalmain": 0.0,
          "othersprojects": {
            "projects": {},
            "total": 0.0
          },
          "others": {
            "context": {},
            "total": 0.0
          },
        },
        "years": {},
      };

      // Annèes
      this.years.forEach(year => {
        out.years[year] = {
          "key": year,
          "label": year,
          "totalmain": 0.0,
          "open": this.openYear.indexOf(year) >= 0,
          "periods": {}
        }
      });

      //
      this.periods.forEach(period => {
        let year = period.substring(0, 4);
        let month = period.substring(5, 7);
        out.years[year].periods[period] = {
          "key": period,
          "label": periodFullStr(year, parseInt(month)),
          "totalmain": 0.0,
          "persons": {},
          "open": this.openPeriod.indexOf(period) >= 0,
        }
      });

      this.synthese.synthesis.forEach(d => {

        let period = d.period;
        let year = parseInt(d.period.substring(0, 4));
        let month = parseInt(d.period.substring(5, 7));
        let person_id = d.person_id;
        let person = d.displayname;
        let context = d.context;
        let activity_id = d.activity_id;
        let type = d.type;
        let duration = parseFloat(d.duration);

        if (this.filterPersons.indexOf(person_id) >= 0) {
          return;
        }

        if (!out.years[year].periods[period].persons.hasOwnProperty(person_id)) {
          let indexOpen = year+"-"+period+"-"+person_id;
          out.years[year].periods[period].persons[person_id] = {
            "key": person_id,
            "label": person,
            "indexOpen": indexOpen,
            "open": this.openPerson.indexOf(indexOpen) >= 0,
            "totalmain": 0.0,
            "total": 0.0,
            "othersprojects": {
              "projects": {},
              "total": 0.0
            },
            "others": {
              "context": {},
              "total": 0.0
            },
          }
        }

        // --- Comptabilisé pour le projet principal
        if (activity_id == this.mainContextId) {
          out.totaux.totalmain += duration;
          out.years[year].totalmain += duration;
          out.years[year].periods[period].totalmain += duration;
          out.years[year].periods[period].persons[person_id].totalmain += duration;
        }
        // --- Comptabilisé pour les autres projets
        else if (type == "wp") {
          out.years[year].periods[period].persons[person_id].othersprojects.total += duration;
          //
          if (!out.years[year].periods[period].persons[person_id].othersprojects.projects.hasOwnProperty(context)) {
            out.years[year].periods[period].persons[person_id].othersprojects.projects[context] = 0.0;
          }
          out.years[year].periods[period].persons[person_id].othersprojects.projects[context] += duration;
          let project = context;
          if (!out.totaux.othersprojects.projects.hasOwnProperty(project)) {
            out.totaux.othersprojects.projects[project] = 0.0;
          }
          out.totaux.othersprojects.projects[project] += duration;
          out.totaux.othersprojects.total += duration;
        }
        // --- Autre (Congès, enseignement, etc...)
        else {
          out.years[year].periods[period].persons[person_id].others.total += duration;
          if (!out.years[year].periods[period].persons[person_id].others.context.hasOwnProperty(context)) {
            out.years[year].periods[period].persons[person_id].others.context[context] = 0.0;
          }
          out.years[year].periods[period].persons[person_id].others.context[context] += duration;
        }
        out.years[year].periods[period].persons[person_id].total += duration;
        out.totaux.all += duration;
      });

      return out;
    }
  },

  methods: {
    handlerTooglePerson(id) {
      let personId = parseInt(id);
      let indexId = this.filterPersons.indexOf(personId);
      if (indexId < 0) {
        this.filterPersons.push(personId);
      } else {
        this.filterPersons.splice(indexId, 1);
      }
    },

    handlerOpenPerson(person) {
      console.log(person);
      let indexId = this.openPerson.indexOf(person.indexOpen);
      if (indexId < 0) {
        this.openPerson.push(person.indexOpen);
      } else {
        this.openPerson.splice(indexId, 1);
      }
    },

    handlerOpenYear(year) {
      let i = this.openYear.indexOf(year.key);
      if (i < 0) {
        this.openYear.push(year.key);
      } else {
        this.openYear.splice(i, 1);
      }
    },

    handlerOpenPeriod(period) {
      console.log(period.key, this.openPeriod);
      let i = this.openPeriod.indexOf(period.key);
      if (i < 0) {
        this.openPeriod.push(period.key);
      } else {
        this.openPeriod.splice(i, 1);
      }
    },

    handlerOpenSynthesis() {
      this.open = true;
      this.loadSynthesis();
    },

    handlerCloseSynthesis() {
      this.open = false;
      console.log("CLOSE Synthesis 'update'");
    },

    buildStructuredDatas() {
      // @deprecated
    },

    buildSynthese(datas) {
      // TODO Tester les dates de début/fin
      // ...
      this.mainContext = datas.acronym;
      this.mainContextId = datas.activity_id;

      // Constuction de la liste des périodes
      let fromYear = parseInt(datas.start.substring(0, 4));
      let toYear = parseInt(datas.end.substring(0, 4));
      let fromMonth = parseInt(datas.start.substring(5, 7));
      let toMonth = parseInt(datas.end.substring(5, 7));

      let periods = [];
      let years = [];
      let j = fromMonth;
      for (let i = fromYear; i <= toYear; i++) {
        years.push(i);
        for (; j <= 12 || (j <= toMonth && i == toYear); j++) {
          periods.push(periodStr(i, j));
        }
        j = 1;
      }

      let persons = {};
      let contexts = [];

      datas.synthesis.forEach(e => {
        if (!persons.hasOwnProperty(e.person_id)) {
          persons[e.person_id] = e.displayname;
        }
        if (contexts.indexOf(e.context) < 0 && e.activity_id != this.mainContextId) {
          contexts.push(e.context)
        }
      });

      this.periods = periods;
      this.contexts = contexts;
      this.persons = persons;
      this.years = years;

    },

    loadSynthesis() {
      this.loading = "Chargement de la synthèse...";
      this.$http.get(this.url).then(
          ok => {
            console.log(ok);
            this.synthese = ok.body;
            this.buildSynthese(ok.body);
          }, ko => {

          }
      ).then(foo => {
        this.loading = false;
      })
    }
  },

  mounted() {
    console.log("ActivityTimesheetSynthesisMenu.vue");
    this.loadSynthesis();
  }

});

// CONCATENATED MODULE: ./src/ActivityTimesheetSynthesisMenu.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ActivityTimesheetSynthesisMenuvue_type_script_lang_js_ = (ActivityTimesheetSynthesisMenuvue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/ActivityTimesheetSynthesisMenu.vue





/* normalize component */

var component = normalizeComponent(
  src_ActivityTimesheetSynthesisMenuvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ActivityTimesheetSynthesisMenu = (component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (ActivityTimesheetSynthesisMenu);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=ActivityTimesheetSynthesisMenu.umd.js.map
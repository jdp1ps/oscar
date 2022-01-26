(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["TimesheetDeclarationsList"] = factory();
	else
		root["TimesheetDeclarationsList"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetDeclarationsList.vue?vue&type=template&id=1288d79d&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',{staticClass:"validations-admin"},[_c('transition',{attrs:{"name":"fade"}},[(_vm.loading)?_c('div',{staticClass:"pending overlay"},[_c('div',{staticClass:"overlay-content"},[_c('i',{staticClass:"icon-spinner animate-spin"}),_vm._v(" "+_vm._s(_vm.loading)+" ")])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.error)?_c('div',{staticClass:"pending overlay"},[_c('div',{staticClass:"overlay-content"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" "+_vm._s(_vm.error)+" ")])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.create),expression:"create"}],staticClass:"overlay"},[_c('div',{staticClass:"overlay-content",staticStyle:{"overflow-y":"visible"}},[_c('span',{staticClass:"overlay-closer",on:{"click":function($event){_vm.create = null}}},[_vm._v("X")]),_vm._v(" Choisissez une personne à ajouter : "),_c('personautocompleter',{on:{"change":_vm.handlerAddPerson}}),_c('button',{staticClass:"btn btn-primary",class:{ 'disabled' : _vm.addedPerson == null },on:{"click":function($event){return _vm.handlerConfirmAdd(_vm.create, _vm.addedPerson.id)}}},[(_vm.addedPerson != null)?_c('span',[_vm._v("Ajouter "),_c('strong',[_vm._v(_vm._s(_vm.addedPerson.displayname))]),_vm._v(" comme validateur")]):_c('span',[_vm._v("Selectionner une personne")])])],1)])]),_c('transition',{attrs:{"name":"fade"}},[(_vm.addvalidatorperson)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content",staticStyle:{"overflow-y":"visible"}},[_c('span',{staticClass:"overlay-closer",on:{"click":function($event){_vm.addvalidatorperson = null}}},[_vm._v("X")]),_c('h3',[_vm._v("Assigner un validateur Hors-lot")]),_c('p',{staticClass:"alert alert-info"},[_vm._v("Selectionnez un validateur pour les "),_c('strong',[_vm._v("créneaux Hors-Lot")]),_vm._v(" de "),_c('strong',[_vm._v(_vm._s(_vm.filterPerson))]),_vm._v(". Cet opération affectera également le validateur pour les déclarations en cours non-validée. ")]),_c('personautocompleter',{on:{"change":_vm.handlerSelectValidateur}}),_c('form',{attrs:{"action":"","method":"post"}},[_c('input',{attrs:{"type":"hidden","name":"action","value":"addvalidator"}}),_c('input',{attrs:{"type":"hidden","name":"person"},domProps:{"value":_vm.selectedPerson.id}}),_c('input',{attrs:{"type":"hidden","name":"validatorId"},domProps:{"value":_vm.validatorId}}),_c('button',{staticClass:"btn btn-primary",class:{ 'disabled' : !_vm.validatorId },attrs:{"type":"submit"}},[_vm._v("Ajouter "),_c('strong',[_vm._v(_vm._s(_vm.validatorLabel))]),_vm._v(" comme validateur hors-lot")])]),_c('button',{on:{"click":function($event){_vm.addvalidatorperson = null}}},[_vm._v("Annuler")])],1)]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.schedule)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content",staticStyle:{"overflow-y":"visible"}},[_c('personschedule',{attrs:{"schedule":_vm.schedule.schedule,"editable":true},on:{"cancel":function($event){_vm.schedule = null},"changeschedule":_vm.handlerSaveSchedule}})],1)]):_vm._e()]),_c('h1',[_vm._v("Liste des déclarations")]),_c('div',{staticClass:"declarations-ui cols"},[_c('div',{staticClass:"persons-list col-1 onglets"},[_vm._m(0),_c('div',[_vm._m(1),_c('div',{staticClass:"tab-content"},[_c('div',{staticClass:"tab-pane active",attrs:{"role":"tabpanel","id":"declarants"}},_vm._l((_vm.declarers),function(p){return _c('article',{staticClass:"list-item",class:{'selected': p.displayname == _vm.filterPerson },on:{"click":function($event){$event.preventDefault();return _vm.handlerFilterPerson(p)}}},[_c('i',{staticClass:"icon-user"}),_vm._v(" "+_vm._s(p.displayname)+" "),(p.referents.length == 0)?_c('i',{staticClass:"icon-attention-1"}):_vm._e()])}),0),_c('div',{staticClass:"tab-pane",attrs:{"role":"tabpanel","id":"activities"}},_vm._l((_vm.activities),function(a){return _c('article',{staticClass:"list-item",class:{'selected': a == _vm.filterActivity },on:{"click":function($event){$event.preventDefault();return _vm.handlerFilterActivity(a)}}},[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(a)+" ")])}),0)])])]),_c('div',{staticClass:"declarations-list col-4"},[_vm._m(2),_vm._l((_vm.filteredDeclarations),function(line,k){return _c('section',{staticClass:"card declaration reactive",on:{"click":function($event){line.open = !line.open}}},[_c('span',{staticClass:"opener"},[(line.open)?_c('i',{staticClass:"icon-angle-down"}):_c('i',{staticClass:"icon-angle-right"})]),_c('strong',[_vm._v(_vm._s(line.person))]),_vm._v(" "),_c('time',[_vm._v(_vm._s(_vm._f("period")(line.period)))]),_c('span',{staticClass:"validations-icon"},[(line.warnings.length > 0)?_c('i',{staticClass:"icon-attention-1 bg-danger rounded",staticStyle:{"border-radius":"8px"}}):_vm._e(),_vm._l((line.declarations),function(d){return _c('i',{staticClass:"icon",class:'icon-' +d.status,attrs:{"title":d.label}})})],2),_c('nav',[_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":'/feuille-de-temps/excel?action=export2&period=' + line.period +'&personid=' + line.person_id}},[_c('i',{staticClass:"icon-file-pdf"}),_vm._v("Voir")]),_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerChangeSchedule(line)}}},[_c('i',{staticClass:"icon-clock"}),_vm._v("Horaires")]),_c('a',{staticClass:"btn btn-danger btn-xs",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerCancelDeclaration(line)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v("Annuler")])]),_c('transition',{attrs:{"name":"slide"}},[(line.open)?_c('section',{staticClass:"validations text-small"},[(line.warnings.length > 0)?_c('ul',{staticClass:"alert-danger alert"},_vm._l((line.warnings),function(w){return _c('li',[_vm._v(_vm._s(w))])}),0):_vm._e(),_vm._l((line.declarations),function(validation){return _c('article',{staticClass:"validation",class:{ 'selected': _vm.selectedValidation == validation },on:{"click":function($event){$event.preventDefault();$event.stopPropagation();_vm.selectedValidation = validation}}},[_c('span',[_c('i',{class:validation.object == 'activity' ? 'icon-cube' : 'icon-' + validation.object}),_c('strong',[_vm._v(_vm._s(validation.label))])]),(validation.object == 'activity')?_c('span',[(validation.validation.validationactivity_by)?_c('span',{staticClass:"cartouche green",attrs:{"title":"Validation projet"}},[_c('i',{staticClass:"icon-cube"}),_vm._v(_vm._s(validation.validation.validationactivity_by)+" ")]):_c('span',{directives:[{name:"else",rawName:"v-else"}],staticClass:"validators"},[_c('i',{staticClass:"icon-cube"}),_vm._l((validation.validateursPrj),function(p){return _c('span',[_vm._v(_vm._s(p.person))])})],2)]):_c('span',[_vm._v("~")]),(validation.object == 'activity')?_c('span',[(validation.validation.validationsci_by)?_c('span',{staticClass:"cartouche green",attrs:{"title":"Validation scientifique"}},[_c('i',{staticClass:"icon-beaker"}),_vm._v(_vm._s(validation.validation.validationsci_by)+" ")]):_c('span',{staticClass:"validators"},[_c('i',{staticClass:"icon-beaker"}),_vm._l((validation.validateursSci),function(p){return _c('span',[_vm._v(_vm._s(p.person))])})],2)]):_c('span',[_vm._v("~")]),_c('span',[(validation.validation.validationadm_by)?_c('span',{staticClass:"cartouche green",attrs:{"title":"Validation administrative"}},[_c('i',{staticClass:"icon-book"}),_vm._v(_vm._s(validation.validation.validationadm_by)+" ")]):_c('span',{staticClass:"validators"},[_c('i',{staticClass:"icon-book"}),_vm._l((validation.validateursAdm),function(p){return _c('span',[_vm._v(_vm._s(p.person))])})],2)]),_c('em',[_c('i',{class:'icon-' +validation.status})])])})],2):_vm._e()])],1)})],2),_c('div',{staticClass:"declaration-details col-2"},[(_vm.filterPerson)?_c('div',[_c('h3',[_c('i',{staticClass:"icon-cog"}),_vm._v(" "+_vm._s(_vm.filterPerson))]),(_vm.selectedPerson.referents.length == 0)?_c('div',{staticClass:"alert alert-danger"},[_vm._v(" Aucun référent pour "),_c('strong',[_vm._v("valider les déclarations Hors-lot")])]):_c('div',[_c('h4',[_vm._v("Validateur :")]),_c('ul',_vm._l((_vm.selectedPerson.referents),function(r){return _c('li',{staticClass:"cartouche cartouche-default"},[_vm._v(_vm._s(r.displayname))])}),0)]),_c('button',{staticClass:"btn-primary btn",on:{"click":function($event){_vm.addvalidatorperson = true}}},[_vm._v(" Ajouter un validateur pour les créneaux "),_c('strong',[_vm._v("Hors-Lot")])]),_c('a',{staticClass:"btn-primary btn",attrs:{"href":'/person/show/' + _vm.selectedPerson.id}},[_vm._v(" Voir la fiche de "),_c('strong',[_vm._v(_vm._s(_vm.filterPerson))])])]):_vm._e(),_vm._m(3),(!_vm.selectedValidation)?_c('p',{staticClass:"alert alert-info"},[_vm._v(" Selectionnez une ligne d'une déclaration pour afficher les détails et "),_c('strong',[_vm._v("gérer les validateurs")])]):_vm._e(),_c('transition',{attrs:{"name":"fade"}},[(_vm.selectedValidation)?_c('div',{staticClass:"validation-details"},[_c('h3',[_c('small',[_vm._v("Validation pour les créneaux")]),_c('br'),(_vm.selectedValidation.object == 'activity')?_c('strong',[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(_vm.selectedValidation.label)+" ")]):_c('strong',[_c('i',{class:'icon-' +_vm.selectedValidation.object}),_vm._v(" "+_vm._s(_vm.selectedValidation.label)+" ")]),_c('br'),_c('small',[_vm._v(" de "),_c('strong',[_vm._v(_vm._s(_vm.selectedValidation.person))]),_vm._v(" en "),_c('strong',[_vm._v(_vm._s(_vm._f("period")(_vm.selectedValidation.period)))])])]),(_vm.selectedValidation.object == 'activity')?_c('div',[(_vm.selectedValidation.validation.validationactivity_by)?_c('div',{staticClass:"card valid"},[_c('i',{staticClass:"icon-ok-circled"}),_vm._v(" Validation projet par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(_vm.selectedValidation.validation.validationactivity_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm._f("humandate")(_vm.selectedValidation.validation.validationactivity_at)))])]):(_vm.selectedValidation.validation.rejectactivity_by)?_c('div',{staticClass:"card reject"},[_c('i',{staticClass:"icon-attention-circled"}),_vm._v(" Rejet des créneaux par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(_vm.selectedValidation.validation.rejectactivity_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm._f("humandate")(_vm.selectedValidation.validation.rejectactivity_at)))]),_vm._v(" : "),_c('pre',[_vm._v(_vm._s(_vm.selectedValidation.validation.rejectactivity_message))])]):_c('div',{staticClass:"card waiting"},[_c('strong',[_vm._v("Validation projet en attente")]),_vm._v(" par l'un des validateurs suivant : "),_c('ul',_vm._l((_vm.selectedValidation.validatorsPrj),function(p){return _c('li',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(p.person)+" "),_c('a',{staticClass:"link",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerDelete('prj', p)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" Supprimer")])])}),0),_c('a',{staticClass:"btn btn-xs btn-primary",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerAdd('prj')}}},[_vm._v("Ajouter un validateur")])]),(_vm.selectedValidation.validation.validationsci_by)?_c('div',{staticClass:"card valid"},[_vm._v(" Validation scientifique par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(_vm.selectedValidation.validation.validationsci_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm._f("humandate")(_vm.selectedValidation.validation.validationsci_at)))])]):(_vm.selectedValidation.validation.rejectsci_by)?_c('div',{staticClass:"card reject"},[_c('i',{staticClass:"icon-attention-circled"}),_vm._v(" Rejet scientifique des créneaux par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(_vm.selectedValidation.validation.rejectsci_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm._f("humandate")(_vm.selectedValidation.validation.rejectsci_at)))]),_c('pre',[_vm._v(_vm._s(_vm.selectedValidation.validation.rejectsci_message))])]):_c('div',{staticClass:"card waiting"},[_c('strong',[_vm._v("Validation scientifique en attente")]),_vm._v(" par l'un des validateurs suivant : "),_c('ul',_vm._l((_vm.selectedValidation.validatorsSci),function(p){return _c('li',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(p.person)+" "),_c('a',{staticClass:"link",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerDelete('sci', p)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" Supprimer")])])}),0),_c('a',{staticClass:"btn btn-xs btn-primary",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerAdd('sci')}}},[_vm._v("Ajouter un validateur")])])]):_vm._e(),(_vm.selectedValidation.validation.validationadm_by)?_c('div',{staticClass:"card valid"},[_vm._v(" Validation administrative par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(_vm.selectedValidation.validation.validationadm_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm._f("humandate")(_vm.selectedValidation.validation.validationadm_at)))])]):(_vm.selectedValidation.validation.rejectadm_by)?_c('div',{staticClass:"card reject"},[_c('i',{staticClass:"icon-attention-circled"}),_vm._v(" Rejet administrative des créneaux par "),_c('strong',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(_vm.selectedValidation.validation.rejectadm_by))]),_vm._v(" le "),_c('time',[_vm._v(_vm._s(_vm._f("humandate")(_vm.selectedValidation.validation.rejectadm_at)))]),_c('pre',[_vm._v(_vm._s(_vm.selectedValidation.validation.validationadm_message))])]):_c('div',{staticClass:"card waiting"},[_c('strong',[_vm._v("Validation administrative en attente")]),_vm._v(" par l'un des validateurs suivant : "),_c('ul',_vm._l((_vm.selectedValidation.validatorsAdm),function(p){return _c('li',[_c('i',{staticClass:"icon-user"}),_vm._v(_vm._s(p.person)+" "),_c('a',{staticClass:"link",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerDelete('adm', p)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" Supprimer")])])}),0),_c('a',{staticClass:"btn btn-xs btn-primary",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerAdd('adm')}}},[_vm._v("Ajouter un validateur")])])]):_vm._e()])],1)])],1)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-sort"}),_vm._v(" Filtres")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('ul',{staticClass:"nav nav-tabs",attrs:{"role":"tablist"}},[_c('li',{staticClass:"active",attrs:{"role":"presentation"}},[_c('a',{attrs:{"href":"#declarants","aria-controls":"home","role":"tab","data-toggle":"tab"}},[_c('i',{staticClass:"icon-group"}),_vm._v(" Déclarants")])]),_c('li',{attrs:{"role":"presentation"}},[_c('a',{attrs:{"href":"#activities","aria-controls":"profile","role":"tab","data-toggle":"tab"}},[_c('i',{staticClass:"icon-cubes"}),_vm._v(" Activités")])])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h2',[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Déclaration")])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"icon-zoom-in-outline"}),_vm._v(" Détails")])}]


// CONCATENATED MODULE: ./src/TimesheetDeclarationsList.vue?vue&type=template&id=1288d79d&

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
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"55ddf09a-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PersonSchedule.vue?vue&type=template&id=9b4a1af6&
var PersonSchedulevue_type_template_id_9b4a1af6_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',{staticClass:"schedule"},[_c('transition',{attrs:{"name":"fade"}},[(_vm.loading)?_c('div',{staticClass:"pending overlay"},[_c('div',{staticClass:"overlay-content"},[_c('i',{staticClass:"icon-spinner animate-spin"}),_vm._v(" "+_vm._s(_vm.loading)+" ")])]):_vm._e()]),_c('transition',{attrs:{"name":"fade"}},[(_vm.error)?_c('div',{staticClass:"pending overlay"},[_c('div',{staticClass:"overlay-content"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" "+_vm._s(_vm.error)+" ")])]):_vm._e()]),_c('p',[_vm._v("La répartition horaire est issue de "+_vm._s(_vm.from)+" "),(_vm.from == 'application')?_c('strong',[_vm._v("la configuration Oscar par défaut")]):_vm._e(),(_vm.from == 'sync')?_c('strong',[_vm._v("la synchronisation (Connector)")]):_vm._e(),(_vm.from == 'custom')?_c('strong',[_vm._v("la configuration prédéfinie")]):_vm._e(),(_vm.from == 'free')?_c('strong',[_vm._v("la configuration manuelle")]):_vm._e()]),_vm._l((_vm.days),function(total,day){return _c('article',{staticClass:"card xs"},[_c('h3',{staticClass:"card-title"},[_c('strong',[_vm._v(_vm._s(_vm.daysLabels[day]))]),(_vm.editDay)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.days[day]),expression:"days[day]"}],attrs:{"type":"text"},domProps:{"value":(_vm.days[day])},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.days, day, $event.target.value)}}}):_c('em',{staticClass:"big right",on:{"click":function($event){return _vm.handlerEditDays()}}},[_vm._v(_vm._s(_vm._f("heures")(total)))])])])}),_c('article',{staticClass:"card"},[_c('h3',{staticClass:"card-title"},[_c('strong',[_vm._v("Total / semaine")]),_c('em',{staticClass:"big right"},[_vm._v(_vm._s(_vm._f("heures")(_vm.totalWeek)))])])]),(_vm.editable)?_c('nav',[(!_vm.editDay)?_c('button',{staticClass:"btn btn-default",on:{"click":function($event){$event.preventDefault();return _vm.handlerEditDays()}}},[_c('i',{staticClass:"icon-pencil"}),_vm._v(" modifier")]):_vm._e(),(_vm.editDay)?_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){$event.preventDefault();return _vm.handlerSaveDays()}}},[_c('i',{staticClass:"icon-floppy"}),_vm._v(" enregistrer")]):_vm._e(),(_vm.models && _vm.editDay)?_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.model),expression:"model"}],staticClass:"form-inline",on:{"change":[function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.model=$event.target.multiple ? $$selectedVal : $$selectedVal[0]},function($event){return _vm.handlerSaveDays(_vm.model)}]}},[_c('option',{attrs:{"value":"default"}},[_vm._v("Aucun")]),_vm._l((_vm.models),function(m,key){return _c('option',{domProps:{"value":key,"selected":_vm.model == key}},[_vm._v(_vm._s(m.label))])})],2):_vm._e(),(_vm.editDay && _vm.from != 'default')?_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){$event.preventDefault();return _vm.handlerSaveDays('default')}}},[_c('i',{staticClass:"icon-floppy"}),_vm._v(" Horaires par défaut")]):_vm._e(),(_vm.editDay)?_c('button',{staticClass:"btn btn-primary",on:{"click":function($event){$event.preventDefault();return _vm.handlerCancel()}}},[_c('i',{staticClass:"icon-cancel-circled"}),_vm._v(" annuler")]):_vm._e()]):_vm._e()],2)}
var PersonSchedulevue_type_template_id_9b4a1af6_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/PersonSchedule.vue?vue&type=template&id=9b4a1af6&

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
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PersonSchedule.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

// poi watch --format umd --moduleName  PersonSchedule --filename.css PersonSchedule.css --filename.js PersonSchedule.js --dist public/js/oscar/dist public/js/oscar/src/PersonSchedule.vue


/* harmony default export */ var PersonSchedulevue_type_script_lang_js_ = ({
    name: 'PersonSchedule',

    props: {
        urlapi: {default: ''},
        editable: { default: false },
        schedule: null
    },

    data() {
        return {
            daysLabels: {
                '1': 'Lundi',
                '2': 'Mardi',
                '3': 'Mercredi',
                '4': 'Jeudi',
                '5': 'Vendredi',
                '6': 'Samedi',
                '7': 'Dimanche'
            },
            loading: null,
            error: null,
            dayLength: 0.0,
            from: null,
            days: {},
            editDay: null,
            newValue: 0,
            models: [],
            model: null
        }
    },

    computed: {
        totalWeek(){
            let total = 0.0;
            Object.keys(this.days).forEach(i => {
                total += parseFloat(this.days[i]);
            });
            return total;
        }
    },

    methods: {
        day(index){
            if( this.days.hasOwnProperty(index) ){
                return this.days[index];
            }
            return this.dayLength;
        },

        handlerEditDays(){
            this.editDay = true;
        },

        handlerCancel(){
            if( !this.urlapi ){
                this.$emit('cancel');
            } else {
                this.fetch();
            }
        },

        handlerSaveDays( model = 'input'){
            if( !this.urlapi ){
                this.$emit('changeschedule', this.days);
            }
            else {
                this.loading = "Enregistrement des horaires";
                let datas = new FormData();
                if( model == 'input' ){
                    datas.append('days', JSON.stringify(this.days));
                }
                else {
                    datas.append('model', model);
                }


                this.$http.post(this.urlapi, datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de modifier les horaires', ko);
                    }
                ).then(foo => {
                    this.loading = false
                });
            }
        },


        fetch(clear = true) {
            if( this.schedule == null ){

                this.loading = "Chargement des données";

                this.$http.get(this.urlapi).then(
                    ok => {
                        console.log(ok.body);
                        this.days = ok.body.days;
                        this.dayLength = ok.body.dayLength;
                        this.from = ok.body.from;
                        this.models = ok.body.models;
                        this.model = ok.body.model;
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                    }
                ).then(foo => {
                    this.loading = false;
                    this.editDay = null;
                });
            } else {
                console.log(this.schedule);
                this.days = this.schedule.days;
                this.dayLength = this.schedule.dayLength;
                this.editDay = true;
            }
        }
    },

    mounted() {
        this.fetch(true)
    }
});

// CONCATENATED MODULE: ./src/components/PersonSchedule.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_PersonSchedulevue_type_script_lang_js_ = (PersonSchedulevue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/PersonSchedule.vue





/* normalize component */

var PersonSchedule_component = normalizeComponent(
  components_PersonSchedulevue_type_script_lang_js_,
  PersonSchedulevue_type_template_id_9b4a1af6_render,
  PersonSchedulevue_type_template_id_9b4a1af6_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var PersonSchedule = (PersonSchedule_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/TimesheetDeclarationsList.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

// nodejs node_modules/.bin/poi watch --format umd --moduleName  TimesheetDeclarationsList --filename.css TimesheetDeclarationsList.css --filename.js TimesheetDeclarationsList.js --dist public/js/oscar/dist public/js/oscar/src/TimesheetDeclarationsList.vue







/* harmony default export */ var TimesheetDeclarationsListvue_type_script_lang_js_ = ({
    name: 'TimesheetDeclarationsList',

    props: {
        moment: {required: true},
        bootbox: {required: true},
        urlapi: {default: null},
        editable: { default: false }
    },

    components: {
        'personautocompleter': PersonAutoCompleter,
        'personschedule': PersonSchedule
    },

    data() {
        return {
            loading: null,
            schedule: null,
            declarations: {},
            declarers: {},
            error: null,
            selectedValidation: null,
            create: false,
            addedPerson: null,
            filterPerson: "",
            filterActivity: "",
            selectedPerson: null,
            validatorId: null,
            validatorLabel: null,
            addvalidatorperson: null
        }
    },

    computed: {
        declarants(){
            let declarants = {};
            if( this.declarations ){
                Object.keys(this.declarations).forEach( k => {
                    let person = this.declarations[k].person;
                    let person_id = this.declarations[k].person_id;

                    if( !declarants.hasOwnProperty(person) ){
                        declarants[person] = {
                            person: person,
                            person_id: person_id
                        }
                    }
                })
            }
            return declarants;
        },

        activities(){
            let activities = [];
            if( this.declarations ){
                Object.keys(this.declarations).forEach( k => {
                    for( let i=0; i<this.declarations[k].declarations.length; i++ ){
                        if( activities.indexOf(this.declarations[k].declarations[i].label) < 0 ){
                            activities.push(this.declarations[k].declarations[i].label)
                        }
                    }
                })
            }
            return activities;
        },

        filteredDeclarations(){
            let declarations = [];

            let keys = Object.keys(this.declarations);
            for( let i=0; i<keys.length; i++ ){
                let k = keys[i];
                let period = this.declarations[k];

                if( this.filterPerson != "" ){
                    if( period.person != this.filterPerson ){
                        continue;
                    }
                }

                if( this.filterActivity != "" ){
                    let keep = false;
                    period.declarations.forEach(v => {
                        if( v.label == this.filterActivity ){
                            keep = true;
                        }
                    });
                    if( keep == false ) continue;
                }



                declarations.push(period);
            }

            return declarations;
        }
    },

    methods: {

        /**
         * Enregistrement de la modification de la répartition horaire.
         * @param evt
         */
        handlerSaveSchedule(evt){
            let datas = new FormData();
            datas.append('person_id', this.schedule.person_id);
            datas.append('period', this.schedule.period);
            datas.append('action', 'changeschedule');
            datas.append('days', JSON.stringify(evt));

            this.$http.post('', datas).then(
                ok => {
                    this.fetch();
                },
                ko => {
                    // this.error = AjaxResolve.resolve("Impossible de modifier les horaires", ko);

                }
            ).then(foo => {
                this.addedPerson = null;
                this.schedule = null;
                this.create = null;
                this.loading = false
            });
        },

        handlerChangeSchedule(line){
          console.log(line.settings);
          this.schedule = {
            schedule : JSON.parse(line.settings),
            person_id: line.person_id,
            period: line.period,
          };
        },

        handlerSelectValidateur(validator){
          this.validatorLabel = validator.displayname;
          this.validatorId = validator.id;
        },

        handlerAdd(type){
            this.create = type;
        },

        handlerAddPerson(data){
          this.addedPerson = data;
        },

        handlerDelete( type, person ){
            this.loading = "Suppression du validateur";

            let datas = new FormData();
            datas.append('person_id', person.id);
            datas.append('declaration_id', this.selectedValidation.id);
            datas.append('action', 'delete');
            datas.append('type', type);

            this.$http.post('', datas).then(
                ok => {

                    switch( type ){
                        case "prj":
                            this.selectedValidation.validatorsPrj.splice(this.selectedValidation.validatorsPrj.indexOf(person, 1));
                            break;
                        case "sci":
                            this.selectedValidation.validatorsSci.splice(this.selectedValidation.validatorsSci.indexOf(person, 1));
                            break;
                        case "adm":
                            this.selectedValidation.validatorsAdm.splice(this.selectedValidation.validatorsAdm.indexOf(person, 1));
                            break;
                    }
                },
                ko => {
                    this.error = AjaxResolve.resolve("Impossible de supprimer ce validateur", ko);

                }
            ).then(foo => {
                this.addedPerson = null;
                this.create = null;
                this.loading = false
            });
        },

        handlerConfirmAdd(type, personId){

            this.loading = "Ajout du validateur";

            let datas = new FormData();
            datas.append('person_id', personId);
            datas.append('declaration_id', this.selectedValidation.id);
            datas.append('type', type);

            this.$http.post('', datas).then(
                ok => {
                    switch( type ){
                        case "prj":
                            this.selectedValidation.validatorsPrj.push(ok.body);
                            break;
                        case "sci":
                            this.selectedValidation.validatorsSci.push(ok.body);
                            break;
                        case "adm":
                            this.selectedValidation.validatorsAdm.push(ok.body);
                            break;
                    }
                },
                ko => {
                    this.error = AjaxResolve.resolve("Impossible d'ajouter le validateur", ko);
                }
            ).then(foo => {
                this.addedPerson = null;
                this.create = null;
                this.loading = false
            });
        },


        fetch(clear = true) {
            this.loading = "Chargement des données";

            this.$http.get('').then(
                ok => {
                    for( let item in ok.body.periods ){
                        ok.body.periods[item].open = false;
                    }
                    this.declarations = ok.body.periods;
                    this.declarers = ok.body.declarants;
                },
                ko => {
                    this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                }
            ).then(foo => {
                this.loading = false
            });
        },

        handlerCancelDeclaration(declaration){
            console.log(declaration);
            this.bootbox.confirm("Supprimer la déclaration (le déclarant devra réenvoyer la déclaration) ?", ok => {
                if( ok ){
                    this.loading = "Suppression de la déclaration";

                    this.$http.delete('?person_id=' + declaration.person_id +"&period=" +declaration.period).then(
                        ok => {
                            this.fetch();
                        },
                        ko => {
                            this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                        }
                    ).then(foo => {
                        this.loading = false
                    });
                }
            })

        },
        //////////////////////////////////////
        // Application des filtres d'affichage

        handlerFilterPerson( person ){
            this.selectedValidation = null;
            this.filterPerson = this.filterPerson == person.displayname ? "" : person.displayname;
            this.selectedPerson = person;
        },

        handlerFilterActivity( activity ){
            this.selectedValidation = null;
            this.filterActivity = this.filterActivity == activity ? "" : activity;
        }
    },

    mounted() {
        this.fetch(true)
    }
});

// CONCATENATED MODULE: ./src/TimesheetDeclarationsList.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_TimesheetDeclarationsListvue_type_script_lang_js_ = (TimesheetDeclarationsListvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/TimesheetDeclarationsList.vue





/* normalize component */

var TimesheetDeclarationsList_component = normalizeComponent(
  src_TimesheetDeclarationsListvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var TimesheetDeclarationsList = (TimesheetDeclarationsList_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (TimesheetDeclarationsList);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=TimesheetDeclarationsList.umd.js.map
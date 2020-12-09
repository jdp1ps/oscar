(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ActivityDocument"] = factory();
	else
		root["ActivityDocument"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"0a1b3af9-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityDocument.vue?vue&type=template&id=412a2e59&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',[(_vm.deleteData)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('h2',[_vm._v(" Suppression du fichier ? "),_c('span',{staticClass:"overlay-closer",on:{"click":function($event){_vm.deleteData = null}}},[_vm._v("X")])]),_c('p',{staticClass:"alert-danger alert"},[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Souhaitez-vous supprimer le fichier "),_c('strong',[_vm._v(_vm._s(_vm.deleteData.fileName))]),_vm._v(" ? ")]),_c('button',{staticClass:"btn btn-danger",on:{"click":function($event){_vm.deleteData = null}}},[_c('i',{staticClass:"icon-cancel-alt"}),_vm._v(" Annuler ")]),_c('a',{staticClass:"btn btn-success",attrs:{"href":_vm.deleteData.urlDelete}},[_c('i',{staticClass:"icon-valid"}),_vm._v(" Confirmer ")])])]):_vm._e(),_c('div',[_c('div',{staticClass:"oscar-sorter"},[_c('i',{staticClass:" icon-sort"}),_vm._v(" Tier les résultats par : "),_c('a',{staticClass:"oscar-sorter-item",class:_vm.cssSort('dateUpload'),attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.order('dateUpload')}}},[_vm._v(" Date d'upload "),_c('i',{directives:[{name:"show",rawName:"v-show",value:(_vm.sortDirection == 1),expression:"sortDirection == 1"}],staticClass:"icon-angle-down"}),_c('i',{directives:[{name:"show",rawName:"v-show",value:(_vm.sortDirection == -1),expression:"sortDirection == -1"}],staticClass:"icon-angle-up"})]),_c('a',{staticClass:"oscar-sorter-item",class:_vm.cssSort('fileName'),attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.order('fileName')}}},[_vm._v(" Nom du fichier "),_c('i',{directives:[{name:"show",rawName:"v-show",value:(_vm.sortDirection == 1),expression:"sortDirection == 1"}],staticClass:"icon-angle-down"}),_c('i',{directives:[{name:"show",rawName:"v-show",value:(_vm.sortDirection == -1),expression:"sortDirection == -1"}],staticClass:"icon-angle-up"})]),_c('a',{staticClass:"oscar-sorter-item",class:_vm.cssSort('categoryText'),attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();return _vm.order('categoryText')}}},[_vm._v(" Type de document "),_c('i',{directives:[{name:"show",rawName:"v-show",value:(_vm.sortDirection == 1),expression:"sortDirection == 1"}],staticClass:"icon-angle-down"}),_c('i',{directives:[{name:"show",rawName:"v-show",value:(_vm.sortDirection == -1),expression:"sortDirection == -1"}],staticClass:"icon-angle-up"})])])]),_vm._l((_vm.documentsPacked),function(document){return _c('article',{staticClass:"card xs"},[_c('div',{staticClass:"card-title"},[_c('i',{staticClass:"picto icon-doc",class:'doc' + document.extension}),(document.editmode)?[_c('select',{on:{"change":function($event){return _vm.changeTypeDocument(document, $event)},"blur":function($event){document.editmode = false}}},_vm._l((_vm.documentTypes),function(documentType,key){return _c('option',{domProps:{"value":key,"selected":document.categoryText == documentType}},[_vm._v(_vm._s(documentType))])}),0)]:[_c('small',{staticClass:"text-light",on:{"dblclick":function($event){document.editmode = true}}},[_vm._v(_vm._s(document.categoryText)+" ~ ")])],_c('strong',[_vm._v(_vm._s(document.fileName))]),_c('small',{staticClass:"text-light",attrs:{"title":document.fileSize + ' octet(s)'}},[_vm._v(" ("+_vm._s(_vm._f("filesize")(document.fileSize))+")")])],2),_c('p',[_vm._v(" "+_vm._s(document.information)+" ")]),_c('div',{staticClass:"card-content"},[_c('p',{staticClass:"text-highlight"},[_vm._v(" Fichier "),_c('strong',[_vm._v(_vm._s(document.extension))]),_vm._v(" version "+_vm._s(document.version)+", téléversé le "),_c('time',[_vm._v(_vm._s(_vm._f("dateFull")(document.dateUpload)))]),(document.uploader)?_c('span',[_vm._v(" par "),_c('strong',[_vm._v(_vm._s(document.uploader.displayname))])]):_vm._e()]),(document.previous.length)?_c('div',{staticClass:"exploder",on:{"click":function($event){document.explode = !document.explode}}},[_vm._v(" Versions précédentes "),_c('i',{directives:[{name:"show",rawName:"v-show",value:(!document.explode),expression:"!document.explode"}],staticClass:"icon-angle-down"}),_c('i',{directives:[{name:"show",rawName:"v-show",value:(document.explode),expression:"document.explode"}],staticClass:"icon-angle-up"})]):_vm._e(),(document.previous.length)?_c('div',{directives:[{name:"show",rawName:"v-show",value:(document.explode),expression:"document.explode"}]},_vm._l((document.previous),function(sub){return _c('article',{staticClass:"subdoc text-highlight"},[_c('i',{staticClass:"picto icon-doc",class:'doc' + sub.extension}),_c('strong',[_vm._v(_vm._s(sub.fileName))]),_vm._v(" version "),_c('em',[_vm._v(_vm._s(sub.version)+" ")]),_vm._v(", téléchargé le "),_c('time',[_vm._v(_vm._s(_vm._f("dateFullSort")(sub.dateUpload)))]),(sub.uploader)?_c('span',[_vm._v(" par "),_c('strong',[_vm._v(_vm._s(sub.uploader.displayname))])]):_vm._e(),_c('a',{attrs:{"href":sub.urlDownload}},[_c('i',{staticClass:"icon-download-outline"}),_vm._v(" Télécharger cette version ")])])}),0):_vm._e(),_c('nav',{staticClass:"text-right show-over"},[(document.urlDownload)?_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":document.urlDownload}},[_c('i',{staticClass:"icon-download-outline"}),_vm._v(" Télécharger le fichier ")]):_vm._e(),(document.urlReupload)?_c('a',{staticClass:"btn btn-default btn-xs",attrs:{"href":document.urlReupload}},[_c('i',{staticClass:"icon-download-outline"}),_vm._v(" Nouvelle version ")]):_vm._e(),_c('a',{staticClass:"btn btn-default btn-xs",on:{"click":function($event){$event.preventDefault();return _vm.deleteDocument(document)}}},[_c('i',{staticClass:"icon-trash"}),_vm._v(" supprimer le fichier ")])])])])})],2)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/ActivityDocument.vue?vue&type=template&id=412a2e59&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivityDocument.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* harmony default export */ var ActivityDocumentvue_type_script_lang_js_ = ({

    setup(){
        console.log("Setup")
    },

    props: {
        url: { required: true },
        documentTypes: { required: true },
        urlDocumentType: { required: true }
    },

    data(){
        return {
            formData: null,
            error: null,
            deleteData: null,
            documents: [],
            loading: true,
            sortField: 'dateUpload',
            sortDirection: -1,
            editable: true
        }
    },

    computed:{
        /**
         * Retourne les documents triés.
         * @returns {Array}
         */
        documentsPacked(){
            var out = this.documents.sort( function(a,b) {
                if( a[this.sortField] < b[this.sortField] )
                    return -1 * this.sortDirection;
                if( a[this.sortField] > b[this.sortField] )
                    return 1 * this.sortDirection;
                return 0;
            }.bind(this));
            return out;
        }
    },

    methods:{
        deleteDocument(document) {
            this.deleteData = document;
        },

        order: function (field) {
            if( this.sortField == field ){
                this.sortDirection *= -1;
            } else {
                this.sortField = field;
            }
        },

        cssSort: function(compare){
            return compare == this.sortField ? "active" : "";
        },

        changeTypeDocument: function( document, event ){
            var newType = $(event.target.selectedOptions[0]).text();

            $.post(this.urlDocumentType, {
                documentId: document.id,
                type: newType
            }).then(ok => {
                flashMessage('success', 'Le document a bien été modifié');
                document.categoryText = newType;
                document.editMode = false;
                this.$forceUpdate();
                //this.$forceUpdate();
            }, error => {
                flashMessage('error', 'Erreur' + error.responseText);
                document.editMode = false;
            });
        },

        fetch(){
            this.$http.get(this.url).then(
                ok => {
                    let data = ok.data.datas;
                    let documentsOrdered = [];
                    let documents = {};

                    data.forEach(function(doc){
                        doc.categoryText = doc.category ? doc.category.label : "";
                        doc.editmode = false;
                        doc.explode = false;
                        var filename = doc.fileName;
                        if( ! documents[filename] ){
                            documents[filename] = doc;
                            documents[filename].previous = [];
                            documentsOrdered.push(doc);
                        } else {
                            documents[filename].previous.push(doc);
                        }
                    });
                    this.documents = documentsOrdered;
                },
                ko => {
                    console.log("ERROR", ko);
                }
            )

        }
    },

    mounted(){
        this.fetch();
    }

});

// CONCATENATED MODULE: ./src/ActivityDocument.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ActivityDocumentvue_type_script_lang_js_ = (ActivityDocumentvue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/ActivityDocument.vue





/* normalize component */

var component = normalizeComponent(
  src_ActivityDocumentvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ActivityDocument = (component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (ActivityDocument);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=ActivityDocument.umd.js.map
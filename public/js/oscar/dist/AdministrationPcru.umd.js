(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["AdministrationPcru"] = factory();
	else
		root["AdministrationPcru"] = factory();
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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"a257e54e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/AdministrationPcru.vue?vue&type=template&id=40141870&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('section',{staticStyle:{"position":"relative","min-height":"100px"}},[(_vm.configuration)?_c('div',{staticClass:"container"},[_c('form',{attrs:{"action":"","method":"post"}},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-3"},[_vm._v(" Module "),_c('strong',[_vm._v(_vm._s(_vm.configuration.pcru_enabled ? 'Actif' : 'Inactif'))])]),_c('div',{staticClass:"col-md-9"},[_c('div',{staticClass:"material-switch"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.configuration.pcru_enabled),expression:"configuration.pcru_enabled"}],attrs:{"id":"pcru_enabled","name":"pcru_enabled","type":"checkbox"},domProps:{"checked":Array.isArray(_vm.configuration.pcru_enabled)?_vm._i(_vm.configuration.pcru_enabled,null)>-1:(_vm.configuration.pcru_enabled)},on:{"change":function($event){var $$a=_vm.configuration.pcru_enabled,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.$set(_vm.configuration, "pcru_enabled", $$a.concat([$$v])))}else{$$i>-1&&(_vm.$set(_vm.configuration, "pcru_enabled", $$a.slice(0,$$i).concat($$a.slice($$i+1))))}}else{_vm.$set(_vm.configuration, "pcru_enabled", $$c)}}}}),_c('label',{staticClass:"label-primary",attrs:{"for":"pcru_enabled"}})])])]),_c('section',{class:_vm.configuration.pcru_enabled ? 'enabled' : 'disabled'},[_c('div',{staticClass:"row"},[_vm._m(0),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-5 col-md-push-1"},[_c('div',{staticClass:"form-group"},[_c('label',{staticClass:"sr-only",attrs:{"for":"host"}}),_c('div',{staticClass:"input-group input-lg"},[_vm._m(1),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.configuration.pcru_host),expression:"configuration.pcru_host"}],staticClass:"form-control",attrs:{"type":"text","id":"host","name":"host"},domProps:{"value":(_vm.configuration.pcru_host)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.configuration, "pcru_host", $event.target.value)}}})])])])]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-5 col-md-push-1"},[_c('div',{staticClass:"form-group"},[_c('label',{staticClass:"sr-only",attrs:{"for":"port"}}),_c('div',{staticClass:"input-group input-lg"},[_vm._m(2),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.configuration.pcru_port),expression:"configuration.pcru_port"}],staticClass:"form-control",attrs:{"type":"text","id":"port","name":"port"},domProps:{"value":(_vm.configuration.pcru_port)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.configuration, "pcru_port", $event.target.value)}}})])])])]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-5 col-md-push-1"},[_c('div',{staticClass:"form-group"},[_c('label',{staticClass:"sr-only",attrs:{"for":"user"}}),_c('div',{staticClass:"input-group input-lg"},[_vm._m(3),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.configuration.pcru_user),expression:"configuration.pcru_user"}],staticClass:"form-control",attrs:{"type":"text","id":"user","name":"user"},domProps:{"value":(_vm.configuration.pcru_user)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.configuration, "pcru_user", $event.target.value)}}})])])])]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-5 col-md-push-1"},[_c('password-field',{attrs:{"value":_vm.configuration.pcru_pass,"name":'pass',"text":'Mot de passe'},on:{"change":function($event){_vm.configuration.pcru_pass = $event}}})],1)]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-5 col-md-push-1"},[_c('div',{staticClass:"form-group"},[_c('label',{staticClass:"sr-only",attrs:{"for":"ssh"}}),_c('div',{staticClass:"input-group input-lg"},[_vm._m(4),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.configuration.pcru_ssh),expression:"configuration.pcru_ssh"}],staticClass:"form-control",attrs:{"type":"text","id":"ssh","name":"ssh"},domProps:{"value":(_vm.configuration.pcru_ssh)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.configuration, "pcru_ssh", $event.target.value)}}})])])])]),_c('div',{staticClass:"row"},[_c('button',{staticClass:"btn btn-primary",attrs:{"type":"button"},on:{"click":_vm.performEdit}},[_c('i',{staticClass:"icon-floppy"}),_vm._v(" Enregistrer ")])])])])])]):_vm._e()])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"col-md-12"},[_c('h2',[_vm._v("Accès FTP")])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"input-group-addon"},[_c('i',{staticClass:"glyphicon icon-building"}),_vm._v(" "),_c('strong',[_vm._v("Hôte")])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"input-group-addon"},[_c('i',{staticClass:"glyphicon icon-logout"}),_vm._v(" "),_c('strong',[_vm._v("Port")])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"input-group-addon"},[_c('i',{staticClass:"glyphicon icon-user"}),_vm._v(" "),_c('strong',[_vm._v("Identifiant")])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"input-group-addon"},[_c('i',{staticClass:"glyphicon icon-plug"}),_vm._v(" "),_c('strong',[_vm._v("Clef SSH")])])}]


// CONCATENATED MODULE: ./src/AdministrationPcru.vue?vue&type=template&id=40141870&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"a257e54e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PasswordField.vue?vue&type=template&id=2be9a996&
var PasswordFieldvue_type_template_id_2be9a996_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"form-group"},[_c('label',{staticClass:"sr-only",attrs:{"for":_vm.name}},[_vm._v("Mot de passe "+_vm._s(_vm.type)+" / "+_vm._s(_vm.value))]),_c('div',{staticClass:"input-group input-lg password-field"},[_c('div',{staticClass:"input-group-addon"},[_c('i',{staticClass:"glyphicon icon-lock"}),_vm._v(" "),_c('strong',[_vm._v(_vm._s(_vm.label))])]),(_vm.type == 'text')?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"form-control",staticStyle:{"font-family":"monospace"},attrs:{"name":_vm.name,"type":"text","placeholder":"Mot de passe","id":_vm.name},domProps:{"value":(_vm.value)},on:{"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"form-control",staticStyle:{"font-family":"monospace"},attrs:{"name":_vm.name,"type":"password","placeholder":"Mot de passe","id":_vm.name},domProps:{"value":(_vm.value)},on:{"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value}}}),_c('div',{staticClass:"input-group-addon",class:{'password-displayed': _vm.type == 'text'},staticStyle:{"cursor":"pointer","background":"white"},attrs:{"title":"Afficher le mot de passe pendant 5 secondes"},on:{"click":_vm.handlerShowPassword}},[(_vm.type == 'text')?_c('i',{staticClass:"glyphicon icon-eye"}):_c('i',{staticClass:"glyphicon icon-eye-off"})])])])}
var PasswordFieldvue_type_template_id_2be9a996_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/PasswordField.vue?vue&type=template&id=2be9a996&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PasswordField.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

let tempo = null;
const TYPE_PASSWORD = "password";
const TYPE_TEXT = "text";

/* harmony default export */ var PasswordFieldvue_type_script_lang_js_ = ({
    props: {
        name: { required: true },
        value: { default: "" },
        label: { default: "" }
    },
    data(){
        return {
            displayPassword: false,
            type: TYPE_PASSWORD
        }
    },
    watch: {
        value(val){
            this.$emit('change', val);
            this.$emit('input', val);
        }
    },
    methods: {
        handlerShowPassword(){

            if( tempo === null ){
                this.type = TYPE_TEXT;
                tempo = new Promise( resolve => {
                    setTimeout( () => {
                        this.type = TYPE_PASSWORD;
                        tempo = null;
                    }, 5000);
                })
            } else {
                tempo = null;
                this.type = TYPE_PASSWORD;
            }



        }
    }
});

// CONCATENATED MODULE: ./src/components/PasswordField.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_PasswordFieldvue_type_script_lang_js_ = (PasswordFieldvue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/components/PasswordField.vue





/* normalize component */

var component = normalizeComponent(
  components_PasswordFieldvue_type_script_lang_js_,
  PasswordFieldvue_type_template_id_2be9a996_render,
  PasswordFieldvue_type_template_id_2be9a996_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var PasswordField = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/AdministrationPcru.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
node node_modules/.bin/gulp administrationPcruWatch

Pour compiler :
node node_modules/.bin/gulp administrationPcru

 */



function flashMessage() {
}

/* harmony default export */ var AdministrationPcruvue_type_script_lang_js_ = ({

  components: {
    "password-field": PasswordField
  },

  props: {
    url: {required: true}
  },

  data() {
    return {
      formData: null,
      configuration: null
    }
  },

  methods: {


    performEdit() {

      let formData = new FormData();
      formData.append('pcru_enabled', this.configuration.pcru_enabled);
      formData.append('host', this.configuration.pcru_host);
      formData.append('port', this.configuration.pcru_port);
      formData.append('user', this.configuration.pcru_user);
      formData.append('pass', this.configuration.pcru_pass);
      formData.append('ssh', this.configuration.pcru_ssh);

      this.$http.post(this.url, formData).then(ok=>{
        this.fetch();
      })
    },

    handlerSuccess(success) {
      let data = success.data;
      this.configuration = data.configuration_pcru;
    },

    fetch() {
      this.$http.get(this.url).then(
          ok => {
            this.handlerSuccess(ok)
          }
      )
    }
  },

  mounted() {
    this.fetch();
  }

});

// CONCATENATED MODULE: ./src/AdministrationPcru.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_AdministrationPcruvue_type_script_lang_js_ = (AdministrationPcruvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/AdministrationPcru.vue





/* normalize component */

var AdministrationPcru_component = normalizeComponent(
  src_AdministrationPcruvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var AdministrationPcru = (AdministrationPcru_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (AdministrationPcru);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=AdministrationPcru.umd.js.map
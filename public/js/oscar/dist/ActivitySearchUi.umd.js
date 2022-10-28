(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ActivitySearchUi"] = factory();
	else
		root["ActivitySearchUi"] = factory();
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

/***/ "4a7a":
/***/ (function(module, exports, __webpack_require__) {

!function(t,e){ true?module.exports=e():undefined}("undefined"!=typeof self?self:this,(function(){return(()=>{var t={646:t=>{t.exports=function(t){if(Array.isArray(t)){for(var e=0,n=new Array(t.length);e<t.length;e++)n[e]=t[e];return n}}},713:t=>{t.exports=function(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}},860:t=>{t.exports=function(t){if(Symbol.iterator in Object(t)||"[object Arguments]"===Object.prototype.toString.call(t))return Array.from(t)}},206:t=>{t.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance")}},319:(t,e,n)=>{var o=n(646),i=n(860),s=n(206);t.exports=function(t){return o(t)||i(t)||s()}},8:t=>{function e(n){return"function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?t.exports=e=function(t){return typeof t}:t.exports=e=function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},e(n)}t.exports=e}},e={};function n(o){var i=e[o];if(void 0!==i)return i.exports;var s=e[o]={exports:{}};return t[o](s,s.exports,n),s.exports}n.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return n.d(e,{a:e}),e},n.d=(t,e)=>{for(var o in e)n.o(e,o)&&!n.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:e[o]})},n.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var o={};return(()=>{"use strict";n.r(o),n.d(o,{VueSelect:()=>m,default:()=>O,mixins:()=>_});var t=n(319),e=n.n(t),i=n(8),s=n.n(i),r=n(713),a=n.n(r);const l={props:{autoscroll:{type:Boolean,default:!0}},watch:{typeAheadPointer:function(){this.autoscroll&&this.maybeAdjustScroll()},open:function(t){var e=this;this.autoscroll&&t&&this.$nextTick((function(){return e.maybeAdjustScroll()}))}},methods:{maybeAdjustScroll:function(){var t,e=(null===(t=this.$refs.dropdownMenu)||void 0===t?void 0:t.children[this.typeAheadPointer])||!1;if(e){var n=this.getDropdownViewport(),o=e.getBoundingClientRect(),i=o.top,s=o.bottom,r=o.height;if(i<n.top)return this.$refs.dropdownMenu.scrollTop=e.offsetTop;if(s>n.bottom)return this.$refs.dropdownMenu.scrollTop=e.offsetTop-(n.height-r)}},getDropdownViewport:function(){return this.$refs.dropdownMenu?this.$refs.dropdownMenu.getBoundingClientRect():{height:0,top:0,bottom:0}}}},c={data:function(){return{typeAheadPointer:-1}},watch:{filteredOptions:function(){for(var t=0;t<this.filteredOptions.length;t++)if(this.selectable(this.filteredOptions[t])){this.typeAheadPointer=t;break}},open:function(t){t&&this.typeAheadToLastSelected()},selectedValue:function(){this.open&&this.typeAheadToLastSelected()}},methods:{typeAheadUp:function(){for(var t=this.typeAheadPointer-1;t>=0;t--)if(this.selectable(this.filteredOptions[t])){this.typeAheadPointer=t;break}},typeAheadDown:function(){for(var t=this.typeAheadPointer+1;t<this.filteredOptions.length;t++)if(this.selectable(this.filteredOptions[t])){this.typeAheadPointer=t;break}},typeAheadSelect:function(){var t=this.filteredOptions[this.typeAheadPointer];t&&this.selectable(t)&&this.select(t)},typeAheadToLastSelected:function(){this.typeAheadPointer=0!==this.selectedValue.length?this.filteredOptions.indexOf(this.selectedValue[this.selectedValue.length-1]):-1}}},u={props:{loading:{type:Boolean,default:!1}},data:function(){return{mutableLoading:!1}},watch:{search:function(){this.$emit("search",this.search,this.toggleLoading)},loading:function(t){this.mutableLoading=t}},methods:{toggleLoading:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null;return this.mutableLoading=null==t?!this.mutableLoading:t}}};function p(t,e,n,o,i,s,r,a){var l,c="function"==typeof t?t.options:t;if(e&&(c.render=e,c.staticRenderFns=n,c._compiled=!0),o&&(c.functional=!0),s&&(c._scopeId="data-v-"+s),r?(l=function(t){(t=t||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext)||"undefined"==typeof __VUE_SSR_CONTEXT__||(t=__VUE_SSR_CONTEXT__),i&&i.call(this,t),t&&t._registeredComponents&&t._registeredComponents.add(r)},c._ssrRegister=l):i&&(l=a?function(){i.call(this,(c.functional?this.parent:this).$root.$options.shadowRoot)}:i),l)if(c.functional){c._injectStyles=l;var u=c.render;c.render=function(t,e){return l.call(e),u(t,e)}}else{var p=c.beforeCreate;c.beforeCreate=p?[].concat(p,l):[l]}return{exports:t,options:c}}const h={Deselect:p({},(function(){var t=this.$createElement,e=this._self._c||t;return e("svg",{attrs:{xmlns:"http://www.w3.org/2000/svg",width:"10",height:"10"}},[e("path",{attrs:{d:"M6.895455 5l2.842897-2.842898c.348864-.348863.348864-.914488 0-1.263636L9.106534.261648c-.348864-.348864-.914489-.348864-1.263636 0L5 3.104545 2.157102.261648c-.348863-.348864-.914488-.348864-1.263636 0L.261648.893466c-.348864.348864-.348864.914489 0 1.263636L3.104545 5 .261648 7.842898c-.348864.348863-.348864.914488 0 1.263636l.631818.631818c.348864.348864.914773.348864 1.263636 0L5 6.895455l2.842898 2.842897c.348863.348864.914772.348864 1.263636 0l.631818-.631818c.348864-.348864.348864-.914489 0-1.263636L6.895455 5z"}})])}),[],!1,null,null,null).exports,OpenIndicator:p({},(function(){var t=this.$createElement,e=this._self._c||t;return e("svg",{attrs:{xmlns:"http://www.w3.org/2000/svg",width:"14",height:"10"}},[e("path",{attrs:{d:"M9.211364 7.59931l4.48338-4.867229c.407008-.441854.407008-1.158247 0-1.60046l-.73712-.80023c-.407008-.441854-1.066904-.441854-1.474243 0L7 5.198617 2.51662.33139c-.407008-.441853-1.066904-.441853-1.474243 0l-.737121.80023c-.407008.441854-.407008 1.158248 0 1.600461l4.48338 4.867228L7 10l2.211364-2.40069z"}})])}),[],!1,null,null,null).exports},d={inserted:function(t,e,n){var o=n.context;if(o.appendToBody){var i=o.$refs.toggle.getBoundingClientRect(),s=i.height,r=i.top,a=i.left,l=i.width,c=window.scrollX||window.pageXOffset,u=window.scrollY||window.pageYOffset;t.unbindPosition=o.calculatePosition(t,o,{width:l+"px",left:c+a+"px",top:u+r+s+"px"}),document.body.appendChild(t)}},unbind:function(t,e,n){n.context.appendToBody&&(t.unbindPosition&&"function"==typeof t.unbindPosition&&t.unbindPosition(),t.parentNode&&t.parentNode.removeChild(t))}};const f=function(t){var e={};return Object.keys(t).sort().forEach((function(n){e[n]=t[n]})),JSON.stringify(e)};var y=0;const b=function(){return++y};function g(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,o)}return n}function v(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?g(Object(n),!0).forEach((function(e){a()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):g(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}const m=p({components:v({},h),directives:{appendToBody:d},mixins:[l,c,u],props:{value:{},components:{type:Object,default:function(){return{}}},options:{type:Array,default:function(){return[]}},disabled:{type:Boolean,default:!1},clearable:{type:Boolean,default:!0},deselectFromDropdown:{type:Boolean,default:!1},searchable:{type:Boolean,default:!0},multiple:{type:Boolean,default:!1},placeholder:{type:String,default:""},transition:{type:String,default:"vs__fade"},clearSearchOnSelect:{type:Boolean,default:!0},closeOnSelect:{type:Boolean,default:!0},label:{type:String,default:"label"},autocomplete:{type:String,default:"off"},reduce:{type:Function,default:function(t){return t}},selectable:{type:Function,default:function(t){return!0}},getOptionLabel:{type:Function,default:function(t){return"object"===s()(t)?t.hasOwnProperty(this.label)?t[this.label]:console.warn('[vue-select warn]: Label key "option.'.concat(this.label,'" does not')+" exist in options object ".concat(JSON.stringify(t),".\n")+"https://vue-select.org/api/props.html#getoptionlabel"):t}},getOptionKey:{type:Function,default:function(t){if("object"!==s()(t))return t;try{return t.hasOwnProperty("id")?t.id:f(t)}catch(e){return console.warn("[vue-select warn]: Could not stringify this option to generate unique key. Please provide'getOptionKey' prop to return a unique key for each option.\nhttps://vue-select.org/api/props.html#getoptionkey",t,e)}}},onTab:{type:Function,default:function(){this.selectOnTab&&!this.isComposing&&this.typeAheadSelect()}},taggable:{type:Boolean,default:!1},tabindex:{type:Number,default:null},pushTags:{type:Boolean,default:!1},filterable:{type:Boolean,default:!0},filterBy:{type:Function,default:function(t,e,n){return(e||"").toLocaleLowerCase().indexOf(n.toLocaleLowerCase())>-1}},filter:{type:Function,default:function(t,e){var n=this;return t.filter((function(t){var o=n.getOptionLabel(t);return"number"==typeof o&&(o=o.toString()),n.filterBy(t,o,e)}))}},createOption:{type:Function,default:function(t){return"object"===s()(this.optionList[0])?a()({},this.label,t):t}},resetOnOptionsChange:{default:!1,validator:function(t){return["function","boolean"].includes(s()(t))}},clearSearchOnBlur:{type:Function,default:function(t){var e=t.clearSearchOnSelect,n=t.multiple;return e&&!n}},noDrop:{type:Boolean,default:!1},inputId:{type:String},dir:{type:String,default:"auto"},selectOnTab:{type:Boolean,default:!1},selectOnKeyCodes:{type:Array,default:function(){return[13]}},searchInputQuerySelector:{type:String,default:"[type=search]"},mapKeydown:{type:Function,default:function(t,e){return t}},appendToBody:{type:Boolean,default:!1},calculatePosition:{type:Function,default:function(t,e,n){var o=n.width,i=n.top,s=n.left;t.style.top=i,t.style.left=s,t.style.width=o}},dropdownShouldOpen:{type:Function,default:function(t){var e=t.noDrop,n=t.open,o=t.mutableLoading;return!e&&(n&&!o)}},uid:{type:[String,Number],default:function(){return b()}}},data:function(){return{search:"",open:!1,isComposing:!1,pushedTags:[],_value:[]}},computed:{isTrackingValues:function(){return void 0===this.value||this.$options.propsData.hasOwnProperty("reduce")},selectedValue:function(){var t=this.value;return this.isTrackingValues&&(t=this.$data._value),null!=t&&""!==t?[].concat(t):[]},optionList:function(){return this.options.concat(this.pushTags?this.pushedTags:[])},searchEl:function(){return this.$scopedSlots.search?this.$refs.selectedOptions.querySelector(this.searchInputQuerySelector):this.$refs.search},scope:function(){var t=this,e={search:this.search,loading:this.loading,searching:this.searching,filteredOptions:this.filteredOptions};return{search:{attributes:v({disabled:this.disabled,placeholder:this.searchPlaceholder,tabindex:this.tabindex,readonly:!this.searchable,id:this.inputId,"aria-autocomplete":"list","aria-labelledby":"vs".concat(this.uid,"__combobox"),"aria-controls":"vs".concat(this.uid,"__listbox"),ref:"search",type:"search",autocomplete:this.autocomplete,value:this.search},this.dropdownOpen&&this.filteredOptions[this.typeAheadPointer]?{"aria-activedescendant":"vs".concat(this.uid,"__option-").concat(this.typeAheadPointer)}:{}),events:{compositionstart:function(){return t.isComposing=!0},compositionend:function(){return t.isComposing=!1},keydown:this.onSearchKeyDown,blur:this.onSearchBlur,focus:this.onSearchFocus,input:function(e){return t.search=e.target.value}}},spinner:{loading:this.mutableLoading},noOptions:{search:this.search,loading:this.mutableLoading,searching:this.searching},openIndicator:{attributes:{ref:"openIndicator",role:"presentation",class:"vs__open-indicator"}},listHeader:e,listFooter:e,header:v({},e,{deselect:this.deselect}),footer:v({},e,{deselect:this.deselect})}},childComponents:function(){return v({},h,{},this.components)},stateClasses:function(){return{"vs--open":this.dropdownOpen,"vs--single":!this.multiple,"vs--multiple":this.multiple,"vs--searching":this.searching&&!this.noDrop,"vs--searchable":this.searchable&&!this.noDrop,"vs--unsearchable":!this.searchable,"vs--loading":this.mutableLoading,"vs--disabled":this.disabled}},searching:function(){return!!this.search},dropdownOpen:function(){return this.dropdownShouldOpen(this)},searchPlaceholder:function(){return this.isValueEmpty&&this.placeholder?this.placeholder:void 0},filteredOptions:function(){var t=[].concat(this.optionList);if(!this.filterable&&!this.taggable)return t;var e=this.search.length?this.filter(t,this.search,this):t;if(this.taggable&&this.search.length){var n=this.createOption(this.search);this.optionExists(n)||e.unshift(n)}return e},isValueEmpty:function(){return 0===this.selectedValue.length},showClearButton:function(){return!this.multiple&&this.clearable&&!this.open&&!this.isValueEmpty}},watch:{options:function(t,e){var n=this;!this.taggable&&("function"==typeof n.resetOnOptionsChange?n.resetOnOptionsChange(t,e,n.selectedValue):n.resetOnOptionsChange)&&this.clearSelection(),this.value&&this.isTrackingValues&&this.setInternalValueFromOptions(this.value)},value:{immediate:!0,handler:function(t){this.isTrackingValues&&this.setInternalValueFromOptions(t)}},multiple:function(){this.clearSelection()},open:function(t){this.$emit(t?"open":"close")}},created:function(){this.mutableLoading=this.loading,this.$on("option:created",this.pushTag)},methods:{setInternalValueFromOptions:function(t){var e=this;Array.isArray(t)?this.$data._value=t.map((function(t){return e.findOptionFromReducedValue(t)})):this.$data._value=this.findOptionFromReducedValue(t)},select:function(t){this.$emit("option:selecting",t),this.isOptionSelected(t)?this.deselectFromDropdown&&(this.clearable||this.multiple&&this.selectedValue.length>1)&&this.deselect(t):(this.taggable&&!this.optionExists(t)&&this.$emit("option:created",t),this.multiple&&(t=this.selectedValue.concat(t)),this.updateValue(t),this.$emit("option:selected",t)),this.onAfterSelect(t)},deselect:function(t){var e=this;this.$emit("option:deselecting",t),this.updateValue(this.selectedValue.filter((function(n){return!e.optionComparator(n,t)}))),this.$emit("option:deselected",t)},clearSelection:function(){this.updateValue(this.multiple?[]:null)},onAfterSelect:function(t){var e=this;this.closeOnSelect&&(this.open=!this.open,this.searchEl.blur()),this.clearSearchOnSelect&&(this.search=""),this.noDrop&&this.multiple&&this.$nextTick((function(){return e.$refs.search.focus()}))},updateValue:function(t){var e=this;void 0===this.value&&(this.$data._value=t),null!==t&&(t=Array.isArray(t)?t.map((function(t){return e.reduce(t)})):this.reduce(t)),this.$emit("input",t)},toggleDropdown:function(t){var n=t.target!==this.searchEl;n&&t.preventDefault();var o=[].concat(e()(this.$refs.deselectButtons||[]),e()([this.$refs.clearButton]||false));void 0===this.searchEl||o.filter(Boolean).some((function(e){return e.contains(t.target)||e===t.target}))?t.preventDefault():this.open&&n?this.searchEl.blur():this.disabled||(this.open=!0,this.searchEl.focus())},isOptionSelected:function(t){var e=this;return this.selectedValue.some((function(n){return e.optionComparator(n,t)}))},isOptionDeselectable:function(t){return this.isOptionSelected(t)&&this.deselectFromDropdown},optionComparator:function(t,e){return this.getOptionKey(t)===this.getOptionKey(e)},findOptionFromReducedValue:function(t){var n=this,o=[].concat(e()(this.options),e()(this.pushedTags)).filter((function(e){return JSON.stringify(n.reduce(e))===JSON.stringify(t)}));return 1===o.length?o[0]:o.find((function(t){return n.optionComparator(t,n.$data._value)}))||t},closeSearchOptions:function(){this.open=!1,this.$emit("search:blur")},maybeDeleteValue:function(){if(!this.searchEl.value.length&&this.selectedValue&&this.selectedValue.length&&this.clearable){var t=null;this.multiple&&(t=e()(this.selectedValue.slice(0,this.selectedValue.length-1))),this.updateValue(t)}},optionExists:function(t){var e=this;return this.optionList.some((function(n){return e.optionComparator(n,t)}))},normalizeOptionForSlot:function(t){return"object"===s()(t)?t:a()({},this.label,t)},pushTag:function(t){this.pushedTags.push(t)},onEscape:function(){this.search.length?this.search="":this.searchEl.blur()},onSearchBlur:function(){if(!this.mousedown||this.searching){var t=this.clearSearchOnSelect,e=this.multiple;return this.clearSearchOnBlur({clearSearchOnSelect:t,multiple:e})&&(this.search=""),void this.closeSearchOptions()}this.mousedown=!1,0!==this.search.length||0!==this.options.length||this.closeSearchOptions()},onSearchFocus:function(){this.open=!0,this.$emit("search:focus")},onMousedown:function(){this.mousedown=!0},onMouseUp:function(){this.mousedown=!1},onSearchKeyDown:function(t){var e=this,n=function(t){return t.preventDefault(),!e.isComposing&&e.typeAheadSelect()},o={8:function(t){return e.maybeDeleteValue()},9:function(t){return e.onTab()},27:function(t){return e.onEscape()},38:function(t){return t.preventDefault(),e.typeAheadUp()},40:function(t){return t.preventDefault(),e.typeAheadDown()}};this.selectOnKeyCodes.forEach((function(t){return o[t]=n}));var i=this.mapKeydown(o,this);if("function"==typeof i[t.keyCode])return i[t.keyCode](t)}}},(function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"v-select",class:t.stateClasses,attrs:{dir:t.dir}},[t._t("header",null,null,t.scope.header),t._v(" "),n("div",{ref:"toggle",staticClass:"vs__dropdown-toggle",attrs:{id:"vs"+t.uid+"__combobox",role:"combobox","aria-expanded":t.dropdownOpen.toString(),"aria-owns":"vs"+t.uid+"__listbox","aria-label":"Search for option"},on:{mousedown:function(e){return t.toggleDropdown(e)}}},[n("div",{ref:"selectedOptions",staticClass:"vs__selected-options"},[t._l(t.selectedValue,(function(e){return t._t("selected-option-container",[n("span",{key:t.getOptionKey(e),staticClass:"vs__selected"},[t._t("selected-option",[t._v("\n            "+t._s(t.getOptionLabel(e))+"\n          ")],null,t.normalizeOptionForSlot(e)),t._v(" "),t.multiple?n("button",{ref:"deselectButtons",refInFor:!0,staticClass:"vs__deselect",attrs:{disabled:t.disabled,type:"button",title:"Deselect "+t.getOptionLabel(e),"aria-label":"Deselect "+t.getOptionLabel(e)},on:{click:function(n){return t.deselect(e)}}},[n(t.childComponents.Deselect,{tag:"component"})],1):t._e()],2)],{option:t.normalizeOptionForSlot(e),deselect:t.deselect,multiple:t.multiple,disabled:t.disabled})})),t._v(" "),t._t("search",[n("input",t._g(t._b({staticClass:"vs__search"},"input",t.scope.search.attributes,!1),t.scope.search.events))],null,t.scope.search)],2),t._v(" "),n("div",{ref:"actions",staticClass:"vs__actions"},[n("button",{directives:[{name:"show",rawName:"v-show",value:t.showClearButton,expression:"showClearButton"}],ref:"clearButton",staticClass:"vs__clear",attrs:{disabled:t.disabled,type:"button",title:"Clear Selected","aria-label":"Clear Selected"},on:{click:t.clearSelection}},[n(t.childComponents.Deselect,{tag:"component"})],1),t._v(" "),t._t("open-indicator",[t.noDrop?t._e():n(t.childComponents.OpenIndicator,t._b({tag:"component"},"component",t.scope.openIndicator.attributes,!1))],null,t.scope.openIndicator),t._v(" "),t._t("spinner",[n("div",{directives:[{name:"show",rawName:"v-show",value:t.mutableLoading,expression:"mutableLoading"}],staticClass:"vs__spinner"},[t._v("Loading...")])],null,t.scope.spinner)],2)]),t._v(" "),n("transition",{attrs:{name:t.transition}},[t.dropdownOpen?n("ul",{directives:[{name:"append-to-body",rawName:"v-append-to-body"}],key:"vs"+t.uid+"__listbox",ref:"dropdownMenu",staticClass:"vs__dropdown-menu",attrs:{id:"vs"+t.uid+"__listbox",role:"listbox",tabindex:"-1"},on:{mousedown:function(e){return e.preventDefault(),t.onMousedown(e)},mouseup:t.onMouseUp}},[t._t("list-header",null,null,t.scope.listHeader),t._v(" "),t._l(t.filteredOptions,(function(e,o){return n("li",{key:t.getOptionKey(e),staticClass:"vs__dropdown-option",class:{"vs__dropdown-option--deselect":t.isOptionDeselectable(e)&&o===t.typeAheadPointer,"vs__dropdown-option--selected":t.isOptionSelected(e),"vs__dropdown-option--highlight":o===t.typeAheadPointer,"vs__dropdown-option--disabled":!t.selectable(e)},attrs:{id:"vs"+t.uid+"__option-"+o,role:"option","aria-selected":o===t.typeAheadPointer||null},on:{mouseover:function(n){t.selectable(e)&&(t.typeAheadPointer=o)},click:function(n){n.preventDefault(),n.stopPropagation(),t.selectable(e)&&t.select(e)}}},[t._t("option",[t._v("\n          "+t._s(t.getOptionLabel(e))+"\n        ")],null,t.normalizeOptionForSlot(e))],2)})),t._v(" "),0===t.filteredOptions.length?n("li",{staticClass:"vs__no-options"},[t._t("no-options",[t._v("\n          Sorry, no matching options.\n        ")],null,t.scope.noOptions)],2):t._e(),t._v(" "),t._t("list-footer",null,null,t.scope.listFooter)],2):n("ul",{staticStyle:{display:"none",visibility:"hidden"},attrs:{id:"vs"+t.uid+"__listbox",role:"listbox"}})]),t._v(" "),t._t("footer",null,null,t.scope.footer)],2)}),[],!1,null,null,null).exports,_={ajax:u,pointer:c,pointerScroll:l},O=m})(),o})()}));
//# sourceMappingURL=vue-select.js.map

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

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivitySearchUi.vue?vue&type=template&id=6d49a6f2&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[(_vm.error)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('a',{attrs:{"href":"#"},on:{"click":function($event){_vm.error = ''}}},[_vm._v("Fermer")]),_vm._v(" "+_vm._s(_vm.error)+" ")])]):_vm._e(),(_vm.debug)?_c('div',{staticClass:"overlay"},[_c('div',{staticClass:"overlay-content"},[_c('a',{attrs:{"href":"#"},on:{"click":function($event){_vm.debug = ''}}},[_c('i',{staticClass:"icon-bug"}),_vm._v(" Fermer")]),_c('pre',[_vm._v("        "+_vm._s(_vm.debug)+"\n      ")])])]):_vm._e(),_c('transition',{attrs:{"name":"fade"}},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.loaderMsg),expression:"loaderMsg"}],staticClass:"vue-loader"},[_c('div',{staticClass:"content-loader"},[_c('i',{staticClass:"icon-spinner animate-spin"}),_vm._v(" "+_vm._s(_vm.loaderMsg)+" ")])])]),_c('h1',[_c('i',{staticClass:"icon-cube"}),_vm._v(" "+_vm._s(_vm.title)+" ")]),_c('form',{attrs:{"action":""}},[_c('div',{staticClass:"input-group input-group-lg"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.search),expression:"search"}],staticClass:"form-control input-lg",attrs:{"placeholder":"Rechercher dans l'intitulé, code PFI...…","name":"q","type":"search"},domProps:{"value":(_vm.search)},on:{"input":function($event){if($event.target.composing){ return; }_vm.search=$event.target.value}}}),_vm._m(0)]),(_vm.showCriteria)?_c('section',[_c('h3',[_vm._v("Critères de recherche")]),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-4"},[_c('h5',[_vm._v("Filtres")]),_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selecting_filter),expression:"selecting_filter"}],staticClass:"form-control",on:{"change":[function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selecting_filter=$event.target.multiple ? $$selectedVal : $$selectedVal[0]},_vm.handlerSelectFilter]}},[_c('option',{attrs:{"value":""}},[_vm._v("Ajouter un filtre…")]),_vm._l((_vm.filters),function(label,f){return _c('option',{domProps:{"value":f}},[_vm._v(_vm._s(label))])})],2)]),_c('div',{staticClass:"col-md-2"},[_c('h5',[_vm._v("Status")]),_c('super-select',{staticStyle:{"min-width":"250px"},attrs:{"options":_vm.status,"name":'st'},on:{"change":_vm.updateSelected},model:{value:(_vm.used_status),callback:function ($$v) {_vm.used_status=$$v},expression:"used_status"}})],1),_c('div',{staticClass:"col-md-2"},[_c('h5',[_vm._v("Trier par")]),_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.sorter),expression:"sorter"}],staticClass:"form-control",attrs:{"name":"t"},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.sorter=$event.target.multiple ? $$selectedVal : $$selectedVal[0]}}},_vm._l((_vm.sortters),function(text,s){return _c('option',{domProps:{"value":s}},[_vm._v(_vm._s(text))])}),0)]),_c('div',{staticClass:"col-md-2"},[_c('h5',[_vm._v("Ordre")]),_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.direction),expression:"direction"}],staticClass:"form-control",attrs:{"name":"d"},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.direction=$event.target.multiple ? $$selectedVal : $$selectedVal[0]}}},_vm._l((_vm.directions),function(text,s){return _c('option',{domProps:{"value":s}},[_vm._v(_vm._s(s)+" - "+_vm._s(text))])}),0)]),_c('div',{staticClass:"col-md-2"},[_c('h5',[_vm._v("Ignorer si null")]),_c('label',{staticClass:"label-primary",attrs:{"for":"ui_vuecompact"}},[_vm._v(" Mode compact "),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.ui_vuecompact),expression:"ui_vuecompact"}],attrs:{"id":"ui_vuecompact","name":"ui_vuecompact","type":"checkbox"},domProps:{"checked":Array.isArray(_vm.ui_vuecompact)?_vm._i(_vm.ui_vuecompact,null)>-1:(_vm.ui_vuecompact)},on:{"change":function($event){var $$a=_vm.ui_vuecompact,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.ui_vuecompact=$$a.concat([$$v]))}else{$$i>-1&&(_vm.ui_vuecompact=$$a.slice(0,$$i).concat($$a.slice($$i+1)))}}else{_vm.ui_vuecompact=$$c}}}})])])]),_c('hr'),_vm._l((_vm.filters_obj),function(f){return _c('section',[(f.type == 'ap')?_c('a-s-filter-person',{attrs:{"type":'ap',"value1":f.value1,"value2":f.value2,"roles_values":_vm.roles_person,"error":f.error},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'pm')?_c('a-s-filter-person',{attrs:{"type":'pm',"value1":f.value1,"value2":f.value2,"multiple":true,"roles_values":_vm.roles_person,"error":f.error},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'ao')?_c('a-s-filter-organization',{attrs:{"type":'ao',"value1":f.value1,"value2":f.value2,"roles_values":_vm.roles_organizations,"error":f.error},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'so')?_c('a-s-filter-organization',{attrs:{"type":'so',"label":'N\'impliquant pas',"value1":f.value1,"value2":f.value2,"roles_values":_vm.roles_organizations,"error":f.error},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'sp')?_c('a-s-filter-person',{attrs:{"type":'sp',"value1":f.value1,"value2":f.value2,"label":'N\'impliquant pas',"roles_values":_vm.roles_person,"error":f.error},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'cnt')?_c('a-s-filter-select',{attrs:{"type":'cnt',"value1":f.value1,"label":'Pays (d\'une organisation)',"icon":'icon-flag',"error":f.error,"options":_vm.options_pays},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'tnt')?_c('select-key-value',{attrs:{"type":'tnt',"value1":f.value1,"label":'Type d\'organisation',"icon":'icon-tag',"error":f.error,"options":_vm.options_organization_types},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'add')?_c('single-date-field',{attrs:{"type":f.type,"moment":_vm.moment,"value1":f.value1,"value2":f.value2,"label":'Date de début',"error":f.error},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'adf')?_c('single-date-field',{attrs:{"type":f.type,"moment":_vm.moment,"error":f.error,"value1":f.value1,"value2":f.value2,"label":'Date de fin'},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'adc')?_c('single-date-field',{attrs:{"type":f.type,"moment":_vm.moment,"error":f.error,"value1":f.value1,"value2":f.value2,"label":'Date de Création'},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):(f.type == 'adm')?_c('single-date-field',{attrs:{"type":f.type,"moment":_vm.moment,"error":f.error,"value1":f.value1,"value2":f.value2,"label":'Date de Mise à jour'},on:{"delete":function($event){return _vm.handlerDeleteFilter(f)}}}):_c('div',{staticClass:"card critera"},[_vm._v(" non géré "+_vm._s(f)+" ")])],1)}),_vm._m(1)],2):_vm._e()]),(_vm.search !== null)?_c('section',[_c('h2',{staticClass:"text-right"},[_vm._v(_vm._s(_vm.totalResultQuery)+" résultat(s)")]),_c('transition-group',{attrs:{"name":"list","tag":"div"}},_vm._l((_vm.activities),function(activity){return _c('activity',{key:activity.id,attrs:{"activity":activity,"compact":_vm.ui_vuecompact},on:{"debug":_vm.catchDebug}})}),1)],1):_vm._e()],1)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"input-group-btn"},[_c('button',{staticClass:"btn btn-primary",attrs:{"type":"submit"}},[_vm._v("Rechercher")])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('nav',{staticClass:"text-right"},[_c('button',{staticClass:"btn btn-default",attrs:{"type":"reset"}},[_vm._v(" Réinitialiser le recherche ")]),_c('button',{staticClass:"btn btn-primary",attrs:{"type":"submit"}},[_vm._v(" Actualiser la recherche ")])])}]


// CONCATENATED MODULE: ./src/ActivitySearchUi.vue?vue&type=template&id=6d49a6f2&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivitySearchItem.vue?vue&type=template&id=cd3e4886&
var ActivitySearchItemvue_type_template_id_cd3e4886_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('article',{staticClass:"card",class:'activity-item-' +_vm.activity.statutId,on:{"click":function($event){if(!$event.shiftKey){ return null; }return _vm.handlerDebug(_vm.activity)}}},[_c('h3',{staticClass:"card-title"},[_c('span',{staticClass:"picto",class:'status-'+_vm.activity.statutId},[_c('i',{staticClass:"icon"}),_c('span',{staticClass:"text"},[_vm._v(_vm._s(_vm.activity.statut))])]),_c('small',[_vm._v(_vm._s(_vm.activity.typeOscar)+" ")]),_c('span',[_c('a',{attrs:{"href":'/activites-de-recherche/fiche-detaillee/' + _vm.activity.id}},[(_vm.activity.projectacronym)?_c('strong',{staticClass:"text-light"},[_vm._v("["+_vm._s(_vm.activity.projectacronym)+"] / ")]):_vm._e(),_c('strong',[_vm._v(_vm._s(_vm.activity.numOscar))]),_vm._v(" : "+_vm._s(_vm.activity.label)+" ")]),_c('a',{staticClass:"more",attrs:{"href":'/activites-de-recherche/fiche-detaillee/' + _vm.activity.id}},[_vm._v("Fiche")])]),(_vm.activity.amount)?_c('span',{staticClass:"montant recette"},[_c('span',{staticClass:"currency",attrs:{"title":_vm.activity.amount.value +' ' + _vm.activity.amount.currency}},[_c('span',{staticClass:"value"},[_vm._v(_vm._s(_vm._f("money")(_vm.activity.amount.value)))]),_c('span',{staticClass:"currency"},[_vm._v(_vm._s(_vm.activity.amount.currency))])])]):_vm._e()]),_c('div',{staticClass:"card-content"},[_c('div',{staticClass:"row metas"},[_c('div',{staticClass:"col-md-12"},[_c('span',{staticClass:"number"},[_c('small',{staticClass:"key number-label"},[_vm._v("N°OSCAR")]),_c('strong',{staticClass:"value number-value"},[_vm._v(" "+_vm._s(_vm.activity.numOscar)+" ")])]),(_vm.activity.PFI)?_c('span',{staticClass:"number"},[_c('small',{staticClass:"key number-label"},[_vm._v("PFI")]),_c('strong',{staticClass:"value number-value"},[_vm._v(" "+_vm._s(_vm.activity.PFI)+" ")])]):_vm._e(),_vm._l((_vm.activity.numbers),function(value,key){return _c('span',{staticClass:"number"},[_c('small',{staticClass:"key number-label"},[_vm._v(_vm._s(key))]),_c('strong',{staticClass:"value number-value"},[_vm._v(" "+_vm._s(value)+" ")])])}),(_vm.activity.has_workpackages)?_c('span',{staticClass:"cartouche secondary1"},[_c('i',{staticClass:"icon-calendar"}),_vm._v(" Soumis aux feuille de temps ")]):_vm._e()],2),_c('div',{staticClass:"col-sm-12"},[_vm._v(" Signature : "),(_vm.activity.dateSigned)?_c('strong',[_vm._v(_vm._s(_vm._f("fullDate")(_vm.activity.dateSigned)))]):_c('strong',[_vm._v("Non signé")]),_vm._v(" ~ "),(_vm.activity.dateStart || _vm.activity.dateEnd)?_c('span',[_vm._v(" Active "),(_vm.activity.dateStart)?_c('span',[_vm._v(" du "),_c('time',[_vm._v(_vm._s(_vm._f("fullDate")(_vm.activity.dateStart)))])]):_vm._e(),(_vm.activity.dateEnd)?_c('span',[_vm._v(" au "),_c('time',[_vm._v(_vm._s(_vm._f("fullDate")(_vm.activity.dateEnd)))])]):_vm._e()]):_vm._e(),_c('br'),_vm._v(" Créé le "),_c('time',[_vm._v(_vm._s(_vm._f("fullDate")(_vm.activity.dateCreated)))]),_vm._v(" Dernière mise à jour : "),_c('time',[_vm._v(_vm._s(_vm._f("fullDate")(_vm.activity.dateUpdated)))])])]),(!_vm.compact)?_c('div',{staticClass:"row metas"},[_c('div',{staticClass:"col-sm-6"},_vm._l((_vm.activity.persons_primary),function(persons,role){return _c('div',[_c('i',{class:_vm._f("slugify")('icon-' + role)}),_vm._v(_vm._s(role)+" : "),_vm._l((persons),function(person){return _c('a',{staticClass:"person",class:{'unclickable': !person.url},attrs:{"href":person.url,"title":person.affectation},on:{"click":function($event){return _vm.handlerClickPerson($event, person)}}},[_c('i',{class:'icon-' + (person.spot == 'activity' ? 'cube' : 'cubes')}),_vm._v(" "+_vm._s(person.displayName)+" ")])})],2)}),0),_c('div',{staticClass:"col-sm-6"},_vm._l((_vm.activity.organizations_primary),function(organizations,role){return _c('div',[_c('i',{class:_vm._f("slugify")('icon-' + role)}),_vm._v(_vm._s(role)+" : "),_vm._l((organizations),function(organization){return _c('a',{staticClass:"organization",class:{'unclickable': !organization.url},attrs:{"href":organization.url},on:{"click":function($event){return _vm.handlerClickOrganization($event, organization)}}},[_c('i',{class:'icon-' + (organization.spot == 'activity' ? 'cube' : 'cubes')}),_vm._v(" "+_vm._s(organization.displayName)+" ")])})],2)}),0)]):_vm._e(),(_vm.activity.persons && !_vm.compact)?_c('p',{staticClass:"text-highlight"},[_c('i',{staticClass:"icon-user grey"}),_vm._v("Membres : "),_vm._l((_vm.activity.persons),function(persons,role){return _c('span',_vm._l((persons),function(person){return _c('a',{staticClass:"person cartouche xs",class:{'unclickable': !person.url},attrs:{"title":person.affectation,"href":person.url},on:{"click":function($event){return _vm.handlerClickPerson($event, person)}}},[_c('i',{class:'icon-' + (person.spot == 'activity' ? 'cube' : 'cubes')}),_vm._v(" "+_vm._s(person.displayName)+" "),_c('span',{staticClass:"addon"},[_vm._v(_vm._s(role))])])}),0)})],2):_vm._e(),(_vm.activity.organizations && !_vm.compact)?_c('p',{staticClass:"text-highlight"},[_c('i',{staticClass:"icon-building-filled grey"}),_vm._v("Partenaires : "),_vm._l((_vm.activity.organizations),function(organizations,role){return _c('span',_vm._l((organizations),function(organization){return _c('a',{staticClass:"organization cartouche xs",class:{'unclickable': !organization.url},attrs:{"href":organization.url},on:{"click":function($event){return _vm.handlerClickOrganization($event, organization)}}},[_c('i',{class:'icon-' + (organization.spot == 'activity' ? 'cube' : 'cubes')}),_vm._v(" "+_vm._s(organization.displayName)+" "),_c('span',{staticClass:"addon"},[_vm._v(_vm._s(role))])])}),0)})],2):_vm._e(),(!_vm.compact)?_c('div',{staticClass:"content-expand"},[(_vm.activity.project)?_c('div',{staticClass:"text-highlight"},[_vm._v(" Project "),_c('span',[_c('a',{staticClass:"project",attrs:{"href":'/project/show/' + _vm.activity.project.id}},[_c('i',{staticClass:"icon-cubes"}),_vm._v(_vm._s(_vm.activity.project.displayName)+" ")])])]):_c('div',[_c('i',{staticClass:"icon-attention-1"}),_vm._v(" Cette activité n'a pas de projet ")])]):_vm._e()])])}
var ActivitySearchItemvue_type_template_id_cd3e4886_staticRenderFns = []


// CONCATENATED MODULE: ./src/ActivitySearchItem.vue?vue&type=template&id=cd3e4886&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivitySearchItem.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var ActivitySearchItemvue_type_script_lang_js_ = ({
  props: {
    activity: {required: true},
    compact: {type: Boolean, default: false},
    person_url: {type: Boolean, default: true},
    organization_url: {type: Boolean, default: false}
  },
  methods: {
    handlerDebug(dt){
      console.log(dt);
      this.$emit('debug', dt);
    },
    handlerClickPerson(evt, person){
      if( !person.url ){
        evt.preventDefault();
      }
    },
    handlerClickOrganization(evt, organization){
      if( !organization.url ){
        evt.preventDefault();
      }
    }
  }
});

// CONCATENATED MODULE: ./src/ActivitySearchItem.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ActivitySearchItemvue_type_script_lang_js_ = (ActivitySearchItemvue_type_script_lang_js_); 
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

// CONCATENATED MODULE: ./src/ActivitySearchItem.vue





/* normalize component */

var component = normalizeComponent(
  src_ActivitySearchItemvue_type_script_lang_js_,
  ActivitySearchItemvue_type_template_id_cd3e4886_render,
  ActivitySearchItemvue_type_template_id_cd3e4886_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ActivitySearchItem = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/SuperSelect.vue?vue&type=template&id=261f9cdc&
var SuperSelectvue_type_template_id_261f9cdc_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"superselect",on:{"mouseleave":function($event){_vm.mode='display'},"click":_vm.handlerSwitchMode}},[_c('span',[_c('span',{staticClass:"labeling"},[(_vm.selected.length == 0)?_c('em',{staticStyle:{"cursor":"pointer"}},[_vm._v(_vm._s(_vm.label))]):_vm._e(),_vm._l((_vm.selected),function(v){return _c('strong',{staticClass:"cartouche"},[_vm._v(_vm._s(_vm.options[v]))])})],2),_vm._m(0)]),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.mode == 'edit'),expression:"mode == 'edit'"}],staticClass:"selector"},[_c('input',{attrs:{"type":"hidden","name":_vm.name},domProps:{"value":_vm.sendValue}}),_vm._l((_vm.options),function(label,key){return _c('div',[_c('label',{attrs:{"for":'choose-' +key}},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],attrs:{"type":"checkbox","id":'choose-' +key},domProps:{"value":key,"checked":Array.isArray(_vm.selected)?_vm._i(_vm.selected,key)>-1:(_vm.selected)},on:{"change":function($event){var $$a=_vm.selected,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=key,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.selected=$$a.concat([$$v]))}else{$$i>-1&&(_vm.selected=$$a.slice(0,$$i).concat($$a.slice($$i+1)))}}else{_vm.selected=$$c}}}}),_vm._v(" "+_vm._s(label)+" ")])])}),_c('hr'),_c('button',{staticClass:"btn btn-default",on:{"click":function($event){$event.preventDefault();_vm.value = []}}},[_vm._v("Effacer")])],2)])}
var SuperSelectvue_type_template_id_261f9cdc_staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',[_c('i',{staticClass:"icon-down-dir"})])}]


// CONCATENATED MODULE: ./src/components/SuperSelect.vue?vue&type=template&id=261f9cdc&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/SuperSelect.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var SuperSelectvue_type_script_lang_js_ = ({
  props: {
    options: {
      required: true
    },
    value: {
      required: true
    },
    label: {
      default: "Selectionnez une valeur"
    },
    name: {
      default: "foo"
    }
  },
  watch:{
    selected(){
      this.value = this.sendValue;
      this.$emit('change', this.sendValue);
      this.$emit('input', this.sendValue);
    }
  },
  computed: {
    sendValue(){
      return this.selected ? this.selected.join(',') : '';
    }
  },
  methods: {
    handlerSwitchMode(evt){
      this.mode = this.mode == 'display' ? 'edit' : 'display';
    }
  },
  data(){
    return {
      open: false,
      mode: 'display',
      selected: []
    }
  },

  mounted(){
    console.log('options:', this.options);
    console.log('value:', this.value);
    if( this.value ){
      this.selected = this.value.split(',');
    }
  }
});

// CONCATENATED MODULE: ./src/components/SuperSelect.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_SuperSelectvue_type_script_lang_js_ = (SuperSelectvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/SuperSelect.vue





/* normalize component */

var SuperSelect_component = normalizeComponent(
  components_SuperSelectvue_type_script_lang_js_,
  SuperSelectvue_type_template_id_261f9cdc_render,
  SuperSelectvue_type_template_id_261f9cdc_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var SuperSelect = (SuperSelect_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PersonAutoCompleter2.vue?vue&type=template&id=719a0771&
var PersonAutoCompleter2vue_type_template_id_719a0771_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('v-select',{attrs:{"placeholder":"Rechercher une personne","options":_vm.options,"label":"label","multiple":"","selectable":function () { return _vm.selectable; },"reduce":function (item) { return item.id; }},on:{"search":_vm.handlerSearch,"input":_vm.setSelected},scopedSlots:_vm._u([{key:"option",fn:function(ref){
var id = ref.id;
var label = ref.label;
var firstName = ref.firstName;
var lastName = ref.lastName;
var affectation = ref.affectation;
var ucbnSiteLocalisation = ref.ucbnSiteLocalisation;
return [_c('div',{staticClass:"result-item",class:{'person-closed': _vm.closed }},[_c('h4',{staticStyle:{"margin":"0"}},[_c('span',[_c('em',[_vm._v(_vm._s(firstName))]),_c('strong',[_vm._v(_vm._s(lastName))])])]),(affectation || _vm.location)?_c('div',{staticClass:"location"},[_c('i',{staticClass:"icon-location"}),(affectation)?_c('strong',[_vm._v(_vm._s(affectation))]):_vm._e(),(ucbnSiteLocalisation)?_c('em',[_vm._v(_vm._s(ucbnSiteLocalisation))]):_vm._e()]):_vm._e()])]}}]),model:{value:(_vm.value),callback:function ($$v) {_vm.value=$$v},expression:"value"}})}
var PersonAutoCompleter2vue_type_template_id_719a0771_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/PersonAutoCompleter2.vue?vue&type=template&id=719a0771&

// EXTERNAL MODULE: ./node_modules/vue-select/dist/vue-select.js
var vue_select = __webpack_require__("4a7a");
var vue_select_default = /*#__PURE__*/__webpack_require__.n(vue_select);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/PersonAutoCompleter2.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/**
 * {
	"2": {
		"id": 11232,
		"label": "!FERME! [904] UFR SCIENCES HOMME ",
		"closed": true
	}
}
 */



let intify = function( value ){
  if( value ){
    let ints = value.split(',');
    return ints.map( i => parseInt(i));
  }
  return [];
}

/* harmony default export */ var PersonAutoCompleter2vue_type_script_lang_js_ = ({
  props: {
    value: { default: '' },
    multiple: { default: false }
  },

  components: {
    vSelect: vue_select_default.a
  },

  data(){
    return {
      options: [],
      lastUpdatedSearch: 0,
      delay: null,
      preloadedValue: false,
      selectedValue: null
    }
  },



  mounted() {
    if( this.value ){
      this.selectedValue = this.value;
      this.options.push({
        id: this.value,
        label: "Waiting for data"
      })
      this.value = null;
      this.searchPerson(null, 'id:' +this.selectedValue);
    } else {
      this.preloadedValue = true;
    }
  },

  computed: {
    values(){
      console.log('value:', this.value);
      if( !Array.isArray(this.value) ){
        return this.value ? intify(this.value) : [];
      } else {
        return this.value;
      }
    },
    selectable(){
      if( this.multiple ) return true;
      else {
        return !this.value || this.value.length == 0;
      }

    }
  },

  methods: {

    isSelectable(){
      return this.selectable;
    },

    setSelected(selected){
      this.value = selected;
      this.$emit('change', this.value);
      this.$emit('input', this.value);
    },

    handlerSearch(search, loading) {
      if (search.length) {
        loading(true);
        // Système de retardement Eco+
        let delayFunction = function(){
          this.searchPerson(loading, search, this);
          this.delay = null;
        }.bind(this);

        if( this.delay != null ) {
          clearTimeout(this.delay);
        }
        this.delay = setTimeout(delayFunction, 1000);
      }
    },

    searchPerson(loading, search, vm) {
      this.$http.get('/person?l=m&q=' + encodeURI(search)).then(
          ok => {
            // Cas 1 : Premier chargement
            if( this.preloadedValue == false ){
              console.log("Préchargement");
              this.preloadedValue = true;
              this.options = ok.data.datas;
              this.value = intify(this.selectedValue);
              console.log(this.options, typeof this.selectedValue);
            } else {
              console.log("Résultat de recherche");
              let newOptions = [];
              if( this.options && this.value ){
                console.log("On garde les anciennes données ?", this.values);
                this.options.forEach(item => {
                  console.log(item.id, item.label, this.values.indexOf(item.id));
                  if( this.values.indexOf(item.id) >= 0 ){
                    console.log("On garde ", item);
                    newOptions.push(item);
                  }
                });
              }

              console.log("on ajoute les résultats de la recherche")
              ok.data.datas.forEach(item => {
                newOptions.push(item)
              });

              console.log("On affecte", JSON.parse(JSON.stringify(newOptions)));
              this.options = newOptions;
            }

            if( loading )
              loading(false);
          },
          ko => {

          }
      )
    },
  }
});

// CONCATENATED MODULE: ./src/components/PersonAutoCompleter2.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_PersonAutoCompleter2vue_type_script_lang_js_ = (PersonAutoCompleter2vue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/PersonAutoCompleter2.vue





/* normalize component */

var PersonAutoCompleter2_component = normalizeComponent(
  components_PersonAutoCompleter2vue_type_script_lang_js_,
  PersonAutoCompleter2vue_type_template_id_719a0771_render,
  PersonAutoCompleter2vue_type_template_id_719a0771_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var PersonAutoCompleter2 = (PersonAutoCompleter2_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/OrganizationAutoCompleter.vue?vue&type=template&id=10ee20bd&
var OrganizationAutoCompletervue_type_template_id_10ee20bd_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('v-select',{attrs:{"placeholder":"Rechercher une structure","options":_vm.filteredOptions,"label":"label","reduce":function (item) { return item.id; }},on:{"search":_vm.handlerSearchOrganisation,"input":_vm.setSelected},scopedSlots:_vm._u([{key:"list-header",fn:function(){return [_c('li',{staticStyle:{"text-align":"center"}},[_c('label',{attrs:{"for":"display_closed"}},[_vm._v(" Afficher les structures fermées "),_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.displayClosed),expression:"displayClosed"}],attrs:{"type":"checkbox","id":"display_closed"},domProps:{"checked":Array.isArray(_vm.displayClosed)?_vm._i(_vm.displayClosed,null)>-1:(_vm.displayClosed)},on:{"change":function($event){var $$a=_vm.displayClosed,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.displayClosed=$$a.concat([$$v]))}else{$$i>-1&&(_vm.displayClosed=$$a.slice(0,$$i).concat($$a.slice($$i+1)))}}else{_vm.displayClosed=$$c}}}})])])]},proxy:true},{key:"option",fn:function(ref){
var id = ref.id;
var code = ref.code;
var shortname = ref.shortname;
var longname = ref.longname;
var city = ref.city;
var country = ref.country;
var label = ref.label;
var closed = ref.closed;
var email = ref.email;
var phone = ref.phone;
return [_c('div',{staticClass:"result-item",class:{'organization-closed': closed }},[_c('h4',{staticStyle:{"margin":"0"}},[(code)?_c('code',[_vm._v(_vm._s(code))]):_vm._e(),_c('span',[(shortname)?_c('strong',[_vm._v(_vm._s(shortname))]):_vm._e(),(longname)?_c('em',[_vm._v(_vm._s(longname))]):_vm._e()])]),(email || phone)?_c('div',{staticClass:"infos"},[(email)?_c('span',[_c('i',{staticClass:"icon-mail"}),_vm._v(" "+_vm._s(email))]):_vm._e(),(phone)?_c('span',[_c('i',{staticClass:"icon-phone-outline"}),_vm._v(" "+_vm._s(phone))]):_vm._e()]):_vm._e(),(country || city)?_c('div',{staticClass:"location"},[_c('i',{staticClass:"icon-location"}),(city)?_c('strong',[_vm._v(_vm._s(city))]):_vm._e(),(city)?_c('em',[_vm._v(_vm._s(country))]):_vm._e()]):_vm._e()])]}}]),model:{value:(_vm.value),callback:function ($$v) {_vm.value=$$v},expression:"value"}})}
var OrganizationAutoCompletervue_type_template_id_10ee20bd_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/OrganizationAutoCompleter.vue?vue&type=template&id=10ee20bd&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/OrganizationAutoCompleter.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var OrganizationAutoCompletervue_type_script_lang_js_ = ({

  props: {
    value: {default: null}
  },

  components: {
    vSelect: vue_select_default.a
  },

  data() {
    return {
      // Liste des structures
      options: [],

      lastUpdatedSearch: 0,
      delay: null,
      preloadedValue: false,
      selectedValue: null,
      displayClosed: false,
      last_search: "",
      loading_ref: null
    }
  },


  mounted() {
    // Détection d'un valeur initiale
    if (this.value) {
      this.selectedValue = this.value;
      this.options.push({
        id: this.value,
        label: "Waiting for data"
      })
      this.value = null;
      this.searchOrganization(null, 'id:' + this.selectedValue);
    } else {
      this.preloadedValue = true;
    }
  },

  computed: {
    filteredOptions(){
      let opts = [];
      if( this.displayClosed ){
        return this.options;
      } else {
        this.options.forEach(item => {
          if( !item.closed ){
            opts.push(item);
          }
        });
        return opts;
      }
    }
  },

  methods: {

    /**
     * Selection d'une option.
     *
     * @param selected
     */
    setSelected(selected) {
      this.value = selected;
      this.$emit('change', this.value);
      this.$emit('input', this.value);
    },

    /**
     * Déclenchement de la recherche (à la saisie).
     *
     * @param search
     * @param loading
     */
    handlerSearchOrganisation(search, loading) {
      this.last_search = search;
      this.loading_ref = loading;
      if (search.length) {
        loading(true);
        // Système de retardement Eco+
        let delayFunction = function () {
          this.searchOrganization(loading, search, this);
          this.delay = null;
        }.bind(this);
        if (this.delay != null) {
          clearTimeout(this.delay);
        }
        this.delay = setTimeout(delayFunction, 1000);
      }
    },

    /**
     * Recherche via l'API
     * @param loading
     * @param search
     * @param vm
     */
    searchOrganization(loading, search, vm) {
      let closeOpt = this.displayClosed ? '&active=' : '&active=ON'
      this.$http.get('/organization?l=m&q=' + encodeURI(search)).then(
          ok => {
            if (this.preloadedValue == false) {
              this.preloadedValue = true;
              this.value = this.selectedValue;
              this.options[0].label = ok.data.datas[0].label;
            } else {
              this.options = ok.data.datas;
            }
            if (loading)
              loading(false);
          },
          ko => {
            // TODO
          }
      )
    },
  }
});

// CONCATENATED MODULE: ./src/components/OrganizationAutoCompleter.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_OrganizationAutoCompletervue_type_script_lang_js_ = (OrganizationAutoCompletervue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/OrganizationAutoCompleter.vue





/* normalize component */

var OrganizationAutoCompleter_component = normalizeComponent(
  components_OrganizationAutoCompletervue_type_script_lang_js_,
  OrganizationAutoCompletervue_type_template_id_10ee20bd_render,
  OrganizationAutoCompletervue_type_template_id_10ee20bd_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var OrganizationAutoCompleter = (OrganizationAutoCompleter_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/ASFilterPerson.vue?vue&type=template&id=932659e8&
var ASFilterPersonvue_type_template_id_932659e8_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"criteria card",class:_vm.valueObj.type + _vm.error ? ' has-error' : ''},[_c('span',{staticClass:"filter-label"},[_c('i',{staticClass:"icon-user"}),_vm._v(" "+_vm._s(_vm.label)+" "),_c('input',{attrs:{"type":"hidden","name":"f[]"},domProps:{"value":_vm.valueObj.type +';' +_vm.valueObj.value1 +';' + _vm.valueObj.value2}})]),_c('span',[_c('person-auto-completer',{attrs:{"multiple":_vm.multiple},model:{value:(_vm.valueObj.value1),callback:function ($$v) {_vm.$set(_vm.valueObj, "value1", $$v)},expression:"valueObj.value1"}}),(_vm.error)?_c('div',{staticClass:"alert alert-danger"},[_vm._v(" "+_vm._s(_vm.error)+" ")]):_vm._e()],1),(_vm.multiple == false)?_c('span',[_vm._v(" ayant le rôle "),_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.valueObj.value2),expression:"valueObj.value2"}],on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.$set(_vm.valueObj, "value2", $event.target.multiple ? $$selectedVal : $$selectedVal[0])}}},[_c('option',{attrs:{"value":"-1"}},[_vm._v("N'importe quel role")]),_vm._l((_vm.roles_values),function(role,id){return _c('option',{domProps:{"value":id}},[_vm._v(_vm._s(role))])})],2)]):_vm._e(),_c('span',{staticClass:"nav-actions",on:{"click":function($event){$event.preventDefault();return _vm.$emit('delete')}}},[_c('i',{staticClass:"icon-trash"})])])}
var ASFilterPersonvue_type_template_id_932659e8_staticRenderFns = []


// CONCATENATED MODULE: ./src/searchfilters/ASFilterPerson.vue?vue&type=template&id=932659e8&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/ASFilterPerson.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var ASFilterPersonvue_type_script_lang_js_ = ({
  props: {
    value: {require: true},
    value1: {require: true},
    value2: {require: true},
    error: {require: false, default: ""},
    label: {default: "Impliquant la personne"},
    type: {default: "ap"},
    multiple: { default: false },
    initaleOptions: [],

    searched_values: [],
    roles_values: []
  },

  data() {
    return {
      valueObj: {
        type: this.type,
        value1: this.value1,
        value2: this.value2
      }
    }
  },

  components: {
    PersonAutoCompleter: PersonAutoCompleter2
  },

  computed: {},

  methods: {
    setValue(val) {
      let split = val.split(';');
      this.valueObj.type = split[0];
      this.valueObj.value1 = split[1];
      this.valueObj.value2 = split[2];
    }
  },

  mounted() {
    if (this.value) {
      this.setValue(this.value);
    }
  }
});

// CONCATENATED MODULE: ./src/searchfilters/ASFilterPerson.vue?vue&type=script&lang=js&
 /* harmony default export */ var searchfilters_ASFilterPersonvue_type_script_lang_js_ = (ASFilterPersonvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/searchfilters/ASFilterPerson.vue





/* normalize component */

var ASFilterPerson_component = normalizeComponent(
  searchfilters_ASFilterPersonvue_type_script_lang_js_,
  ASFilterPersonvue_type_template_id_932659e8_render,
  ASFilterPersonvue_type_template_id_932659e8_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ASFilterPerson = (ASFilterPerson_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/ASFilterOrganization.vue?vue&type=template&id=08ac8bcb&
var ASFilterOrganizationvue_type_template_id_08ac8bcb_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"criteria card",class:_vm.valueObj.type + _vm.error ? ' has-error' : ''},[_c('span',{staticClass:"filter-label"},[_c('i',{staticClass:"icon-building-filled"}),_vm._v(" "+_vm._s(_vm.label)+" "),_c('input',{attrs:{"type":"hidden","name":"f[]"},domProps:{"value":_vm.valueObj.type +';' +_vm.valueObj.value1 +';' + _vm.valueObj.value2}})]),_c('span',[_c('organization-auto-completer',{model:{value:(_vm.valueObj.value1),callback:function ($$v) {_vm.$set(_vm.valueObj, "value1", $$v)},expression:"valueObj.value1"}}),(_vm.error)?_c('div',{staticClass:"alert alert-danger"},[_vm._v(" "+_vm._s(_vm.error)+" ")]):_vm._e()],1),_c('span',[_vm._v(" ayant le rôle "),_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.valueObj.value2),expression:"valueObj.value2"}],on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.$set(_vm.valueObj, "value2", $event.target.multiple ? $$selectedVal : $$selectedVal[0])}}},[_c('option',{attrs:{"value":"-1"}},[_vm._v("N'importe quel role")]),_vm._l((_vm.roles_values),function(role,id){return _c('option',{domProps:{"value":id}},[_vm._v(_vm._s(role))])})],2)]),_c('span',{staticClass:"nav-actions",on:{"click":function($event){$event.preventDefault();return _vm.$emit('delete')}}},[_c('i',{staticClass:"icon-trash"})])])}
var ASFilterOrganizationvue_type_template_id_08ac8bcb_staticRenderFns = []


// CONCATENATED MODULE: ./src/searchfilters/ASFilterOrganization.vue?vue&type=template&id=08ac8bcb&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/ASFilterOrganization.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var ASFilterOrganizationvue_type_script_lang_js_ = ({
  props: {
    value: {require: true},
    value1: {require: true},
    value2: {require: true},
    error: {require: false, default: ""},
    label: {default: "Impliquant la structure"},
    type: {default: "ap"},
    initaleOptions: [],

    searched_values: [],
    roles_values: []
  },

  data() {
    return {
      valueObj: {
        type: this.type,
        value1: this.value1,
        value2: this.value2
      }
    }
  },

  components: {
    OrganizationAutoCompleter: OrganizationAutoCompleter
  },

  computed: {},

  methods: {
    setValue(val) {
      let split = val.split(';');
      this.valueObj.type = split[0];
      this.valueObj.value1 = split[1];
      this.valueObj.value2 = split[2];
    }
  },

  mounted() {
    if (this.value) {
      this.setValue(this.value);
    }
  }
});

// CONCATENATED MODULE: ./src/searchfilters/ASFilterOrganization.vue?vue&type=script&lang=js&
 /* harmony default export */ var searchfilters_ASFilterOrganizationvue_type_script_lang_js_ = (ASFilterOrganizationvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/searchfilters/ASFilterOrganization.vue





/* normalize component */

var ASFilterOrganization_component = normalizeComponent(
  searchfilters_ASFilterOrganizationvue_type_script_lang_js_,
  ASFilterOrganizationvue_type_template_id_08ac8bcb_render,
  ASFilterOrganizationvue_type_template_id_08ac8bcb_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ASFilterOrganization = (ASFilterOrganization_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/ASFilterSelect.vue?vue&type=template&id=21aeb43b&
var ASFilterSelectvue_type_template_id_21aeb43b_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"criteria card",class:_vm.valueObj.type + _vm.error ? ' has-error' : ''},[_c('span',{staticClass:"filter-label"},[_c('i',{class:_vm.icon}),_vm._v(" "+_vm._s(_vm.label)+" "),_c('input',{attrs:{"type":"text","name":"f[]"},domProps:{"value":_vm.valueObj.type +';' +_vm.valueObj.value1.join(',') +';' + _vm.valueObj.value2}})]),_c('span',[_c('v-select',{attrs:{"placeholder":_vm.placeholder,"multiple":"","options":_vm.chooses},model:{value:(_vm.valueObj.value1),callback:function ($$v) {_vm.$set(_vm.valueObj, "value1", $$v)},expression:"valueObj.value1"}}),(_vm.error)?_c('div',{staticClass:"alert alert-danger"},[_vm._v(" "+_vm._s(_vm.error)+" ")]):_vm._e()],1),_c('span',{staticClass:"nav-actions",on:{"click":function($event){$event.preventDefault();return _vm.$emit('delete')}}},[_c('i',{staticClass:"icon-trash"})])])}
var ASFilterSelectvue_type_template_id_21aeb43b_staticRenderFns = []


// CONCATENATED MODULE: ./src/searchfilters/ASFilterSelect.vue?vue&type=template&id=21aeb43b&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/ASFilterSelect.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var ASFilterSelectvue_type_script_lang_js_ = ({
  props: {
    value: {require: true},
    value1: {require: true},
    value2: {require: true},
    error: {require: false, default: ""},
    icon: { default: 'icon-tag' },
    label: {default: "Liste (label)"},
    placeholder: {default: ""},
    type: {default: "ap"},
    multiple: { default: false },
    options: { default: []}
  },

  data() {
    return {
      valueObj: {
        type: this.type,
        value1: this.value1.split(','),
        value2: this.value2
      }
    }
  },

  computed: {
    chooses(){
      let out = [];
      this.options.forEach(item => {
        if( item ){
          out.push(item)
        }
      })
      return out;
    }
  },

  components: {
    vSelect: vue_select_default.a
  },

  methods: {
    setValue(val) {
      let split = val.split(';');
      this.valueObj.type = split[0];
      this.valueObj.value1 = split[1];
      this.valueObj.value2 = split[2] ? split[2] : '';
    }
  },

  mounted() {
    if (this.value) {
      this.setValue(this.value);
    }
  }
});

// CONCATENATED MODULE: ./src/searchfilters/ASFilterSelect.vue?vue&type=script&lang=js&
 /* harmony default export */ var searchfilters_ASFilterSelectvue_type_script_lang_js_ = (ASFilterSelectvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/searchfilters/ASFilterSelect.vue





/* normalize component */

var ASFilterSelect_component = normalizeComponent(
  searchfilters_ASFilterSelectvue_type_script_lang_js_,
  ASFilterSelectvue_type_template_id_21aeb43b_render,
  ASFilterSelectvue_type_template_id_21aeb43b_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ASFilterSelect = (ASFilterSelect_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/SelectKeyValue.vue?vue&type=template&id=6bec54c2&
var SelectKeyValuevue_type_template_id_6bec54c2_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"criteria card",class:_vm.valueObj.type + _vm.error ? ' has-error' : ''},[_c('span',{staticClass:"filter-label"},[_c('i',{class:_vm.icon}),_vm._v(" "+_vm._s(_vm.label)+" "),_c('input',{attrs:{"type":"text","name":"f[]"},domProps:{"value":_vm.valueObj.type +';' +_vm.valueObj.value1.join(',') +';' + _vm.valueObj.value2}})]),_c('span',[_c('v-select',{attrs:{"placeholder":_vm.placeholder,"multiple":"","label":"label","multiple":"","reduce":function (item) { return item.id; },"options":_vm.chooses},model:{value:(_vm.valueObj.value1),callback:function ($$v) {_vm.$set(_vm.valueObj, "value1", $$v)},expression:"valueObj.value1"}}),(_vm.error)?_c('div',{staticClass:"alert alert-danger"},[_vm._v(" "+_vm._s(_vm.error)+" ")]):_vm._e()],1),_c('span',{staticClass:"nav-actions",on:{"click":function($event){$event.preventDefault();return _vm.$emit('delete')}}},[_c('i',{staticClass:"icon-trash"})])])}
var SelectKeyValuevue_type_template_id_6bec54c2_staticRenderFns = []


// CONCATENATED MODULE: ./src/searchfilters/SelectKeyValue.vue?vue&type=template&id=6bec54c2&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/SelectKeyValue.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var SelectKeyValuevue_type_script_lang_js_ = ({
  props: {
    value: {require: true},
    value1: {require: true},
    value2: {require: true},
    error: {require: false, default: ""},
    icon: { default: 'icon-tag' },
    label: {default: "Liste (label)"},
    placeholder: {default: ""},
    type: {default: "ap"},
    multiple: { default: false },
    options: { default: []}
  },

  data() {
    return {
      valueObj: {
        type: this.type,
        value1: this.value1.split(','),
        value2: this.value2
      }
    }
  },

  computed: {
    chooses(){
       console.log("build chooses : ", this.options);
      let out = [];
      Object.keys(this.options).forEach(key => {
        out.push({
          id: key,
          label: this.options[key]
        })
      });
      return out;
    }
  },

  components: {
    vSelect: vue_select_default.a
  },

  methods: {
    setValue(val) {
      let split = val.split(';');
      this.valueObj.type = split[0];
      this.valueObj.value1 = split[1];
      this.valueObj.value2 = split[2] ? split[2] : '';
    }
  },

  mounted() {
    if (this.value) {
      this.setValue(this.value);
    }
  }
});

// CONCATENATED MODULE: ./src/searchfilters/SelectKeyValue.vue?vue&type=script&lang=js&
 /* harmony default export */ var searchfilters_SelectKeyValuevue_type_script_lang_js_ = (SelectKeyValuevue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/searchfilters/SelectKeyValue.vue





/* normalize component */

var SelectKeyValue_component = normalizeComponent(
  searchfilters_SelectKeyValuevue_type_script_lang_js_,
  SelectKeyValuevue_type_template_id_6bec54c2_render,
  SelectKeyValuevue_type_template_id_6bec54c2_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var SelectKeyValue = (SelectKeyValue_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/SingleDateField.vue?vue&type=template&id=2a082584&
var SingleDateFieldvue_type_template_id_2a082584_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"criteria card",class:_vm.valueObj.type + _vm.error ? ' has-error' : ''},[_c('span',{staticClass:"filter-label"},[_c('i',{staticClass:"icon-calendar"}),_vm._v(" "+_vm._s(_vm.label)+" "),_c('input',{attrs:{"type":"hidden","name":"f[]"},domProps:{"value":_vm.valueObj.type +';' +_vm.valueObj.value1 +';' + _vm.valueObj.value2}})]),_c('span',{staticClass:"date-input"},[_c('span',[_vm._v(" Entre / à partir ")]),_c('span',[_c('datepicker',{attrs:{"moment":_vm.moment},model:{value:(_vm.valueObj.value1),callback:function ($$v) {_vm.$set(_vm.valueObj, "value1", $$v)},expression:"valueObj.value1"}})],1)]),_c('span',{staticClass:"date-input"},[_c('span',[_vm._v(" Jusqu'à ")]),_c('span',[_c('datepicker',{attrs:{"moment":_vm.moment,"format":'YYYY-mm-dd'},model:{value:(_vm.valueObj.value2),callback:function ($$v) {_vm.$set(_vm.valueObj, "value2", $$v)},expression:"valueObj.value2"}})],1)]),(_vm.error)?_c('div',{staticClass:"alert alert-danger"},[_vm._v(" "+_vm._s(_vm.error)+" ")]):_vm._e(),_c('span',{staticClass:"nav-actions",on:{"click":function($event){$event.preventDefault();return _vm.$emit('delete')}}},[_c('i',{staticClass:"icon-trash"})])])}
var SingleDateFieldvue_type_template_id_2a082584_staticRenderFns = []


// CONCATENATED MODULE: ./src/searchfilters/SingleDateField.vue?vue&type=template&id=2a082584&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b5c7376e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Datepicker.vue?vue&type=template&id=58610f52&
var Datepickervue_type_template_id_58610f52_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{on:{"mouseenter":_vm.handlerShow,"mouseleave":_vm.handlerHide}},[_c('div',{staticClass:"input-group"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.renderValue),expression:"renderValue"}],staticClass:"form-control",attrs:{"type":"text"},domProps:{"value":(_vm.renderValue)},on:{"input":function($event){if($event.target.composing){ return; }_vm.renderValue=$event.target.value}}}),_vm._m(0)]),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.picker),expression:"picker"}],staticClass:"datepicker-selector",on:{"mouseleave":_vm.handlerHide}},[_c('div',{staticClass:"datepicker-wrapper"},[_c('header',[_c('nav',[_c('span',{attrs:{"href":"#"},on:{"click":function($event){$event.stopPropagation();$event.preventDefault();return _vm.pickerPrevMonth.apply(null, arguments)}}},[_c('i',{staticClass:"icon-angle-left"})]),_c('strong',{staticClass:"heading"},[_c('span',{staticClass:"currentMonth",on:{"click":function($event){$event.stopPropagation();return _vm.handlerPickerMonth.apply(null, arguments)}}},[_vm._v(_vm._s(_vm.currentMonth))]),_c('span',{staticClass:"currentYear",on:{"click":function($event){$event.stopPropagation();return _vm.handlerPickerYear.apply(null, arguments)}}},[_vm._v(_vm._s(_vm.currentYear))])]),_c('span',{attrs:{"href":"#"},on:{"click":function($event){$event.stopPropagation();return _vm.pickerNextMonth.apply(null, arguments)}}},[_c('i',{staticClass:"icon-angle-right"})])]),(_vm.pickerMode == 'day')?_c('div',{staticClass:"day-labels week"},[_c('span',{staticClass:"week-label"},[_vm._v(" ")]),_vm._l((_vm.pickerData.dayslabels),function(d){return _c('span',{staticClass:"day-label"},[_vm._v(_vm._s(d))])})],2):_vm._e()]),(_vm.pickerMode == 'day')?_c('section',_vm._l((_vm.pickerData.weeks),function(week){return _c('div',{staticClass:"weeks"},[_c('span',{staticClass:"week"},[_c('span',{staticClass:"week-label"},[_vm._v(_vm._s(week.num))]),_vm._l((week.days),function(d){return _c('span',{staticClass:"week-day",class:{ active: d.active, disabled: !d.enabled },on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.changeDate(d.date)}}},[_vm._v(" "+_vm._s(d.day)+" ")])})],2)])}),0):_vm._e(),(_vm.pickerMode == 'month')?_c('section',{staticClass:"months"},_vm._l((_vm.months),function(month){return _c('span',{staticClass:"month",class:{ active: _vm.pickerMonthRef == month },on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerSelectMonth(month)}}},[_vm._v(" "+_vm._s(month)+" ")])}),0):_vm._e(),(_vm.pickerMode == 'year')?_c('section',{staticClass:"years"},[_c('span',{staticClass:"year",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();_vm.pickerYearRef -= 22}}},[_vm._v("<<")]),_vm._l((_vm.years),function(year){return _c('span',{staticClass:"year",class:{ active: _vm.pickerYearRef == year },on:{"click":function($event){$event.preventDefault();$event.stopPropagation();return _vm.handlerSelectYear(year)}}},[_vm._v(" "+_vm._s(year)+" ")])}),_c('span',{staticClass:"year",on:{"click":function($event){$event.preventDefault();$event.stopPropagation();_vm.pickerYearRef += 22}}},[_vm._v(">>")])],2):_vm._e()]),_c('div',{staticStyle:{"text-align":"center","cursor":"pointer"},on:{"click":_vm.handlerClear}},[_c('i',{staticClass:"icon-cancel-alt"}),_vm._v(" Supprimer la date ")])])])}
var Datepickervue_type_template_id_58610f52_staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"input-group-addon"},[_c('i',{staticClass:"icon-calendar"})])}]


// CONCATENATED MODULE: ./src/components/Datepicker.vue?vue&type=template&id=58610f52&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Datepicker.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var Datepickervue_type_script_lang_js_ = ({
    // Configuration
    model: {
        prop: 'value',
        event: 'input'
    },
    props: {
        moment: {
            required: true
        },

        // Valeur par défaut
        value: {
            default: null
        },

        // Deprecated
        i18n: {
            default: "fr"
        },

        limitFrom: {
            default: null
        },

        // Liste des jours utilisés dans l'UI
        daysShort: {
            default: () => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
        },

        // Liste des mois utilisés dans l'UI
        months: {
            default: () => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
        },

        // Format utilisé pour la valeur
        valueFormat: {
            default: 'YYYY-MM-DD'
        },

        // Format d'affichage
        displayFormat: {
            default: 'D MMMM YYYY'
        },

        // Format utilisé pour l'affichage
        format: {
            default: 'dddd D MMMM YYYY'
        }
    },

    data() {
        return {
            picker: false,
            pickerMode: 'day',
            pickerDayRef: this.moment().format(),
            pickerYearRef: this.moment().format('YYYY'),
            pickerMonthRef: this.moment().month(),
            realValue: this.value,
            manualChange: ""
        }
    },

    computed: {

        // Liste des années affichées dans le datepicker
        years() {
            let from = this.pickerYearRef - 11;
            let to = this.pickerYearRef + 11;
            let years = [];
            for (var i = from; i < to; i++) {
                years.push(i);
            }
            return years;
        },

        /**
         * Retourne la valeur active sous la forme d'un objet Moment.
         */
        mmValue() {
            if (this.realValue)
                return this.moment(this.realValue);
            else
                return this.moment();
        },

        /**
         * Retourne les données utilisées pour afficher le selecteur de date en mode JOUR du MOIS.
         */
        pickerData() {
            this.moment.locale(this.i18n);

            // Make list of days
            var days = this.daysShort;

            var realValueFormatted = this.moment(this.realValue).format(this.valueFormat);
            // Début du mois
            let weekStart = this.moment(this.pickerDayRef).startOf('month').startOf('isoWeek');
            let weekEnd = this.moment(this.pickerDayRef).endOf('month').startOf('isoWeek');

            let datas = []
            for (; weekStart.unix() <= weekEnd.unix();) {
                let week = {
                    num: weekStart.week(),
                    days: []
                }
                for (let d = 1; d <= 7; d++) {
                    let enabled = !this.limitFrom || (this.limitFrom && this.limitFrom < weekStart.format());
                    week.days.push({
                        enabled,
                        date: weekStart.format(),
                        active: weekStart.format(this.valueFormat) == realValueFormatted,
                        day: weekStart.format('D')
                    });
                    weekStart.add(1, 'day');
                }
                datas.push(week);
            }
            return {
                dayslabels: days,
                weeks: datas
            }
        },

        currentMonth() {
            return this.moment(this.pickerDayRef).format('MMMM');
        },

        /**
         * Retourne l'année courante.
         *
         * @returns {string}
         */
        currentYear() {
            return this.moment(this.pickerDayRef).format('YYYY');
        },

        /**
         * Rendu de la date en utilisant de format 'humain'
         * @returns {*}
         */
        renderDate() {
            if (this.realValue == null) {
                return ""
            }
            else {
                return this.moment(this.realValue).format(this.format)
            }
        },

        /**
         * Rendu de la valeur courante en utilisant le format.
         *
         * @returns {string}
         */
        renderValue: {
            get() {
                return !this.realValue ? '' : this.mmValue.format(this.displayFormat);
            },
            set( text ){
                console.log('convert ', text);
                try {
                    let v = this.moment(text, this.displayFormat);
                    if( v.isValid() )
                        this.changeDate(v.format(this.valueFormat));
                }catch (e) {
                    return;
                }
            }
        }
    },

    ////////////////////////////////////////////////////////////////////: METHODES
    methods: {
        /////////////////////////////////////////////////////////////////// HANDLERS
        handlerClear(){
            console.log(this.realValue);
            this.changeDate(null);
        },

        handlerInputChange(e){

            try {
                var v = this.moment(e.target.value);
                this.changeDate(v.format(this.valueFormat));
            } catch (e) {
                console.error("WTF DATE", e);
            }

        },

        /**
         * Déclenché quand un mois un selectionné.
         *
         * @param month
         */
        handlerSelectMonth(month) {
            let monthIndex = this.months.indexOf(month);
            this.pickerDayRef = this.moment(this.pickerDayRef).month(monthIndex).format();
            this.pickerMode = 'day';
        },

        /**
         * Déclanché quand une année est selectionnée.
         *
         * @param year
         */
        handlerSelectYear(year) {
            this.pickerDayRef = this.moment(this.pickerDayRef).year(year).format();
            this.pickerMode = 'day';
        },

        /**
         * Méthode à utiliser pour modifier la date saisie.
         */
        changeDate(date) {
            console.log("Modification de la date", date);
            this.picker = false;
            this.realValue = date ? this.moment(date).format(this.valueFormat) : '';
            this.$emit('input', this.realValue);
            this.$emit('change', this.realValue);
            this.handlerHide();
        },

        /**
         * Déclenché lors d'un défilement vers le mois suivant
         */
        pickerNextMonth() {
            this.pickerDayRef = this.moment(this.pickerDayRef).add(1, 'month').format();
        },

        /**
         * Déclenché lors d'un défilement vers le mois précédent
         */
        pickerPrevMonth() {
            this.pickerDayRef = this.moment(this.pickerDayRef).subtract(1, 'month').format();
        },

        /**
         * Affichage du selecteur de mois.
         */
        handlerPickerMonth(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            this.pickerMode = 'month';
        },

        /**
         * Affichage du selecteur d'année.
         */
        handlerPickerYear() {
            this.pickerMode = 'year';
        },

        handlerShow() {

            this.initPickerVar();
            this.picker = true;
            this.watchOut();
        },

        watchOut() {
            // console.log(document.querySelector('body'));
            //window.addEventListener('mouseup', this.handlerHide);
        },

        handlerHide(event) {
            //window.removeEventListener('mouseup', this.handlerHide);
            this.picker = false;
        },

        /**
         * Initialisation des données pour l'affichage du picker.
         */
        initPickerVar() {
            var ref = this.moment(this.pickerDayRef ? this.pickerDayRef : moment());
            this.pickerYearRef = ref.year();
            this.pickerMonthRef = ref.format('MMMM');
        }
    },

    created() {
        this.moment.locale(this.i18n);
        this.pickerDayRef = this.value ? this.value : this.moment().format();
        this.initPickerVar();
    }
});

// CONCATENATED MODULE: ./src/components/Datepicker.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_Datepickervue_type_script_lang_js_ = (Datepickervue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/Datepicker.vue





/* normalize component */

var Datepicker_component = normalizeComponent(
  components_Datepickervue_type_script_lang_js_,
  Datepickervue_type_template_id_58610f52_render,
  Datepickervue_type_template_id_58610f52_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var Datepicker = (Datepicker_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/searchfilters/SingleDateField.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var SingleDateFieldvue_type_script_lang_js_ = ({
  props: {
    moment: {require: true},
    value: {require: true},
    value1: {require: true},
    value2: {require: true},
    error: {require: false, default: ""},
    icon: { default: 'icon-tag' },
    label: {default: "Liste (label)"},
    placeholder: {default: ""},
    type: {default: "ap"},
    multiple: { default: false },
    options: { default: []}
  },

  data() {
    return {
      valueObj: {
        type: this.type,
        value1: this.value1,
        value2: this.value2
      }
    }
  },

  computed: {

  },

  components: {
    Datepicker: Datepicker
    //Datepicker
  },

  methods: {
    setValue(val) {
      console.log('setValue', val);
      if( val ){
        let split = val.split(';');
        this.valueObj.type = split[0];
        this.valueObj.value1 = split[1];
        this.valueObj.value2 = split[2] ? split[2] : '';
      }
    }
  },

  mounted() {
    console.log("Datepicker mounted");
    if (this.value) {
      this.setValue(this.value);
    }
  }
});

// CONCATENATED MODULE: ./src/searchfilters/SingleDateField.vue?vue&type=script&lang=js&
 /* harmony default export */ var searchfilters_SingleDateFieldvue_type_script_lang_js_ = (SingleDateFieldvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/searchfilters/SingleDateField.vue





/* normalize component */

var SingleDateField_component = normalizeComponent(
  searchfilters_SingleDateFieldvue_type_script_lang_js_,
  SingleDateFieldvue_type_template_id_2a082584_render,
  SingleDateFieldvue_type_template_id_2a082584_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var SingleDateField = (SingleDateField_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/ActivitySearchUi.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//







// Filtres






// import OscarGrowl from './OscarGrowl.vue';
// import OscarBus from './OscarBus.js';

/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :
cd front
Pour compiler en temps réél :
node node_modules/.bin/vue-cli-service build --name ActivitySearchUi --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/ActivitySearchUi.vue --watch
 */

//node node_modules/.bin/poi watch --format umd --moduleName  ActivitySearchUi --filename.css ActivitySearchUi.css --filename.js ActivitySearchUi.js --dist public/js/oscar/dist public/js/oscar/src/ActivitySearchUi.vue


/* harmony default export */ var ActivitySearchUivue_type_script_lang_js_ = ({
  props: {
    url: {required: true},
    first: {required: true, typ: Boolean},
    title: {default: "Activités de recherche"},
    sortters: {required: true},
    moment: {require: true },
    filters: {required: true},
    directions: {required: true},
    direction: { default: 'desc' },
    sorter: { default: 'hit' },
    status: {required: true},
    roles_person: {required: true},
    roles_organizations: {required: true},
    search: { require: false, default: "" },
    selected_status: { default: [] },
    options_pays: { default: [] },
    options_organization_types: { default: [] },
    used_filters: { require: false, default: []},
    used_status: { require: false, default: []},
    showCriteria: { default: true },
    selectedOrganization: null
  },

  components: {
    ASFilterOrganization: ASFilterOrganization,
    activity: ActivitySearchItem,
    SuperSelect: SuperSelect,
    PersonAutoCompleter: PersonAutoCompleter2,
    vSelect: vue_select_default.a,
    OrganizationAutoCompleter: OrganizationAutoCompleter,
    ASFilterPerson: ASFilterPerson,
    ASFilterSelect: ASFilterSelect,
    SelectKeyValue: SelectKeyValue,
    SingleDateField: SingleDateField
  },

  data() {
    return {
      loaderMsg: "",
      page: 1,
      totalPages: 0,
      totalResultQuery: 0,
      previous: null,
      activities: [],
      ui_vuecompact: false,
      filters_obj: [],
      selecting_filter: "",
      debug: ''
    }
  },

  computed: {
    displayedFilters(){
      return [];
    },

    urlSearch() {
      // Filtres
      let filters = [];
      this.filters_obj.forEach(f => {
        filters.push('f[]=' +f.type +';' +f.value1 +';' +f.value2);
      })


      return this.url
          + "?q=" + this.search
          + "&p=" + this.page
          + "&t=" + this.sorter
          + "&d=" + this.direction
          + '&st=' + this.used_status
          + '&' +filters.join('&');
    }
  },

  methods: {
    catchDebug(arg){
      this.debug = arg;
    },

    ///////////////////////////////////////////////////////////
    // Capture des interactions
    handlerSelectPerson(dataPerson, filter) {
      console.log(dataPerson, filter);
      filter.value1 = dataPerson.id;
      filter.value1displayed = dataPerson.displayname;
    },

    handlerDeleteFilter(filter) {
      this.filters_obj.splice(this.filters_obj.indexOf(filter), 1);
    },

    handlerSelectPersonRole( role, filter ){
      filter.value2 = role.target.value;
    },

    handlerSelectFilter() {
      this.addNewFilter(this.selecting_filter);
      this.selecting_filter = "";
    },

    handlerSubmit() {
      this.performSearch(this.search, 1, 'Recherche...')
    },

    addNewFilter(filterKey, value1 = "", value2 = "") {
      console.log('Ajout du filtre', filterKey);
      this.filters_obj.push({
        type: filterKey,
        value1: value1,
        value2: value2
      })
    },

    updateSelected(evt) {
      console.log('evt', evt);
    },

    /**
     * Retourne le filtre (objet) en fonction de l'entrée.
     *
     * @param str
     * @return {null|*}
     */
    getFilterByStr( str ){
      for( let i = 0; i<this.filters_obj.length; i++ ){
        if( this.filters_obj[i].input == str ){
          return this.filters_obj[i];
        }
      }
      return null;
    },

    /**
     * Déclenche la recherche.
     *
     * @param what
     * @param page
     * @param msg
     */
    performSearch(what, page, msg) {
      this.loaderMsg = msg;
      this.search = what === null ? '' : what;
      this.$http.get(this.urlSearch).then(
          (ok) => {
            if (ok.body.page == 1) {
              this.activities = [];
            }
            this.activities = this.activities.concat(ok.body.datas.content);
            this.totalResultQuery = ok.body.result_total;
            this.totalPages = ok.body.totalPages;
            this.page = ok.body.page;

            ok.body.filters_infos.forEach( info => {
              if( info.error ){
                let f = this.getFilterByStr(info.input);
                if( f ){
                  f.error = info.error;
                }
              }
            });
          },
          (ko) => {
            console.log(ko);
            this.error = "Impossible de charger le résultat de la recherche !";
            this.activities = [];
            this.totalResultQuery = 0;
            this.totalPages = 0;
            this.page = 0;
            if (ko.status == 403) {
              this.error += " Vous avez probablement été déconnecté de l'application"
            }
          }
      ).then(foo => {
        this.loaderMsg = "";
      });
    },

    loadNextPage() {
      if (this.page < this.totalPages) {
        this.page++;
        this.performSearch(this.search, this.page, 'Chargement de la page ' + this.page + "/" + this.totalPages);
      }
    }
  },

  mounted() {

    let params = new URLSearchParams(window.location.search);

    // Filtres
    this.used_filters.forEach(filterStr => {
      console.log("Traitement du filtre ", filterStr);
      let spt = filterStr.split(';');
      let obj = {
        type: spt[0],
        value1: spt[1],
        value2: spt[2],
        input: filterStr
      };
      this.filters_obj.push(obj);
    });

    this.handlerSubmit();

    window.onscroll = () => {
      let bottomOfWindow = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;
      if (bottomOfWindow) {
        this.loadNextPage();
      }
    };


  }
});

// CONCATENATED MODULE: ./src/ActivitySearchUi.vue?vue&type=script&lang=js&
 /* harmony default export */ var src_ActivitySearchUivue_type_script_lang_js_ = (ActivitySearchUivue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/ActivitySearchUi.vue





/* normalize component */

var ActivitySearchUi_component = normalizeComponent(
  src_ActivitySearchUivue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ActivitySearchUi = (ActivitySearchUi_component.exports);
// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib.js


/* harmony default export */ var entry_lib = __webpack_exports__["default"] = (ActivitySearchUi);



/***/ })

/******/ })["default"];
});
//# sourceMappingURL=ActivitySearchUi.umd.js.map
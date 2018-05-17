(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["polyfill"] = factory();
	else
		root["polyfill"] = factory();
})(typeof self !== 'undefined' ? self : this, function() {
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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
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
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/*!***********************************************!*\
  !*** multi ./public/js/oscar/src/Polyfill.js ***!
  \***********************************************/
/*! dynamic exports provided */
/*! all exports used */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /home/jacksay/Projects/Unicaen/oscar/public/js/oscar/src/Polyfill.js */1);


/***/ }),
/* 1 */
/*!*****************************************!*\
  !*** ./public/js/oscar/src/Polyfill.js ***!
  \*****************************************/
/*! dynamic exports provided */
/*! all exports used */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\n//\nconsole.log(\"Load POLYFILL UNICAEN\");\n\nArray.prototype.testTata = function () {\n    console.log('TEST TATA : ', this);\n};//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi9ob21lL2phY2tzYXkvUHJvamVjdHMvVW5pY2Flbi9vc2Nhci9wdWJsaWMvanMvb3NjYXIvc3JjL1BvbHlmaWxsLmpzIl0sIm5hbWVzIjpbImNvbnNvbGUiLCJsb2ciLCJBcnJheSIsInByb3RvdHlwZSIsInRlc3RUYXRhIl0sIm1hcHBpbmdzIjoiOztBQUFBO0FBQ0FBLFFBQVFDLEdBQVIsQ0FBWSx1QkFBWjs7QUFHQUMsTUFBTUMsU0FBTixDQUFnQkMsUUFBaEIsR0FBMkIsWUFBVTtBQUNqQ0osWUFBUUMsR0FBUixDQUFZLGNBQVosRUFBNEIsSUFBNUI7QUFDSCxDQUZEIiwiZmlsZSI6IjEuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvL1xuY29uc29sZS5sb2coXCJMb2FkIFBPTFlGSUxMIFVOSUNBRU5cIik7XG5cblxuQXJyYXkucHJvdG90eXBlLnRlc3RUYXRhID0gZnVuY3Rpb24oKXtcbiAgICBjb25zb2xlLmxvZygnVEVTVCBUQVRBIDogJywgdGhpcyk7XG59XG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vcHVibGljL2pzL29zY2FyL3NyYy9Qb2x5ZmlsbC5qcyJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///1\n");

/***/ })
/******/ ]);
});
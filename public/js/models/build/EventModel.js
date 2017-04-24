;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory();
  } else {
    root.EventModel = factory();
  }
}(this, function() {
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _moment = require("moment");

var _moment2 = _interopRequireDefault(_moment);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } } /**
                                                                                                                                                           * Created by jacksay on 17-02-27.
                                                                                                                                                           */


var EventModel = function EventModel() {
    _classCallCheck(this, EventModel);

    this.toto = "tata";
};

exports.default = EventModel;
return EventModel;
}));

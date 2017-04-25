;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory();
  } else {
    root.EventModel = factory();
  }
}(this, function() {
define(["exports", "moment"], function (exports, _moment) {
    "use strict";

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _moment2 = _interopRequireDefault(_moment);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var EventModel = function EventModel() {
        _classCallCheck(this, EventModel);

        this.toto = "tata";
    };

    exports.default = EventModel;
});
return EventModel;
}));

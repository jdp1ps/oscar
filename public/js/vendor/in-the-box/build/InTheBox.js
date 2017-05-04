;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory();
  } else {
    root.InTheBox = factory();
  }
}(this, function() {
"use strict";

var InTheBox = {
  version: "0.0.1",
  authors: ["Stéphane Bouvry"],

  Color: {
    /**
     * Convert hexa color to rgba color
     */
    hexaToRgba: function hexaToRgba(hexa) {
      var r = hexa.substr(1, 2),
          g = hexa.substr(3, 2),
          b = hexa.substr(5, 2),
          a = hexa.substr(7, 2),
          rgba = void 0;
      r = parseInt(r, 16);
      g = parseInt(g, 16);
      b = parseInt(b, 16);
      a = a ? parseInt(a, 16) / 255 : 1;

      return "rgba(" + r + "," + g + "," + b + "," + a + ")";
    },


    /**
     * Generate random colors.
     */
    generateColor: function generateColor(nbr) {
      var opacity = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
      var format = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'hexa';

      var total = 16777215;

      var pas = Math.floor(total / nbr),
          colors = [],
          pattern = 'FFFFFF';

      if (format == 'rgba') {
        format = InTheBox.Color.hexaToRgba;
      } else {
        format = function format(v) {
          return v;
        };
      }

      for (var i = 0; i < nbr; i++) {
        var intval = Math.round(Math.random() * pas + pas * i),
            hex = intval.toString(16);
        hex = '#' + pattern.substring(0, pattern.length - hex.length) + hex;
        colors.push(format(hex + opacity));
      }

      return colors;
    }
  }
};
return InTheBox;
}));

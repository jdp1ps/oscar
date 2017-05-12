define(['exports', 'vue', 'vue-resource', 'LocalDB'], function (exports, _vue, _vueResource, _LocalDB) {
  'use strict';

  Object.defineProperty(exports, "__esModule", {
    value: true
  });

  var _vue2 = _interopRequireDefault(_vue);

  var _vueResource2 = _interopRequireDefault(_vueResource);

  var _LocalDB2 = _interopRequireDefault(_LocalDB);

  function _interopRequireDefault(obj) {
    return obj && obj.__esModule ? obj : {
      default: obj
    };
  }

  _vue2.default.use(_vueResource2.default); /**
                                             * Created by jacksay on 17-05-11.
                                             */


  _vue2.default.http.options.emulateJSON = true;
  _vue2.default.http.options.emulateHTTP = true;

  var ConnectorConfig = {
    template: '<section></section>'
  };

  exports.default = ConnectorConfig;
});
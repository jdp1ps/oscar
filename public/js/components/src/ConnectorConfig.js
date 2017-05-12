/**
 * Created by jacksay on 17-05-11.
 */
import Vue from 'vue';
import VueResource from 'vue-resource';
import LocalDB from 'LocalDB';

Vue.use(VueResource);

Vue.http.options.emulateJSON = true;
Vue.http.options.emulateHTTP = true;

var ConnectorConfig = {
    template: `<section></section>`
};

export default ConnectorConfig;
define(["exports", "vue", "vue-resource", "mm"], function (exports, _vue, _vueResource, _mm) {
    "use strict";

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _vue2 = _interopRequireDefault(_vue);

    var _vueResource2 = _interopRequireDefault(_vueResource);

    var _mm2 = _interopRequireDefault(_mm);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    _vue2.default.use(_vueResource2.default); /**
                                               * Created by jacksay on 17-01-12.
                                               */


    var FormulaireVersement = {
        props: ['formData', 'bstatus', 'bcurrencies'],
        data: function data() {
            return {
                status: this.bstatus,
                currencies: this.bcurrencies
            };
        },

        template: "\n        <div>\n        <div class=\"form-group\">\n            <label for=\"\">Montant</label>\n            <input type=\"text\" v-model=\"formData.amount\" class=\"form-control\">\n        </div>\n        <div class=\"form-group\">\n            <label for=\"\">Type</label>\n            <select v-model=\"formData.status\" class=\"form-control\">\n                <option :value=\"k\" v-for=\"(st, k) in status\">{{ st }}</option>\n            </select>\n        </div>\n        <div class=\"form-group\">\n            <label for=\"\">Devise</label>\n            <select v-model=\"formData.currency.id\" class=\"form-control\">\n                <option :value=\"c.id\" v-for=\"c in currencies\">{{ c.label }}</option>\n            </select>\n        </div>\n        </div>\n    "
    };

    var versements = _vue2.default.extend({
        http: {
            emulateHTTP: true,
            emulateJSON: true
        },
        components: {
            'formulaire-versement': FormulaireVersement
        },
        template: "\n<div class=\"versements\">\n    <h1>Versements</h1>\n    <section>\n        <formulaire-versement\n            :bstatus=\"status\"\n            :bcurrencies=\"currencies\"\n            :form-data=\"formData\"\n            v-if=\"formData\"></formulaire-versement>\n        <article v-for=\"versement in versements\" class=\"card xs payment\" :class=\"'status-' +versement.status +' ' + (versement.late ? 'past' : '')\">\n            <div class=\"heading\">\n                <strong class=\"amount\">{{ versement.amount | currency }}{{ versement.currency.symbol }}</strong>\n                <div class=\"date\">\n                    <i class=\"icon-calendar\"></i>\n                    \n                    <template v-if=\"versement.status == 1\">\n                    <time :datetime=\"versement.datePredicted\" class=\"date\" v-if=\"versement.datePredicted\">\n                        {{ versement.datePredicted | date}}\n                    </time>\n                    <strong class=\"text-danger\">\n                        Pas de date pr\xE9vue !\n                    </strong>\n                    </template>\n                    <time :datetime=\"versement.datePayment\" class=\"date\" v-else>\n                        {{ versement.datePayment | date}}\n                    </time>\n                <br>\n                 N\xB0 <strong>{{ versement.codeTransaction }}</strong>\n                </div>\n                <nav>\n                    <a href=\"#\" class=\"btn-delete\" @click.prevent=\"remove(versement)\">\n                        <i class=\"icon-trash\"></i>\n                    </a>\n                    <a href=\"#\" class=\"btn-edit\" @click.prevent=\"update(versement)\">\n                        <i class=\"icon-pencil\"></i>\n                    </a>\n                </nav>\n            </div>\n            <p class=\"comment\">{{ versement.comment }}</p>\n        </article>\n        <article class=\"payment total\">\n            <div class=\"heading\">\n                <strong class=\"amount\">{{ total.effectif | currency }}\u20AC</strong>\n                <span class=\"date\">\n                    <span class=\"curreny\">\n                        <span class=\"value\">{{ total.prevu | currency }}</span>\n                        <span class=\"curreny\">\u20AC</span>\n                    </span>\n                </span>\n            </div>\n        </article>\n    </section>\n    <pre>{{ $data.status }}</pre>\n</div>",
        filters: {
            date: function date(v) {
                if (!v || !v.date) return "Pas de date";else return (0, _mm2.default)(v.date).format('dddd Do MMMM YYYY');
            },
            currency: function currency(amount) {
                if (amount == undefined) return "error";
                var split,
                    unit,
                    fraction,
                    i,
                    j,
                    formattedUnits,
                    value,
                    decimal = 2,
                    decimalSeparator = ",",
                    hundredSeparator = " ";

                // Format decimal
                value = amount.toFixed(decimal).toString();

                // split
                split = value.split('.');
                unit = split[0];
                fraction = split[1];
                formattedUnits = "";

                if (unit.length > 3) {
                    for (i = unit.length - 1, j = 0; i >= 0; i--, j++) {
                        if (j % 3 === 0 && i < unit.length - 1) {
                            formattedUnits = hundredSeparator + formattedUnits;
                        }
                        formattedUnits = unit[i] + formattedUnits;
                    }
                } else {
                    formattedUnits = unit;
                }
                return formattedUnits + decimalSeparator + fraction;
            }
        },
        data: function data() {
            return {
                versements: [],
                status: [],
                currencies: [],
                formData: null
            };
        },
        created: function created() {
            this.fetch();
        },

        methods: {
            fetch: function fetch() {
                var _this = this;

                this.$http.get(this.url).then(function (res) {
                    _this.versements = res.body.payments;
                    _this.status = res.body.payments_status;
                    _this.currencies = res.body.currencies;
                }, function (err) {
                    console.error(err);
                    _this.$emit('notifications', 'error', 'Impossible de charger les versements', err);
                });
                console.log('fetch', this.url, this);
            },
            remove: function remove(versement) {
                var _this2 = this;

                console.log("Suppression de ", versement);
                this.$http.delete(this.url + '/' + versement.id).then(function (res) {
                    _this2.versements.splice(_this2.versements.indexOf(versement), 1);
                }, function (err) {
                    console.error(err);
                    _this2.$emit('notifications', 'error', 'Impossible de supprimer le versement', err);
                });
            },
            update: function update(versement) {
                this.formData = JSON.parse(JSON.stringify(versement));
                /*
                this.$http.put(this.url+'/'+versement.id, versement).then(
                    (res)=> {
                //                    this.versements.splice(this.versements.indexOf(versement), 1);
                    },
                    (err)=> {
                        console.error(err)
                        this.$emit('notifications', 'error', 'Impossible de supprimer le versement', err);
                    }
                );*/
            }
        },
        computed: {
            total: function total() {
                var total = {
                    prevu: 0.0,
                    effectif: 0.0
                };
                this.versements.forEach(function (v) {
                    var montant = v.amount * v.currency.rate;
                    if (v.status != 1) total.effectif += montant;
                    total.prevu += montant;
                });
                return total;
            }
        }
    });

    exports.default = versements;
});
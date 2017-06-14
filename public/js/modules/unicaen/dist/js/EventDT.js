;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['moment'], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory(require('moment'));
  } else {
    root.EventDT = factory(root.moment);
  }
}(this, function(moment) {
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

//import moment from "moment";

moment.locale('fr');

var EventDT = function () {
  function EventDT(id, label, start, end) {
    var description = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : "";
    var actions = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : {};
    var status = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : 'draft';
    var owner = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : "";
    var owner_id = arguments.length > 8 && arguments[8] !== undefined ? arguments[8] : null;
    var rejectedSciComment = arguments.length > 9 && arguments[9] !== undefined ? arguments[9] : "";
    var rejectedSciAt = arguments.length > 10 && arguments[10] !== undefined ? arguments[10] : null;
    var rejectedAdminComment = arguments.length > 11 && arguments[11] !== undefined ? arguments[11] : "";
    var rejectedAdminAt = arguments.length > 12 && arguments[12] !== undefined ? arguments[12] : null;
    var validatedSciAt = arguments.length > 13 && arguments[13] !== undefined ? arguments[13] : null;
    var validatedSciBy = arguments.length > 14 && arguments[14] !== undefined ? arguments[14] : null;
    var validatedAdminAt = arguments.length > 15 && arguments[15] !== undefined ? arguments[15] : null;
    var validatedAdminBy = arguments.length > 16 && arguments[16] !== undefined ? arguments[16] : null;

    _classCallCheck(this, EventDT);

    this.id = id;

    // From ICS format
    this.uid = EventDT.UID++;

    // ICS : summary
    this.label = label;

    // ICS : description
    this.description = description;

    this.owner = owner;
    this.owner_id = owner_id;
    this.intersect = 0;
    this.intersectIndex = 0;

    this.rejectedSciComment = rejectedSciComment;
    this.rejectedSciAt = rejectedSciAt;
    this.rejectedAdminComment = rejectedAdminComment;
    this.rejectedAdminAt = rejectedAdminAt;

    this.validatedSciAt = validatedSciAt;
    this.validatedSciBy = validatedSciBy;
    this.validatedAdminAt = validatedAdminAt;
    this.validatedAdminBy = validatedAdminBy;

    // OSCAR
    this.editable = actions.editable || false;
    this.deletable = actions.deletable || false;
    this.validable = actions.validable || false;
    this.sendable = actions.sendable || false;
    this.validableSci = actions.validableSci || false;
    this.validableAdm = actions.validableAdm || false;

    // Status
    // - DRAFT, SEND, VALID, REJECT
    this.status = status;

    this.start = start;
    this.end = end;
  }

  /**
   * Retourne un objet moment pour la date de début.
   */


  _createClass(EventDT, [{
    key: 'inWeek',


    /**
     * Test si l'événement est présent dans la semaine.
     * @return boolean
     */
    value: function inWeek(year, week) {
      var mmStart = this.mmStart.unix(),
          mmEnd = this.mmEnd.unix();

      // Récupération de la plage de la semaine
      var weekStart = moment().year(year).week(week).startOf('week'),
          plageStart = weekStart.unix(),
          plageFin = weekStart.endOf('week').unix();

      if (mmStart > plageFin || mmEnd < plageStart) return false;

      return mmStart < plageFin || mmEnd > plageStart;
    }
  }, {
    key: 'overlap',
    value: function overlap(otherEvent) {
      var startU1 = this.mmStart.unix(),
          endU1 = this.mmEnd.unix(),
          startU2 = otherEvent.mmStart.unix(),
          endU2 = otherEvent.mmEnd.unix();
      return startU1 < endU2 && startU2 < endU1;
    }
  }, {
    key: 'isBefore',
    value: function isBefore(eventDT) {
      if (this.mmStart < eventDT.mmStart) {
        return true;
      }
      return false;
    }
  }, {
    key: 'sync',
    value: function sync(data) {
      console.log("Synchronisation de l'événement", this.id, "avec", data);

      if (data.id) this.id = data.id;
      if (data.label) this.label = data.label;
      if (data.description) this.description = data.description;
      if (data.start) this.start = data.start;
      if (data.end) this.end = data.end;
      if (data.status) this.status = data.status;
      if (data.rejectedComment) this.rejectedComment = data.rejectedComment;
      if (data.rejectedCommentAt) this.rejectedCommentAt = data.rejectedCommentAt;
      if (data.rejectedAdminComment) this.rejectedAdminComment = data.rejectedAdminComment;
      if (data.rejectedAdminCommentAt) this.rejectedAdminCommentAt = data.rejectedAdminCommentAt;

      if (data.credentials) {
        this.editable = data.credentials.editable;
        this.deletable = data.credentials.deletable;
        this.validable = false;
        this.validableSci = data.credentials.validableSci;
        this.validableAdm = data.credentials.validableAdm;
        this.sendable = data.credentials.sendable;
      }
    }
  }, {
    key: 'mmStart',
    get: function get() {
      return moment(this.start);
    }

    /**
     * Retourne un objet moment pour la date de fin.
     */

  }, {
    key: 'mmEnd',
    get: function get() {
      return moment(this.end);
    }

    /**
     * Retourne la durée de l'événement en minutes.
     * @returns {number}
     */

  }, {
    key: 'durationMinutes',
    get: function get() {
      return (this.mmEnd.unix() - this.mmStart.unix()) / 60;
    }

    /**
     * Retourne la durée de l'événement en heure.
     * @returns {number}
     */

  }, {
    key: 'duration',
    get: function get() {
      return this.durationMinutes / 60;
    }
  }, {
    key: 'dayTime',
    get: function get() {
      return "de " + this.mmStart.format('hh:mm') + " à " + this.mmEnd.format('hh:mm') + ", le " + this.mmStart.format('dddd D MMMM YYYY');
    }
  }], [{
    key: 'first',
    value: function first(events) {
      var first = null;
      events.forEach(function (e1) {
        if (first == null) {
          first = e1;
        } else {
          if (e1.isBefore(first)) {
            first = e1;
          }
        }
      });
      return first;
    }
  }, {
    key: 'sortByStart',
    value: function sortByStart(events) {
      var sorted = events.sort(function (e1, e2) {
        if (e1.mmStart < e2.mmStart) return -1;else if (e1.mmStart > e2.mmStart) return 1;

        return 0;
      });
      return sorted;
    }
  }]);

  return EventDT;
}();

EventDT.UID = 1;
return EventDT;
}));

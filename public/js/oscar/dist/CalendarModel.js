(function(t,e){"object"==typeof exports&&"object"==typeof module?module.exports=e():"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?exports.CalendarModel=e():t.CalendarModel=e()})("undefined"!=typeof self?self:this,function(){return function(t){function e(n){if(i[n])return i[n].exports;var a=i[n]={i:n,l:!1,exports:{}};return t[n].call(a.exports,a,a.exports,e),a.l=!0,a.exports}var i={};return e.m=t,e.c=i,e.d=function(t,i,n){e.o(t,i)||Object.defineProperty(t,i,{configurable:!1,enumerable:!0,get:n})},e.n=function(t){var i=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(i,"a",i),i},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/",e(e.s=0)}([function(t,e,i){t.exports=i(1)},function(t,e,i){"use strict";function n(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(e,"__esModule",{value:!0});var a,r=function(){function t(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}return function(e,i,n){return i&&t(e.prototype,i),n&&t(e,n),e}}(),d=function(){function t(){n(this,t),this.events=[]}return r(t,null,[{key:"getInstance",value:function(){return a||(console.log("Create single instance"),a=new t),a}}]),t}();d.ValidationEvent=function(){function t(e){n(this,t),console.log(e&&e.instanceOf(d.EventDT)),this.label="Validation test",this.validateState=0,this.validatedBy=null,this.validateDate=null,this.validatedMessage=""}return r(t,[{key:"isValid",value:function(){return 1==this.validateState}},{key:"isReject",value:function(){return 0==this.validateState}},{key:"isValidable",value:function(){return this.validateState>-1}}]),t}(),d.ValidationEventChain=function(){function t(){n(this,t),this.validations=[]}return r(t,[{key:"addValidation",value:function(t){this.validations.push(t)}},{key:"isValid",value:function(){for(var t=0;t<this.validations.length;t++)if(!this.validations[t].isValid())return!1;return!0}},{key:"isReject",value:function(){for(var t=0;t<this.validations.length;t++)if(this.validations[t].isReject())return!0;return!1}}]),t}(),d.EventDT=function(){function t(e){n(this,t),this.sync(e)}return r(t,[{key:"inWeek",value:function(t,e){var i=this.mmStart.unix(),n=this.mmEnd.unix(),a=moment().year(t).week(e).startOf("week"),r=a.unix(),d=a.endOf("week").unix();return!(i>d||n<r)&&(i<d||n>r)}},{key:"overlap",value:function(t){var e=this.mmStart.unix(),i=this.mmEnd.unix(),n=t.mmStart.unix();return e<t.mmEnd.unix()&&n<i}},{key:"isBefore",value:function(t){return this.mmStart<t.mmStart}},{key:"sync",value:function(t){this.id=t.id,this.label=t.label,this.description="undefined"==t.description?"":t.description,this.start=t.start,this.end=t.end,this.icsfileuid=t.icsfileuid,this.icsfilename=t.icsfilename,this.icsfiledateadded=t.icsfiledateadded,this.icsuid=t.icsuid,this.status=t.status,this.uid||(this.uid=EventDT.UID++),this.workpackageId=t.workpackage_id||null,this.workpackageCode=t.workpackage_code||null,this.workpackageLabel=t.workpackage_label||null,this.activityId=t.activity_id||null,this.activityLabel=t.activity_label||null,this.owner=t.owner,this.owner_id=t.owner_id,this.decaleY=0,this.rejectedComment=t.rejectedComment,this.rejectedCommentAt=t.rejectedCommentAt,this.rejectedAdminComment=t.rejectedAdminComment,this.rejectedAdminCommentAt=t.rejectedAdminCommentAt,this.rejectedSciComment=t.rejectedSciComment,this.rejectedSciAt=t.rejectedSciAt,this.rejectedSciBy=t.rejectedSciBy,this.rejectedAdminComment=t.rejectedAdminComment,this.rejectedAdminAt=t.rejectedAdminAt,this.rejectedAdminBy=t.rejectedAdminBy,this.validatedSciAt=t.validatedSciAt,this.validatedSciBy=t.validatedSciBy,this.validatedAdminAt=t.validatedAdminAt,this.validatedAdminBy=t.validatedAdminBy,this.editable=!1,this.deletable=!1,this.validable=!1,this.validableAdm=!1,this.validableSci=!1,this.sendable=!1,t.credentials&&(this.editable=t.credentials.editable,this.deletable=t.credentials.deletable,this.validable=!1,this.validableAdm=t.credentials.validableAdm,this.validableSci=t.credentials.validableSci,this.sendable=t.credentials.sendable)}},{key:"isLocked",get:function(){return!(this.sendable||this.validableAdm||this.validableSci||this.editable||this.deletable)}},{key:"isSend",get:function(){return"send"==this.status}},{key:"isInfo",get:function(){return"info"==this.status}},{key:"isValidSci",get:function(){return null!=this.validatedSciAt}},{key:"isValidAdmin",get:function(){return null!=this.validatedAdminAt}},{key:"isRejecteSci",get:function(){return null!=this.rejectedSciAt}},{key:"isRejecteAdmin",get:function(){return null!=this.rejectedAdminAt}},{key:"isValid",get:function(){return this.isValidAdmin&&this.isValidSci}},{key:"isReject",get:function(){return this.isRejecteAdmin||this.isRejecteSci}},{key:"mmStart",get:function(){return moment(this.start)}},{key:"mmEnd",get:function(){return moment(this.end)}},{key:"durationMinutes",get:function(){return(this.mmEnd.unix()-this.mmStart.unix())/60}},{key:"duration",get:function(){return this.durationMinutes/60}},{key:"dayTime",get:function(){return"de "+this.mmStart.format("hh:mm")+" à "+this.mmEnd.format("hh:mm")+", le "+this.mmStart.format("dddd D MMMM YYYY")}}],[{key:"first",value:function(t){var e=null;return t.forEach(function(t){null==e?e=t:t.isBefore(e)&&(e=t)}),e}},{key:"sortByStart",value:function(t){return t.sort(function(t,e){return t.mmStart<e.mmStart?-1:t.mmStart>e.mmStart?1:0})}}]),t}(),d.EventDT.UID=1,e.default=d}])});
//# sourceMappingURL=CalendarModel.js.map
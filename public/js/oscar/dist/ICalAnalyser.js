(function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.icalanalyser=t():e.icalanalyser=t()})("undefined"!=typeof self?self:this,function(){return function(e){function t(i){if(n[i])return n[i].exports;var a=n[i]={i:i,l:!1,exports:{}};return e[i].call(a.exports,a,a.exports,t),a.l=!0,a.exports}var n={};return t.m=e,t.c=n,t.d=function(e,n,i){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:i})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="/",t(t.s=0)}([function(e,t,n){e.exports=n(1)},function(e,t,n){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var a=function(){function e(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,n,i){return n&&e(t.prototype,n),i&&e(t,i),t}}(),r=function(){function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:new Date,n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:[];if(i(this,e),t instanceof String&&(t=new Date(t)),!(t instanceof Date))throw"Bad usage, date or string required.";this.ending="string"==typeof t?new Date(t):t,this.daysString=["SU","MO","TU","WE","TH","FR","SA"],this.dailyStrategy=n,this.summaries=[],this.debugMode=!1}return a(e,[{key:"getDailyStrategy",value:function(){return this.dailyStrategy}}]),a(e,[{key:"debug",value:function(){!0===this.debugMode&&console.log.apply(this,arguments)}},{key:"generateItem",value:function(e){var t=moment(e.start),n=moment(e.end);if(t.date()!=n.date()){var i=JSON.parse(JSON.stringify(e)),a=JSON.parse(JSON.stringify(e)),r=t.endOf("day");i.end=r.toISOString();var o=r.add(1,"day").startOf("day");return a.start=o.toISOString(),a.start==a.end?this.generateItem(i):[].concat(this.generateItem(i)).concat(this.generateItem(a))}return[{uid:e.uid,icsuid:e.icsuid,icsfileuid:e.icsfileuid,icsfilename:e.icsfilename,icsfiledateaddedd:e.icsfiledateaddedd,label:e.summary,summary:e.summary,lastimport:!0,start:e.start,end:e.end,exception:e.exception?e.exception:null,description:void 0==e.description?null:e.description}]}},{key:"repeat",value:function(e,t){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,i=[];if(e.recursive=!0,"DAILY"==t.freq||"WEEKLY"==t.freq){var a=new Date(e.start),r=new Date(e.end),o=t.until?new Date(t.until):this.ending,s=t.interval||1,l="DAILY"==t.freq?1:7,d=t.count||null,c=t.byday||this.daysString;if(c instanceof String&&(c=[c]),d)for(var u=0;u<d;u++){var f=JSON.parse(JSON.stringify(e));f.start=moment(a).toISOString(),f.end=moment(r).toISOString(),f.recursive=!0,n.indexOf(a.toISOString())<0&&(i=i.concat(this.generateItem(f))),a.setDate(a.getDate()+s*l),r.setDate(r.getDate()+s*l)}else for(;a<o;){var m=this.daysString[a.getDay()];if("allday"==e.daily&&n.indexOf(moment(a).format("YYYY-MM-DD")+"T00:00:00.000Z")>-1);else if(!(c.indexOf(m)<0||n.indexOf(a.toISOString())>-1)){var g=JSON.parse(JSON.stringify(e));g.start=moment(a).format(),g.end=moment(r).format(),g.recursive=!0,i=i.concat(this.generateItem(g))}a.setDate(a.getDate()+s*l),r.setDate(r.getDate()+s*l)}}else console.log("RECURENCE NON-TRAITEE",t);return 0==i.length?(console.log(" !!!!!!!!!!!!!!!! RIEN de CRÉÉ",e,t),console.log(" TO => ",new Date(t.until)),console.log(" TO => ",this.ending),console.log(" TO => ",o)):console.log(" ================ ",i.length," créé(s)"),i}},{key:"parse",value:function(e){var t=this,n=moment.tz.guess(),i=[],a=[],r=e[1][0][3],o=e[1][1][3],s=moment().format("YYYY-MM-DD");return e[2].forEach(function(e){var l={icsfileuid:o,icsfilename:r,icsfiledateaddedd:s,warnings:[]},d=null,c=[];if("vevent"==e[0])if(e[1].forEach(function(e){if("uid"==e[0])l.uid=e[3],l.icsuid=e[3];else if("rrule"==e[0])d=e[3];else if("exdate"==e[0]){var i=moment.tz(e[3],e[1].tzid);c.push(i.tz(n).toISOString())}else if("organizer"==e[0])l.email=e[3];else if("description"==e[0])l.description=e[3],"undefined"==l.description&&(l.description="");else if("dtstart"==e[0]){var i=moment.tz(e[3],e[1].tzid);l.start=i.tz(n).format()}else if("recurrence-id"==e[0]){l.recurenceid=e[2];var i=moment.tz(e[3],e[1].tzid);l.exception=i.tz(n).format()}else if("dtend"==e[0]){var i=moment.tz(e[3],e[1].tzid);l.end=i.tz(n).format()}else"last-modified"==e[0]?l.lastModified=moment(e[3]).format():"summary"==e[0]?(l.summary=l.label=e[3],t.summaries.indexOf(l.summary)<0&&t.summaries.push(l.summary)):"x-microsoft-cdo-alldayevent"==e[0]&&(l.daily="allday")}),l.exception)a=a.concat(t.generateItem(l));else if("allday"==l.daily){var u=moment(l.start);t.dailyStrategy&&t.dailyStrategy.forEach(function(e){var n=e.startTime.split(":"),a=parseInt(n[0]),r=parseInt(n[1]),o=e.endTime.split(":"),s=parseInt(o[0]),f=parseInt(o[1]),m={uid:l.uid,icsuid:l.icsuid,icsfileuid:l.icsfileuid,icsfilename:l.icsfilename,icsfiledateadded:l.icsfiledateadded,daily:"allday",label:l.label,summary:l.label,description:l.description,email:l.email,start:u.hours(a).minutes(r).format(),end:u.hours(s).minutes(f).format()};i=d?i.concat(t.repeat(m,d,c)):i.concat(t.generateItem(m))})}else i=d?i.concat(t.repeat(l,d,c)):i.concat(t.generateItem(l))}),a.forEach(function(e){for(var t=0;t<i.length;t++){i[t];i[t].uid==e.uid&&i[t].start==e.exception&&i.splice(t,1,e)}}),i}},{key:"loadIcsFile",value:function(e){var t=this,n=new FileReader;n.onloadend=function(e){t.parseFileContent(ICAL.parse(n.result))},n.readAsText(e.target.files[0])}},{key:"parseFileContent",value:function(e){try{var t=ICAL.parse(e);if(t.length<2)throw"Bad format";return this.parse(t)}catch(e){throw e}}}]),e}();t.default=r}])});
//# sourceMappingURL=ICalAnalyser.js.map

const regex = /\[Person:(\d*):([\w -]*)\]/gm;
const regexActivity = /\[Activity:(\d*):(.*)\]/gm;
const regexOrganization = /\[Organization:(\d*):(.*)\]/gm;
const regexProject = /\[Project:(\d*):(.*)\]/gm;
const urlPerson = '/person/show/';
const urlProject = '/project/show/';
const urlOrganization = '/organization/show/';
const urlActivity = '/activites-de-recherche/fiche/';

export default {
    log(message) {
        return this.organization(this.activity(this.person(this.project(message))));
    },
    person(message) {
       return message.replace(regex, `<a href="`+urlPerson+`$1" class="person">$2</a>`);
    },
    activity(message) {
       return message.replace(regexActivity, `<a href="`+urlActivity+`$1" class="person">$2</a>`);
    },
    organization(message) {
        return message.replace(regexOrganization, `<a href="`+urlOrganization+`$1" class="organization">$2</a>`);
    },
    project(message) {
        return message.replace(regexProject, `<a href="`+urlProject+`$1" class="project">$2</a>`);
    },
};
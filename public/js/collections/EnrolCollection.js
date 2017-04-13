/*!
 * Oscar 1.0
 *
 * Copyright 2015, Université de Caen Normandie
 *
 */
define(['underscore', 'backbone', 'models/EnrolModel'], function (_, Backbone, EnrolModel) {
    var EnrolCollection = Backbone.Collection.extend({

        model: EnrolModel,
        byObject: null,
        owner: null,

        initialize: function () {
            this.on('sync', function () {
                this.orderByObject();
            }.bind(this));
        },

        /**
         * Réorganise les roles par enrollé.
         * Note:
         *   AVANT: [role1-enrolléA, role2-enrolléA, role1-enrolléB]
         *   APRES: [enrolléA-[role1,role2], enrolléB-[role1]]
         */
        orderByObject: function () {
            this.byObject = new Backbone.Collection([], {model: EnrolModel});
            this.byObject.original = this;

            this.forEach(function (role) {
                console.log(role);
                var enrolId = role.get('object').id, enrol;
                if (!this.byObject.get(enrolId)) {
                    this.byObject.add(role.get('object'))
                }
                enrol = this.byObject.get(enrolId);
                enrol.get('roles').push(role.toJSON());
            }.bind(this));
        },

        /**
         *
         */
        getByObject: function () {
            if (!this.byObject) {
                this.orderByObject();
            }
            return this.byObject;
        },

        /**
         * Déclenche la suppression d'un role-enrollé.
         */
        deleteRole: function (id) {
            $.ajax({
                method: "delete",
                url: this.urlDelete + id
            }).done(function (response) {
                this.reset();
                this.fetch();
            }.bind(this)).fail(function (xhr) {
                // @todo Mettre en place un retour visuel lors d'une erreur.
                console.error('fail', xhr.responseJSON.error);
            });
        },

        /**
         * Enregistre le role-enrollé. Conserne la mise à jour ou la création.
         * @param datas {}
         * @param enrolId null|integer L'identifiant le l'objet (pour une mise à jour).
         */
        saveRole: function (datas, enrolId) {
            if (enrolId) {
                datas.enrolid = enrolId;
            }
            datas.ownerid = this.ownerId;
            console.log(datas);
            return $.ajax({
                url: this.urlInsert,
                method: 'post',
                data: datas
            }).done(function () {
                this.fetch();
            }.bind(this));

            /*.fail(function(xhr){
             console.error('fail', xhr.responseJSON.error);
             }).always(function(){
             modal.close();
             });*/
        }
    });
    return EnrolCollection;
});

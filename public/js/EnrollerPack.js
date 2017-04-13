/**
 * Created by jacksay on 16-12-19.
 */
define('EnrollerPack', ['jquery', 'vue', 'LocalDB', 'text!templates/enrollerpack.vue', 'bootbox', 'modalform'], function($, Vue, LocalDB, enrollerPackTpl, Bootbox, ModalForm) {
    "use strict";


    var EnrollerPack = function( opt ){

        var conf = new LocalDB(opt.lsname || 'enroller_pack', {
            packerKey: "enrolled",
            packedKey: "role"
        });

        $.getJSON(opt.urlDatas, function(datas){
            var vue = new Vue({
                el: opt.el,
                template: enrollerPackTpl,
                data: {
                    label: opt.label || "enrolled",
                    packerKey: conf.get("packerKey"),
                    packedKey: conf.get("packedKey"),
                    roles: datas,
                    urlNewRole: opt.urlNewRole,
                    deleteMessage: "Supprimer définitivement ce rôle ?"
                },
                methods: {
                    changeStack: function(newPacker, newPacked){
                        conf.set('packerKey', newPacker);
                        conf.set('packedKey', newPacked);
                        this.packerKey = newPacker;
                        this.packedKey = newPacked;
                    },
                    editRole: function(role){
                        ModalForm.showForm(role.urlEdit, function(){
                                $.getJSON(opt.urlDatas, function(datas){
                                    datas.forEach(function(p){
                                        if(p.id == role.id ){
                                            console.log("update", role, p);
                                            role.roleLabel = p.roleLabel;
                                            role.role = p.role;
                                            role.start = p.start;
                                            role.end = p.end;
                                        }
                                    }.bind(this));
                                }.bind(this));
                        }.bind(this));
                    },
                    deleteRole: function(role){
                        Bootbox.confirm(this.deleteMessage, function(response){
                            if( response ){
                                $.get(role.urlDelete, function(){
                                    this.roles.forEach(function(r, i){
                                        if( role.id == r.id ){
                                            this.roles.splice(i, 1);
                                            return;
                                        }
                                    }.bind(this));
                                }.bind(this));
                            }
                        }.bind(this))
                    }
                },
                computed: {
                    packed: function(){
                        var packers = [],
                            self = this,
                            r = {};
                        this.roles.forEach(function(role){
                            var packerId = role[self.packerKey]
                                , packer = role[self.packerKey +'Label']
                                , packedId =  role[self.packedKey]
                                , packed =  role[self.packedKey+"Label"];

                            if(!r[packerId]){
                                r[packerId] = {
                                    label: packer,
                                    extraClass: "",
                                    packed: []
                                }
                            }

                            if( role.rolePrincipal )
                                r[packerId].extraClass = "primary";


                           // console.log('obsolete', role.end < (new Date()));

                            r[packerId].packed.push({
                                'id': role.id,
                                'label': packed,
                                'role': role
                            });
                        });
                        return r;
                    }
                }
            });
        });
    };
    EnrollerPack.version = "1.0.0";
    EnrollerPack.appname = "EnrollerPack";

    return EnrollerPack;
});
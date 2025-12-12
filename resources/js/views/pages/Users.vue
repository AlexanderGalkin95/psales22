<script>
import _ from 'lodash'
import Table from '../../components/table/Table'
import vInput from '../../components/input'
import VSelect from '../../components/vue-select'
import "../../validator";
import "@artamas/vue-select/src/scss/vue-select.scss";
import Swal from "sweetalert2"
import { mapGetters } from 'vuex';
import { VueTelInput } from 'vue-tel-input'

export default {
    name: "Users",
    components: {Table, vInput, VSelect, VueTelInput },
    data() {
        return{
            breadcrumbs: [
                { title: "Wizard" },
                { title: "Wizard-1" }
            ],
            userEdit: false,
            user: {
                id: null,
                name: '',
                email: '',
                password: '',
                role: '',
                phone: '',
                duoMode: false,
                telegram: ''
            },
            passwordConfirm: '',
            users: null,
            settings: {
                delete_user_callback: function (col, item, $this) {
                    const msg = 'Вы уверены, что хотите деактивировать пользователя: ' + item.name + '?';
                    Swal.fire({
                        title: 'Деактивация пользователя',
                        html: msg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#F64E60',
                        confirmButtonText: 'Удалить',
                        cancelButtonText: `Отмена`,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            return $this.$emit('deleteUser', item)
                        }
                    });
                },
                edit_user_callback: function (col, item, $this) {
                    $this.$emit('editUser', item)
                },

            },
            filters: {
                name: '',
                role: '',
                email: '',
            },
            dicts: {
                roles: [
                    {label:'Проектный менеджер', value:'Проектный менеджер'},
                    {label:'Старший асессор', value:'Старший асессор'},
                    {label:'Асессор', value:'Асессор'},
                ],
            },
            phoneInfo: {},
            loaded: true,
            reload_table: '',
            is2FAEnabled: window.is2FAEnabled,
            selectedItem: {},
            rolesOptions: [],
            isFormLoading: false,
        }
    },
    mounted() {
        this.$parent.page = "Пользователи";
        this.$http.get('/api/dictionaries/roles')
            .then(response => {
                if (response.status === 200) {
                   this.dicts.roles = response.data.roles;
                }
            });
        this.$http.get('/api/roles')
            .then(response => {
                if (response.status === 200) {
                    this.rolesOptions = response.data.roles;
                }
            });
    },
    computed: {
        ...mapGetters(['currentUser']),
        current_user() {
            return this.currentUser
        },
        user_role() {
            return this.current_user.role_name
        },
        columns() {
          let $this = this
          return [
            { name: 'name', title: 'Имя', format: 'text', width: '20%', sortable: true, searchable: true, align: 'left',},
            { name: 'email', title: 'Email', format: 'text', width: '20%', sortable: true, searchable: true, align: 'left',},
            { name: 'role', title: 'Роль', format: 'text', width: '30%', sortable: true, searchable: true, search_dict: 'roles', align: 'left',},
            { type: 'buttons', align: 'left', title: '', onclick: 'users', width: '3%',
              items: [
                { type: 'button', class: 'btn-success', id: 'edit_user', icon: 'fa-edit', width: '1%', show: true,
                  show_callback: function(col, item) {
                    return $this.user_role === 'sa' || ($this.user_role === "pm" && item.role_name !== 'sa')
                  },
                },
                { type: 'button', class: 'btn-danger', id: 'delete_user', icon: 'fa-trash', width: '1%', show: true,
                  show_callback: function(col, item) {
                    return $this.user_role === 'sa' || ($this.user_role === "pm" && item.role_name !== 'sa')
                  },
                },
              ]
            },
          ]

        },
        samePassword: function() {
            if(!this.user.password){
                return false
            } else {
                return this.user.password === this.passwordConfirm;
            }
        },
    },
    watch: {
        phoneInfo: {
            handler(newVal){
                if(this.phoneInfo.number){
                    if(this.phoneInfo.number.input === '') this.phoneInfo = {};
                }
            },
            deep: true
        },
    },
    methods: {
        setData: function (items) {
            this.loaded = true;
        },
        deleteUser: function (item) {
            this.loaded = false;
            this.$http.delete('/api/users/'+item.id)
                .then(response => {
                    if (response.status === 200) {
                        this.loaded = true;
                        this.reloadTable()
                        Swal.fire({
                            title: "",
                            html: response.data.message,
                            icon: "success",
                            showConfirmButton: true,
                            timer: 3000,
                        });
                    }
                }).catch(response => {
                    Swal.fire({
                        title: "",
                        html: response.data.message,
                        icon: "error",
                        showConfirmButton: true,
                        timer: 3000,
                    });
                    this.loaded = true;
                });
        },
        reloadTable() {
            this.reload_table = Math.random().toString(36).substring(1, 10);
        },
        close:function (){
            this.phoneInfo = {}
            this.user = {
                id: null,
                name:'',
                email:'',
                password:'',
                role:'',
                phone: '',
                twoFactorMode: false,
                telegram: ''
            };
        },
        async editUser (item) {
            this.$refs.observer.reset();
            this.isFormLoading = true
            this.userEdit = true;
            this.user.id = item.id;
            this.user.name = item.name;
            this.user.email = item.email;
            this.user.phone = item.phone || '';
            this.user.telegram = item.telegram;
            this.user.is_blocked = item.is_blocked;
            this.user.duoMode = !!item.duoMode;
            this.user.password = '';
            this.passwordConfirm = '';
            $('#createUser').modal('show');
            this.user.role = _.find(this.rolesOptions, (role) =>{
                return role.value === item.role_id;
            });
            this.isFormLoading = false
        },
        create: function () {
            this.userEdit = false;
            this.$refs.observer.reset();
            this.user = {
                id: null,
                name:'',
                email:'',
                password:'',
                phone:'',
                telegram:'',
                duoMode: false,
                role:''
            };
            this.passwordConfirm = '';
            $('#createUser').modal('show');
        },
        createUser: function () {
            this.$refs.observer.validate().then(result => {
                if(result) {
                    this.loaded = false;
                    var user = _.clone(this.user)
                    user.role = user.role.value;
                    user.phone = !this.phoneInfo.valid ? this.user.phone : this.phoneInfo.number.significant
                    user.confirm = this.passwordConfirm;
                    this.$http.post('/api/user', user)
                        .then(response => {
                            if (response.status === 200) {
                                this.loaded = true;
                                this.reloadTable();
                                $('#createUser').modal('hide');
                                Swal.fire({
                                    title: "",
                                    html: response.data.message,
                                    icon: "success",
                                    showConfirmButton: true,
                                    timer: 3000,
                                });
                                this.close();
                            }
                        }).catch(response => {
                        if (response.status === 422) {
                            this.$refs.observer.setErrors(response.data.fields)
                            this.loaded = true;
                        }
                        else {
                            Swal.fire({
                                title: "",
                                html: response.data.message,
                                icon: "error",
                                showConfirmButton: true,
                                timer: 3000,
                            });
                            this.loaded = true;
                        }
                    });
                }
            });
        },
        updateUser: function () {
            this.$refs.observer.reset();
            if (!_.isEmpty(this.phoneInfo) && this.phoneInfo.formatted !== '') {
                if (!this.phoneInfo.valid) {
                    this.$refs.observer.setErrors({'phone': 'Введенный номер телефона неправильный'})
                    return
                }
            }

            this.$refs.observer.validate().then(result => {
                if(result) {
                    this.loaded = false;
                    var user = _.clone(this.user)
                    user.role = user.role.value;
                    user.confirm = this.passwordConfirm;
                    user.phone = !this.phoneInfo.valid ? this.user.phone : this.phoneInfo.number.significant
                    this.$http.put('/api/users/'+this.user.id, user)
                        .then(response => {
                            if (response.status === 200) {
                                this.loaded = true;
                                this.reloadTable();
                                $('#createUser').modal('hide');
                                Swal.fire({
                                    title: "",
                                    html: response.data.message,
                                    icon: "success",
                                    showConfirmButton: true,
                                    timer: 3000,
                                });
                                this.close();
                            }
                        }).catch(response => {
                        if (response.status === 422) {
                            this.$refs.observer.setErrors(response.data.fields)
                            this.loaded = true;
                        }
                        else {
                            Swal.fire({
                                title: "",
                                html: response.data.message,
                                icon: "error",
                                showConfirmButton: true,
                                timer: 3000,
                            });
                            this.loaded = true;
                        }
                    });
                }
            });
        },
        validatePhone(value, info) {
            this.phoneInfo = info
        },
        getClass(cl){
            cl['vue-tel-input'] = true;
            return cl;
        },
    }
}
$('#createUser').modal({
    backdrop: 'static',
    keyboard: false
})
</script>

<template>
    <div class="row m-0">
        <div style="margin: 0 0 15px 19px;">
            <i class="flaticon-users-1" aria-hidden="true" style="font-size: 21px;"></i>
            <div style="display: inline-block;">
                <h4>Управление пользователями</h4>
            </div>
        </div>
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div v-show="!loaded" class="card">
                    <div class="card-body loading" style="padding-top:110px;min-height:350px;">
                        <span class="fa fa-spinner fa-3x fa-spin" style="position: absolute; top: 99px;left: 50%;"></span>
                    </div>
                </div>
                <div v-show="loaded" class="card card-custom">
                    <div>
                        <div v-if="user_role === 'sa' || user_role === 'pm'" class="card-header text-left" style="padding: 16px;">
                            <button id="process" class="btn btn-primary" type="button" value="return"
                                    @click="create">
                                <i class="flaticon2-plus"></i>
                                Создать
                            </button>
                        </div>
                        <div class="card-body" style="padding: 15px">
                            <Table
                                id="users"
                                :data_src="'/api/users'"
                                data_prop_name="users"
                                :columns="columns"
                                :filters="filters"
                                :settings="settings"
                                :dicts="dicts"
                                :reload= "reload_table"
                                @loaded="setData"
                                @deleteUser="deleteUser"
                                @editUser="editUser"
                            ></Table>
                        </div>
                    </div>
                </div>
            </article>

            <div id="createUser" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 495px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 v-if="!userEdit" class="modal-title tx-left pd-x-30">Создание пользователя</h5>
                            <h5 v-if="userEdit" class="modal-title tx-left pd-x-30">Редактирование пользователя</h5>
                            <button type="button" class="close" @click="close" data-dismiss="modal" aria-label="Close" style="padding: 1.5rem 1.75rem;margin: -1.5rem -1.75rem -1.5rem auto;">
                                <i class="flaticon2-cross" aria-hidden="true"></i>
                            </button>
                        </div>
                        <b-overlay :show="isFormLoading" rounded="sm" spinner-variant="primary">
                            <validation-observer  ref="observer" tag="form">
                            <div class="modal-body tx-left pd-y-20 pd-x-20">
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">Имя</label>
                                    <div class="col-md-8">
                                        <vInput id="name" type="text" rules="required" name="Имя" v-model="user.name"  maxlength="50"></vInput>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">Email</label>
                                    <div class="col-md-8">
                                        <v-input id="email" type="text" name="Email" rules="email|required" v-model="user.email" maxlength="50"></v-input>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">Пароль</label>
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <v-input id="password" type="password" name="Пароль" :rules="userEdit ? 'min:8' : 'required|min:8'" v-model="user.password" maxlength="50"></v-input>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <v-input id="confirm" type="password"  :rules="user.password ? 'required|confirm:@password' : ''"  name="Подтверждение пароля" v-model="passwordConfirm"></v-input>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-xs-4">
                                        <i v-if="samePassword" class="fa fa-check-square fa-lg" aria-hidden="true" style="color: #38c172;  margin-top: 12px;"></i>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label
                                        class="col-md-2 col-form-label text-md-right">Роль</label>
                                    <div class="col-md-8">
                                        <v-select id="role"
                                                  v-model="user.role"
                                                  rules="required"
                                                  placeholder="Выберите роль"
                                                  name="Роль"
                                                  :selectable="(o) => o.disabled !== true"
                                                  :options="rolesOptions"
                                        >
                                        </v-select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">Телефон</label>
                                    <div class="col-md-8">
                                        <validation-provider
                                            mode="passive"
                                            name="phone"
                                            vid="phone"
                                            :rules="(user.duoMode ? 'required': '')"
                                            :custom-messages="{ required: 'Номер телефона обязателен' }"
                                            v-slot="{ errors }"
                                        >
                                            <vue-tel-input
                                                v-model="user.phone"
                                                name="phone"
                                                input-id="phone"
                                                input-classes="form-control"
                                                mode="international"
                                                :class="getClass({'is-error': errors[0] })"
                                                placeholder=""
                                                :input-options="{ showDialCode: true, tabindex: 0 }"
                                                :valid-characters-only="true"
                                                @input="validatePhone"
                                            ></vue-tel-input>
                                            <span v-if="errors[0]" :class="{'error-message': errors[0] }"> {{ errors[0] }} </span>
                                        </validation-provider>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">Телеграм</label>
                                    <div class="col-md-8">
                                        <v-input id="telegram"
                                                 v-model="user.telegram"
                                                 name="Телеграм"
                                                 type="text"
                                                 maxlength="50"
                                                 :lefticon="'fa-at'"
                                                 rules="regex:^[^@]"
                                        >
                                            <template slot="lefticon">
                                                <span class="input-group-append">
                                                    <label class="input-group-text">
                                                        <i class="fa fa-at"></i>
                                                    </label>
                                                </span>
                                            </template>
                                        </v-input>
                                    </div>
                                </div>
                                <div v-if="(user_role === 'sa' || user_role === 'pm') && userEdit" class="form-group mb-0 row">
                                    <label class="col-6 col-form-label">Пользователь заблокирован:</label>
                                    <div class="col-4 text-right">
                                        <span class="switch switch-md danger">
                                          <label>
                                            <input id="userBlock" name="userBlock" type="checkbox" v-model="user.is_blocked" />
                                            <span></span>
                                          </label>
                                        </span>
                                    </div>
                                </div>
                                <div v-if="is2FAEnabled" class="form-group mb-0 row">
                                    <label class="col-6 col-form-label">Активировать двуфакторную авторизацию:</label>
                                    <div class="col-4 text-right">
                                        <span class="switch switch-md">
                                          <label>
                                            <input id="duoMode" name="duoMode" type="checkbox" v-model="user.duoMode" />
                                            <span></span>
                                          </label>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- modal-body -->
                            <div class="modal-footer tx-right pd-y-20 pd-x-20">
                                <button v-if="!userEdit" type="button" class="btn btn-success" @click="createUser">Создать</button>
                                <button v-if="userEdit" type="button" class="btn btn-success" @click="updateUser()">Сохранить</button>
                            </div><!-- modal-footer -->
                        </validation-observer>
                        </b-overlay>
                    </div><!-- modal-content -->
                </div><!-- modal-dialog -->
            </div><!-- modal -->

        </div>
</template>
<style scoped>
.vue-tel-input {
    border-radius: 0.85rem;
    border: 1px solid #E4E6EF;
}
.vti__dropdown {
    cursor: not-allowed;
}
.switch input:checked ~ span:after {
    opacity: 1;
    color: #ffffff;
    background-color: #1BC5BD;
}
.switch.danger input:checked ~ span:after {
    opacity: 1;
    color: #ffffff;
    background-color: #ff0000;
}
</style>

<script>
import _ from 'lodash'
import VInput from '../../../components/input.vue'
import VContent from "../../../components/VContent.vue";
import Swal from "sweetalert2";

export default {
    name: "CallTypes",
    props: {
        project: {
            type: Object,
            default: () => {},
            required: true
        }
    },
    components: { VContent, VInput },
    data() {
        return {
            callTypes: [],
        }
    },
    computed: {
        projectExists () {
            return !_.isEmpty(this.project)
        },

        hasEmptyFields() {
            return _.some(this.callTypes, item => {
                return _.isEmpty(item.name) || _.isEmpty(item.short_name)
            })
        },

        hasChanged () {
            return !_.isEqual(
                this.project.call_types,
                _.map(_.cloneDeep(this.callTypes), field => {
                    delete field.delete
                    return field
                })
            )
        },

        emptyCallTypes () {
            return _.isEmpty(this.callTypes)
        },
    },
    watch: {
        project: {
            handler(newVal) {
                if (newVal) {
                    this.callTypes = _.isEmpty(newVal.call_types)
                        ? this.callTypes
                        : _.map(_.cloneDeep(newVal.call_types), field => {
                            field.delete = true
                            return field
                        });
                }
            },
            deep: true,
            immediate: true
        },
        hasEmptyFields: {
            handler(newVal) {
                setTimeout(() => {
                    this.$emit('has-empty-fields', newVal)
                }, 500)
            },
            immediate: true
        },
        hasChanged: {
            handler(newVal) {
                this.$emit('has-changed', newVal)
            },
            immediate: true
        },
    },
    methods: {
        cancel() {
            this.$router.push('/projects');
        },

        addCallType () {
            this.callTypes.push({ name: '', short_name: '', rate_crm: true, delete: true })
        },

        scrollToBottom () {
            let elm = this.$refs.callsScrollbar.$el
            elm.scrollTo({ top: elm.scrollHeight, behavior: 'smooth' })
        },

        removeCallType (index) {
            this.callTypes.splice(index, 1)
        },

        save () {
            this.$refs.callTypesTab.validate().then( result => {
                if (result) {
                  this.$emit('saving', true)
                    this.$http.post(
                        `/api/projects/${this.project.id}/call_types`,
                        {
                            call_types: this.callTypes
                        }
                    ).then(response => {
                        if (response.status === 200) {
                            this.project.call_types = response.data.call_types
                            Swal.fire(response.data.message, '', 'success')
                          this.$emit('saving', false)
                        }
                    }, error => {
                        if (error.status === 422) {
                            let errorMessage = ''
                            this.$refs.callTypesTab.setErrors(error.data.fields)
                            _.forEach(error.data.fields, (field, key) => {
                                if (_.includes(['projectId', 'call_types.exists'], key)) {
                                    errorMessage += `${field[0]}. `
                                }
                            })
                            if (!_.isEmpty(errorMessage)) {
                                Swal.fire(errorMessage, '', 'error')
                            }
                        } else if (error.status === 403) {
                          Swal.fire(error.data.message, '', 'error')
                        } else {
                          Swal.fire('Невозможо выполнить ваш запрос', '', 'error')
                        }
                      this.$emit('saving', false)
                    });
                }
            })
        },
    },
}
</script>

<template>
    <validation-observer ref="callTypesTab" tag="form">
        <v-content>
            <div v-if="!emptyCallTypes" class="col-12">
                <b-row>
                    <b-input-group>
                        <label class="col-md-6">Название</label>
                        <label class="col-md-3 m-0 d-none d-sm-none d-md-block">Короткое название</label>
                        <label class="col-md-3 m-0 d-none d-sm-none d-md-block text-left">CRM</label>
                        <label style="flex: 1 1 15%"></label>
                    </b-input-group>
                </b-row>
            </div>
            <div class="col-12">
                <perfect-scrollbar ref="callsScrollbar"
                                   class="scroll"
                                   style="max-height:350px;position: relative;"
                                   v-bind:options="{ suppressScrollX: true, railBorderYWidth: 8 }"
                >
                    <div v-for="(call, index) in callTypes"
                         :key="index"
                         class="form-group">
                        <b-row>
                            <div class="input-group">
                                <div class="mb-1 col-md-6">
                                    <v-input :id="`call_types.${index}.name`"
                                             v-model="call.name"
                                             type="text"
                                             :name="`Название ${index + 1}`"
                                             rules="required"
                                             placeholder=" "
                                             maxlength="50" />
                                </div>
                                <div class="mb-1 col-md-3">
                                    <v-input :id="`call_types.${index}.short_name`"
                                             v-model="call.short_name"
                                             type="text"
                                             :name="`Короткое название ${index + 1}`"
                                             rules="required"
                                             placeholder=" "
                                             maxlength="50" />
                                </div>
                                <div class="mb-1 col-md-3 d-flex flex-wrap">
                                    <div class="text-right"
                                        v-b-tooltip.hover.topright="{
                                            title: 'Включить/отключить оценку CRM для этого типа звонка',
                                            interactive: false,
                                        }"
                                    >
                                        <span class="switch switch-md">
                                          <label>
                                            <input :id="`call_crm_${index}`"
                                                   v-model="call.rate_crm"
                                                   :name="`Оценка CRM ${index + 1}`"
                                                   type="checkbox" />
                                            <span></span>
                                          </label>
                                        </span>
                                    </div>
                                    <div v-if="call.delete">
                                        <b-button variant="light" @click="removeCallType(index)">
                                            <i class="fa fa-minus-circle"></i>
                                        </b-button>
                                        <b-button v-if="callTypes.length === index + 1" variant="light"
                                                  @click="addCallType">
                                            <i class="fa fa-plus-circle"></i>
                                        </b-button>
                                    </div>
                                </div>
                            </div>
                        </b-row>
                    </div>
                </perfect-scrollbar>
            </div>
            <button v-if="callTypes.length === 0" class="btn btn-primary"
                    type="button"
                    @click="addCallType">
                <i class="fa fa-plus"></i>
                Добавить типы звонков
            </button>
        </v-content>
        <b-row no-gutters class="card-footer justify-content-between">
            <router-link tag="a" class="btn btn-light"
                         :to="{ name: 'projects.list' }">
              <i class="fa fa-arrow-left"></i>
              Список проектов
            </router-link>
            <a href="javascript:void(0)"
               class="btn btn-primary"
               :disabled="!projectExists "
               @click="save">
              <i class="fa fa-save"></i>
              Сохранить
            </a>
        </b-row>
    </validation-observer>
</template>

<style lang="scss" scoped>
.switch input:empty ~ span:before {
    background: #f64e60;
    opacity: 0.3;
}
</style>

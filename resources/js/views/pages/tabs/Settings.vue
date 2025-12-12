<script>
import VContent from "../../../components/VContent.vue";
import VInput from "../../../components/input";
import {
    isEmpty,
    range,
    some,
    cloneDeep,
    isEqual,
} from "lodash";
import Swal from "sweetalert2";

export default {
    name: "Settings",
    components: { VContent, VInput },
    props: {
        project: {
            type: Object,
            default: () => {},
            required: true
        },
        load: {
            type: Boolean,
            default: false
        },
    },
    data () {
        return {
            projectSettings: {},
            weights: range(1, 11),
            isLoading: false,
        }
    },
    computed: {
        columns() {
            return this.project.call_types || []
        },
        dataArray() {
            return this.project.criteria || []
        },
        computedColumns () {
            let arr = cloneDeep(this.columns) || []
            if (this.columns.length) {
                arr.unshift({ name: 'Критерии \\ Типы звонков' })
            }
            return arr
        },

        criteriaMissing () {
            return !this.dataArray.length
        },

        callsMissing () {
            return this.computedColumns.length <= 1
        },

        hasEmptyFields() {
            return some(this.projectSettings, setting => {
                return some(setting, item => {
                    return item.enabled && !item.points
                })
            })
        },

        hasChanged () {
            return !isEqual(this.project.settings, this.projectSettings)
        },

        emptySettings () {
            return isEmpty(this.projectSettings)
        },
    },
    watch: {
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
        load(newVal) {
            if (newVal) {
                this.loadSettings()
            }
        },
    },
    methods: {
        saveSettings() {
            this.$refs.projectSettings.validate().then(result => {
                if (result) {
                    this.$emit('saving', true)
                    this.$http.post(
                        `/api/projects/${this.project.id}/settings`,
                        {
                            settings: this.projectSettings
                        }
                    ).then( response => {
                        this.$emit('saving', false)
                        if (response.status === 200) {
                            this.project.settings = response.data.settings
                            Swal.fire({
                                title: "",
                                html: response.data.message,
                                icon: "success",
                                showConfirmButton: true,
                                timer: 3000,
                            });
                        }
                    }, error => {
                        this.$emit('saving', false)
                        if (error.data) {
                            Swal.fire({
                                title: "",
                                html: error.data.message,
                                icon: "error",
                                showConfirmButton: true,
                                timer: 3000,
                            });
                        }
                    })
                }
            })
        },
        formatPoints(setting) {
            if (!setting.enabled) setting.points = null
        },
        async loadSettings () {
            if (!this.project.id) {
                this.projectSettings = {}
                return false
            }
            this.isLoading = true
            await this.$http.get(`/api/projects/${this.project.id}/settings`)
                .then( response => {
                    this.project.settings = isEmpty(response.data.settings) ? {} : response.data.settings
                    this.projectSettings = cloneDeep(this.project.settings)
                    this.isLoading = false
                })
        },
    },
}
</script>

<template>
    <b-overlay :show="isLoading" rounded="sm" spinner-variant="primary">
    <validation-observer ref="projectSettings" tag="form">
        <b-row no-gutters cols="12">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th v-for="(type, tIndex) in computedColumns" :key="tIndex" style="min-width: 100px;">
                        {{ type.name }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-show="!callsMissing && !criteriaMissing"
                    v-for="(item, index) in dataArray" :key="index">
                    <td>{{ item.label }}</td>
                    <td v-for="(setting, sIndex) in projectSettings[item.id]" :key="sIndex">
                        <div class="form-inline flex-nowrap">
                            <input v-model="setting.enabled"
                                   type="checkbox"
                                   class="mr-1"
                                   :name="`criteria_${setting.criteria_id}_${setting.call_type_id}`"
                                   @change="formatPoints(setting)">
                            <validation-provider mode="passive"
                                                 :rules="{required: setting.enabled}"
                                                 :vid="`criteria_${setting.criteria_id}_${setting.call_type_id}_points_${sIndex}`"
                            >
                                <b-form-select :id="`criteria_${setting.criteria_id}_${setting.call_type_id}_points_${sIndex}`"
                                               v-if="setting.enabled"
                                               v-model.number="setting.points"
                                               size="sm"
                                               :name="`criteria_${setting.criteria_id}_${setting.call_type_id}_points_${sIndex}`"
                                               :options="weights"
                                               :state="!!setting.points"
                                />
                            </validation-provider>
                        </div>
                    </td>
                </tr>
                <tr v-if="callsMissing">
                    <td colspan="100%" class="text-center">
                        <button class="btn btn-primary"
                                type="button"
                                @click="$emit('tab', 'call-types-tab')">
                            <i class="fa fa-plus"></i>
                            Добавить типы звонков
                        </button>
                    </td>
                </tr>
                <tr v-if="criteriaMissing">
                    <td colspan="100%" class="text-center">
                        <button class="btn btn-primary"
                                type="button"
                                @click="$emit('tab', 'criteria-tab')">
                            <i class="fa fa-plus"></i>
                            Добавить критерии
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </b-row>
        <b-row no-gutters class="card-footer justify-content-between">
            <router-link tag="a" class="btn btn-light"
                         :to="{ name: 'projects.list' }">
                <i class="fa fa-arrow-left"></i>
                Список проектов
            </router-link>
            <b-button variant="primary"
                      :disabled="!project || criteriaMissing || callsMissing"
                      @click="saveSettings">
                <i class="fa fa-save"></i>Сохранить
            </b-button>
        </b-row>
    </validation-observer>
    </b-overlay>
</template>

<style scoped>
.table {
    position: relative;
}

.table thead th {
    position: sticky;
    top: 79px;
    background: #ffffff;
}
</style>

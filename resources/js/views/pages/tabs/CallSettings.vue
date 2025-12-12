<script>
import DropdownList from '../../../components/dropdown/DropdownList.vue'
import VContent from "../../../components/VContent.vue";
import VInput from "../../../components/input.vue";
import Swal from "sweetalert2";
import {
  isEmpty,
  cloneDeep,
  map,
  find,
  isEqual,
  merge,
  isArray,
  findIndex,
  some,
  filter,
  forEach,
  size
} from "lodash";
import Table from "../../../components/table/Table.vue";
import srx from '../../../functions';
import { mapGetters } from 'vuex';

export default {
  name: "CallSettings",
  components: { VContent, VInput, Table, DropdownList },
  props: {
    load: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      managerAssessors: {},
      assessorsOptions: [],
      statusOptions: [],
      salesManagers: [],
      integrationPipelines: [],
      isLoading: false,
      isLoadingData: false,
      draggable: false,
      loadedCallSettings: null,
      loadedCallSettingsSalesManagers: null,
      loadedCallSettingsIntegrationPipelines: null,
      loadedProjectManagerAssessors: [],
      localSettings: {
        call_settings: {
          statuses: [],
          filter_duration_from: 0,
          filter_duration_to: 0,
        },
        call_settings_sales_managers: [],
        call_settings_integration_pipelines: [],
      },
    };
  },
  computed: {
    ...mapGetters('project', [
      'project',
    ]),
    hasEmptyFields() {
      return (
        isEmpty(this.localSettings.call_settings.statuses) ||
        !this.localSettings.call_settings.filter_duration_from ||
        !this.localSettings.call_settings.filter_duration_to
      );
    },
    hasChanged() {
      let settings = cloneDeep(this.localSettings.call_settings);
      if (this.emptyCallSettings) {
        settings = null;
      }
      return (
        !isEqual(this.loadedCallSettings, settings) ||
        !isEqual(
          this.loadedCallSettingsSalesManagers,
          this.localSettings.call_settings_sales_managers
        ) ||
        !isEqual(
          this.loadedCallSettingsIntegrationPipelines,
          this.localSettings.call_settings_integration_pipelines
        )
      );
    },
    emptyCallSettings() {
      return isEqual(this.localSettings.call_settings, this.emptyObject);
    },
    emptyObject() {
      return {
        statuses: [],
        filter_duration_from: 0,
        filter_duration_to: 0,
      };
    },
    allowMultiple() {
      return this.project && this.project.project_type_name === "amo_crm";
    },
    statusesAreChecked() {
      return this.localSettings.call_settings.statuses.length > 0;
    },
    statusesAreFullyChecked() {
      return this.localSettings.call_settings.statuses.length === this.statusOptions.length
    },
    managersTotalTime() {
      return this.localSettings.call_settings_sales_managers.reduce((result, currItem) => {
        return currItem.duration_limit ? result + currItem.duration_limit : result;
      }, 0)
    },
  },
  watch: {
    async load(newVal) {
      if (newVal) {
        this.$emit("has-empty-fields", false);
        await this.loadCallSettings();
        await this.loadSalesManagers();
      }
    },
    hasEmptyFields: {
      handler(newVal) {
        setTimeout(() => {
          this.$emit("has-empty-fields", newVal);
        }, 500);
      },
      immediate: true,
    },
    hasChanged: {
      handler(newVal) {
        this.$emit("has-changed", newVal);
      },
      immediate: true,
    },
  },
  async mounted() {
    this.$emit("has-empty-fields", false);
    await this.loadCallSettings()
    await Promise.all([
      this.loadSalesManagers(),
      this.loadIntegrationPipelines(),
      this.loadAssessors()
    ])
    await this.loadManagerAssessors()
    this.isLoading = false;
  },
  methods: {
    async loadManagerAssessors() {
      this.$http.get(`/api/projects/${this.project.id}/manager-assessors`).then((response) => {
        if (response.status === 200) {
          this.loadedProjectManagerAssessors = cloneDeep(response.data);
          let managerAssessors = {}
          forEach(this.loadedCallSettingsSalesManagers, (manager, index) => {
            managerAssessors[index] = response.data.reduce(
              (result, item) => {
                if (item.project_call_settings_sales_manager_id === manager.call_settings_sales_manager_id) {
                  result.push(item.assessor)
                }
                return result
              },
              []
            )
          })
          this.managerAssessors = managerAssessors
        }
      })
    },
    async loadAssessors() {
      this.$http.get("/api/roles/assessors").then((response) => {
        if (response.status === 200) {
          this.assessorsOptions = response.data.assessors;
        }
      })
    },
    async loadCallSettings() {
      if (!this.project.id) {
        this.localSettings.call_settings = this.emptyObject;
        this.localSettings.call_settings_sales_managers = [];
        this.localSettings.call_settings_integration_pipelines = [];
        return false;
      }
      this.isLoading = true;
      if (isEmpty(this.statusOptions)) {
        await this.loadCallStatuses();
      }
      await this.$http
        .get(`/api/projects/${this.project.id}/call_settings`)
        .then((response) => {
          if (isEmpty(response.data)) {
            this.localSettings.call_settings = this.emptyObject;
          } else {
            let settings = response.data;
            settings.statuses = map(settings.statuses, (status) => {
              return find(this.statusOptions, (o) => {
                return o.system_name === status;
              });
            });
            this.loadedCallSettings = cloneDeep(settings);
            this.localSettings.call_settings = settings;
          }
        });
      await Promise.all([
        this.loadCallSettingsSalesManagers(),
        this.loadCallSettingsIntegrationPipelines()
      ])
    },
    async loadCallSettingsSalesManagers() {
      this.$http
        .get(`/api/projects/${this.project.id}/call-settings-sales-managers`)
        .then((response) => {
          if (response.status === 200) {
            this.localSettings.call_settings_sales_managers = map(
              response.data || [],
              (item) => {
                item.call_settings_sales_manager_id = item.id
                const manager = item.sales_manager;
                delete item.sales_manager;
                return merge(item, manager);
              }
            );
            this.loadedCallSettingsSalesManagers = cloneDeep(this.localSettings.call_settings_sales_managers)
          }
        });
    },
    async loadCallStatuses() {
      await this.$http
        .get(`/api/dictionaries/call_statuses?projectId=${this.project.id}`)
        .then((response) => {
          if (response.status === 200) {
            this.statusOptions = response.data.call_statuses;
          }
        });
    },
    async loadSalesManagers() {
      await this.$http
        .get(`/api/projects/${this.project.id}/sales-managers`)
        .then((response) => {
          if (response.status === 200) {
            this.salesManagers = map(response.data, (group) => {
              return {
                ...group,
                items: filter(group.items, (item) => {
                  return !some(
                    this.localSettings.call_settings_sales_managers,
                    (manager) => manager.sales_manager_id === item.id
                  );
                }),
              };
            });
          }
        });
    },
    async loadCallSettingsIntegrationPipelines() {
      this.$http
        .get(`/api/projects/${this.project.id}/call-settings-integration-pipelines`)
        .then((response) => {
          if (response.status === 200) {
            this.localSettings.call_settings_integration_pipelines = map(
              response.data || [],
              (item) => {
                const pipeline = item.pipeline;
                item.selectedStatuses = map(item.selected_statuses, status => status.status)
                delete item.pipeline;
                return merge(item, pipeline);
              }
            );
            this.loadedCallSettingsIntegrationPipelines = cloneDeep(this.localSettings.call_settings_integration_pipelines)
          }
        });
    },
    async loadIntegrationPipelines() {
      if (this.project.project_type_name === "amo_crm") {
        await this.$http
          .get(`/api/projects/${this.project.id}/integration-pipelines`)
          .then((response) => {
            if (response.status === 200) {
              this.integrationPipelines = filter(
                map(response.data, item => {
                  return {
                    ...item,
                    selectedStatuses: [],
                  }
                }),
                (item) => !some(
                  this.localSettings.call_settings_integration_pipelines,
                  (pipeline) => pipeline.integration_pipeline_id === item.id
                )
              )
            }
          });
      }
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    saveCallSettings() {
      this.$refs.callSettings.validate().then((result) => {
        if (result) {
          let manager_assessors = []
          this.localSettings.call_settings_sales_managers.forEach((item, idx) => {
            if (this.managerAssessors[idx]?.length) {
              manager_assessors.push({
                manager_id: item.id,
                assessors: this.managerAssessors[idx]
              })
            }
          })
          if (size(manager_assessors) !== this.localSettings.call_settings_sales_managers.length) return;

          this.$emit("saving", true);
          let payload = cloneDeep(this.localSettings.call_settings);
          if (!isArray(payload.statuses)) {
            payload.statuses = [payload.statuses];
          }

          payload.manager_assessors = manager_assessors
          this.$http
            .post(`/api/projects/${this.project.id}/call_settings`, {
              ...payload,
              sales_managers: map(
                this.localSettings.call_settings_sales_managers,
                (item) => {
                  return {
                    ...item,
                    duration_limit: item.duration_limit || 0,
                    sales_manager_id: item.id,
                  };
                }
              ),
              pipelines: map(
                this.localSettings.call_settings_integration_pipelines,
                (item) => {
                  return {
                    integration_pipeline_id: item.id,
                    statuses: item.selectedStatuses
                  }
                }
              )
            })
            .then(
              (response) => {
                this.loadCallSettings();
                this.isLoading = false;
                Swal.fire({
                  title: "",
                  html: response.data.message,
                  icon: "success",
                  showConfirmButton: true,
                  timer: 6000,
                });
                this.$emit("saving", false);
              },
              (error) => {
                this.$emit("saving", false);
                if (error.status === 422) {
                  this.$refs.callSettings.setErrors(error.data.fields);
                } else {
                  Swal.fire({
                    title: "",
                    html: error.data.message,
                    icon: "error",
                    showConfirmButton: true,
                    timer: 6000,
                  });
                }
              }
            );
        }
      });
    },
    confirmRemoveItemAt(item, iIndex) {
      let $this = this;
      Swal.fire({
        title: 'Распределять время автоматически?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F64E60',
        confirmButtonText: 'Да',
        cancelButtonText: `Нет`,
      }).then((result) => {
        if (result.isConfirmed) {
          $this.distributeDurationTime(item)
        }
          Swal.fire('Готово!', '', 'success')
        $this.removeItemAt(item, iIndex)
      })
    },
    removeItemAt(item, iIndex) {
      this.localSettings.call_settings_sales_managers.splice(iIndex, 1);
      let groupIndex = findIndex(
        this.salesManagers,
        (group) => group.group === item.group_name
      );
      if (groupIndex !== -1) {
        this.salesManagers[groupIndex].items.push(item);
      }
    },
    removePipelineAt(pipeline, iIndex) {
      this.localSettings.call_settings_integration_pipelines.splice(iIndex, 1);
      this.integrationPipelines.push(pipeline);
    },
    addGroup(group, gIndex) {
      this.localSettings.call_settings_sales_managers = [
        ...this.localSettings.call_settings_sales_managers,
        ...group.items,
      ];
      this.salesManagers[gIndex].items = [];
    },
    addItemAt(item, iIndex, gIndex) {
      this.localSettings.call_settings_sales_managers.push(item);
      this.salesManagers[gIndex].items.splice(iIndex, 1);
    },
    addPipelineAt(pipeline, iIndex) {
      this.localSettings.call_settings_integration_pipelines.push(pipeline);
      this.integrationPipelines.splice(iIndex, 1);
    },
    async handleRefreshData() {
      this.isLoadingData = true;
      if (!this.project.integration_id) {
        this.isLoadingData = false;
        Swal.fire("У данного проекта отсуствует интеграции", "", "warning");
      }
      await Promise.all([
        this.refreshSalesManagers(),
        this.refreshIntegrationPipelines()
      ])
      .then(() => {
        this.isLoadingData = false;
      })
      .catch(() => {
        this.isLoadingData = false;
      });
    },
    async refreshSalesManagers() {
      await this.$http
        .post(`/api/projects/sales-managers`, {
          project_type: this.project.project_type,
          integration_domain: this.project.integration_domain,
        })
        .then((response) => {
          if (response.status === 200) {
            this.salesManagers = response.data;
          }
        })
    },
    handleDurationType(event, item) {
      if (event.target.checked) {
        item.duration_limit = null;
      }
    },
    clearAllSelectedSalesManagers() {
      this.localSettings.call_settings_sales_managers.forEach((item) => {
        let groupIndex = findIndex(
          this.salesManagers,
          (group) => group.group === item.group_name
        );
        if (groupIndex !== -1) {
          this.salesManagers[groupIndex].items.push(item);
        }
      });
      this.localSettings.call_settings_sales_managers = [];
    },
    async refreshIntegrationPipelines() {
      if (this.project.project_type_name === "amo_crm") {
        await this.$http
          .post(`/api/projects/integration-pipelines`, {
            project_type: this.project.project_type,
            integration_domain: this.project.integration_domain,
          })
          .then((response) => {
            if (response.status === 200) {
              this.integrationPipelines = response.data;
            }
          })
      }
    },
    handleCheckAllStatuses(event) {
      event.stopPropagation();
      event.preventDefault();

      if (this.statusesAreChecked) {
        this.localSettings.call_settings.statuses = []
      } else {
        this.localSettings.call_settings.statuses = cloneDeep(this.statusOptions)
      }
    },
    itemStatusesSelected(item) {
      return item.selectedStatuses.length === item.statuses.length ? true : item.selectedStatuses.length > 0 ? 1 : false
    },
    handleCheckAllItemStatuses(event, item) {
      event.stopPropagation();
      event.preventDefault();
       if (this.itemStatusesSelected(item)) {
        item.selectedStatuses = []
      } else {
        item.selectedStatuses = cloneDeep(item.statuses)
      }
    },
    distributeDurationTime(item) {
      let projectSalesMangers = filter(
        this.localSettings.call_settings_sales_managers,
        manager => !manager.no_duration_limit
      );
      const projectSalesMangersCount = projectSalesMangers.length;
      if (projectSalesMangersCount > 1) {
        if (item.duration_limit > 0) {
          const quotient = Math.round((item.duration_limit || 0) / (projectSalesMangersCount - 1));
          if (quotient > 0) {
            forEach(projectSalesMangers, manager => {
              manager.duration_limit += quotient;
            })
          }
        }
      }
    },
    getDate(date) {
      return srx.customDate(date)
    },
    countManagerAssessors(assessors) {
      return assessors?.length ? assessors[0].label : 0
    },
    checkManagerAssessorsAreInvalid(assessors) {
      return !assessors?.length
    }
  },
};
</script>

<template>
  <b-overlay :show="isLoading" rounded="sm" spinner-variant="primary">
    <validation-observer ref="callSettings" tag="form">
      <v-content class="pt-1">
        <b-col cols="12">
          <div class="d-flex justify-content-end align-items-center py-2">
            <legend>Параметры импорта звонков</legend>
            <b-button
              class="ml-2 p-1"
              @click="handleRefreshData"
              v-b-tooltip.hover="'Обновить данные из Интеграции'"
            >
              <span
                :class="{
                  'icon-spin': isLoadingData,
                }"
              >
                <inline-svg src="/media/svg/icons/General/Update.svg" />
              </span>
            </b-button>
          </div>
        </b-col>
        <b-col sm="12" md="6" lg="6">
          <validation-provider
            vid="statuses"
            name="Статусы"
            rules="required"
            v-slot="validationContext"
          >
            <b-form-group
              :invalid-feedback="validationContext.errors[0]"
              :state="getValidationState(validationContext)"
              class="mb-0"
            >
              <b-dropdown
                variant="outline-light"
                block
                toggle-class="d-inline-flex align-items-center"
                menu-class="w-100"
              >
                <template #button-content>
                  <div class="d-flex w-100">
                    <b-form-checkbox
                      class="d-inline-flex"
                      :checked="statusesAreChecked"
                      :class="{
                        minus: statusesAreChecked && !statusesAreFullyChecked
                      }"
                      @click.native="handleCheckAllStatuses"
                    >
                    </b-form-checkbox>
                    <span class="mx-2">Статусы звонков</span>
                  </div>
                </template>
                <b-form-checkbox-group
                  v-model="localSettings.call_settings.statuses"
                  stacked
                >
                  <b-form-checkbox
                    v-for="(status, sIndex) in statusOptions"
                    :key="sIndex"
                    size="md"
                    class="w-100 text-nowrap text-hover-primary material-checkbox"
                    :name="status.label"
                    :value="status"
                  >
                    {{ status.label }}
                  </b-form-checkbox>
                </b-form-checkbox-group>
              </b-dropdown>
            </b-form-group>
          </validation-provider>
        </b-col>
        <b-col sm="12" md="6" lg="6">
          <b-form-group
            label="Длительность (секунды)"
            label-cols="12"
            label-class="overflow-wrap-normal"
            label-cols-sm="12"
            label-cols-md="3"
            label-cols-lg="3"
            class="mb-0"
          >
            <b-row>
              <div class="col-4 col-sm-4 m-2 min-w-110px">
                <validation-provider
                  vid="filter_duration_from"
                  name="От"
                  rules="integer|between:0,86400"
                  v-slot="validationContext"
                >
                  <b-form-group :invalid-feedback="validationContext.errors[0]">
                    <b-form-input
                      id="filter_duration_from"
                      v-model.number="
                        localSettings.call_settings.filter_duration_from
                      "
                      type="number"
                      :min="0"
                      :max="86400"
                      name="От"
                      :state="getValidationState(validationContext)"
                    />
                  </b-form-group>
                  <sup
                    style="
                      position: absolute;
                      font-style: italic;
                      right: 1px;
                      top: 0;
                    "
                    >От</sup
                  >
                </validation-provider>
              </div>
              <div class="col-4 col-sm-4 m-2 min-w-110px">
                <validation-provider
                  vid="filter_duration_to"
                  :rules="`integer|between:0,86400|min_value:${localSettings.call_settings.filter_duration_from}`"
                  name="До"
                  v-slot="validationContext"
                >
                  <b-form-group :invalid-feedback="validationContext.errors[0]">
                    <b-form-input
                      id="filter_duration_to"
                      v-model.number="
                        localSettings.call_settings.filter_duration_to
                      "
                      type="number"
                      :min="localSettings.call_settings.filter_duration_from"
                      :max="86400"
                      name="От"
                      :state="getValidationState(validationContext)"
                    />
                    <sup
                      style="
                        position: absolute;
                        font-style: italic;
                        right: 1px;
                        top: 0;
                      "
                      >До</sup
                    >
                  </b-form-group>
                </validation-provider>
              </div>
            </b-row>
          </b-form-group>
        </b-col>
        <!-----------------------------------------------
        ------------------------------------------------>
        <b-col cols="12" class="border-bottom border-bottom-secondary mb-10 py-2">
          <legend>
            <div class="d-flex justify-content-between align-items-center">
              Воронки, из которых слушаем звонки
            </div>
          </legend>
          <b-row>
            <b-col sm="12" md="6" lg="6">
              <perfect-scrollbar
                ref="prefectScrollbar"
                class="scroll overflow-y-auto max-h-550px"
                :options="{ suppressScrollX: true, railBorderYWidth: 8 }"
              >
                <div
                  v-if="!integrationPipelines.length"
                  class="text-center text-muted"
                >
                  Ничего не найдено
                </div>
                <draggable
                  class="list-group min-h-100px"
                  tag="ul"
                  group="niche"
                  ghostClass="ghost"
                  :animation="200"
                  :list="integrationPipelines"
                >
                  <li
                    v-for="(item, eIndex) in integrationPipelines"
                    :key="eIndex"
                    class="
                      list-group-item
                      d-flex
                      justify-content-between
                      align-items-center
                    "
                  >
                    {{ item.name }}
                    <i
                      class="
                        fa fa-plus-circle
                        cursor-pointer
                        text-hover-primary
                      "
                      @click="addPipelineAt(item, eIndex)"
                    ></i>
                  </li>
                </draggable>
              </perfect-scrollbar>
            </b-col>
            <b-col sm="12" md="6" lg="6">
              <perfect-scrollbar
                ref="prefectScrollbar"
                class="scroll overflow-y-auto max-h-550px"
                :options="{ suppressScrollX: true, railBorderYWidth: 8 }"
              >
                <div
                  v-if="!salesManagers.length"
                  class="text-center text-muted"
                >
                  Ничего не найдено
                </div>
                <draggable
                  class="list-group min-h-100px"
                  tag="ul"
                  group="niche"
                  ghostClass="ghost"
                  :animation="200"
                  :list="localSettings.call_settings_integration_pipelines"
                >
                  <li
                    v-for="(item, eIndex) in localSettings.call_settings_integration_pipelines"
                    :key="eIndex"
                    class="list-group-item"
                    v-b-toggle="[`list-group-item-${eIndex + 1}`]"
                  >
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex text-nowrap text-hover-primary">
                        <b-form-checkbox
                          size="md"
                          class="d-inline-flex"
                          :name="item.name"
                          :checked="!!itemStatusesSelected(item)"
                          :class="{
                            minus: itemStatusesSelected(item) === 1
                          }"
                          @click.native="handleCheckAllItemStatuses($event, item)"
                        ></b-form-checkbox>
                        <span class="ml-3">{{ item.name }}</span>
                      </div>
                      <i
                        class="
                          fa fa-minus-circle
                          cursor-pointer
                          text-hover-primary
                        "
                        @click="removePipelineAt(item, eIndex)"
                      ></i>
                    </div>
                    <b-collapse
                      :id="`list-group-item-${eIndex + 1}`"
                      :visible="eIndex === 0"
                      accordion="niche-accordion"
                      role="tabpanel"
                      class="py-2"
                    >
                      <b-form-checkbox-group
                        :id="`list-checkbox-group-${eIndex + 1}`"
                        v-model="item.selectedStatuses"
                        stacked
                      >
                        <b-form-checkbox
                          v-for="(itemStatus, statusIndex) in item.statuses"
                          :key="`niche-checkbox-${eIndex}-${statusIndex}`"
                          size="md"
                          class="w-100 text-nowrap text-hover-primary material-checkbox"
                          :style="{
                            backgroundColor: itemStatus.color
                          }"
                          :name="itemStatus.name"
                          :value="itemStatus"
                        >
                          {{ itemStatus.name }}
                        </b-form-checkbox>
                      </b-form-checkbox-group>
                    </b-collapse>
                  </li>
                </draggable>
              </perfect-scrollbar>
            </b-col>
          </b-row>
        </b-col>
        <!-----------------------------------------------
        ------------------------------------------------>
        <b-col cols="12" class="border-bottom border-bottom-secondary mb-10 py-2">
          <b-row>
            <b-col cols="6">
              <legend>Менеджеры по продажам</legend>
              <perfect-scrollbar
                ref="prefectScrollbar"
                class="overflow-y-auto"
                :options="{ suppressScrollX: true, railBorderYWidth: 8 }"
              >
                <div
                  v-if="!salesManagers.length"
                  class="text-center text-muted"
                >
                  Ничего не найдено
                </div>
                <draggable
                  class="list-group min-h-100px"
                  tag="ul"
                  group="people"
                  ghostClass="ghost"
                  :animation="200"
                  :list="localSettings.call_settings_sales_managers"
                  :disabled="!draggable"
                >
                  <li
                    v-for="(group, gIndex) in salesManagers"
                    :key="gIndex"
                    class="
                      list-group-item
                      d-flex
                      justify-content-between
                      align-items-center
                    "
                    @mouseover="draggable = false"
                    @mouseleave="draggable = true"
                  >
                    <div class="w-100">
                      <div
                        class="
                          d-flex
                          justify-content-between
                          w-100
                          cursor-pointer
                        "
                        role="tab"
                        v-b-toggle="[`accordion-${gIndex + 1}`]"
                      >
                        <span>
                          {{ group.group }}
                        </span>
                        <i
                          class="
                            fa fa-plus-circle
                            cursor-pointer
                            text-hover-primary
                          "
                          @click="addGroup(group, gIndex)"
                        ></i>
                      </div>
                      <b-collapse
                        :id="`accordion-${gIndex + 1}`"
                        :visible="gIndex === 0"
                        accordion="my-accordion"
                        role="tabpanel"
                        class="py-2"
                      >
                        <draggable
                          class="list-group min-h-100px"
                          tag="ul"
                          group="people"
                          ghostClass="ghost"
                          :animation="200"
                          :list="group.items"
                        >
                          <li
                            v-for="(item, eIndex) in group.items"
                            :key="eIndex"
                            class="
                              list-group-item
                              d-flex
                              justify-content-between
                              align-items-center
                            "
                          >
                            {{ item.name }}
                            <i
                              class="
                                fa fa-plus-circle
                                cursor-pointer
                                text-hover-primary
                              "
                              @click="addItemAt(item, eIndex, gIndex)"
                            ></i>
                          </li>
                        </draggable>
                      </b-collapse>
                    </div>
                  </li>
                </draggable>
              </perfect-scrollbar>
            </b-col>
            <b-col cols="6">
              <legend class="d-flex justify-content-between align-items-center">
                Менеджеры для прослушки
                <span
                  class="text-hover-primary font-size-base"
                  role="button"
                  @click="clearAllSelectedSalesManagers"
                >Удалить всех</span>
              </legend>
              <perfect-scrollbar
                ref="prefectScrollbar"
                class="overflow-y-auto"
                :options="{ suppressScrollX: true, railBorderYWidth: 8 }"
              >
                <draggable
                  class="list-group min-h-100px"
                  tag="ul"
                  group="people"
                  ghostClass="ghost"
                  :animation="200"
                  :list="localSettings.call_settings_sales_managers"
                >
                  <li
                    v-for="(
                      item, iIndex
                    ) in localSettings.call_settings_sales_managers"
                    :key="iIndex"
                    class="
                      list-group-item
                      d-flex
                      p-0
                    "
                  >
                    <table class="table table-borderless mb-0">
                      <tr>
                        <td class="d-flex flex-column" style="vertical-align: middle">
                          <span>{{ item.name }}</span>
                          <dropdown-list
                            v-model="managerAssessors[iIndex]"
                            boundary="viewport"
                            variant="text"
                            toggle-class="btn btn-transparent btn-sm text-muted text-left px-0"
                            :items="assessorsOptions"
                            :class="{
                              'is-error': checkManagerAssessorsAreInvalid(managerAssessors[iIndex])
                            }"
                          >
                            <template #button-content>
                              {{ countManagerAssessors(managerAssessors[iIndex]) }}
                            </template>
                          </dropdown-list>
                        </td>
                        <td style="width: 40%; vertical-align: middle">
                          <div class="d-flex">
                            <validation-provider
                              :vid="`duration_limit.${iIndex}`"
                              name="Лимит"
                              :rules="{
                                required: !item.no_duration_limit,
                                integer: true,
                                between: [0, 8400],
                                totaltimelimit: managersTotalTime > project.total_time_limit
                              }"
                              v-slot="validationContext"
                              class="mr-2"
                            >
                              <b-form-group
                                class="mb-0"
                                :invalid-feedback="validationContext.errors[0]"
                                :disabled="item.no_duration_limit"
                              >
                                <b-form-input
                                  :id="`duration_limit.${iIndex}`"
                                  v-model.number="item.duration_limit"
                                  type="number"
                                  name="Лимит"
                                  :min="0"
                                  :max="86400"
                                  :state="getValidationState(validationContext)"
                                />
                              </b-form-group>
                            </validation-provider>
                            <span class="switch switch-sm">
                              <label v-b-tooltip.hover="'Нет ограничения'">
                                <input
                                  id="no_duration_limit"
                                  v-model="item.no_duration_limit"
                                  name="Ограничения"
                                  type="checkbox"
                                  @change="handleDurationType($event, item)"
                                />
                                <span></span>
                              </label>
                            </span>
                          </div>
                        </td>
                        <td style="width: 10%; vertical-align: middle">
                          <i
                            class="
                              fa fa-minus-circle
                              cursor-pointer
                              text-hover-primary
                            "
                            @click="confirmRemoveItemAt(item, iIndex)"
                          ></i>
                        </td>
                      </tr>
                    </table>
                  </li>
                </draggable>
              </perfect-scrollbar>
            </b-col>
          </b-row>
        </b-col>
      </v-content>
      <b-row no-gutters class="card-footer justify-content-between">
        <router-link
          tag="a"
          class="btn btn-light"
          :to="{ name: 'projects.list' }"
        >
          <i class="fa fa-arrow-left"></i>
          Список проектов
        </router-link>
        <b-button variant="primary" @click="saveCallSettings">
          <i class="fa fa-save"></i>Сохранить
        </b-button>
      </b-row>
    </validation-observer>
  </b-overlay>
</template>

<style>
</style>
<style lang="scss" scoped>
.icon-spin {
  svg {
    animation-name: spin;
    animation-duration: 1000ms;
    animation-iteration-count: infinite;
    animation-timing-function: linear;
  }
}
@keyframes spin {
  from {
    transform: rotate(360deg);
  }
  to {
    transform: rotate(0deg);
  }
}

.material-checkbox {
  padding-left: 2rem;

  ::v-deep .custom-control-label {
    padding: 0.5rem;
    cursor: pointer;
  }

  &:hover {
    background-color: #EBEDF3;
  }
}
</style>

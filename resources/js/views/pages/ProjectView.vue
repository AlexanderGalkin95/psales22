<script>
import Table from "../../components/table/Table";
import VInput from "../../components/input";
import VSelect from "../../components/vue-select";
import "../../validator";
import "@artamas/vue-select/src/scss/vue-select.scss";
import Swal from "sweetalert2";
import {
    isEmpty,
    find,
    debounce,
    each,
} from "lodash";
import Objections from "./tabs/Objections";
import VContent from "../../components/VContent";
import Crm from "./tabs/Crm";
import CallTypes from "./tabs/CallTypes";
import Criteria from "./tabs/Criteria";
import Settings from "./tabs/Settings";
import CallSettings from "./tabs/CallSettings";
import Project from './tabs/Project.vue';
import { mapGetters, mapActions, mapMutations } from 'vuex';

export default {
  name: "ProjectView",
  components: {
    CallSettings,
    Settings,
    Criteria,
    CallTypes,
    Crm,
    VContent,
    Objections,
    Table,
    VInput,
    VSelect,
    Project,
  },
  data() {
    return {
      criteriaActive: false,
      callTypesActive: false,
      editCallTypesValues: "",
      callTypesValues: "",
      hasSettingsEmptyFields: false,
      hasCallSettingsEmptyFields: false,
      settingsHaveChanged: false,
      callSettingsHaveChanged: false,
      hasObjectionsEmptyFields: false,
      hasProjectEmptyFields: false,
      projectHasChanged: false,
      objectionsHaveChanged: false,
      hasCrmEmptyFields: false,
      crmFieldsHaveChanged: false,
      criteriaHaveChanged: false,
      hasCriteriaEmptyFields: false,
      hasCallTypesEmptyFields: false,
      callTypesHaveChanged: false,
      criteriaValidating: null,
      crmValidating: null,
      objectionsValidating: null,
      loadSettings: false,
      loadCallSettings: false,
    };
  },
  computed: {
    ...mapGetters('project', [
      'project',
    ]),
    companyId() {
      return this.$route.params.companyId;
    },
    projectId() {
      return this.$route.params.projectId;
    },

    projectExists() {
      return !!this.projectId && !isEmpty(this.project);
    },

    criteriaExist() {
      return this.projectExists && !isEmpty(this.project?.criteria);
    },
    currentTab() {
      let tab = find(this.$refs.tabs.tabs, (tab) => tab.localActive);
      return tab.id;
    },
  },
  beforeRouteLeave(to, from, next) {
    const check =
      this.projectHasChanged ||
      this.criteriaHaveChanged ||
      this.callTypesHaveChanged ||
      this.crmFieldsHaveChanged ||
      this.objectionsHaveChanged ||
      this.settingsHaveChanged ||
      this.callSettingsHaveChanged;
    if (this.projectExists && check) {
      this.bootboxDialog(() => {
        this.SET_PROJECT({})
        next();
      });
    } else {
      this.SET_PROJECT({})
      next();
    }
  },
  watch: {
    loadSettings: debounce(function (newVal) {
      if (newVal) this.loadSettings = false;
    }, 500),
    loadCallSettings: debounce(function (newVal) {
        if (newVal) this.loadCallSettings = false
    }, 500),
    companySearch: debounce(function () {
      this.loadCompanies(this.companySearch)
    }, 500)
  },
  async mounted() {
    await this.loadProject();
  },
  methods: {
    ...mapMutations('project', [
      'SET_PROJECT'
    ]),
    ...mapActions('project', [
      'LOAD_PROJECT'
    ]),
    async loadProject(newTabIndex, bEvent) {
      if (this.projectId) {
        await this.LOAD_PROJECT(this.projectId);
      }

      this.switchTab(newTabIndex, bEvent);
    },
    tabActivated(newTabIndex, oldTabIndex, event) {
      event.preventDefault();
      let currentTab = find(event.vueTarget.tabs, (tab) => tab.localActive);
      if (this.projectExists) {
        let check = false;
        if (!isEmpty(currentTab)) {
          switch (currentTab.id) {
            case "project-tab":
              check = this.projectHasChanged;
              break;
            case "criteria-tab":
              check = this.criteriaHaveChanged;
              break;
            case "call-types-tab":
              check = this.callTypesHaveChanged;
              break;
            case "crm-tab":
              check = this.crmFieldsHaveChanged;
              break;
            case "objections-tab":
              check = this.objectionsHaveChanged;
              break;
            case "settings-tab":
              check = this.settingsHaveChanged;
              break;
            case "call-settings-tab":
              check = this.callSettingsHaveChanged;
              break;
          }
        }
        if (check) {
          return this.showWarning(newTabIndex, event);
        }
        event.vueTarget.currentTab = newTabIndex;
      }
    },
    showWarning(newTabIndex, event) {
      this.bootboxDialog(() => {
        this.loadProject(newTabIndex, event);
      });
    },
    bootboxDialog(callback) {
      Swal.fire({
        title: 'Предупреждение!',
        html: 'Все изменения будут потеряны!<br>Вы уверены?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F64E60',
        confirmButtonText: 'Да',
        cancelButtonText: `Нет`,
      }).then((result) => {
        if (result.isConfirmed) {
          return callback()
        }
      })
    },

    switchTab(newTab, bEvent) {
      if (bEvent !== undefined) {
        bEvent.vueTarget.currentTab = newTab;
      } else {
        if (typeof newTab === "string") {
          each(this.$refs.tabs.tabs, (tab) => {
            if (tab.id === newTab) {
              tab.activate();
            }
          });
        }
      }
    },

    setProjectFieldsAreEmpty(value) {
      this.hasProjectEmptyFields = value;
    },

    setObjectionsAreEmpty(value) {
      this.hasObjectionsEmptyFields = value;
    },

    setCrmFieldsAreEmpty(value) {
      this.hasCrmEmptyFields = value;
    },

    setCallTypesAreEmpty(value) {
      this.hasCallTypesEmptyFields = value;
    },

    setCriteriaAreEmpty(value) {
      this.hasCriteriaEmptyFields = value;
    },

    setSettingsAreEmpty(value) {
      this.hasSettingsEmptyFields = value;
    },

    setCallSettingsAreEmpty(value) {
      this.hasCallSettingsEmptyFields = value;
    },

    setProjectHasChanged(value) {
      this.projectHasChanged = value;
    },

    setObjectionsHaveChanged(value) {
      this.objectionsHaveChanged = value;
    },

    setCrmFieldsHaveChanged(value) {
      this.crmFieldsHaveChanged = value;
    },

    setCallTypesHaveChanged(value) {
      this.callTypesHaveChanged = value;
    },

    setCriteriaHaveChanged(value) {
      this.criteriaHaveChanged = value;
    },

    setSettingsHaveChanged(value) {
      this.settingsHaveChanged = value;
    },

    setCallSettingsHaveChanged(value) {
      this.callSettingsHaveChanged = value;
    },

    setValidating(value) {
      if (this.currentTab !== "criteria-tab") {
        this.criteriaValidating = value;
      }
      if (this.currentTab !== "crm-tab") {
        this.crmValidating = value;
      }
      if (this.currentTab !== "objections-tab") {
        this.objectionsValidating = value;
      }
    },
  },
};
</script>
<template>
  <div class="row m-0">
    <div style="margin: 0 0 15px 19px">
      <i class="fa fa-pie-chart" aria-hidden="true" style="font-size: 21px"></i>
      <div style="display: inline-block">
        <h4 v-if="projectId">Редактирование проекта</h4>
        <h4 v-else>Создание проекта</h4>
      </div>
    </div>
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="card card-custom">
        <div>
          <validation-observer ref="observer" tag="form">
            <div class="card-body" style="padding: 0">
              <div class="row">
                <div id="project-tabs" class="col-md-12">
                  <b-tabs
                    ref="tabs"
                    @activate-tab="tabActivated"
                    content-class="mt-3"
                  >
                    <b-tab id="project-tab" lazy>
                      <template #title>
                        <b-icon
                          v-show="hasProjectEmptyFields"
                          variant="danger"
                          icon="exclamation-triangle"
                          class="mr-2"
                          small
                        >
                        </b-icon>
                        Данные по проекту
                      </template>
                      <project
                        :project="project"
                        @has-empty-fields="setProjectFieldsAreEmpty"
                        @has-changed="setProjectHasChanged"
                      />
                    </b-tab>
                    <b-tab
                      id="criteria-tab"
                      :active="criteriaActive"
                      :disabled="!projectExists"
                    >
                      <template #title>
                        <b-icon
                          v-show="hasCriteriaEmptyFields"
                          variant="danger"
                          icon="exclamation-triangle"
                          class="mr-2"
                          small
                        >
                        </b-icon>
                        Критерии
                      </template>
                      <criteria
                        :project="project"
                        :validating="criteriaValidating"
                        @has-empty-fields="setCriteriaAreEmpty"
                        @has-changed="setCriteriaHaveChanged"
                        @validating="setValidating"
                      />
                    </b-tab>
                    <b-tab
                      id="call-types-tab"
                      :active="callTypesActive"
                      :disabled="!criteriaExist"
                    >
                      <template #title>
                        <b-icon
                          v-show="hasCallTypesEmptyFields"
                          variant="danger"
                          icon="exclamation-triangle"
                          class="mr-2"
                          small
                        >
                        </b-icon>
                        Типы звонков
                      </template>
                      <call-types
                        :project="project"
                        @has-empty-fields="setCallTypesAreEmpty"
                        @has-changed="setCallTypesHaveChanged"
                      />
                    </b-tab>

                    <b-tab id="crm-tab" :disabled="!projectExists">
                      <template #title>
                        <b-icon
                          v-show="hasCrmEmptyFields"
                          variant="danger"
                          icon="exclamation-triangle"
                          class="mr-2"
                          small
                        >
                        </b-icon>
                        CRM
                      </template>
                      <crm
                        :project="project"
                        :validating="crmValidating"
                        @has-empty-fields="setCrmFieldsAreEmpty"
                        @has-changed="setCrmFieldsHaveChanged"
                        @validating="setValidating"
                      />
                    </b-tab>

                    <b-tab id="objections-tab" :disabled="!projectExists">
                      <template #title>
                        <b-icon
                          v-show="hasObjectionsEmptyFields"
                          variant="danger"
                          icon="exclamation-triangle"
                          class="mr-2"
                          small
                        >
                        </b-icon>
                        Возражения
                      </template>
                      <objections
                        :project="project"
                        :validating="objectionsValidating"
                        @has-empty-fields="setObjectionsAreEmpty"
                        @has-changed="setObjectionsHaveChanged"
                        @validating="setValidating"
                      />
                    </b-tab>

                    <b-tab
                      id="settings-tab"
                      title="Настройки"
                      :disabled="!projectExists || !criteriaExist"
                      @update:active="(v) => loadSettings = !!v"
                    >
                      <template #title>
                        <b-icon
                          v-show="hasSettingsEmptyFields"
                          variant="danger"
                          icon="exclamation-triangle"
                          class="mr-2"
                          small
                        >
                        </b-icon>
                        Настройки критериев
                      </template>
                      <settings
                        :project="project"
                        :load="loadSettings"
                        @has-empty-fields="setSettingsAreEmpty"
                        @has-changed="setSettingsHaveChanged"
                        @tab="switchTab"
                      />
                    </b-tab>

                    <b-tab
                      id="call-settings-tab"
                      lazy
                      :disabled="!projectExists"
                      @update:active="(v) => loadCallSettings = !!v"
                    >
                      <template #title>
                        <b-icon
                          v-show="hasCallSettingsEmptyFields"
                          variant="danger"
                          icon="exclamation-triangle"
                          class="mr-2"
                          small
                        >
                        </b-icon>
                        Настройка импорта звонков
                      </template>
                      <call-settings
                        :project="project"
                        :load="loadCallSettings"
                        @has-empty-fields="setCallSettingsAreEmpty"
                        @has-changed="setCallSettingsHaveChanged"
                      />
                    </b-tab>
                  </b-tabs>
                </div>
              </div>
            </div>
          </validation-observer>
        </div>
      </div>
    </article>
  </div>
</template>
<style scoped>
label {
  white-space: nowrap;
}
</style>

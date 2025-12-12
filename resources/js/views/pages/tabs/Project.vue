<script>
import moment from 'moment';
import VInput from "../../../components/input";
import VSelect from "../../../components/vue-select";
import Swal from "sweetalert2";
import "../../../validator";
import "@artamas/vue-select/src/scss/vue-select.scss";
import {
  isEmpty,
  clone,
  find,
  debounce,
  some,
  isEqual,
} from "lodash";
import VContent from "../../../components/VContent";
import js_settings from '../../../js_settings';
import { mapGetters, mapMutations } from 'vuex';

moment.locale('ru');

export default {
  name: "Project",
  components: {
    VContent,
    VInput,
    VSelect,
  },
  data() {
    return {
      isFormLoading: false,
      uploadRange: [],
      projectFields: [
        "company_id",
        "name",
        "rating",
        "pm",
        "senior",
        "assessors",
        "integration_domain",
        "googleSpreadsheet",
        "google_spreadsheet_id",
        "googleConnection",
      ],
      editableProject: {
        company_id: null,
        name: null,
        pm: null,
        senior: null,
        assessors: null,
        integration_domain: null,
        project_type: null,
        googleSpreadsheet: null,
        google_spreadsheet_id: null,
        googleConnection: null,
        criteria: [],
        settings: [],
        rating: null,
        date_start: this.getDateStart(),
        total_time_limit: null,
        permissible_error: 10,
        tasks_generation_status: false,
      },
      dicts: {
        projectManagers: [],
        seniorManagers: [],
        assessors: [],
        ratings: [],
        integrations: [],
        companies: [],
      },
      company: {},
      companySearch: '',
      isLoading: false,
      checkingGoogleSheet: false,
      projectGoogleConnection: false,
      projectIntegration: false,
      checkingIntegration: false,
      companiesLoading: false,
      isSavingGenerateTaskStatus: false,
    };
  },
  computed: {
    ...mapGetters('project', [
      'project',
    ]),
    companyId() {
      return this.project?.company_id || this.$route.params.companyId;
    },
    projectId() {
      return this.$route.params.projectId;
    },
    googleChecked() {
      return this.projectGoogleConnection;
    },

    integrationChecked() {
      return this.projectIntegration;
    },

    hasEmptyFields() {
      return some(this.projectFields, (field) => {
        return typeof this.editableProject[field] === 'object' ?
            isEmpty(this.editableProject[field]) :
            !this.editableProject[field]
      });
    },

    hasChanged() {
      return some(this.projectFields, (field) => {
        return !isEqual(this.editableProject[field], this.project[field]);
      });
    },

    integrationDomainValidator() {
      if (this.editableProject.project_type) {
        let integration = find(
          this.dicts.integrations,
          (item) => item.value === this.editableProject.project_type
        );
        if (integration) return integration.validator;
      }
      return "";
    },
    tasksGenerationStatus() {
      return !!this.editableProject.tasks_generation_status;
    }
  },

  watch: {
    companySearch: debounce(function () {
      this.loadCompanies(this.companySearch)
    }, 500),
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
  async mounted() {
    this.isLoading = true;
    await Promise.all([
      this.$http.get("/api/roles/assessors").then((response) => {
        if (response.status === 200) {
          this.dicts.assessors = response.data.assessors;
        }
      }),
      this.$http.get("/api/roles/senior").then((response) => {
        if (response.status === 200) {
          this.dicts.seniorManagers = response.data.senior;
        }
      }),
      this.$http.get("/api/roles/pm").then((response) => {
        if (response.status === 200) {
          this.dicts.projectManagers = response.data.pm;
        }
      }),
      this.$http.get("/api/dictionaries/ratings").then((response) => {
        this.dicts.ratings = response.data.ratings;
      }),
      this.$http.get("/api/dictionaries/integrations").then((response) => {
        this.dicts.integrations = response.data.integrations;
      }),
    ])
    await this.getData();
    this.checkIntegration();
    this.checkGoogleConnection();
  },
  methods: {
    ...mapMutations('project', [
      'SET_PROJECT'
    ]),
    disabledDate(date) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      return date > today || date < new Date(today.getTime() - 7 * 24 * 3600 * 1000);
    },
    async callsUploadHandler(){
        if(!this.uploadRange[0]){
            this.uploadRange = []
            return
        }
        this.isFormLoading = true
        this.$http.post(
        `/api/projects/${this.projectId}/download-calls`,
        {
            date_start: moment(this.uploadRange[0], 'DD.MM.YYYY').format('YYYY-MM-DD'),
            date_end: moment(this.uploadRange[1], 'DD.MM.YYYY').format('YYYY-MM-DD'),
         }
        )
        .then(response => {
          if (response.status !== 200) {
            throw new Error(response.statusText)
          }
          Swal.fire({
            title: "",
            html: response.data.message,
            icon: "success",
            showConfirmButton: true,
            timer: 3000,
        });
        })
        .catch(error => {
          Swal.fire({
            title: "",
            html: error.data.message,
            icon: "error",
            showConfirmButton: true,
            timer: 3000,
            });
        })
        .finally(()=>{
            this.isFormLoading = false
            this.closeCallsUploadModal()
        })
    },
    closeCallsUploadModal(){
      $('#uploadCalls').modal('hide');
    },
    showUploadModal(){
      $('#uploadCalls').modal('show');
    },
    getDateStart(date) {
      return date ? moment(date).format(js_settings.formats.date) : moment().format(js_settings.formats.date);
    },
    async getData() {
      if (this.companyId) {
        await this.$http
          .get(`/api/companies/${this.companyId}`)
          .then((response) => {
            this.company = response.data;
          })
          .catch((error) => {
            if (error && error.status === 422) {
              Swal.fire("Компания не найдена", "", "error");
              this.$router.push({ name: "companies.list" });
            }
          });
      } else {
        await this.loadCompanies();
      }

      this.mapEditableProject();

      this.isLoading = false;
    },
    mapEditableProject() {
      if (this.project) {
        this.editableProject = {
          ...this.editableProject,
          ...this.project,
        };
        let project = {};
        if (this.dicts.projectManagers.length === 1) {
          project.pm = this.dicts.projectManagers[0];
        }
        this.editableProject.company_id =
          this.companyId || this.editableProject.company_id;
        this.editableProject.date_start = moment(this.editableProject.date_start).format(js_settings.formats.date)
      }
    },
    create(update) {
      this.$refs.projectTab.reset();
      this.$refs.projectTab.validate().then(async (result) => {
        if (result) {
          this.isLoading = true;
          await Promise.all([
            this.checkIntegration(),
            this.checkGoogleConnection()
          ])
          let data = clone(this.editableProject);
          data.company_id = this.companyId || data.company_id;
          data.assessors = data.assessors.map((it) => it.value);
          data.pm = data.pm.value;
          data.senior = data.senior.value;
          data.rating = data.rating.value;
          data.date_start = moment(data.date_start, js_settings.formats.date).format('YYYY-MM-DD')
          if (update) {
            this.$http
              .put("/api/projects/" + this.projectId + "", data)
              .then((response) => {
                if (response.status === 200) {
                  Swal.fire({
                    title: "",
                    html: response.data.message,
                    icon: "success",
                    showConfirmButton: true,
                    timer: 3000,
                  });
                  this.SET_PROJECT(response.data.project);
                  this.mapEditableProject();
                  this.isLoading = false;
                }
              })
              .catch((response) => {
                if (response.status === 422) {
                  this.$refs.projectTab.setErrors(response.data.fields);
                } else {
                  Swal.fire({
                    title: "",
                    html: response.data.message,
                    icon: "error",
                    showConfirmButton: true,
                    timer: 3000,
                  });
                }
                this.isLoading = false;
              });
          } else {
            this.$http
              .post("/api/project", data)
              .then((response) => {
                if (response.status === 200) {
                  this.$router.push("/projects/" + response.data.project.id);
                  Swal.fire({
                    title: "",
                    html: response.data.message,
                    icon: "success",
                    showConfirmButton: true,
                    timer: 3000,
                  });
                  this.isLoading = false;
                }
              })
              .catch((response) => {
                if (response.status === 422) {
                  this.$refs.projectTab.setErrors(response.data.fields);
                } else {
                  Swal.fire({
                    title: "",
                    html: response.data.message,
                    icon: "error",
                    showConfirmButton: true,
                    timer: 3000,
                  });
                }
                this.isLoading = false;
              });
          }
        }
      });
    },

    checkIntegration() {
      return new Promise((resolve) => {
        if (!this.editableProject.integration_domain) resolve(true);
        this.$refs.projectType.validate().then((result) => {
          if (result) {
            this.checkingIntegration = true;
            this.$http
              .post("/api/integration/check", {
                project_type: this.editableProject.project_type,
                integration_domain: this.editableProject.integration_domain,
              })
              .then(() => {
                this.projectIntegration = true;
                this.checkingIntegration = false;
                resolve(true);
              })
              .catch((error) => {
                if (error.status === 422) {
                  this.$refs.projectTab.setErrors(error.data.fields);
                }
                this.projectIntegration = false;
                this.checkingIntegration = false;
                resolve(false);
              });
          }
        });
      });
    },

    checkGoogleConnection() {
      if (
        !this.editableProject.googleConnection ||
        !this.editableProject.googleSpreadsheet ||
        !this.editableProject.google_spreadsheet_id
      )
        return;
      this.checkingGoogleSheet = true;
      this.$http
        .post("/api/google/check", {
          google_connection: this.editableProject.googleConnection,
          google_spreadsheet: this.editableProject.googleSpreadsheet,
          google_spreadsheet_id: this.editableProject.google_spreadsheet_id,
        })
        .then(() => {
          this.projectGoogleConnection = true;
          this.checkingGoogleSheet = false;
        })
        .catch((error) => {
          if (error.status === 422) {
            this.$refs.projectTab.setErrors(error.data.fields);
          }
          this.projectGoogleConnection = false;
          this.checkingGoogleSheet = false;
          return false;
        });
    },

    handleGoBack() {
      return this.companyId
        ? {
            name: "companies.projects.list",
            params: { companyId: this.companyId },
          }
        : { name: "projects.list" };
    },

    fetchCompanies(search) {
      this.companySearch = search
    },

    setProjectCompany(companyId) {
      this.editableProject.integration_domain = this.editableProject.integration_domain ||
        find(this.dicts.companies, (item) => item.id === companyId)?.domain ||
        null;
    },

    async loadCompanies(search) {
        const url = search ? `/api/companies?limit=20&search=${search}` : `/api/companies?limit=20`
        this.companiesLoading = true
        await this.$http.get(url).then((response) => {
            if (response.status === 200) {
                this.dicts.companies = response.data.companies;
            }
            this.companiesLoading = false
        });
    },

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    confirmSaveGenerationStatus() {
      if (this.tasksGenerationStatus) {
        Swal.fire({
          title: 'Вы действительно хотите остановить генерацию заданий?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#F64E60',
          confirmButtonText: 'Остановить',
          cancelButtonText: `Отмена`,
        }).then((result) => {
          if (result.isConfirmed) {
            this.saveTasksGenerationStatus()
          }
        })
      } else {
        this.saveTasksGenerationStatus()
      }
    },
    confirmRedistribution() {
      if (this.tasksGenerationStatus) {
        Swal.fire({
          title: 'Вы действительно хотите перераспределять звонки?',
          input: 'text',
          showCancelButton: true,
          confirmButtonColor: '#F64E60',
          confirmButtonText: 'Да',
          cancelButtonText: `Отмена`,
          inputValidator: (value) => {
            if (!value) {
              return 'Поле обязательно для заполнения'
            }

            if (!moment(value, 'DD.MM.YYYY', true).isValid()) {
              return 'Формат даты неправильный'
            }
          },
          preConfirm: (startDate) => {
            Swal.showLoading()
            return this.sendRedistribution(moment(startDate, 'DD.MM.YYYY').format('YYYY-MM-DD'))
          },
          allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: `${result.value.message}`,
              timer: 1500
            })
          }
        })
      } else {
        this.saveTasksGenerationStatus()
      }
    },
    saveTasksGenerationStatus() {
      this.isSavingGenerateTaskStatus = true
      this.$http.post(`/api/projects/${this.project.id}/tasks-generation-status`)
        .then(() => {
          this.editableProject.tasks_generation_status = !this.editableProject.tasks_generation_status;
          this.isSavingGenerateTaskStatus = false
        })
        .catch((error) => {
          Swal.fire({
            title: '',
            html: error.data.message,
            icon: 'error',
            showConfirmButton: false,
            timer: 3000
          })
          this.isSavingGenerateTaskStatus = false
        })
    },
    sendRedistribution(startDate) {
      return this.$http.post(
        `/api/projects/${this.project.id}/redistribute-calls`,
        { start_date: startDate }
        )
        .then(response => {
          if (response.status !== 200) {
            throw new Error(response.statusText)
          }
          return response.data
        })
        .catch(error => {
          Swal.showValidationMessage(
            `${error.data.message}`
          )
        })
    },
    confirmLoadIntegrationCalls() {
      Swal.fire({
          title: 'Вы действительно хотите выгрузить звонки вручную?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#F64E60',
          confirmButtonText: 'Да',
          cancelButtonText: `Отмена`,
          preConfirm: () => {
            Swal.showLoading()
            return this.sendLoadIntegrationCalls()
          },
          allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: `${result.value.message}`,
              timer: 1500
            })
          }
        })
    },
    sendLoadIntegrationCalls() {
      return this.$http.post(
        `/api/integration/calls`,
        {
          project_type: this.project.project_type,
          integration_domain: this.editableProject.integration_domain,
        }
        )
        .then(response => {
          if (response.status !== 200) {
            throw new Error(response.statusText)
          }
          return response.data
        })
        .catch(error => {
          Swal.showValidationMessage(
            `${error.data.message}`
          )
        })
    },
    onHiddenGenerateTaskStatus() {
      //
    },
  },
};
</script>

<template>
  <b-overlay :show="isLoading" rounded="sm" spinner-variant="primary">
    <validation-observer ref="projectTab">
      <v-content>
        <div class="col-md-6">
          <div v-if="!companyId" class="form-group row">
            <label class="col-md-3 col-form-label text-md-left"
              >Компания</label
            >
            <div class="col-md-8">
              <v-select
                id="company"
                v-model="editableProject.company_id"
                rules="required"
                name="Компания"
                label="name"
                :filterable="false"
                :loading="companiesLoading"
                :options="dicts.companies"
                :reduce="(o) => o.id"
                @search="fetchCompanies"
                @input="setProjectCompany"
              />
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-left"
              >Название</label
            >
            <div class="col-md-8">
              <v-input
                id="name"
                type="text"
                rules="required"
                name="Название"
                v-model="editableProject.name"
                maxlength="50"
              ></v-input>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-left"
              >Тип проекта</label
            >
            <div class="col-md-8">
              <validation-observer ref="projectType">
                <v-select
                  id="project_type"
                  :options="dicts.integrations"
                  rules="required|required_if:integration_domain,"
                  v-model="editableProject.project_type"
                  name="Тип проекта"
                  :reduce="(o) => o.value"
                />
              </validation-observer>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-left"
              >Тип оценки</label
            >
            <div class="col-md-8">
              <v-select
                :options="dicts.ratings"
                rules="required"
                v-model="editableProject.rating"
                id="rating"
                name="Тип оценки"
              >
              </v-select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-left">ПМ</label>
            <div class="col-md-8">
              <v-select
                :options="dicts.projectManagers"
                rules="required"
                v-model="editableProject.pm"
                id="pm"
                name="ПМ"
              >
              </v-select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-left">Стас</label>
            <div class="col-md-8">
              <v-select
                :options="dicts.seniorManagers"
                rules="required"
                v-model="editableProject.senior"
                id="senior"
                name="Стас"
              >
              </v-select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-left">Асессоры</label>
            <div class="col-md-8">
              <v-select
                :options="dicts.assessors"
                rules="required"
                v-model="editableProject.assessors"
                id="assessors"
                name="Асессоры"
                multiple
              >
              </v-select>
            </div>
          </div>

          <b-form-group v-if="projectId" class="mb-4">
            <b-overlay
              :show="isSavingGenerateTaskStatus"
              rounded
              opacity="0.6"
              spinner-small
              spinner-variant="primary"
              class="d-inline-block"
              @hidden="onHiddenGenerateTaskStatus"
            >
              <b-button
                :variant="(tasksGenerationStatus ? 'secondary' : 'light-info')"
                :disabled="isSavingGenerateTaskStatus"
                @click="confirmSaveGenerationStatus"
              >
                {{ tasksGenerationStatus ? 'Остановить выгрузку' : 'Начать выгрузку' }}
              </b-button>
            </b-overlay>
          </b-form-group>

          <b-form-group v-if="projectId" class="mb-4">
            <b-overlay
              rounded
              opacity="0.6"
              spinner-small
              spinner-variant="primary"
              class="d-inline-block"
            >
              <b-button
                variant="light-info"
                @click="confirmRedistribution"
              >
                Перераспределять звонки
              </b-button>
            </b-overlay>
          </b-form-group>

          <b-form-group v-if="projectId" class="mb-4">
            <b-overlay
              rounded
              opacity="0.6"
              spinner-small
              spinner-variant="primary"
              class="d-inline-block"
            >
              <b-button
                variant="light-info"
                @click="confirmLoadIntegrationCalls"
              >
                Выгрузить сегодняшние звонки
              </b-button>
            </b-overlay>
          </b-form-group>
          <b-form-group v-if="projectId" class="mb-4">
            <b-overlay
              rounded
              opacity="0.6"
              spinner-small
              spinner-variant="primary"
              class="d-inline-block"
            >
              <b-button
                variant="light-info"
                @click="showUploadModal"
              >
                Выгрузка звонков за
              </b-button>
            </b-overlay>
          </b-form-group>
        </div>
        <div class="col-md-6">
          <legend>Данные Интеграции</legend>
          <div class="form-group row">
            <label class="col-sm-12 col-md-3 col-form-label text-md-left"
              >Домен</label
            >
            <div class="col-sm-10 col-md-7">
              <v-input
                id="integration_domain"
                v-model="editableProject.integration_domain"
                type="text"
                name="Домен интеграции"
                :rules="`required`"
                placeholder="Домен интеграции"
                maxlength="50"
                @input="projectIntegration = false"
              ></v-input>
            </div>
            <div>
              <b-spinner v-if="checkingIntegration"></b-spinner>
              <template v-else>
                <i
                  v-show="integrationChecked"
                  class="fa fa-check-square fa-lg"
                  aria-hidden="true"
                  style="color: #38c172; margin-top: 12px"
                ></i>
                <b-button
                  v-show="!integrationChecked"
                  variant="light"
                  size="sm"
                  class="btn-sm"
                  @click="checkIntegration"
                >
                  <i
                    class="fa fa-window-close fa-lg"
                    aria-hidden="true"
                    style="color: #c14d38"
                  ></i>
                </b-button>
              </template>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-md-3 col-form-label text-md-left">
              Дата старта
            </label>
            <div class="col-sm-10 col-md-7">
              <validation-provider
                tag="div"
                rules="required"
                vid="editableProject.date_start"
                name="Дата начала"
                v-slot="validationContext"
              >
                <date-picker
                  v-model="editableProject.date_start"
                  :input-class="[
                  'form-control date-start',
                  {
                    'is-valid': !validationContext.errors.length,
                    'is-invalid': validationContext.errors.length
                  }]"
                  class="w-100"
                  lang="ru"
                  value-type="format"
                  type="date"
                  format="DD.MM.YYYY"
                />
              </validation-provider>
            </div>
          </div>
          <legend>Данные Google Sheets</legend>
          <div class="form-group row">
            <label class="col-sm-12 col-md-3 col-form-label text-md-left">Таблица</label>
            <div class="col-sm-10 col-md-7">
              <v-input
                id="google_spreadsheet"
                type="text"
                :rules="projectId ? '' : 'required'"
                v-model="editableProject.googleSpreadsheet"
                placeholder="Название Google таблицы"
                name="Название Google таблицы"
                maxlength="50"
                @input="projectGoogleConnection = false"
              ></v-input>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-md-3 col-form-label text-md-left">Идентификатор файла таблицы</label>
            <div class="col-sm-10 col-md-7">
              <v-input
                id="google_spreadsheet_id"
                type="text"
                :rules="projectId ? '' : 'required'"
                v-model="editableProject.google_spreadsheet_id"
                placeholder="Идентификатор файла Google таблицы"
                name="Идентификатор файла Google таблицы"
                maxlength="64"
                @input="projectGoogleConnection = false"
              ></v-input>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-md-3 col-form-label text-md-left"
              >Лист</label
            >
            <div class="col-sm-10 col-md-7">
              <v-input
                id="google_connection"
                v-model="editableProject.googleConnection"
                type="text"
                name="Название Google листа"
                :rules="projectId ? '' : 'required'"
                placeholder="Название Google листа"
                maxlength="50"
                @input="projectGoogleConnection = false"
              ></v-input>
            </div>
            <div>
              <b-spinner v-if="checkingGoogleSheet"></b-spinner>
              <template v-else>
                <i
                  v-show="googleChecked"
                  class="fa fa-check-square fa-lg"
                  aria-hidden="true"
                  style="color: #38c172; margin-top: 12px"
                ></i>
                <b-button
                  v-show="!googleChecked"
                  variant="light"
                  size="sm"
                  class="btn-sm"
                  @click="checkGoogleConnection"
                >
                  <i
                    class="fa fa-window-close fa-lg"
                    aria-hidden="true"
                    style="color: #c14d38"
                  ></i>
                </b-button>
              </template>
            </div>
          </div>
          <legend>Общий объём времени на проект</legend>
          <div class="form-group row">
            <label class="col-sm-12 col-md-3 col-form-label text-md-left">
              Минуты
            </label>
            <div class="col-sm-10 col-md-7">
              <v-input
                id="total_time_limit"
                v-model="editableProject.total_time_limit"
                type="number"
                name="Общий объем времени"
                rules="integer|between:0,86400"
                min="0"
                placeholder="Общий объем времени"
                maxlength="50"
              ></v-input>
            </div>
          </div>
          <div class="row">
            <validation-provider
              class="col-10"
              name="Допустимое превышение"
              rules="required|integer|between:10,100"
              vid="permissible_error"
              v-slot="validationContext"
            >
              <b-form-group
                label="Допустимое превышение (%)"
                label-cols="12"
                label-cols-sm="12"
                label-cols-md="7"
                label-cols-lg="8"
                style="margin-bottom: unset;"
                :invalid-feedback="validationContext.errors[0]"
              >
                <b-form-input
                  id="permissible_error"
                  v-model="editableProject.permissible_error"
                  name="Допустимое превышение"
                  type="number"
                  min="10"
                  maxlength="3"
                  placeholder="Допустимое превышение"
                  :state="getValidationState(validationContext)"
                />
              </b-form-group>
            </validation-provider>
          </div>
        </div>
      </v-content>
      <b-row no-gutters class="card-footer justify-content-between">
        <router-link tag="a" class="btn btn-light" :to="handleGoBack()">
          <i class="fa fa-arrow-left"></i>
          Список проектов
        </router-link>
        <button
          v-if="projectId"
          class="btn btn-primary"
          type="button"
          value="return"
          @click="create(true)"
        >
          <i class="fa fa-save"></i>
          Сохранить
        </button>
        <button
          v-else
          class="btn btn-primary"
          type="button"
          value="return"
          @click="create(false)"
        >
          <i class="fa fa-save"></i>
          Создать проект
        </button>
      </b-row>
    </validation-observer>
    <div id="uploadCalls" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 495px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title tx-left pd-x-30">Выгрузка звонков</h5>
                        <button type="button" class="close" @click="closeCallsUploadModal" data-dismiss="modal" aria-label="Close" style="padding: 1.5rem 1.75rem;margin: -1.5rem -1.75rem -1.5rem auto;">
                            <i class="flaticon2-cross" aria-hidden="true"></i>
                        </button>
                    </div>
                    <b-overlay :show="isFormLoading" rounded="sm" spinner-variant="primary">
                        <!-- <validation-observer  ref="observer" tag="form"> -->
                        <div class="modal-body tx-left pd-y-20 pd-x-20">
                            <div class="form-group row">
                                <label class="mr-2 col-form-label text-md-right">Выберите дату</label>
                                <validation-provider
                                    ref="callsUpload"
                                    tag="div"
                                    rules="required"
                                    :vid="uploadRange[0]"
                                    v-slot="validationContext"
                                >
                                    <date-picker
                                    v-model="uploadRange"
                                    :input-class="[
                                    'form-control date-start',
                                    {
                                        'is-valid': !validationContext.errors.length,
                                        'is-invalid': validationContext.errors.length
                                    }]"
                                    :disabled-date="disabledDate"
                                    range
                                    class="w-100"
                                    lang="ru"
                                    value-type="format"
                                    type="date"
                                    format="DD.MM.YYYY"
                                    />
                                </validation-provider>
                            </div>
                        </div><!-- modal-body -->
                        <div class="modal-footer tx-right pd-y-20 pd-x-20">
                            <button type="button" class="btn btn-danger" @click="closeCallsUploadModal">Отмена</button>
                            <button type="button" class="btn btn-success" @click="callsUploadHandler">Выгрузить</button>
                        </div><!-- modal-footer -->
                    <!-- </validation-observer> -->
                    </b-overlay>
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- modal -->
  </b-overlay>
</template>

<style lang="scss" scoped>
.date-start {
  ::v-deep &.is-valid, ::v-deep &.is-invalid {
    padding-right: calc(2.6em + 1.3rem) !important;
    background-position: right calc(1.4em + 0.725rem) center;
  }
}
</style>

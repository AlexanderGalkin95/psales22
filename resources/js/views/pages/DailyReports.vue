<script>
import Table from '../../components/table/Table.vue'
import VInput from '../../components/input.vue'
import "../../validator";
import Swal from "sweetalert2"
import cloneDeep from "lodash/cloneDeep"
import { mapGetters } from "vuex"
import timezonesJson from '../../../lang/timezones.json'
import moment from 'moment';
import { nextTick } from 'vue'

export default {
  name: "DailyReports",
  components: { Table, VInput },
  data() {
    return {
      project: {
        name: '',
        google_spreadsheet_id: '',
        telegram: '',
        report_time: '17:00',
        managers: [{ name: '' }],
        timezone: 'Europe/Moscow',
        include_holidays: false,
        period: [],
        sending_period: [],
        sending_include_holidays: false,
        is_active: true,
        override_report_sent_at: null
      },
      columns: [
        { name: 'name', title: 'НАЗВАНИЕ', format: 'text', width: '20%', sortable: true, searchable: true, align: "left" },
        { name: 'telegram', title: 'ТЕЛЕГРАМ', format: 'text', width: '10%', sortable: true, searchable: true, align: 'left' },
        { name: 'managers', title: 'Менеджеры', format: 'text', width: '10%', sortable: false, searchable: true, align: 'left',
          display_callback: (col, item) => {
            return item['managers'].map((it) => it.name).join(', ')
          }
        },
        { name: 'report_time', title: 'Время', format: 'text', width: '10%', sortable: true, searchable: true, align: 'left' },
        { name: 'table_switch', type: "tableSwitch", class: "", id: "table-switch", title: "Включено", width: "2%", sortable: true, align: 'center' },
        { type: 'buttons', align: 'left', title: '', width: '3%',
          items: [
            { type: "button", class: "btn-success", id: "edit_project", title: "", icon: "fa-edit", width: "1%", tooltip: "Редактировать" },
            {
              type: "button",
              class: "btn-danger",
              id: "delete_project",
              title: "",
              icon: "fa-trash",
              width: "1%",
              tooltip: "Удалить",
              show: true,
              show_callback: () => ['sa', 'pm'].includes(this.user_role),
            },
          ]
        }
      ],
      filters: {
        name: '',
        telegram: '',
        report_time: '',
        table_switch: '',
        managers: '',
      },
      settings: {
        edit_project_callback: (col, item, $this, event) => {
          $this.$emit("edit-project", item);
        },
        delete_project_callback: function (col, item, $this) {
          const msg = 'Вы уверены, что хотите удалить проект: <b>' + item.name + '</b>?';
          Swal.fire({
            title: 'Удалить проект',
            html: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F64E60',
            confirmButtonText: 'Удалить',
            cancelButtonText: `Отмена`,
            preConfirm: () => {
              return $this.$http.delete(`/api/google-projects/${item.id}/destroy`)
                .catch((error) => {
                  return error
                })
            }
          })
          .then((result) => {
            if (result.isConfirmed && result.value.status === 200) {
              Swal.fire(result.value.data.message, '', 'success')
              $this.refreshImmediately()
              } else if (result.value && result.value.status) {
                if (result.value.status === 422) {
                  Swal.fire(result.value.data.fields[0][0], '', 'error')
                } else {
                  Swal.fire(result.value.data.message, '', 'error')
                }
              }
          });
        },
      },
      dicts: {
        results: []
      },
      loaded: true,
      refreshTable: '',
      isFormLoading: false,
      timezones: timezonesJson,
      days: [
        { text: 'M', short: 'Пн', value: 1},
        { text: 'T', short: 'Вт', value: 2 },
        { text: 'W', short: 'Ср', value: 3 },
        { text: 'T', short: 'Чт', value: 4 },
        { text: 'F', short: 'Пт', value: 5 },
        { text: 'S', short: 'Сб', value: 6 },
        { text: 'S', short: 'Вс', value: 7 }
      ],
      selectedDays: [],
      isProjectEdited: false,
      timeErrorState: null
    }
  },
  computed: {
    ...mapGetters(["currentUser"]),
    current_user() {
      return this.currentUser;
    },
    user_role() {
      return this.current_user.role_name;
    },
    isPeriodDate() {
      return this.project.period.length === 1
        && typeof this.project.period[0] === 'string'
        && moment(this.project.period[0]).isValid();
    },
    periodText() {
      moment.locale('ru')
      return this.isPeriodDate
        ? moment(`${this.project.period[0]} ${this.project.report_time}`).format('ddd, D MMM YYYY в LT')
        : `Каждые ${ this.project.period.map((it) => it.short).join(',') }`
    },
  },
  methods: {
    setData() {
      this.loaded = true;
    },
    close() {
      this.$bvModal.hide('create_project');
      this.timeErrorState = null;
    },
    handleCreateProject() {
      this.$bvModal.show('create_project');
    },
    editProject(item) {
      this.$bvModal.show('create_project');
      this.isFormLoading = true;
      this.$http.get(`/api/google-projects/${item.id}`)
        .then((response) => {
          const isDate = response.data.period.length === 1
            && isNaN(response.data.period[0])
            && moment(response.data.period[0]).isValid()
          if (!isDate) {
            response.data.period = this.days.filter((it) => response.data.period.includes(it.value))
          }
          if (!response.data.sending_period) {
            response.data.sending_period = []
          } else {
            response.data.sending_period = this.days.filter((it) => response.data.sending_period.includes(it.value))
          }
          this.project = response.data;
        })
        .finally(() => {
          this.isFormLoading = false;
        })
    },
    reloadTable() {
      this.refreshTable = Math.random().toString(36).substring(1, 10);
    },
    sendReport() {
      this.$refs.observer.validate().then((result) => {
        if (!result) return
        this.isFormLoading = true
        this.$http.post(`/api/google-projects/${this.project.id}/send-report`, this.project)
          .then((response) => {
            if (response.status === 200) {
              Swal.fire({
                title: "Ок",
                html: response.data.message,
                icon: "success",
                showConfirmButton: true,
                timer: 3000,
              });
              this.close();
            }
          })
          .catch((response) => {
              if (response.status === 422) {
                this.$refs.observer.setErrors(response.data.fields)
                this.loaded = true;
              } else {
                Swal.fire({
                  title: "Ошибка",
                  html: response.data.message,
                  icon: "error",
                  showConfirmButton: true,
                  timer: 3000,
                });
                this.loaded = true;
              }
          })
          .finally(() => {
            this.isFormLoading = false;
          });
      })
    },
    saveProject() {
      this.$refs.observer.validate().then((result) => {
        if (!result) return
        this.isFormLoading = true
        const payload = cloneDeep(this.project)
        if (!this.isPeriodDate) {
          payload.period = payload.period
            .filter((it) => typeof it !== 'string')
            .map((it) => it.value)
        }
        payload.sending_period = payload.sending_period.map(item => item.value)
        const handler = payload.id
          ? this.$http.put(`/api/google-projects/${payload.id}`, payload)
          : this.$http.post('/api/google-projects', payload)
        handler.then((response) => {
            if (response.status === 200) {
              this.reloadTable();
              Swal.fire({
                title: "",
                html: response.data.message,
                icon: "success",
                showConfirmButton: true,
                timer: 3000,
              });
              this.$nextTick(() => {
                this.close();
              })
            }
          })
          .catch((response) => {
            if (response.status === 422) {
              this.$refs.observer.setErrors(response.data.fields)
              this.loaded = true;
            } else {
              Swal.fire({
                title: "",
                html: response.data.message,
                icon: "error",
                showConfirmButton: true,
              });
              if (response.data?.error_type === 'time') {
                this.timeErrorState = false;
              }
              this.loaded = true;
            }
          })
          .finally(() => {
            this.isFormLoading = false;
          });
      })
    },
    errorHandler() {
      Swal.fire({
        title: "",
        html: 'Что-то пошло не так!',
        icon: "error",
        showConfirmButton: true,
        timer: 3000,
      });
    },
    addManager() {
      this.project.managers.push({ name: '' });
    },
    removeManager(index) {
      this.project.managers.splice(index, 1);
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    resetForm() {
      this.project = {
        name: '',
        telegram: '',
        report_time: '17:00',
        timezone: 'Europe/Moscow',
        managers: [{ name: '' }],
        include_holidays: false,
        period: [],
        sending_period: [],
        sending_include_holidays: false,
        is_active: true,
        override_report_sent_at: null,
      };
      nextTick(() => this.isProjectEdited = false)
    },
    getVariant(day, type = '') {
      if (type === 'send') {
        return this.project.sending_period.map((it) => it.value).includes(day.value) ? 'outline-success' : '';
      }
      return this.project.period.map((it) => it.value).includes(day.value) ? 'outline-success' : '';
    },
    changeProjectActive(project) {
      this.$http.put(`/api/google-projects/${project.id}`, project)
          .then((response) => {
            if (response.status === 200) {
              this.$toast.open({
                message: `Активность проекта ${project.name} обновлена успешно`,
                type: 'success',
                position: 'bottom-left',
              });
            }
          })
          .catch(() => {
            this.$toast.open({
              message: `Не удалось обновить данные по проекту ${project.name}`,
              type: 'error',
              position: 'bottom-left',
            });
            this.reloadTable();
          })
    }
  },
  mounted() {
    this.selectedDays = this.days.slice(0, 5);
  },
  watch: {
    project: {
      handler: function() {
        if (!this.isFormLoading) {
          this.isProjectEdited = true
        }
      },
      deep: true
    }
  }
}
</script>

<template>
  <div class="row m-0">
    <div style="margin: 0 0 15px 19px">
      <div style="display: inline-block">
        <h3>
          <i class="flaticon-analytics" aria-hidden="true"></i>
          Проекты для ЕО
        </h3>
      </div>
    </div>
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div v-show="!loaded" v-if="!loaded" class="card card-custom">
        <div class="card-body loading" style="padding-top: 110px; min-height: 350px">
          <span class="fa fa-spinner fa-3x fa-spin" style="position: absolute; top: 99px; left: 50%"></span>
        </div>
      </div>
      <div v-show="loaded" class="card card-custom">
        <div>
          <div v-if="user_role === 'sa' || user_role === 'pm' || user_role === 'senior_assessor'" class="card-header text-left">
            <button id="process" class="btn btn-primary" type="button" value="return" @click="handleCreateProject">
              <i class="flaticon2-plus"></i>
              Создать
            </button>
          </div>
          <div class="card-body">
            <Table
              id="google_projects"
              data_prop_name="google_projects"
              :data_src="`/api/google-projects`"
              :columns="columns"
              :filters="filters"
              :settings="settings"
              :dicts="dicts"
              :reload="refreshTable"
              @loaded="setData"
              @edit-project="editProject"
              @active-switch-change="changeProjectActive($event)"
            />
          </div>
        </div>
      </div>
    </article>
    <b-modal
        id="create_project"
        ok-title="Сохранить"
        size="lg"
        ok-only
        scrollable
        body-class="no-body-class"
        :title-html="project.id ? 'Редактирование проекта' : 'Добавление проекта'"
        no-close-on-backdrop
        @ok.prevent="saveProject"
        @hidden="resetForm"
    >
      <template #modal-header-close>
        <button
          type="button"
          class="close"
          data-dismiss="modal"
          aria-label="Close"
          style="padding: 1.5rem 1.75rem;margin: -1.5rem -1.75rem -1.5rem auto;"
          @click="close"
        >
          <i class="flaticon2-cross" aria-hidden="true"></i>
        </button>
      </template>
        <b-overlay :show="isFormLoading" rounded="sm" spinner-variant="primary">
            <validation-observer ref="observer" tag="form">
              <div class="modal-body tx-left pd-y-20 pd-x-20">

                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-md-right">Название (оно же название таблицы)</label>
                  <div class="col-md-9">
                    <v-input id="name" type="text" rules="required" name="Имя" v-model="project.name" maxlength="50"></v-input>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-md-right">Идентификатор файла таблицы</label>
                  <div class="col-md-9">
                    <v-input id="google_spreadsheet_id" type="text" rules="required" name="Имя" v-model="project.google_spreadsheet_id" maxlength="64"></v-input>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-md-right">Время отправки</label>
                  <div class="col-md-9">
                    <b-form-timepicker
                        id="date"
                        rules="required"
                        name="Время отправки"
                        v-model="project.report_time"
                        locale="ru"
                        label-close-button="Выбрать"
                        :state="timeErrorState"
                        @input="timeErrorState = true"
                    />
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-md-right">Телеграм</label>
                  <div class="col-md-9">
                    <v-input id="telegram" v-model="project.telegram" name="Телеграм" type="text" maxlength="50"
                      :lefticon="'fa-at'" rules="regex:^[^@]">
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

                <div class="form-group row mb-0">
                  <label class="col-md-3 col-form-label text-md-right">Менеджер</label>
                  <div class="col-md-9">
                    <div v-for="(manager, index) in project?.managers"
                      :key="index"
                      class="form-group"
                    >
                      <b-row>
                        <div class="input-group">
                          <div class="mb-1 col-md-9">
                            <v-input
                              :id="`manager_${index + 1}`"
                              v-model="manager.name"
                              type="text"
                              :name="`Менеджер ${index + 1}`"
                              rules="required"
                              placeholder=" "
                              maxlength="50"
                            />
                          </div>
                          <div class="col p-0">
                            <b-button v-if="project.managers.length !== 1" variant="light" @click="removeManager(index)">
                              <i class="fa fa-minus-circle"></i>
                            </b-button>
                            <b-button
                              v-if="index === 0"
                              variant="light"
                              @click="addManager"
                            >
                              <i class="fa fa-plus-circle"></i>
                            </b-button>
                          </div>
                        </div>
                      </b-row>
                    </div>
                  </div>
                </div>

                <validation-provider
                    name="Часовой пояс"
                    rules="required"
                    vid="timezones"
                    v-slot="validationContext"
                >
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label text-md-right">Часовой пояс</label>
                        <div class="col-md-9">
                            <b-select v-model="project.timezone" :options="timezones" :state="getValidationState(validationContext)" />
                        </div>
                    </div>
                </validation-provider>

                <div class="form-group row">
                    <div class="col-md-3 col-form-label text-md-right d-none">
                        <b-form-datepicker
                          :value="isPeriodDate ? project.period[0] : ''"
                          button-variant="plain"
                          locale="ru"
                          button-only
                          @input="project.period = [$event]"
                        />
                    </div>
                    <label class="col-md-3 col-form-label text-md-right">Рабочие дни</label>
                    <b-row class="col-md-9 align-items-center" no-gutters>
                        <b-col class="mb-2">
                            <b-form-checkbox-group
                                v-model="project.period"
                                button-variant="outline-secondary"
                                buttons
                                name="work-days"
                                id="work-days"
                            >
                                <b-form-checkbox
                                    v-for="(day, index) in days" :key="index"
                                    name="day-button"
                                    :value="day"
                                    :variant="'primary'"
                                    :class="{ 'text-primary': day.text.toLowerCase() === 's' }"
                                    :button-variant="getVariant(day)"
                                >{{ day.short }}
                                </b-form-checkbox>
                            </b-form-checkbox-group>
                        </b-col>
                        <b-form-checkbox
                            id="work-holidays"
                            name="work-holidays"
                            v-model="project.include_holidays"
                        >
                          В празничные дни
                        </b-form-checkbox>
                    </b-row>
                </div>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-md-right">Отправка отчетов</label>
                  <b-row class="col-md-9 align-items-center" no-gutters>
                    <b-col class="mb-2">
                      <b-form-checkbox-group
                          v-model="project.sending_period"
                          button-variant="outline-secondary"
                          buttons
                          name="sending-days"
                          id="sending-days"
                      >
                        <b-form-checkbox
                            v-for="(day, index) in days" :key="index"
                            name="send-day-button"
                            :value="day"
                            :variant="'primary'"
                            :class="{ 'text-primary': day.text.toLowerCase() === 's' }"
                            :button-variant="getVariant(day, 'send')"
                        >{{ day.short }}
                        </b-form-checkbox>
                      </b-form-checkbox-group>
                    </b-col>
                    <b-form-checkbox
                        id="sending-holidays"
                        name="sending-holidays"
                        v-model="project.sending_include_holidays"
                    >
                      В празничные дни
                    </b-form-checkbox>
                  </b-row>
                </div>
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-md-right" for="last-sent-date">
                    Дата последней отправки
                  </label>
                  <div class="col-md-9">
                    <b-form-datepicker
                        id="last-sent-date"
                        v-model="project.override_report_sent_at"
                        label-no-date-selected="Выберите дату"
                        locale="ru"
                        :max="new Date()"
                        :close-button="true"
                        :hide-header="true"
                        :no-close-on-select="true"
                        start-weekday="1"
                        label-close-button="Закрыть"
                    />
                  </div>
                </div>
              </div>
            </validation-observer>
        </b-overlay>
        <template #modal-footer="{ ok}">
          <div class="tx-right pd-y-20 pd-x-20">
            <b-button
                v-if="project.period.length > 0 && !isProjectEdited"
                variant="light"
                @click="sendReport"
            >
              Отправить отчёт
            </b-button>
            <button type="button" class="btn btn-success ml-2" @click="ok">Сохранить</button>
          </div>
        </template>
    </b-modal>
  </div>
</template>

<style scoped>
.audio-popover {
  max-width: unset !important;
}
</style>

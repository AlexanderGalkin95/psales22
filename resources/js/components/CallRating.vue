<script>
import moment from "moment";
import "../validator";
import AudioPlayer from "./vue-audio/components/player";
import { mapGetters, mapActions } from "vuex";
import { cloneDeep } from "lodash";
import Swal from "sweetalert2";

export default {
  name: "CallRating",
  components: {
    AudioPlayer
  },
  props: {
    projectId: {
      type: Number,
    },
    item: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      confirm: false,
      localItem: {},
      busy: false,
      isLoading: false,
      criteriaOptions: [
        { text: "ПолуДа", value: 0.5 },
        { text: "Да", value: 1 },
        { text: "Нет", value: 0 },
        { text: "Не актульно", value: -1 },
      ],
      crmOptions: [
        { text: "Да", value: 1 },
        { text: "Нет", value: 0 },
      ],
      comments: "",
      call_type: null,
      heat: null,
      objection: null,
      objection_rate: null,
    };
  },
  computed: {
    ...mapGetters([
      "getProject",
      "getCriteria",
      "getAdditionalCriteria",
      "getCrm",
      "getObjections",
      "getCallTypes",
      "getSettings",
      "getHeatTypes",
    ]),
    source() {
      return this.localItem.record_link || null;
    },
    project() {
      return this.getProject;
    },
    callTypes() {
      return this.getCallTypes;
    },
    heatTypes() {
      return this.getHeatTypes;
    },
    criteria() {
      return this.getCriteria;
    },
    additionalCriteria() {
      return this.getAdditionalCriteria;
    },
    crm() {
      return this.getCrm;
    },
    objections() {
      return this.getObjections;
    },
    settings() {
      return this.getSettings;
    },
    selectedCallType() {
      return this.project
        ? _.find(
            this.project.call_types,
            (item) => item.id === this.call_type
          ) || {}
        : {};
    },
  },
  watch: {
    item(newVal) {
      this.localItem = newVal || {};
    },
    localItem(newVal) {},
    call_type(newVal) {
      let criteria = _.map(this.criteria, (criteria) => {
        let value = { enabled: false };
        let setting = this.settings[criteria.id];
        if (this.call_type) {
          value = _.find(
            setting,
            (item) => item.call_type_id === this.call_type
          );
        }
        criteria.value = null
        criteria.disabled = !value.enabled;
        return criteria;
      });
      this.$store.commit("SET_CRITERIA", criteria);
      setTimeout(() => {
        this.$refs.callRating.reset();
      }, 150);
    },
  },
  methods: {
    ...mapActions(["SEND_MESSAGE"]),
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    resetForm() {
      this.comments = "";
      this.call_type = null;
      this.heat = null;
      this.objection = null;
      this.objection_rate = null;
      this.confirm = false;
      this.$store.commit("RESET_DATA", null);
      this.$nextTick(() => {
        this.$refs.callRating.reset();
      });
    },
    send() {
      this.$refs.callRating.validate().then((result) => {
        if (result) {
          let payload = {
            projectId: this.projectId,
            audio_id: this.localItem.record_id,
            audio: this.localItem.download_link,
            record_link_origin: this.localItem.record_link_origin,
            call_type: this.localItem.record_event_type,
            date: moment(this.localItem.record_created_at).format("YYYY-MM-DD"),
            time: moment(this.localItem.record_created_at).format("HH:mm:ss"),
            duration: this.localItem.record_duration,
            link_to_lead: this.localItem.record_element_link,
            manager: this.localItem.record_responsible_name,
            comments: this.comments,
            call_type_id: this.call_type,
            heat: this.heat,
            objection: this.objection,
            objection_rate: this.objection_rate,
            additional_criteria: this.additionalCriteria,
          };
          Object.assign(
            payload,
            { criteria: this.criteria },
            { crm: this.crm }
          );
          this.isLoading = true;
          this.SEND_MESSAGE(payload).then(
            (response) => {
              this.isLoading = false;
              this.$emit("saved", response);
              this.$bvModal.hide("call-rating-modal");
              Swal.fire({
                title: "",
                html: response.data.message,
                icon: "success",
                showConfirmButton: false,
                timer: 3000,
              });
            },
            (error) => {
              this.isLoading = false;
              Swal.fire({
                title: "",
                html: error.response.data.message,
                icon: "error",
                showConfirmButton: false,
                timer: 3000,
              });
            }
          );
        }
      });
    },
  },
  mounted() {
    this.localItem = cloneDeep(this.item);
    if (!_.isEmpty(this.localItem)) {
      this.$bvModal.show("call-rating-modal");
      this.isLoading = true;
      this.$http
        .get(`/api/projects/${this.projectId}/record/${this.localItem.id}`)
        .then((response) => {
          this.localItem.record_link_origin = response.data.record_link_origin;
          this.localItem.record_link = response.data.link;
          this.localItem.download_link = response.data.link;
          if (item.record_source === 'Bitrix24') {
            this.localItem.download_link = response.data.web_download_link;
          }
        });
      this.$store.dispatch("LOAD_PROJECT", this.projectId).then((response) => {
        if (response.rating.system_name === "binary") {
          this.criteriaOptions = [
            { text: "Да", value: 1 },
            { text: "Нет", value: 0 },
            { text: "Не актульно", value: -1 },
          ];
        }
        this.isLoading = false;
      });
    }
  },
};
</script>

<template>
  <b-modal
    id="call-rating-modal"
    title-html="Оценка звонка"
    ok-title="Сохранить"
    size="lg"
    ok-only
    scrollable
    class="dialog-class"
    dialog-class="dialog-class"
    body-class="no-body-class"
    :busy="busy"
    no-close-on-backdrop
    @hide="resetForm"
    @hidden="$emit('closed')"
  >
    <template #modal-title>
      <span class="main__title"
        >Оценка звонка
        <span class="main__date">
          {{ localItem.record_created_at | dateFilter("DD.MM.YYYY HH:mm:ss") }}
        </span>
      </span>
    </template>
    <template #modal-header-close>
      <b-button variant="light">
        <i class="fa fa-times fa-1x"></i>
      </b-button>
    </template>
    <b-overlay :show="isLoading" rounded="sm" spinner-variant="primary">
      <validation-observer ref="callRating" tag="form">
        <b-card-body>
          <b-col class="form-group no-body-class">
            <span v-if="localItem.record_event_type === 'inbound'">
              <a
                :href="
                  project
                    ? `https://${project.integration_domain}${localItem.record_element_link}`
                    : '#'
                "
                target="_blank"
                v-html="(localItem.record_element_name || '...')"
              >
              </a>
              на {{ localItem.record_responsible_name || '...' }}
            </span>
            <span v-else>
              {{ localItem.record_responsible_name || '...' }} на
              <a
                :href="
                  project
                    ? `https://${project.integration_domain}${localItem.record_element_link}`
                    : '#'
                "
                target="_blank"
                v-html="(localItem.record_element_name || '...')"
              >
              </a>
            </span>
          </b-col>
          <b-col class="form-group no-body-class">
            <audio-player :src="source" />
          </b-col>
          <b-col class="form-group no-body-class">
            <validation-provider
              name="comments"
              rules="required"
              v-slot="validationContext"
            >
              <b-form-textarea
                id="comment"
                v-model="comments"
                name="comment"
                placeholder="Комментарий"
                class="comments"
                :state="getValidationState(validationContext)"
              >
              </b-form-textarea>
            </validation-provider>
          </b-col>
        </b-card-body>
        <b-col class="ratingTable__title">
          <span>Оценка</span>
        </b-col>

        <b-col style="padding: 0 2.25rem">
          <b-row>
            <b-col sm="12" md="6" lg="6">
              <validation-provider
                name="call_type"
                rules="required"
                v-slot="validationContext"
              >
                <b-form-group
                  label="Тип звонка"
                  label-cols="12"
                  label-cols-sm="4"
                  label-cols-md="4"
                  label-cols-lg="6"
                >
                  <b-form-select
                    id="call_type"
                    v-model="call_type"
                    name="call_type"
                    text-field="label"
                    :options="callTypes"
                    :state="getValidationState(validationContext)"
                  >
                    <template #first>
                      <b-form-select-option :value="null"
                        >-- не выбрано --</b-form-select-option
                      >
                    </template>
                  </b-form-select>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col sm="12" md="6" lg="6">
              <validation-provider
                name="heat"
                rules="required"
                v-slot="validationContext"
              >
                <b-form-group
                  label="Теплота"
                  label-cols="12"
                  label-cols-sm="4"
                  label-cols-md="4"
                  label-cols-lg="6"
                >
                  <b-form-select
                    id="heat"
                    v-model="heat"
                    name="heat"
                    text-field="label"
                    :options="heatTypes"
                    :state="getValidationState(validationContext)"
                  >
                    <template #first>
                      <b-form-select-option :value="null"
                        >-- не выбрано --</b-form-select-option
                      >
                    </template>
                  </b-form-select>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </b-col>
        <!--            CRITERIA                -->
        <b-col class="card-body col--gray">
          <b-row>
            <b-col
              v-for="(value, index) in criteria"
              :key="index"
              sm="12"
              md="6"
              lg="6"
            >
              <validation-provider
                :name="`criteria_label_${index}`"
                :rules="{ required: !value.disabled }"
                v-slot="validationContext"
              >
                <b-form-group
                  label-cols="12"
                  label-cols-sm="8"
                  label-cols-md="8"
                  label-cols-lg="8"
                >
                  <template #label>
                    <span>{{ value.label }}</span>
                    <i
                      :id="`criteria_label_${index}`"
                      v-b-tooltip.hover.topright
                      class="fa fa-info-circle"
                    >
                      <b-tooltip
                        :target="`criteria_label_${index}`"
                        variant="dark"
                        >{{ value.text }}
                      </b-tooltip>
                    </i>
                  </template>
                  <b-form-select
                    v-model="value['value']"
                    :options="criteriaOptions"
                    :disabled="value.disabled"
                    :state="getValidationState(validationContext)"
                  >
                    <template #first>
                      <b-form-select-option :value="null"
                        >-- не выбрано --</b-form-select-option
                      >
                    </template>
                  </b-form-select>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
          <b-row v-show="!criteria">
            <b-col class="text-uppercase"
              >Критерии для проетка отсутствуют!</b-col
            >
          </b-row>
        </b-col>
        <!--            CRM                     -->
        <b-col v-show="selectedCallType.rate_crm" class="card-body col--blue">
          <b-row>
            <b-col
              v-for="(value, index) in crm"
              :key="index"
              sm="12"
              md="6"
              lg="6"
            >
              <b-form-group
                :label="value.name"
                label-cols="12"
                label-cols-sm="8"
                label-cols-md="8"
                label-cols-lg="8"
              >
                <b-form-select v-model="value['value']" :options="crmOptions">
                  <template #first>
                    <b-form-select-option :value="null"
                      >-- не выбрано --</b-form-select-option
                    >
                  </template>
                </b-form-select>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row v-show="!crm.length">
            <b-col class="text-center text-uppercase"
              >Crm поля для проекта отсутствуют!</b-col
            >
          </b-row>
        </b-col>
        <!--            OBJECTIONS                -->
        <b-col class="card-body col--dark-gray">
          <b-row v-if="objections">
            <b-col sm="12" md="6" lg="6">
              <b-form-group
                label="Тип возражения"
                label-cols="12"
                label-cols-sm="8"
                label-cols-md="8"
                label-cols-lg="8"
                style="margin-bottom: unset"
              >
                <b-form-select
                  id="objections"
                  v-model="objection"
                  name="objections"
                  text-field="name"
                  value-field="id"
                  :options="objections"
                >
                  <template #first>
                    <b-form-select-option :value="null"
                      >-- не выбрано --</b-form-select-option
                    >
                  </template>
                </b-form-select>
              </b-form-group>
            </b-col>
            <b-col sm="12" md="6" lg="6">
              <b-form-group
                label="Оценка возражения"
                label-cols="12"
                label-cols-sm="8"
                label-cols-md="8"
                label-cols-lg="8"
                style="margin-bottom: unset"
              >
                <b-form-select
                  id="objections_rate"
                  v-model="objection_rate"
                  name="objections_rate"
                  :options="[1, 2, 3, 4]"
                >
                  <template #first>
                    <b-form-select-option :value="null"
                      >-- не выбрано --</b-form-select-option
                    >
                  </template>
                </b-form-select>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row v-else>
            <b-col class="text-center text-uppercase"
              >Возражения для проетка отсутствуют!</b-col
            >
          </b-row>
        </b-col>
        <!--             ADDITIONAL CRITERIA      -->
        <b-col class="card-body col--dark-red">
          <b-row v-if="additionalCriteria">
            <b-col
              v-for="(criterion, index) in additionalCriteria"
              :key="index"
              sm="12"
              md="6"
              lg="6"
            >
              <b-form-group
                :label="criterion.name"
                label-cols="12"
                label-cols-sm="8"
                label-cols-md="8"
                label-cols-lg="8"
              >
                <b-form-select
                  v-model.number="criterion['option_id']"
                  text-field="label"
                  value-field="id"
                  :options="criterion.options"
                >
                  <template #first>
                    <b-form-select-option :value="null"
                      >-- не выбрано --</b-form-select-option
                    >
                  </template>
                </b-form-select>
              </b-form-group>
            </b-col>
          </b-row>
        </b-col>
      </validation-observer>
    </b-overlay>
    <template #modal-footer>
      <div class="w-100">
        <b-form-checkbox v-model="confirm" class="float-left">
          <span>подтверждаю</span>
        </b-form-checkbox>
        <b-button
          variant="primary"
          class="float-right"
          :disabled="!confirm || isLoading"
          @click.prevent="send"
        >
          Оценить звонок
        </b-button>
      </div>
    </template>
  </b-modal>
</template>

<style lang="scss" scoped>
.dialog-class {
  margin-top: unset !important;
}
.main__title {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 15px;
  .main__date {
    font-size: 13px;
    margin-left: 10px;
  }
}

.ratingTable__title {
  font-size: 13px;
  text-align: center;
  position: relative;
  padding: 5px 10px;
  span {
    z-index: 1;
    position: relative;
    background: #fff;
    display: inline-block;
    padding: 0 10px;
  }
  &:before {
    display: block;
    content: "";
    height: 2px;
    width: calc(100% - 20px);
    z-index: 0;
    position: absolute;
    margin: auto;
    top: 0;
    bottom: 0;
    left: 10px;
    background: #000000;
  }
}
.col--gray {
  //background: #f7f6f6;
  background: #fff4de;
}

.col--dark-gray {
  background: #afb4bb;
}

.col--dark-red {
  background: #f9c5c5;
}

.col--pink {
  background: #ffe4db;
}

.col--blue {
  background: #b7cdef;
}
</style>

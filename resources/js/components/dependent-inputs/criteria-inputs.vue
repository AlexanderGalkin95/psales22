<template>
  <div class="dependent-inputs">
    <div style="min-height: 255px">
      <div class="row">
        <label :class="labelClass" class="col-md-6 m-0">{{
          mainLabelFieldName
        }}</label>
        <label
          v-if="textInputs"
          :class="labelClass"
          class="col-md-4 m-0 d-none d-sm-none d-md-block"
          >{{ labelFieldName }}</label
        >
        <label
          class="col-md-2 m-0 d-none d-sm-none d-md-block"
          style="white-space: nowrap"
          v-b-tooltip.hover.lefttop="
            'Необходимо указать столбец в Google-таблицах'
          "
        >
          Столбец
        </label>
      </div>
      <draggable class="list-group" :list="localValue" :disabled="undraggable">
        <div
          v-for="(item, index) in localValue"
          :key="index"
          class="list-group-item"
        >
          <div class="row">
            <label
              class="col-md-1"
              style="padding-top: 10px; cursor: pointer"
              @mouseover="undraggable = false"
              ><i class="fas fa-grip-lines-vertical" style="float: left"></i
            ></label>
            <div class="col-md-5" style="margin-bottom: 10px">
              <validation-provider
                :rules="rules"
                :name="
                  textInputs
                    ? mainLabelFieldName + ' ' + (index + 1)
                    : mainLabelFieldName + ' ' + index
                "
                :vid="mainLabelFieldName + '_' + index"
                v-slot="validationContext"
              >
                <div class="input-group">
                  <b-form-input
                    v-if="textInputs"
                    :id="mainLabelFieldName + '_' + index"
                    v-model="localValue[index].label"
                    :maxlength="inputMaxLength"
                    :name="mainLabelFieldName + ' ' + (index + 1)"
                    :state="getValidationState(validationContext)"
                    @mouseover="undraggable = true"
                  />
                  <b-form-input
                    v-else
                    :id="mainLabelFieldName + '_' + index"
                    v-model="localValue[index]"
                    :maxlength="inputMaxLength"
                    :name="mainLabelFieldName + ' ' + index"
                    :state="getValidationState(validationContext)"
                    @mouseover="undraggable = true"
                  />

                  <div
                    v-if="index === localValue.length - 1"
                    class="input-group-append"
                  >
                    <label class="input-group-text"
                      ><i
                        class="fa fa-plus"
                        aria-hidden="true"
                        style="cursor: pointer"
                        @click.stop.prevent="addExtraOption"
                      ></i
                    ></label>
                  </div>
                  <div v-else class="input-group-append">
                    <label class="input-group-text"
                      ><i
                        class="fa fa-minus"
                        aria-hidden="true"
                        style="cursor: pointer"
                        @click.stop.prevent="deleteExtraOption(index)"
                      ></i
                    ></label>
                  </div>
                </div>
                <span v-if="validationContext.errors[0]" :class="{ 'error-message': validationContext.errors[0] }">
                  {{ message ? (validationContext.errors[0] = message) : validationContext.errors[0] }}
                </span>
              </validation-provider>
            </div>
            <div v-if="textInputs" class="col-md-4" style="margin-bottom: 10px">
              <validation-provider
                :name="labelFieldName + ' ' + (index + 1)"
                :rules="rules"
                v-slot="validationContext"
              >
                <div class="input-group" :class="displayTextAria(item)">
                  <b-form-textarea
                    :id="labelFieldName + '_' + index"
                    v-model="localValue[index].text"
                    :name="labelFieldName + ' ' + (index + 1)"
                    :rows="item.rows"
                    :maxlength="textMaxLength"
                    :state="getValidationState(validationContext)"
                    @focus="item.rows = 5"
                    @blur="item.rows = 1"
                  />
                </div>
                <span
                  v-if="validationContext.errors[0]"
                  :class="{ 'error-message': validationContext.errors[0] }"
                >
                  {{
                    message
                      ? (validationContext.errors[0] = message)
                      : validationContext.errors[0]
                  }}
                </span>
              </validation-provider>
            </div>
            <div class="col-md-2" style="margin-bottom: 10px">
              <v-input
                :id="`criteria_${index}_google_column`"
                type="text"
                :rules="criteriaRules"
                :name="`Столбец ${index + 1}`"
                v-model="item['google_column']"
                maxlength="2"
                data-toggle="tooltip"
                v-b-tooltip.hover.lefttop="
                  'Необходимо указать столбец в Google-таблицах'
                "
                @input="
                  (input) => (item['google_column'] = input.toUpperCase())
                "
                @mouseover="undraggable = true"
              />
            </div>
          </div>
        </div>
      </draggable>
    </div>
  </div>
</template>

<script>
import "../../validator";
import "@artamas/vue-select/src/scss/vue-select.scss";
import VInput from "../../components/input";

const _ = require("lodash");

export default {
  name: "DependentInputs",
  components: { VInput },
  props: {
    cclass: {
      type: String,
      default: "form-control",
    },
    message: {
      type: String,
      default: null,
    },
    rules: {
      type: [String, Object],
      default: "",
    },
    criteriaRules: {
      type: [String, Object],
      default: "",
    },
    mode: {
      type: String,
      default: "passive",
    },
    textInputs: {
      type: Boolean,
      default: false,
    },
    inputMaxLength: {
      type: String,
      default: "255",
    },
    textMaxLength: {
      type: String,
      default: "2000",
    },
    value: {
      type: Array,
    },
    mainLabelFieldName: {
      type: String,
      default: "",
    },
    labelFieldName: {
      type: String,
      default: "",
    },
    labelClass: {
      type: String,
      default: "",
    },
    placeholder: {
      type: String,
      default: "",
    },
    clearable: {
      type: Boolean,
    },
  },
  data() {
    return {
      localValue: [{ label: null, text: null, rows: 1, google_column: "" }],
      is_watched: false,
      undraggable: true,
    };
  },
  watch: {
    localValue: {
      handler(newVal) {
        if (this.is_watched) {
          _.forEach(newVal, (item, index) => {
            item.index_number = index;
          });
          this.$emit("input", newVal);
        } else {
          this.is_watched = true;
        }
      },
      deep: true,
    },
    value: {
      handler(newVal) {
        this.is_watched = false;
        if (newVal && newVal.length) {
          let lv = _.cloneDeep(newVal);
          _.forEach(lv, (item) => {
            if (item.rows === undefined) {
              item.rows = 1;
            }
          });
          this.localValue = lv;
        } else {
          this.localValue = [
            { label: null, text: null, rows: 1, google_column: "" },
          ];
        }
      },
      deep: true,
      immediate: true,
    },
  },
  methods: {
    displayTextAria(item) {
      return item.rows === 1 ? "" : "extended";
    },
    getClass(cl) {
      cl[this.cclass] = true;
      return cl;
    },
    addExtraOption() {
      if (this.textInputs) {
        this.localValue.push({
          label: null,
          text: null,
          rows: 1,
          google_column: "",
        });
      } else {
        this.localValue.push(null);
      }
    },
    deleteExtraOption(index) {
      this.localValue.splice(index, 1);
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
  },
};
</script>

<style scoped>
.dependent-inputs .extended textarea {
  position: absolute;
  width: inherit;
  z-index: 2;
}
</style>

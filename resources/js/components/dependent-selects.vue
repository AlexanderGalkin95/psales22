<template>
  <div>
    <div class="form-group">
      <div v-if="selected.main !== undefined" style="margin-bottom: 15px;">
        <label :for="mainFieldId" class="required">{{
          $t(mainFieldName)
        }}</label>
        <v-select
          :id="mainFieldId"
          v-model="selected.main"
          v-validate="'required'"
          placeholder=""
          :options="localOptions"
          data-vv-as=" "
          :name="mainFieldName"
          class="form-control"
          :clearable="false"
        >
          <div slot="no-options">{{ notFoundMessage }}</div>
        </v-select>
      </div>

      <label :for="primaryFieldIdPlural" class="mr-2" :class="labelClass">
        {{ $t(primaryFieldNamePlural) }}
      </label>
      <div class="input-group">
        <v-select
          :id="primaryFieldId"
          v-model="selected.primary"
          v-validate="selected.main === undefined ? 'required' : ''"
          :clearable="clearable"
          placeholder=""
          :options="localOptions"
          data-vv-as=" "
          :name="primaryFieldName"
          class="form-control"
        >
          <div slot="no-options">{{ notFoundMessage }}</div>
        </v-select>

        <div class="input-group-append">
          <label class="input-group-text" :for="primaryFieldIdPlural"
            ><i
              class="fa fa-plus"
              aria-hidden="true"
              style="cursor:pointer"
              @click.stop.prevent="addExtraOption"
            ></i
          ></label>
        </div>
      </div>
    </div>

    <div v-for="(localExtraOption, index) in selected.extra" :key="index">
      <div class="form-group">
        <div class="input-group">
          <v-select
            v-model="selected.extra[index]"
            v-validate="'required'"
            placeholder=""
            :options="localOptions"
            :clearable="clearable"
            data-vv-as=" "
            :name="primaryFieldId + '_' + index"
            class="form-control"
          >
            <div slot="no-options">{{ notFoundMessage }}</div>
          </v-select>
          <div class="input-group-append">
            <label class="input-group-text" :for="primaryFieldId"
              ><i
                class="fa fa-minus"
                aria-hidden="true"
                style="cursor:pointer"
                @click.stop.prevent="deleteExtraOption(index)"
              ></i
            ></label>
          </div>
        </div>
      </div>
    </div>+
  </div>
</template>

<script>
import VSelect from 'vue-select'
import Vue from 'vue'
import VeeValidate from 'vee-validate'
Vue.use(VeeValidate)

const _ = require('lodash')

export default {
  name: 'DependentSelects',
  components: {
    VSelect,
  },
  model: {
    prop: 'selected',
    event: 'change',
  },
  props: {
    options: {
      type: Array,
      default() {
        return []
      },
    },
    selected: {
      type: Object,
      default() {
        return {
          primary: null,
          extra: [],
        }
      },
      validator(input) {
        if (
          input.primary &&
          (typeof input.primary !== 'object' || !Array.isArray(input.extra))
        ) {
          return false
        }

        if (
          input.primary &&
          (input.primary.value === undefined ||
            input.primary.label === undefined)
        ) {
          return false
        }

        let isValid = true
        _.forEach(input.extra, (extraItem) => {
          if (extraItem !== null && typeof extraItem !== 'object') {
            isValid = false
            return
          }

          if (typeof extraItem === 'object') {
            if (
              extraItem !== null &&
              (extraItem.value === undefined || extraItem.label === undefined)
            ) {
              isValid = false
            }
          }
        })

        return isValid
      },
    },
    dependentValue: {
      type: Object,
      default: null,
    },

    validate: {
      type: Boolean,
    },

    mainFieldName: {
      type: String,
      default: '',
    },
    primaryFieldName: {
      type: String,
      default: '',
    },
    primaryFieldNamePlural: {
      type: String,
      default: '',
    },
    labelClass: {
      type: String,
      default: '',
    },
    extraFiledNamePlural: {
      type: String,
      default: '',
    },
    placeholder: {
      type: String,
      default: '',
    },
    notFoundMessage: {
      type: String,
      default: 'Ничего не найдено.',
    },
    clearable: {
      type: Boolean,
    },
  },
  data() {
    return {
      localOptions: [],

      mainFieldId: '',
      primaryFieldId: '',
      primaryFieldIdPlural: '',
    }
  },
  watch: {
    'selected.main': {
      handler(newVal, oldVal) {
        this.changedState()
      },
      deep: true,
    },
    'selected.primary': {
      handler(newVal, oldVal) {
        this.changedState()
      },
      deep: true,
    },
    'selected.extra': {
      handler(newVal, oldVal) {
        this.changedState()
      },
      deep: true,
    },
    options(newVal) {
      this.changedState()
    },
    validate() {
      this.$validator.validateAll()
    },
  },
  mounted() {
    this.changedState()

    this.mainFieldId = this.mainFieldName.toLowerCase().replace(' ', '_')
    this.primaryFieldId = this.primaryFieldName.toLowerCase().replace(' ', '_')
    this.primaryFieldIdPlural = this.primaryFieldNamePlural
      .toLowerCase()
      .replace(' ', '_')
  },
  methods: {
    addExtraOption() {
      this.selected.extra.push(null)
    },
    deleteExtraOption(index) {
      this.selected.extra.splice(index, 1)
    },
    changedState() {
      let options = _.filter(this.options, (option) => {
        return !_.includes(
          _.map(this.selected.extra, (extraOption) => {
            return extraOption ? extraOption.value : null
          }),
          option.value
        )
      })

      if (this.selected.main) {
        options = _.filter(options, (option) => {
          return option.value !== this.selected.main.value
        })
      }

      if (this.selected.primary) {
        options = _.filter(options, (option) => {
          return option.value !== this.selected.primary.value
        })
      }

      this.localOptions = options
    },
  },
}
</script>

<script>
import DatePicker from 'vue2-datepicker'
import moment from "moment";
import js_settings from "../../../js_settings";
export default {
  name: "TableDatepicker",
  components: {
    DatePicker
  },
  props: {
    isHeader: {
      type: Boolean,
      default: true
    },
    col: {
      type: Object,
      default: ''
    },
  },
  computed: {
    table() {
      return this.$parent
    },
    isTimeFormat() {
      return ['longtime', 'time', 'timems'].includes(this.col.format)
    },
    range() {
      return this.col.range || Array.isArray(this.table.local_filters[this.col.name]) || false
    },
    localValue: {
      get() {
        return this.table.local_filters[this.col.name]
      },
      set(newVal) {
        if (Array.isArray(newVal) && newVal.some(item => item === null)) {
          return
        }
        this.table.local_filters[this.col.name] = newVal
      }
    }
  },
  methods: {
    setRange() {
      this.col.range = !this.col.range
      this.localValue = null
    },
    notAfterToday(date) {
      return date > new Date(new Date().setHours(0, 0, 0, 0))
    },
    selectMaxDay(emit, value) {
      if (!this.col.range) {
        emit(new Date())
      } else {
        if(!this.localValue) return
        let value = moment(this.localValue, js_settings.formats.date).format('YYYY-MM-DD')
        emit([new Date('01.01.1976'), new Date(value)])
      }
    },
    selectMinDay(emit, value) {
      if (!this.col.range) {
        emit(new Date('01.01.1976'))
      } else {
        if(!this.localValue) return
        let value = moment(this.localValue, js_settings.formats.date).format('YYYY-MM-DD')
        emit([new Date(value), new Date()])
      }
    },
    handlePick(val) {
      if (!this.isTimeFormat && !this.range) {
        let dt = moment(val);
        val = !dt.isValid() ? val : dt.format(js_settings.formats.date)
        this.localValue = val
      }
    },
  },
}
</script>

<template>
  <div v-if="isHeader" class="input-group" style="width: 100%; text-align:center">
    <date-picker
        v-model="localValue"
        input-class="form-control"
        lang="ru"
        value-type="format"
        :type="isTimeFormat ? 'time' : 'date'"
        :format="isTimeFormat ? 'HH:mm:ss' : 'DD.MM.YYYY'"
        :confirm="isTimeFormat"
        :range="range"
        :disabled-date="notAfterToday"
        @pick="handlePick"
        @clear="localValue = null"
    >
      <template #header="{ emit, value }">
        <button class="mx-btn mx-btn-text" @click="setRange">Интервал</button>
        <button v-if="!range" class="mx-btn mx-btn-text" @click="selectMinDay(emit, value)">Мин</button>
        <button v-if="!range" class="mx-btn mx-btn-text" @click="selectMaxDay(emit, value)">Макс</button>
      </template>
    </date-picker>
  </div>
</template>

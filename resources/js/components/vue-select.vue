<script>
import srx from "../functions";
import { forEach } from 'lodash';

export default {
  name: 'VueSelect',
  props: {
    id: {
      type: String,
      default: "vue-select_" + srx.getRandomId()
    },
    name: {
      type: String,
      default: "vue-select_" + srx.getRandomId()
    },
    rules: {
      type: String,
      default: "",
    },
    mode: {
      type: String,
      default: "passive",
    },
    cclass: {
      type: String,
      default: "",
    },
    value: {}
  },
  data() {
    return {
      emits: [
        'open',
        'close',
        'input',
        'search',
        'search:compositionstart',
        'search:compositionend',
        'search:keydown',
        'search:blur',
        'search:focus',
        'search:input',
        'option:created',
        'option:selecting',
        'option:selected',
        'option:deselecting',
        'option:deselected',
      ],
      localValue: this.value,
    }
  },
  computed: {
    localId() {
        return this.id;
    },
    localName() {
        return this.name;
    },
    events() {
      let events = {}
      forEach(
        this.emits,
        event => Object.assign(events, { [event]: (...payload) => this.$emit(event, ...payload) })
      )
      return events
    },
  },
  watch: {
    value(newValue) {
      this.localValue = newValue
    }
  },
  methods: {
    getClass(cl) {
      cl[this.cclass] = true;
      return cl;
    },
  },
};
</script>

<template>
  <div :id="localId" class="vue-select">
    <validation-provider
      :name="localName"
      :vid="localId"
      :rules="rules"
      v-slot="validationContext"
    >
      <v-select
        :id="localId"
        v-model="localValue"
        :name="localName"
        :class="getClass({
          'is-error': validationContext.errors[0],
          'is-valid': validationContext.validated ? validationContext.valid : null
        })"
        v-bind="$attrs"
        v-on="events"
      >
        <span slot="no-options" @click="$refs.select.open = false">
          Ничего не найдено.
        </span>
      </v-select>
      <span :class="{ 'error-message': validationContext.errors[0] }">
        {{ validationContext.errors[0] }}
      </span>
    </validation-provider>
  </div>
</template>

<style lang="scss" scoped>
::v-deep .v-select {
  .vs__dropdown-toggle {
    width: 100%;
    height: calc(1.6em + 1.3rem + 2px);
    padding: 0.65rem 1rem;
    font-size: 0.9rem;
    font-weight: 400;
    line-height: 1.6;
    color: #3F4254;
    background-color: #ffffff;
    background-clip: padding-box;
    border: 1px solid #E4E6EF;
    border-radius: 0.85rem;
    box-shadow: none;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }

  &.is-valid {
    .vs__dropdown-toggle {
      border: 1px solid #1BC5BD !important;
      border-radius: 0.85rem;
    }
  }
  &.is-error {
    .vs__dropdown-toggle {
      border: none !important;
    }
  }

  &.vs--disabled {
    .vs__dropdown-toggle, input {
      background-color: #F3F6F9;
    }
  }
}
</style>
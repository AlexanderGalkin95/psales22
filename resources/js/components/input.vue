<script>
const randomString = () =>
  Math.random().toString(36).substring(2, 6) +
    Math.random().toString(36).substring(2, 6)
export default {
  props: {
    id: {
      type: String,
      default: randomString()
    },
    value: {
      type: [String, Number, Boolean],
      default: "",
    },
    checked: {
      type: Boolean,
      default: false,
    },
    name: {
      type: String,
      default: randomString()
    },
    type: {
      type: String,
      default: "text",
    },
    rules: {
      type: [String, Object],
      default: "",
    },
    mode: {
      type: String,
      default: "passive",
    },
    tag: {
      type: String,
      default: "span",
    },
    maxlength: {
      type: String,
      default: "150",
    },
    cclass: {
      type: String,
      default: "form-control",
    },
    sstyle: {
      type: String,
      default: "",
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    message: {
      type: String,
      default: null,
    },
    placeholder: {
      type: String,
      default: "",
    },
    leftlabel: {
      type: String,
      default: null,
    },
    labelclass: {
      type: String,
      default: "",
    },
    rightlabel: {
      type: String,
      default: null,
    },
    lefticon: {
      type: [String, Boolean],
      default: null,
    },
    righticon: {
      type: [String, Boolean],
      default: null,
    },
  },
  data: function () {
    return {
      localId: this.id,
      localName: this.name,
      localValue: this.value,
    };
  },
  watch: {
    value(newVal) {
      this.localValue = newVal;
    },
  },
  methods: {
    getClass(cl) {
      cl[this.cclass] = true;
      return cl;
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
  },
};
</script>
<template>
  <validation-provider
    :tag="tag"
    :rules="rules"
    :vid="localId"
    :name="localName"
    v-slot="validationContext"
  >
    <div
      :class="{
        'input-group': rightlabel || leftlabel || righticon || lefticon,
      }"
    >
      <span v-if="leftlabel" :class="labelclass" class="input-group-prepend">{{
        leftlabel
      }}</span>
      <template v-if="lefticon" @click="$emit('clickicon')">
        <slot name="lefticon">
          <span v-if="lefticon" :class="labelclass" class="input-group-prepend">
            <i class="fa" :class="lefticon"></i>
          </span>
        </slot>
      </template>
      <b-form-input
        :id="localId"
        v-model="localValue"
        :type="type"
        :name="localName"
        :readonly="readonly"
        :maxlength="maxlength"
        :placeholder="placeholder"
        :style="sstyle"
        :disabled="disabled"
        :state="getValidationState(validationContext)"
        @input="$emit('input', localValue)"
        @focus="$emit('focus', $event)"
        @click="$emit('click', $event)"
        @blur="$emit('blur', $event)"
        @hover="$emit('hover', $event)"
      ></b-form-input>
      <span v-if="rightlabel" :class="labelclass" class="input-group-append">{{
        rightlabel
      }}</span>
      <template v-if="righticon" @click="$emit('clickicon')">
        <slot name="righticon">
          <span
            v-if="righticon"
            :class="labelclass"
            class="input-group-prepend"
          >
            <i class="fa" :class="righticon"></i>
          </span>
        </slot>
      </template>
    </div>
    <span v-if="validationContext.errors[0]" :class="{ 'error-message': validationContext.errors[0] }">
      {{ message ? (validationContext.errors[0] = message) : validationContext.errors[0] }}
    </span>
  </validation-provider>
</template>

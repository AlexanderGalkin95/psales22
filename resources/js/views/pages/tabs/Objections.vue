<script>
import _ from 'lodash'
import Swal from "sweetalert2"
import VInput from '../../../components/input.vue'
import VContent from "../../../components/VContent.vue";

export default {
  name: "Objections",
  props: {
    project: {
      type: Object,
      default: () => {},
      required: true
    },
    validating: {
      type: [Boolean, Object, Array],
      default: false
    },
  },
  components: { VContent, VInput },
  data() {
        return {
            objections: {
                google_column: '',
                google_column_rate: '',
                options: []
            },
        }
    },
  computed: {
    projectExists () {
            return !_.isEmpty(this.project)
        },
    hasEmptyFields() {
      return !_.isEmpty(this.objections)
          && (
              _.isEmpty(this.objections.google_column)
              || _.isEmpty(this.objections.google_column_rate)
              || _.some(this.objections.options, item => {
                return _.isEmpty(item.name)
              })
          )
    },
    hasChanged () {
      return !_.isEqual(
          this.project.objections,
          _.each(_.cloneDeep(this.objections), field => {
            if (typeof field !== 'string') {
              field = _.map(field, item => {
                delete item.delete
                return item
              })
            }
            return field
          })
      )
    },
    emptyObjections () {
      return _.isEmpty(this.objections.options)
    },
    googleColumns () {
      let objs =  this.objections ? _.map(_.cloneDeep(this.objections), (item, key) => {
        return {[key]: item}
      }): []
      if (objs) objs.splice(2, 1)
      return _.map(
          _.concat(
              objs,
              _.cloneDeep(this.project.criteria),
              _.cloneDeep(this.project.crm)
          ),
          item => item.google_column || item.google_column_rate
      ) || []
    },
  },
  watch: {
    project: {
      handler(newVal) {
        this.$emit('has-empty-fields', false)
        if (!_.isEmpty(newVal)) {
          this.objections = ! _.isEmpty(newVal.objections) ? _.each(_.cloneDeep(newVal.objections), field => {
            if (typeof field !== 'string') {
              field = _.map(field, item => {
                item.delete = true
                return item
              })
            }
            return field
          }) : this.objections
        } else {
          this.objections = {}
        }
      },
      deep: true,
      immediate: true
    },
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
    validating (newVal) {
      this.$emit('has-empty-fields', false)
      if (newVal) {
        this.$refs.objections.reset()
        let objs =  this.objections ? _.map(_.cloneDeep(this.objections), (item, key) => {
          return {[key]: item}
        }): []
        if (objs) objs.splice(2, 1)
        _.forEach(newVal, value => {
          if (!_.isEmpty(value)) {
            let match = _.find(objs, item => {
              return item.google_column === value || item.google_column_rate === value;
            })
            if (match) {
              this.$emit('has-empty-fields', true)
              this.$refs.objections.setErrors({
                [`objection_${Object.keys(match)[0]}`]: ['Такое значение поля уже существует']
              })
            }
          }
        })
      }
    }
  },
  methods: {
    save () {
      this.$refs.objections.validate().then( result => {
        if (result) {
          this.$emit('has-empty-fields', false)
          this.$emit('saving', true)
          this.$http.post(
              `/api/projects/${this.project.id}/objections`,
              {
                objections: this.objections
              }
          ).then(response => {
            if (response.status === 200) {
              this.project.objections = response.data.objections
              Swal.fire({
                title: "",
                html: response.data.message,
                icon: "success",
                showConfirmButton: true,
                timer: 3000,
              });
              this.$emit('saving', false)
            }
          }, error => {
            if (error.status === 422) {
              this.$refs.objections.setErrors(error.data.fields)
            } else {
              Swal.fire({
                title: "",
                html: error.data.message,
                icon: "error",
                showConfirmButton: true,
                timer: 3000,
              });
              this.$emit('saving', false)
            }
          });
          this.$emit('validating', [])
        } else {
          let values = []
          _.forEach(this.$refs.objections.errors, (error, key) => {
            if (error.length > 0) {
              values.push(document.querySelector(`#${key}`).value)
            }
          })
          this.$emit('validating', values)
        }
      })
    },

    addObjections () {
      this.objections = {
        google_column: '',
        google_column_rate: '',
        options: []
      }
      this.objections.options.push({ name: '', delete: true })
    },

    addField () {
      this.objections.options.push({ name: '', delete: true })
      this.$nextTick(() => {
          this.scrollToBottom()
      })
    },

    removeField (index) {
      this.objections.options.splice(index, 1)
      if (_.isEmpty(this.objections.options)) {
          this.objections = {}
      }
    },

    scrollToBottom () {
      let elm = this.$refs.objectionsScrollbar.$el
      elm.scrollTo({ top: elm.scrollHeight, behavior: 'smooth' })
    }
  },
}
</script>

<template>
    <validation-observer ref="objections"
                         tag="form">
        <v-content>
            <button v-if="emptyObjections" class="btn btn-primary"
                    type="button"
                    @click="addObjections">
                <i class="fa fa-plus"></i>
                Добавить возражения
            </button>
            <div class="col-md-4">
                <b-row v-if="!emptyObjections" class="justify-content-between">
                    <div>
                        <b-col sm="12"><label>Столбец возражений</label></b-col>
                        <b-col sm="8">
                            <v-input id="objection_google_column"
                                     v-b-tooltip.hover.righttop="{
                                        title: 'Необходимо указать Столбец возражений в Google-таблицах',
                                        interactive: false
                                     }"
                                     v-model="objections.google_column"
                                     class="pl-0"
                                     type="text"
                                     tag="div"
                                     maxlength="2"
                                     data-toggle="tooltip"
                                     name="Столбец возражений"
                                     :rules="`required|alpha|regex:^[a-zA-Z]+$|unique:${googleColumns}`"
                                     @input="(input) => objections.google_column = input.toUpperCase()"
                            />
                        </b-col>
                    </div>
                    <div>
                        <b-col sm="12"><label>Столбец оценки возражений</label></b-col>
                        <b-col sm="8">
                            <v-input id="objection_google_column_rate"
                                     v-b-tooltip.hover.righttop="{
                                        title: 'Необходимо указать Столбец оценки возражений в Google-таблицах',
                                        interactive: false,
                                     }"
                                     v-model="objections.google_column_rate"
                                     class="pl-0"
                                     type="text"
                                     tag="div"
                                     maxlength="2"
                                     data-toggle="tooltip"
                                     name="Столбец оценки возражений"
                                     :rules="`required|alpha|regex:^[a-zA-Z]+$|unique:${googleColumns}`"
                                     @input="(input) => objections.google_column_rate = input.toUpperCase()"
                          />
                        </b-col>
                    </div>
                </b-row>
            </div>
            <div class="col-md-8">
                <div v-if="!emptyObjections" class="col-12">
                    <b-row>
                        <b-input-group>
                            <label class="col-md-8">Возражения</label>
                            <label style="flex: 1 1 15%"></label>
                        </b-input-group>
                    </b-row>
                </div>
                <div class="col-12">
                    <perfect-scrollbar
                      ref="objectionsScrollbar"
                      class="scroll"
                      v-bind:options="{ suppressScrollX: true, railBorderYWidth: 8 }"
                    >
                        <div v-for="(objection, index) in objections.options"
                             :key="index"
                             class="form-group">
                            <b-row>
                                <div class="input-group">
                                    <div class="mb-1 col-md-9">
                                        <v-input :id="`objection_${index + 1}`"
                                                 v-model="objection.name"
                                                 type="text"
                                                 :name="`Название ${index + 1}`"
                                                 rules="required"
                                                 placeholder=" "
                                                 maxlength="50"
                                        />
                                    </div>
                                    <div v-if="objection.delete" class="col">
                                        <b-button variant="light" @click="removeField(index)">
                                            <i class="fa fa-minus-circle"></i>
                                        </b-button>
                                        <b-button v-if="objections.options.length === index + 1" variant="light"
                                                  @click="addField">
                                            <i class="fa fa-plus-circle"></i>
                                        </b-button>
                                    </div>
                                </div>
                            </b-row>
                        </div>
                    </perfect-scrollbar>
                </div>
            </div>
        </v-content>
        <b-row no-gutters class="card-footer justify-content-between">
            <router-link tag="a" class="btn btn-light"
                         :to="{ name: 'projects.list' }">
                <i class="fa fa-arrow-left"></i>
                Список проектов
            </router-link>
            <a href="javascript:void(0)"
               class="btn btn-primary"
               :disabled="!projectExists"
               @click="save">
              <i class="fa fa-save"></i>
                Сохранить
            </a>
        </b-row>
    </validation-observer>
</template>

<style scoped>

</style>
<script>
import VContent from "../../../components/VContent";
import CriteriaInputs from '../../../components/dependent-inputs/criteria-inputs'
import _ from "lodash";
import Swal from "sweetalert2"
import VInput from "../../../components/input";

export default {
  name: "Criteria",
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
  components: { VContent, CriteriaInputs, VInput },
  data() {
    return {
      criteria: [],
      additionalCriteria: [],
      draggable: false,
    }
  },
  computed: {
    projectExists () {
      return !_.isEmpty(this.project)
    },

    hasEmptyFields() {
      let criteria = _.some(this.criteria, item => {
        return _.isEmpty(item.label) || _.isEmpty(item.text) || _.isEmpty(item.google_column)
      })
      let additionalCriteria = _.some(this.additionalCriteria, criterion => {
        return _.isEmpty(criterion.name)
            || _.isEmpty(criterion.legend)
            || _.some(criterion.options, option => {
              return _.isEmpty(option.label) || _.isEmpty(option.value)
            })
      })
      return criteria || additionalCriteria
    },

    hasChanged () {
      let criteria = !_.isEqual(
          this.project.criteria,
          _.map(_.cloneDeep(this.criteria), field => {
            delete field.rows
            return field
          })
      )
      let additionalCriteria = !_.isEqual(
          this.project.additional_criteria,
          _.each(_.cloneDeep(this.additionalCriteria), field => {
            if (typeof field !== 'string') {
              field = _.map(field, item => {
                delete item?.delete
                return item
              })
            }
            return field
          })
      )
      return criteria || additionalCriteria
    },

    emptyCriteria () {
      return _.isEmpty(this.criteria)
    },

    emptyAdditionalCriteria () {
      return _.isEmpty(this.additionalCriteria)
    },

    googleColumns () {
      let objs =  this.project.objections ? _.map(_.cloneDeep(this.project.objections), (item, key) => {
        return {[key]: item}
      }): []
      if (objs) objs.splice(2, 1)
      return _.map(
          _.concat(
              objs,
              _.cloneDeep(this.project.crm),
              _.cloneDeep(this.criteria)
          ),
          item => item && (item.google_column || item.google_column_rate)
      ) || []
    },

    currentTab() {
      let tab = _.find(this.$refs.criteriaTabs.tabs, tab => tab.localActive)
      return tab.id
    },

    nextIndexNumber() {
      let index = _.maxBy(this.additionalCriteria, criterion => criterion.index_number)
      return (index ? index.index_number : 0) + 1
    },
  },
  watch: {
    project: {
      handler(newVal) {
        this.$emit('has-empty-fields', false)
        if (newVal) {
          this.criteria = _.cloneDeep(newVal.criteria);
          this.additionalCriteria = _.cloneDeep(newVal.additional_criteria);
        }
      },
      deep: true,
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
        this.$refs.criteriaTab.reset()
        let criteriaColumns = _.map(_.cloneDeep(this.criteria), item => item.google_column) || []
        if(criteriaColumns.length) {
          _.forEach(newVal, value => {
            let match = _.find(criteriaColumns, item => {
              return item === value;
            })
            if (match) {
              this.$emit('has-empty-fields', true)
              this.$refs.criteriaTab.setErrors({
                [`criteria_${criteriaColumns.indexOf(match)}_google_column`]: ['Такое значение поля уже существует']
              })
            }
          })
        }
      }
    }
  },
  methods: {
    save () {
      if (this.currentTab === 'criteria') {
        this.saveCriteria()
      }
      if (this.currentTab === 'additional-criteria') {
        this.saveAdditionalCriteria()
      }
    },
    saveCriteria() {
      this.$refs.criteriaTab.validate().then( result => {
        if (result) {
          this.$emit('saving', true)
          this.$http.post(
              `/api/projects/${this.project.id}/criteria`,
              {
                criteria: this.criteria
              }
          ).then(response => {
            if (response.status === 200) {
              this.project.criteria = response.data.criteria
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
              this.$refs.criteriaTab.setErrors(error.data.fields)
            } else {
              Swal.fire({
                title: "",
                html: error.data.message,
                icon: "error",
                showConfirmButton: true,
                timer: 3000,
              });
            }
            this.$emit('saving', false)
          });
          this.$emit('validating', [])
        } else {
          let values = []
          _.forEach(this.$refs.criteriaTab.errors, (error, key) => {
            if (error.length > 0) {
              values.push(document.querySelector(`#${key}`).value)
            }
          })
          this.$emit('validating', values)
        }
      })
    },
    saveAdditionalCriteria() {
      this.$refs.additionalCriteriaTab.validate().then( result => {
        if (result) {
          this.$emit('saving', true)
          this.$http.post(
              `/api/projects/${this.project.id}/criteria/additional`,
              {
                  additional_criteria: this.additionalCriteria
              }
          ).then(response => {
            if (response.status === 200) {
              this.project.additional_criteria = response.data.additional_criteria
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
            this.$emit('saving', false)
            if (error.status === 422) {
              this.$refs.additionalCriteriaTab.setErrors(error.data.fields)
            } else {
              Swal.fire({
                title: "",
                html: error.data.message,
                icon: "error",
                showConfirmButton: true,
                timer: 3000,
              });
            }
          });
          this.$emit('validating', [])
        }
      })
    },
    cancel() {
      this.$router.push('/projects');
    },
    addAdditionalCriteria() {
      this.additionalCriteria.push({
        name: '',
        legend: '',
        index_number: this.nextIndexNumber,
        options: []
      })
      this.addAdditionalCriteriaField(this.additionalCriteria[this.additionalCriteria.length - 1])
    },
    removeAdditionalCriteria(index) {
      this.additionalCriteria.splice(index, 1)
    },
    addAdditionalCriteriaField(criterion) {
      criterion.options.push({ label: '', value: null, delete: true })
    },
    removeAdditionalCriteriaField (criterion, index) {
      criterion.options.splice(index, 1)
      if (_.isEmpty(criterion.options)) {
        this.additionalCriteria.splice(_.indexOf(this.additionalCriteria, criterion), 1)
      }
    },
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    reassignIndexes() {
      _.each(this.additionalCriteria, (criteria, index) => {
        criteria.index_number = index + 1
      })
    },
  },
}
</script>

<template>
  <div>
    <b-tabs ref="criteriaTabs"
            pills
            align="center"
            nav-wrapper-class="card-body pb-0"
            nav-class="pl-3 nav-active-item-bordered"
            content-class="card-body">
      <b-tab id="criteria"
             title="Основные критерии">
        <validation-observer ref="criteriaTab" tag="form">
          <criteria-inputs
              v-model="criteria"
              class="col-12"
              main-label-field-name="Критерии"
              label-field-name="Легенда"
              rules="required"
              style="padding: 0 20px;"
              placeholder=""
              :text-inputs="true"
              :criteria-rules="'required|alpha|regex:^[a-zA-Z]+$|unique:' + googleColumns"
          >
          </criteria-inputs>
        </validation-observer>
      </b-tab>
      <b-tab id="additional-criteria"
             title="Дополнительные критерии">
        <validation-observer ref="additionalCriteriaTab" tag="form">
          <b-col v-if="!emptyAdditionalCriteria">
            <div role="tablist">
              <draggable class="list-group"
                         tag="ul"
                         ghostClass="ghost"
                         :list="additionalCriteria"
                         :animation="200"
                         :disabled="!draggable"
                         @end="reassignIndexes">
                <transition-group type="transition" :name="'flip-list'">
                  <b-card v-for="(criterion, cIndex) in additionalCriteria"
                          :key="criterion.index_number"
                          no-body class="mb-1">
                    <b-card-header header-tag="header"
                                   class="d-flex p-1 bg-dark-o-10 list-group-item"
                                   role="tab">
                      <div class="col-1 d-flex align-items-center"
                           @mouseover="draggable = true"
                           @mouseleave="draggable = false">
                        <i class="fas fa-grip-lines-vertical" style="float: left"></i>
                      </div>
                      <label v-b-toggle="[`accordion-${cIndex + 1}`]"
                             class="col-form-label flex-column-fluid cursor-pointer pl-3">
                        {{ criterion.name }}
                      </label>
                      <b-button variant="light" @click="removeAdditionalCriteria(cIndex)">
                        <i class="fa fa-minus-circle"></i>
                      </b-button>
                      <b-button v-if="additionalCriteria.length === cIndex + 1" variant="light"
                                @click="addAdditionalCriteria">
                        <i class="fa fa-plus-circle"></i>
                      </b-button>
                    </b-card-header>
                    <b-collapse
                        :id="`accordion-${cIndex + 1}`"
                        :visible="cIndex === 0"
                        accordion="my-accordion"
                        role="tabpanel"
                    >
                      <b-card-body>
                        <b-row class="col-12">
                          <validation-provider class="mb-1 col-md-6"
                                               :name="`Название ${cIndex + 1}`"
                                               rules="required"
                                               v-slot="validationContext">
                            <b-form-group label="Название" :invalid-feedback="validationContext.errors[0]">
                              <b-form-input :id="`additional_criteria_${cIndex}_title`"
                                            v-model="criterion.name"
                                            :name="`Название ${cIndex + 1}`"
                                            type="text"
                                            maxlength="50"
                                            :state="getValidationState(validationContext)"
                              />
                            </b-form-group>
                          </validation-provider>
                          <validation-provider class="mb-1 col-md-6"
                                               :name="`Легенда ${cIndex + 1}`"
                                               rules="required"
                                               v-slot="validationContext">
                            <b-form-group label="Легенда" :invalid-feedback="validationContext.errors[0]">
                              <b-form-textarea :id="`additional_criteria_${cIndex}_legend`"
                                               v-model="criterion.legend"
                                               rules="required"
                                               maxlength="6000"
                                               rows="2"
                                               :state="getValidationState(validationContext)"
                              />
                            </b-form-group>
                          </validation-provider>
                          <b-col>
                            <b-form-group label="Значения выпадающего списка" label-class="font-size-h4">
                              <b-row>
                                <b-input-group>
                                  <label class="col-md-6">Название</label>
                                  <label class="col-md-3 m-0 d-none d-sm-none d-md-block">Значение</label>
                                  <label style="flex: 1 1 15%"></label>
                                </b-input-group>
                              </b-row>
                              <div v-for="(option, oIndex) in criterion.options"
                                   :key="oIndex" class="form-group">
                                <b-row>
                                  <div class="input-group">
                                    <div class="mb-1 col-md-6">
                                      <validation-provider :name="`Название ${cIndex}.${oIndex + 1}`"
                                                           rules="required"
                                                           v-slot="validationContext">
                                        <b-form-input :id="`additional_criteria.${cIndex}.${oIndex}.label`"
                                                      v-model="option.label"
                                                      type="text"
                                                      :name="`Название ${oIndex + 1}`"
                                                      placeholder=" "
                                                      maxlength="50"
                                                      :state="getValidationState(validationContext)"
                                        />
                                      </validation-provider>
                                    </div>
                                    <div class="mb-1 col-md-3">
                                      <validation-provider :name="`Значение ${cIndex}.${oIndex + 1}`"
                                                           rules="required"
                                                           v-slot="validationContext">
                                        <b-form-input :id="`additional_criteria.${cIndex}.${oIndex}.value`"
                                                      v-model="option.value"
                                                      type="text"
                                                      :name="`Значение ${oIndex + 1}`"
                                                      placeholder=" "
                                                      maxlength="50"
                                                      :state="getValidationState(validationContext)"
                                        />
                                      </validation-provider>
                                    </div>
                                    <div class="mb-1 col-md-3 d-flex flex-wrap">
                                      <div>
                                        <b-button variant="light" @click="removeAdditionalCriteriaField(criterion, oIndex)">
                                          <i class="fa fa-minus-circle"></i>
                                        </b-button>
                                        <b-button v-if="criterion.options.length === oIndex + 1" variant="light"
                                                  @click="addAdditionalCriteriaField(criterion)">
                                          <i class="fa fa-plus-circle"></i>
                                        </b-button>
                                      </div>
                                    </div>
                                  </div>
                                </b-row>
                              </div>
                            </b-form-group>
                          </b-col>
                        </b-row>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </transition-group>
              </draggable>
            </div>
          </b-col>
          <b-col v-else class="col-12">
            <button class="btn btn-primary"
                    type="button"
                    @click="addAdditionalCriteria">
              <i class="fa fa-plus"></i>
              Добавить доп. критерии
            </button>
          </b-col>
        </validation-observer>
      </b-tab>
    </b-tabs>
    <div v-show="projectExists">
      <b-row no-gutters class="card-footer justify-content-between">
        <router-link tag="a" class="btn btn-light"
                     :to="{ name: 'projects.list' }">
          <i class="fa fa-arrow-left"></i>
          Список проектов
        </router-link>
        <a href="javascript:void(0)"
           class="btn btn-primary"
           @click="save">
          <i class="fa fa-save"></i>Сохранить
        </a>
      </b-row>
    </div>
  </div>
</template>

<style lang="scss">
.nav-active-item-bordered {
  .nav-link.active {
    color: inherit !important;
    background-color: unset !important;
    border-radius: unset;
    border-bottom: 0.2rem solid #F64E60 !important;
  }
}
</style>

<script>
import _ from 'lodash'
import Swal from "sweetalert2"
import VInput from '../../../components/input.vue'
import VContent from "../../../components/VContent.vue";

export default {
  name: "Crm",
  props: {
    project: {
      type: Object,
      default: () => {},
      required: true,
    },
    validating: {
      type: [Boolean, Object, Array],
      default: false,
    },
  },
  components: { VContent, VInput, VContent },
  data() {
    return {
            projectCrm: [],
            dragOptions: {
                animation: 0,
                group: "description",
                ghostClass: "ghost"
            },
        }
  },
  computed: {
    projectExists() {
      return !_.isEmpty(this.project);
    },

    hasEmptyFields() {
      return _.some(this.projectCrm, (item) => {
        return _.isEmpty(item.google_column) || _.isEmpty(item.name);
      });
    },

    hasChanged() {
      return !_.isEqual(
        this.project.crm,
        _.map(_.cloneDeep(this.projectCrm), (field) => {
          delete field.delete;
          return field;
        })
      );
    },

    emptyCrm() {
      return _.isEmpty(this.projectCrm);
    },

    googleColumns() {
      let objs = this.project.objections
        ? _.map(_.cloneDeep(this.project.objections), (item, key) => {
            return { [key]: item };
          })
        : [];
      if (objs) objs.splice(2, 1);
      return (
        _.map(
          _.concat(
            objs,
            _.cloneDeep(this.project.criteria),
            _.cloneDeep(this.projectCrm)
          ),
          (item) => item.google_column || item.google_column_rate
        ) || []
      );
    },

    nextIndexNumber() {
      let index = _.maxBy(this.projectCrm, (crm) => crm.index_number);
      return (index ? index.index_number : 0) + 1;
    },
  },
  watch: {
    project: {
      handler(newVal) {
        this.$emit("has-empty-fields", false);
        if (!_.isEmpty(newVal)) {
          this.projectCrm = _.isEmpty(newVal.crm)
            ? this.projectCrm
            : _.map(_.cloneDeep(newVal.crm), (field) => {
                field.delete = true;
                return field;
              });
        } else {
          this.projectCrm = [];
        }
      },
      deep: true,
      immediate: true,
    },
    hasEmptyFields: {
      handler(newVal) {
        setTimeout(() => {
          this.$emit("has-empty-fields", newVal);
        }, 500);
      },
      immediate: true,
    },
    hasChanged: {
      handler(newVal) {
        this.$emit("has-changed", newVal);
      },
      immediate: true,
    },
    validating(newVal) {
      this.$emit("has-empty-fields", false);
      if (newVal) {
        this.$refs.crmTab.reset();
        let crmColumns =
          _.map(_.cloneDeep(this.projectCrm), (item) => item.google_column) ||
          [];
        if (crmColumns.length) {
          _.forEach(newVal, (value) => {
            if (!_.isEmpty(value)) {
              let match = _.find(crmColumns, (item) => {
                return item === value;
              });
              if (match) {
                this.$emit("has-empty-fields", true);
                this.$refs.crmTab.setErrors({
                  [`crm_${crmColumns.indexOf(match)}_google_column`]: [
                    "Такое значение поля уже существует",
                  ],
                });
              }
            }
          });
        }
      }
    },
  },
  methods: {
    cancel() {
      this.$router.push("/projects");
    },

    addCrmField() {
      this.projectCrm.push({
        name: "",
        google_column: "",
        delete: true,
        index_number: this.nextIndexNumber,
      });
      this.$nextTick(() => {
        this.scrollToBottom();
      });
    },

    scrollToBottom() {
      let elm = this.$refs.prefectScrollbar.$el;
      elm.scrollTo({ top: elm.scrollHeight, behavior: "smooth" });
    },

    removeCrmField(index) {
      this.projectCrm.splice(index, 1);
    },

    save() {
      this.$refs.crmTab.reset();
      this.$refs.crmTab.validate().then((result) => {
        if (result) {
          this.$emit("has-empty-fields", false);
          this.$emit("saving", true);
          this.$http
            .post(`/api/projects/${this.project.id}/crm`, {
              crm: this.projectCrm,
            })
            .then(
              (response) => {
                if (response.status === 200) {
                  this.project.crm = _.cloneDeep(response.data.crm);
                  this.projectCrm = _.isEmpty(response.data.crm)
                    ? this.projectCrm
                    : _.map(_.cloneDeep(response.data.crm), (field) => {
                        field.delete = true;
                        return field;
                      });
                  Swal.fire({
                    title: "",
                    html: response.data.message,
                    icon: "success",
                    showConfirmButton: true,
                    timer: 3000,
                  });
                  this.$emit("saving", false);
                }
              },
              (error) => {
                if (error.status === 422) {
                  this.$refs.crmTab.setErrors(error.data.fields);
                } else {
                  Swal.fire({
                    title: "",
                    html: error.data.message,
                    icon: "error",
                    showConfirmButton: true,
                    timer: 3000,
                  });
                }
                this.$emit("saving", false);
              }
            );
          this.$emit("validating", []);
        } else {
          let values = [];
          _.forEach(this.$refs.crmTab.errors, (error, key) => {
            if (error.length > 0) {
              values.push(document.querySelector(`#${key}`).value);
            }
          });
          this.$emit("validating", values);
        }
      });
    },

    reassignIndexes() {
      _.each(this.projectCrm, (crm, index) => {
        crm.index_number = index + 1;
      });
    },
  },
};
</script>

<template>
  <validation-observer ref="crmTab" tag="form">
    <v-content>
      <button
        v-if="emptyCrm"
        class="btn btn-primary"
        type="button"
        @click="addCrmField"
      >
        <i class="fa fa-plus"></i>
        Добавить CRM
      </button>
      <div v-show="!emptyCrm" class="col-12">
        <b-row>
          <b-input-group>
            <label class="col-md-7">Название</label>
            <label class="col-md-4 m-0 d-none d-sm-none d-md-block"
              >Столбец</label
            >
            <label style="flex: 1 1 15%"></label>
          </b-input-group>
        </b-row>
      </div>
      <div class="col-12">
        <perfect-scrollbar
          ref="prefectScrollbar"
          class="scroll"
          style="max-height: 350px; position: relative"
          v-bind:options="{ suppressScrollX: true, railBorderYWidth: 8 }"
        >
          <draggable
            class="list-group"
            tag="ul"
            ghostClass="ghost"
            :list="projectCrm"
            :animation="200"
            @end="reassignIndexes"
          >
            <transition-group type="transition" :name="'flip-list'">
              <li
                v-for="(crm, index) in projectCrm"
                :key="crm.index_number"
                class="list-group-item"
              >
                <b-row>
                  <div class="input-group">
                    <div class="col-md-1 d-flex align-items-center">
                      <i
                        class="fas fa-grip-lines-vertical"
                        style="float: left"
                      ></i>
                    </div>
                    <div class="mb-1 col-md-6">
                      <v-input
                        :id="`crm_${index}`"
                        v-model="crm.name"
                        type="text"
                        :name="`Название ${index + 1}`"
                        rules="required"
                        placeholder=" "
                        maxlength="50"
                      >
                      </v-input>
                    </div>
                    <div class="mb-1 col-md-2">
                      <v-input
                        :id="`crm_${index}_google_column`"
                        v-b-tooltip.hover.righttop="{
                          title: 'Необходимо указать столбец в Google-таблицах',
                          interactive: false,
                        }"
                        v-model="crm.google_column"
                        type="text"
                        tag="div"
                        :rules="`required|alpha|regex:^[a-zA-Z]+$|unique:${googleColumns}`"
                        maxlength="2"
                        :name="`Столбец ${index + 1}`"
                        @input="
                          (input) => (crm.google_column = input.toUpperCase())
                        "
                      ></v-input>
                    </div>
                    <div v-if="crm.delete" class="col">
                      <b-button variant="light" @click="removeCrmField(index)">
                        <i class="fa fa-minus-circle"></i>
                      </b-button>
                      <b-button
                        v-if="projectCrm.length === index + 1"
                        variant="light"
                        @click="addCrmField"
                      >
                        <i class="fa fa-plus-circle"></i>
                      </b-button>
                    </div>
                  </div>
                </b-row>
              </li>
            </transition-group>
          </draggable>
        </perfect-scrollbar>
      </div>
    </v-content>
    <b-row no-gutters class="card-footer justify-content-between">
      <router-link
        tag="a"
        class="btn btn-light"
        :to="{ name: 'projects.list' }"
      >
        <i class="fa fa-arrow-left"></i>
        Список проектов
      </router-link>
      <a
        href="javascript:void(0)"
        class="btn btn-primary"
        type="button"
        value="return"
        :disabled="!projectExists"
        @click="save"
      >
        <i class="fa fa-save"></i>
        Сохранить
      </a>
    </b-row>
  </validation-observer>
</template>

<style lang="scss" scoped>
.ghost {
  opacity: 0.5;
  background: #f64e6052;
}
.flip-list-move {
  transition: transform 0.5s;
}
.no-move {
  transition: transform 0s;
}
.list-group-item {
  cursor: move;
}
</style>

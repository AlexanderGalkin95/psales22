<script>
import VInput from "../../../components/input.vue";
import VSelect from "../../../components/vue-select.vue";
import VContent from "../../../components/VContent.vue";
import Links from "./Links.vue";
import "../../../validator";
import "@artamas/vue-select/src/scss/vue-select.scss";
import Swal from "sweetalert2";
import { VueTelInput } from "vue-tel-input";
import Projects from "./Projects.vue";
import { mapGetters } from "vuex";
import { isEmpty } from 'lodash';

export default {
  name: "CompanyCreate",
  components: {
    VInput,
    VSelect,
    VContent,
    VueTelInput,
    Projects,
    Links,
  },
  data() {
    return {
      formFields: {
        name: "",
        description: "",
        niche: "",
        logo: "",
        domain: "",
        active: true,
        admin_id: null,
        contact_name: '',
        contact_phone: "",
        contact_tariff: "",
        contact_agreement: "",
        managers: [],
        links: [
          {
            title: "Ссылка на таблицу конверсии (при наличии)",
            link: "",
            static: true,
          },
          {
            title: "Ссылка на Банк звонков",
            link: "",
            static: true,
          },
          {
            title: "Ссылка на Бриф с клиентом",
            link: "",
            static: true,
          },
          {
            title: "Ссылка на согласование Проектной таблицы",
            link: "",
            static: true,
          },
        ],
        projects: [],
      },
      managers: [],
      isLoading: false,
      phoneInfo: {},
    };
  },
  computed: {
    ...mapGetters(["currentUser"]),
    currentUserRole() {
      return this.currentUser.role_name;
    },
    companyId() {
      return this.$route.params.companyId;
    },
    isReadonly() {
      return this.$route.name === 'companies.profile'
    },
    pageTitle() {
      if (!this.companyId) return 'Создание компании'
      return this.$route.name === 'companies.profile' ? "Профиль компании" : "Редактирование компании"
    }
  },
  watch: {
    currentUser(newValue) {
      if (newValue) {
        if (this.currentUserRole === 'sa') {
          this.managers.unshift({
            label: this.currentUser.name,
            value: this.currentUser.id
          })
        }
        this.formFields.admin_id = this.currentUser.id
      }
    },
    phoneInfo: {
      handler() {
        if (this.phoneInfo.number) {
          if (this.phoneInfo.number.input === "") this.phoneInfo = {};
        }
      },
      deep: true,
    },
  },
  mounted() {
    this.$http.get("/api/dictionaries/pm").then((response) => {
      if (response.status === 200) {
        this.managers = [...this.managers, ...response.data.pm];
      }
    });
    if (this.companyId) {
      this.isLoading = true;
      this.$http.get(`/api/companies/${this.companyId}`)
        .then(({ data }) => {
          this.formFields = { ...this.formFields, ...data };
          this.isLoading = false;
        })
        .catch((error) => {
          if (error && error.status === 422) {
            Swal.fire('Компания не найдена', '', 'error')
            this.$router.push({ name: 'companies.list' })
          }
        });
    }
  },
  methods: {
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    handleSave() {
      this.$refs.companyObserver.reset();
      if (!isEmpty(this.phoneInfo) && this.phoneInfo.formatted !== '') {
          if (!this.phoneInfo.valid) {
              this.$refs.companyObserver.setErrors({ 'contact_phone': 'Введенный номер телефона неправильный' })
              return
          }
      }
      this.$refs.companyObserver.validate().then((result) => {
        if (result) {
          if (this.companyId) {
            this.handleHttpRequest(
              this.$http.put(`/api/companies/${this.companyId}/update`, {
                ...this.formFields,
                contact_phone: !this.phoneInfo.valid ? this.formFields.contact_phone : this.phoneInfo.number.significant
              })
            );
          } else {
            this.handleHttpRequest(
              this.$http.post("/api/companies/create", {
                ...this.formFields,
                contact_phone: !this.phoneInfo.valid ? this.formFields.contact_phone : this.phoneInfo.number.significant,
              })
            ).then(({ company }) => {
              this.$router.push({ name: 'companies.edit', params: { companyId: company.id } })
            });
          }
        }
      });
    },
    handleHttpRequest(request) {
      return request
        .then((response) => {
          if (response.status === 200) {
            Swal.fire({
              title: "",
              html: response.data.message,
              icon: "success",
              showConfirmButton: false,
              timer: 3000,
            });
            this.isLoading = false;
            return response.data
          }
        })
        .catch((error) => {
          if (error.status === 422) {
            this.$refs.companyObserver.setErrors(error.data.fields);
            this.isLoading = false;
          } else {
            Swal.fire({
              title: "",
              html: error.data.message,
              icon: "error",
              showConfirmButton: false,
              timer: 3000,
            });
            this.isLoading = false;
          }
        });
    },
    validatePhone(value, phoneObject, validationContext) {
      this.phoneInfo = phoneObject;
      setTimeout(() => {
        validationContext.validate()
      }, 300)
    },
    handlePhoneValidate(phoneObject) {
      this.phoneInfo = phoneObject;
    },
    getClass(cl) {
      cl["vue-tel-input"] = true;
      return cl;
    },
    handleAddManager() {
      this.formFields.managers.push(null);
    },
    handleRemoveManager(mIndex) {
      this.formFields.managers.splice(mIndex, 1);
    },
    handleCreateProject() {
      this.$router.push({ name: 'companies.projects.create', params: { companyId: this.companyId } })
    },
  },
};
</script>

<template>
  <div>
    <div class="row">
      <div class="col-12 d-flex justify-content-between mb-4">
        <span class="d-flex align-items-center">
          <span class="svg-icon svg-icon-2x">
          <inline-svg src="/media/svg/icons/Home/Building.svg" />
          </span>
          <h3 class="m-0">
            {{ pageTitle }}
          </h3>
        </span>
        <div v-if="companyId">
          <button
            class="btn btn-light"
            type="button"
            @click="handleCreateProject"
          >
            <i class="fa fa-plus"></i>
            Создать проект
          </button>
        </div>
      </div>
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="card card-custom">
          <b-overlay :show="isLoading" rounded="sm" spinner-variant="primary">
            <validation-observer ref="companyObserver" tag="form">
              <div class="card-body" style="padding: 0">
                <div class="row">
                  <div class="col-md-12">
                    <v-content>
                      <div class="col-md-6 px-10">
                        <legend>Основные</legend>
                        <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right"
                            >Название</label
                          >
                          <div class="col-md-8">
                            <v-input
                              id="name"
                              v-model="formFields.name"
                              type="text"
                              rules="required"
                              placeholder="Название"
                              name="Название"
                              maxlength="50"
                              :disabled="isReadonly"
                            />
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right"
                            >Описание</label
                          >
                          <div class="col-md-8">
                            <validation-provider
                              name="description"
                              rules="required"
                              v-slot="validationContext"
                            >
                              <b-form-textarea
                                id="description"
                                v-model="formFields.description"
                                name="description"
                                placeholder="Описание"
                                :disabled="isReadonly"
                                :state="getValidationState(validationContext)"
                              />
                            </validation-provider>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right"
                            >Ниша</label
                          >
                          <div class="col-md-8">
                            <v-input
                              id="niche"
                              v-model="formFields.niche"
                              type="text"
                              rules="required"
                              placeholder="Ниша"
                              name="Ниша"
                              maxlength="50"
                              :disabled="isReadonly"
                            />
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right"
                            >Логотип</label
                          >
                          <div class="col-md-8">
                            <div class="personal-page-settings-item__content">
                              <div
                                class="
                                  public-integration-edit-version-image-loader
                                  personal-page-logo-item__image-editer
                                  public-integration-edit-version-image-loader_not-hover
                                "
                                data-dnd-before="Drop your files here"
                                data-dnd-after="Отпустите клавишу мыши, чтобы прикрепить файлы"
                              >
                                <label
                                  for="personal-page-logo-input-id"
                                  class="
                                    public-integration-edit-version-image-loader__upload
                                  "
                                  ><span class="svg-icon svg-icon-4x"
                                    ><inline-svg
                                      src="/media/svg/icons/Files/Pictures1.svg" /></span
                                  ><span
                                    class="
                                      public-integration-edit-version-image-loader__upload-text
                                    "
                                    >до 800x800px</span
                                  ></label
                                >
                              </div>
                              <input
                                name="logo"
                                class="
                                  js-personal-page-logo-loader
                                  hidden
                                  js-form-changes-skip
                                  text-input
                                "
                                id="personal-page-logo-input-id"
                                type="file"
                                value=""
                                placeholder=""
                                accept="image/png"
                                autocomplete="off"
                                hidden
                                :disabled="isReadonly"
                              />
                              <input
                                name="logo_url_change_tracker"
                                class="
                                  js-personal-page-logo-url-change-tracker
                                  d-none
                                  text-input
                                "
                                type="text"
                                value=""
                                placeholder=""
                                autocomplete="off"
                                :disabled="isReadonly"
                              />
                            </div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label
                            class="
                              col-sm-12 col-md-4 col-form-label
                              text-md-right
                            "
                            >Домен интеграции</label
                          >
                          <div class="col-md-8">
                            <v-input
                              id="domain"
                              v-model="formFields.domain"
                              type="text"
                              name="Домен компании"
                              placeholder="Домен компании"
                              maxlength="50"
                              :disabled="isReadonly"
                            />
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right"
                            >Статус</label
                          >
                          <div class="col-md-8">
                            <span class="switch switch-md">
                              <label>
                                <input
                                  v-model="formFields.active"
                                  name="Деактивировать компанию"
                                  type="checkbox"
                                  :disabled="isReadonly"
                                />
                                <span></span>
                              </label>
                            </span>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="company_admin" class="col-md-4 col-form-label text-md-right"
                            >Администратор</label
                          >
                          <div class="col-md-8">
                            <v-select
                              id="company_admin"
                              v-model="formFields.admin_id"
                              rules="required"
                              name="Администратор"
                              :options="managers"
                              :reduce="(o) => o.value"
                              :disabled="isReadonly"
                            />
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-md-4 col-form-label text-md-right"
                            >Проектные Менеджеры</label
                          >
                          <div class="col-md-8">
                            <div class="d-flex flex-column">
                              <div
                                v-for="(manager, mIndex) in formFields.managers"
                                :key="`manager_${mIndex}`"
                                class="
                                  d-flex
                                  flex-row
                                  justify-content-center
                                  mb-2
                                "
                              >
                                <v-select
                                  :id="`manager_${mIndex}`"
                                  v-model="formFields.managers[mIndex]"
                                  rules="required"
                                  class="w-100 mr-2"
                                  :name="`Менеджер ${mIndex + 1}`"
                                  :options="managers"
                                  :reduce="(o) => o.value"
                                  :disabled="isReadonly"
                                />
                                <a
                                  v-if="!isReadonly"
                                  href="#"
                                  class="
                                    text-hover-primary
                                    d-flex
                                    align-items-center
                                  "
                                  @click="handleRemoveManager(mIndex)"
                                >
                                  <span
                                    class="
                                      svg-icon svg-icon-md svg-icon-default
                                    "
                                  >
                                    <inline-svg
                                      src="/media/svg/icons/General/Trash.svg"
                                    />
                                  </span>
                                </a>
                              </div>
                            </div>
                            <a
                              v-if="!isReadonly"
                              href="#"
                              class="btn btn-light btn-xs font-size-sm"
                              @click="handleAddManager"
                            >
                              <i class="fa fa-plus font-size-sm"></i>
                              Добавить
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6 px-10">
                        <legend>Контакты клиента (ЛПР)</legend>
                        <div class="form-group row">
                          <label for="contact_name" class="col-md-4 col-form-label text-md-right"
                            >Имя</label
                          >
                          <div class="col-md-8">
                            <v-input
                              id="contact_name"
                              v-model="formFields.contact_name"
                              type="text"
                              rules="required"
                              placeholder="Имя"
                              name="Имя"
                              maxlength="50"
                              :disabled="isReadonly"
                            />
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="contact_phone" class="col-md-4 col-form-label text-md-right"
                            >Телефон</label
                          >
                          <div class="col-md-8">
                            <validation-provider
                              mode="passive"
                              name="contact_phone"
                              vid="contact_phone"
                              :rules="`required|valid_phone:${phoneInfo.valid}`"
                              :custom-messages="{
                                required: 'Номер телефона контакта обязателен',
                                valid_phone: 'Введенный номер телефона неправильный'
                              }"
                              v-slot="validationContext"
                            >
                              <vue-tel-input
                                v-model="formFields.contact_phone"
                                name="contact_phone"
                                input-id="contact_phone"
                                mode="international"
                                placeholder="Телефон"
                                class="form-control"
                                :class="{
                                  'is-invalid': validationContext.errors[0],
                                  'is-valid': validationContext.validated && validationContext.valid
                                }"
                                default-country="ru"
                                :input-options="{
                                  showDialCode: true,
                                  tabindex: 0,
                                }"
                                :valid-characters-only="true"
                                :disabled="isReadonly"
                                required
                                @input="
                                  (value, phoneObject, refs) => validatePhone(value, phoneObject, validationContext)
                                "
                                @validate="handlePhoneValidate"
                              ></vue-tel-input>
                              <span
                                v-if="validationContext.errors[0]"
                                :class="{ 'error-message': validationContext.errors[0] }"
                              >
                                {{ validationContext.errors[0] }}
                              </span>
                            </validation-provider>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="contact_tariff" class="col-md-4 col-form-label text-md-right"
                            >Тариф</label
                          >
                          <div class="col-md-8">
                            <v-input
                              id="contact_tariff"
                              v-model="formFields.contact_tariff"
                              type="text"
                              rules="required"
                              placeholder="Тариф"
                              name="Тариф"
                              maxlength="50"
                              :disabled="isReadonly"
                            />
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="contact_agreement" class="col-md-4 col-form-label text-md-right"
                            >Договор</label
                          >
                          <div class="col-md-8">
                            <v-input
                              id="contact_agreement"
                              v-model="formFields.contact_agreement"
                              type="text"
                              rules="required"
                              placeholder="Договор"
                              name="Договор"
                              maxlength="50"
                              :disabled="isReadonly"
                            />
                          </div>
                        </div>
                        <div class="w-100">
                          <div class="d-flex justify-content-between mb-2">
                            <legend class="w-auto">Проекты</legend>
                          </div>
                          <Projects :projects="formFields.projects" />
                        </div>
                        <Links v-model="formFields.links" />
                      </div>
                    </v-content>
                    <b-row
                      no-gutters
                      class="card-footer justify-content-between"
                    >
                      <router-link
                        tag="a"
                        class="btn btn-light"
                        :to="{ name: 'companies.list' }"
                      >
                        <i class="fa fa-arrow-left"></i>
                        Список компаний
                      </router-link>
                      <button
                        v-if="!isReadonly"
                        class="btn btn-primary"
                        type="button"
                        @click="handleSave"
                      >
                        <i class="fa fa-save"></i>
                        Сохранить
                      </button>
                    </b-row>
                  </div>
                </div>
              </div>
            </validation-observer>
          </b-overlay>
        </div>
      </article>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.public-integration-edit-version-image-loader {
  position: relative;
  height: 154px;
  border-radius: 5px;
  flex: 0 0 297px;
  margin-right: 8px;
  overflow: hidden;
  display: flex;
}
.personal-page-logo-item__image-editer {
  margin: 0;
  width: 100%;
  height: 140px;
  display: flex;
  justify-content: center;
  align-items: center;
}
.public-integration-edit-version-image-loader__upload {
  position: absolute;
  z-index: 1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  background: rgba(0, 0, 0, 0.5);
  cursor: pointer;
}
.public-integration-edit-version-image-loader
  .svg-settings--widgets--img_icon-dims {
  width: 32px;
  height: 22px;
}
.public-integration-edit-version-image-loader__upload-text {
  padding-top: 4px;
  font-size: 13px;
}
.switch input:empty ~ span:before {
  background: #f64e60;
  opacity: 0.3;
}
.vue-tel-input {
  border-radius: 0.85rem;

  &:not(.is-valid, .is-invalid) {
    border: 1px solid #E4E6EF;
  }

  .vti__dropdown {
    cursor: not-allowed;
  }
  &:focus-within {
    box-shadow: unset;
    border-color: #f87f8c;

    &.is-valid:focus-within {
      border-color: #1BC5BD;
    }
  }
}
</style>

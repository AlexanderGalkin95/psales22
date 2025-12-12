<template>
  <div class="card card-custom gutter-b card-shadowless border-0">
    <div class="card-header min-h-10px border-0 p-0">
      <h3 class="align-items-start flex-column">
        <legend>Ссылки</legend>
      </h3>
      <div>
        <a
          v-if="!isReadonly"
          href="#"
          class="font-size-sm text-secondary text-hover-primary"
          @click="openLinkModal({ title: '', link: '' })"
        >
          <i class="fa fa-plus font-size-sm"></i>
          Добавить ссылку
        </a>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table
          class="table table-head-custom table-vertical-center table-borderless"
          id="kt_advance_table_widget_1"
        >
          <tbody>
            <template v-for="(item, i) in links">
              <tr v-bind:key="i">
                <td class="pl-0">
                  <span class="text-dark-75 font-weight-bolder mb-1 font-size-lg">
                    {{ item.title }}
                    <span class="text-hover-primary" title="Копи" role="button">
                      <i class="ki ki-copy"></i>
                    </span>
                  </span>
                  <a
                    href="#"
                    class="text-muted text-hover-primary font-weight-bold d-block"
                  >
                    {{ item.link }}
                  </a>
                </td>
                <td class="pr-0 text-right">
                  <a
                    v-if="!isReadonly"
                    href="#"
                    class="text-hover-primary mr-1"
                    @click="openLinkModal(item, true)"
                  >
                    <span class="svg-icon svg-icon-md svg-icon-default">
                      <inline-svg
                        src="/media/svg/icons/Communication/Write.svg"
                      />
                    </span>
                  </a>
                  <a
                    v-if="!item.static && !isReadonly"
                    href="#"
                    class="text-hover-primary"
                    @click="handleRemoveLink(item)"
                  >
                    <span class="svg-icon svg-icon-md svg-icon-default">
                      <inline-svg src="/media/svg/icons/General/Trash.svg" />
                    </span>
                  </a>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <b-modal
      id="add-edit-link-modal"
      ok-title="Сохранить"
      cancel-title="Отменить"
      class="dialog-class"
      dialog-class="dialog-class"
      body-class="no-body-class"
      centered
      hide-header
      no-close-on-backdrop
      @ok.prevent="handleSaveLink"
      @hidden="selectedLink = {}"
    >
      <b-card-body>
        <validation-observer ref="linkObserver" tag="form">
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-md-right">Название</label>
            <div class="col-md-8">
              <v-input
                id="link_title"
                v-model="linkForm.title"
                type="text"
                rules="required"
                placeholder="Название"
                name="Название"
                maxlength="50"
              />
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-md-right"> Ссылка </label>
            <div class="col-md-8">
              <v-input
                id="link"
                v-model="linkForm.link"
                type="text"
                rules="required"
                placeholder="Ссылка"
                name="Ссылка"
                maxlength="50"
              />
            </div>
          </div>
        </validation-observer>
      </b-card-body>
    </b-modal>
  </div>
</template>

<script>
import VInput from "../../../components/input.vue";
import Swal from "sweetalert2";

export default {
  name: "Links",
  props: {
    value: {
      type: Array,
      default: () => [],
    },
  },
  components: {
    VInput,
  },
  data() {
    return {
      selectedLink: {},
      linkForm: {},
      links: [],
    }
  },
  computed: {
    isReadonly() {
      return this.$route.name === 'companies.profile'
    },
  },
  watch: {
    value: {
        handler(newValue) {
            if (newValue) this.links = newValue
        },
        immediate: true,
        deep: true,
    },
  },
  methods: {
    openLinkModal(item, edit = false) {
      item._isEditing = edit
      this.selectedLink = item
      this.linkForm = { ...item }
      this.$bvModal.show('add-edit-link-modal')
    },
    handleSaveLink() {
      this.$refs.linkObserver.validate().then((result) => {
        if (result) {
          this.selectedLink.link = this.linkForm.link
          this.selectedLink.title = this.linkForm.title
          if (!this.selectedLink._isEditing) {
            this.links.push(this.selectedLink);
          }
          delete this.selectedLink._isEditing;
          this.$bvModal.hide('add-edit-link-modal');
        }
      })
    },
    handleRemoveLink(link) {
      Swal.fire({
        title: "Вы действительно хотите удалить ссылку?",
        icon: "warning",
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonColor: "#F64E60",
        cancelButtonColor: "#F3F6F9",
        customClass: {
          confirmButton: "btn btn-primary",
          cancelButton: "btn btn-light",
        },
        buttonsStyling: false,
        reverseButtons: true,
        confirmButtonText: "Удалить",
        cancelButtonText: `Отмена`,
        preConfirm: () => {
          return true;
        },
      }).then((result) => {
        if (result.isConfirmed) {
          const lIdx = this.links.indexOf(link);
          if (lIdx !== -1) this.links.splice(lIdx, 1);
        }
      });
    },
  }
};
</script>

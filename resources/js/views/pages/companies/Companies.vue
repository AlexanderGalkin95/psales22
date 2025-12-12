<script>
import Table from "../../../components/table/Table.vue";
import { mapGetters } from "vuex";
import Swal from "sweetalert2";

export default {
  name: "Projects",
  components: { Table },
  data() {
    return {
      project: null,
      settings: {
        edit_project_callback: function (col, item, $this, event) {
          $this.$emit("company:edit", item);
        },
        deactivate_callback(col, item, $this) {
          if (item.projects_count > 0) {
            Swal.fire('Невозможно удалить компанию, так как у этой компнии есть проекты', '', 'warning')
            return
          } else {
            Swal.fire({
              title: 'Вы действительно хотите удалить компанию?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#F64E60',
              confirmButtonText: 'Удалить',
              cancelButtonText: `Отмена`,
              preConfirm: () => {
                return $this.$http.delete(`/api/companies/${item.id}/deactivate`)
                  .catch((error) => {
                    return error
                  })
              }
            }).then((result) => {
              if (result.isConfirmed && result.value.status === 200) {
                Swal.fire(result.value.data.message, '', 'success')
                $this.refreshImmediately()
              } else if (result.value && result.value.status) {
                if (result.value.status === 422) {
                  Swal.fire(result.value.data.fields[0][0], '', 'error')
                } else {
                  Swal.fire(result.value.data.message, '', 'error')
                }
              }
            })
          }
        },
      },
      filters: {
        name: "",
        description: "",
        company_admin: "",
        niche: "",
      },
      dicts: {
        integrations: [],
      },
    };
  },
  computed: {
    ...mapGetters(["currentUser"]),
    current_user() {
      return this.currentUser;
    },
    user_role() {
      return this.current_user.role_name;
    },
    columns() {
      let $this = this;
      return [
        {
          name: "name",
          title: "Название",
          link: "/companies/{id}/profile",
          format: "text",
          width: "40%",
          sortable: true,
          searchable: true,
          align: "left",
        },
        {
          name: "niche",
          title: "Ниша",
          format: "text",
          width: "30%",
          sortable: true,
          searchable: true,
          align: "left",
        },
        {
          name: "projects_count",
          title: "Проекты",
          link: "/companies/{id}/projects",
          width: "10%",
          breakpoints: "xs",
          sortable: false,
          searchable: false,
          align: "left",
        },
        {
          name: "company_admin",
          title: "Менеджер",
          format: "text",
          sortable: true,
          searchable: true,
          display_callback: (col, item) => item[col.name].name
        },
        {
          type: "buttons",
          align: "left",
          title: "",
          width: "3%",
          items: [
            {
              type: "button",
              class: "btn-success",
              id: "edit_project",
              title: "",
              icon: "fa-edit",
              onclick: "project",
              width: "1%",
              show: true,
              show_callback: function (col, item) {
                return ["sa", "pm"].includes($this.user_role);
              },
              tooltip: "Редактировать",
            },
            {
              type: "button",
              class: "btn-danger",
              id: "deactivate",
              icon: "fa-trash",
              width: "1%",
              tooltip: "Деактивировать",
              show: true,
              show_callback: function (col, item) {
                return ["sa"].includes($this.user_role);
              },
            },
          ],
        },
      ];
    },
  },
  created() {
    this.$http.get("/api/dictionaries/integrations").then((response) => {
      this.dicts.integrations = response.data.integrations;
    });
  },
  methods: {
    showCreateCompanyPage() {
      this.$router.push("/companies/create");
    },
    showEditCompanyPage(item) {
      this.$router.push({
        name: "companies.edit",
        params: { companyId: item.id },
      });
    },
  },
};
</script>
<template>
  <div class="row m-0">
    <div style="margin: 0 0 15px 19px">
      <div style="display: inline-block">
        <h3>
          <i class="flaticon-analytics" aria-hidden="true"></i> Управление
          компаниями
        </h3>
      </div>
    </div>
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="card card-custom">
        <div>
          <div class="card-header text-left">
            <button
              id="process"
              class="btn btn-primary"
              type="button"
              value="return"
              @click="showCreateCompanyPage"
            >
              <i class="flaticon2-plus"></i>
              Создать
            </button>
          </div>
          <div class="card-body">
            <Table
              id="companies"
              :data_src="'/api/companies'"
              data_prop_name="companies"
              :columns="columns"
              :filters="filters"
              :settings="settings"
              :dicts="dicts"
              @company:edit="showEditCompanyPage"
          />
          </div>
        </div>
      </div>
    </article>
  </div>
</template>
<style scoped>
</style>

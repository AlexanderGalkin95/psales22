<script>
import Table from "../../components/table/Table.vue";
import { mapGetters } from "vuex";
let styleCallback = function (col, item) {
  return {
    "bg-danger-o-15": item.status === "Нет",
  };
};
export default {
  name: "Projects",
  components: { Table },
  data() {
    return {
      project: null,
      settings: {
        edit_project_callback: function (col, item, $this, event) {
          $this.$emit("edit-project", item);
        },
        calls_callback(col, item, $this) {
          $this.$router.push({
            name: "projects.calls",
            params: {
              projectId: item.id,
            },
          });
        },
        spreadsheet_callback(col, item, $this) {
          $this.$router.push({
            name: "projects.spreadsheet",
            params: { projectId: item.id },
          });
        },
      },
      filters: {
        name: "",
        pm_name: "",
        senior_name: "",
        assessor_name: "",
        status: "",
        reference_name: "",
      },
      dicts: {
        connected: [
          { label: "Да", value: true },
          { label: "Нет", value: false },
        ],
        integrations: [],
      },
      loaded: true,
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
    companyId() {
      return this.$route.params.companyId;
    },
    columns() {
      let $this = this;
      return [
        {
          name: "company_id",
          title: "",
          hidden: true,
        },
        {
          name: "name",
          title: "Название",
          format: "text",
          width: "40%",
          sortable: true,
          searchable: true,
          align: "left",
          style_callback: styleCallback,
        },
        {
          name: "pm_name",
          title: "ПМ",
          format: "text",
          width: "40%",
          breakpoints: "xs",
          sortable: true,
          searchable: true,
          align: "left",
          style_callback: styleCallback,
        },
        {
          name: "senior_name",
          title: "Стас",
          format: "text",
          width: "40%",
          breakpoints: "sm",
          sortable: true,
          searchable: true,
          align: "left",
          style_callback: styleCallback,
        },
        {
          name: "assessors",
          title: "Асессоры",
          format: "text",
          width: "40%",
          breakpoints: "md",
          sortable: false,
          searchable: true,
          align: "left",
          hidden: $this.user_role === "assessor",
          style_callback: styleCallback,
          display_callback: (col, item) => {
            return item[col.name].map(it => it.label).join(', ')
          },
        },
        {
          name: "reference_name",
          title: "Источник",
          format: "text",
          width: "10%",
          breakpoints: "",
          sortable: true,
          searchable: true,
          search_dict: "integrations",
          align: "left",
          style_callback: styleCallback,
        },
        {
          name: "status",
          title: "Подключено",
          format: "text",
          width: "40%",
          breakpoints: "",
          sortable: true,
          searchable: true,
          search_dict: "connected",
          align: "left",
          style_callback: styleCallback,
        },
        {
          type: "buttons",
          align: "left",
          title: "",
          width: "3%",
          style_callback: styleCallback,
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
              class: "btn-warning",
              id: "spreadsheet",
              icon: "fa-table",
              width: "1%",
              tooltip: "Оценки звонков",
            },
            {
              type: "button",
              class: "btn-danger",
              id: "calls",
              icon: "fa-phone-alt",
              width: "1%",
              tooltip: "Звонки",
            },
          ],
        },
      ];
    },
  },
  created() {
    if (this.companyId) {
      this.filters.company_id = this.companyId;
    }
    this.$http.get("/api/dictionaries/integrations").then((response) => {
      this.dicts.integrations = response.data.integrations;
    });
  },
  methods: {
    setData: function (items) {
      this.loaded = true;
    },
    handleCreateProject: function () {
      if (this.companyId) {
        this.$router.push({ name: 'companies.projects.create', params: { companyId: this.companyId } });
      } else {
        this.$router.push({ name: 'projects.create' });
      }
    },
    editProject: function (item) {
      if (item.company_id) {
        this.$router.push({
          name: "companies.projects.edit",
          params: {
            companyId: item.company_id,
            projectId: item.id,
          },
        });
      } else {
        this.$router.push({
          name: "projects.edit",
          params: { projectId: item.id },
        });
      }
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
          проектами
        </h3>
      </div>
    </div>
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div v-show="!loaded" v-if="!loaded" class="card card-custom">
        <div
          class="card-body loading"
          style="padding-top: 110px; min-height: 350px"
        >
          <span
            class="fa fa-spinner fa-3x fa-spin"
            style="position: absolute; top: 99px; left: 50%"
          ></span>
        </div>
      </div>
      <div v-show="loaded" class="card card-custom">
        <div>
          <div
            v-if="user_role === 'sa' || user_role === 'pm'"
            class="card-header text-left"
          >
            <button
              id="process"
              class="btn btn-primary"
              type="button"
              value="return"
              @click="handleCreateProject"
            >
              <i class="flaticon2-plus"></i>
              Создать
            </button>
          </div>
          <div class="card-body">
            <Table
              id="projects"
              data_prop_name="projects"
              :data_src="'/api/projects'"
              :columns="columns"
              :filters="filters"
              :settings="settings"
              :dicts="dicts"
              @loaded="setData"
              @edit-project="editProject"
            />
          </div>
        </div>
      </div>
    </article>
  </div>
</template>
<style scoped>
</style>

<script>
import Table from "@/components/table/Table.vue";
import { mapGetters } from "vuex";
import srx from "@/functions";

export default {
  name: "Users",
  components: {
    Table,
  },
  data: () => ({
    columns: [
    { name: 'id', title: 'ЗАДАНИЕ', format: 'text', width: '20%', sortable: true, searchable: true, align: 'left',
    link: "/tasks/{id}",
      display_callback: function (col, item, $this) {
        return `
          <span>№ ${item.id}</span>
          <p class="m-0">${srx.customDate(item.created_at)}</p>
        `
      }
    },
    { name: 'project_name', title: 'ПРОЕКТ', format: 'text', width: '30%', sortable: true, searchable: true, align: 'left' },
    { name: 'total_duration', title: 'ОБЩИЙ ОБЪЕМ', format: 'text', width: '5%', sortable: true, align: 'right' },
    { name: 'processed', title: 'ОТРАБОТАНО', format: 'text', width: '5%', sortable: true, align: 'right' },
    { name: 'status', title: 'СТАТУС', format: 'text', width: '20%', sortable: true, searchable: true, search_dict: 'statuses', align: 'center',
      display_callback: function (col, item, $this) {
        return item.status.label
      }
    },
    { name: 'calls_count', title: 'КОЛ. ЗВОНКОВ', width: '5%', sortable: true, align: 'right' },
    { name: 'assessor', title: 'АССЕССОР', format: 'text', width: '30%', sortable: true, searchable: true, search_dict: 'extensionStates', align: 'left',
      display_callback: function (col, item, $this) {
        return item.assessor.name
      }
    },
    ]
  }),
  computed: {
    ...mapGetters(["currentUser"]),
    current_user() {
      return this.currentUser;
    },
    user_role() {
      return this.current_user.role_name;
    },
  },
};
</script>

<template>
  <div class="row m-0">
    <div style="margin: 0 0 15px 19px">
      <i
        class="flaticon-file-1"
        aria-hidden="true"
        style="font-size: 21px"
      ></i>
      <div style="display: inline-block">
        <h4>Список заданий</h4>
      </div>
    </div>
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <b-card class="card-custom">
        <Table
          id="tasks"
          data_prop_name="tasks"
          data_src="/api/tasks"
          :columns="columns"
        />
      </b-card>
    </article>
  </div>
</template>

<style scoped>

</style>
    
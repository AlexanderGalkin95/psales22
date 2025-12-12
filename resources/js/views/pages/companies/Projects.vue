<template>
  <div class="card card-custom card-stretch gutter-b card-shadowless bg-light">
    <div class="card-body py-0">
      <div class="table-responsive">
        <table
          class="table table-head-custom table-vertical-center"
          id="kt_advance_table_widget_1"
        >
          <thead>
            <tr class="text-left">
              <th style="max-width: 200px">Название</th>
              <th style="max-width: 150px">Дата старта</th>
              <th style="max-width: 50px">МОП</th>
              <th class="pr-0 text-right"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in projects" :key="i">
              <td class="pl-0">
                  <span
                    class="text-dark-75 font-weight-bolder text-hover-primary mb-1 font-size-lg"
                    role="button"
                    @click="showProject(item)"
                  >
                      {{ item.name }}
                  </span>
              </td>
              <td>
                  <span class="text-dark-75 font-weight-bolder d-block font-size-lg">
                      {{ customDate(item.date_start) }}
                  </span>
              </td>
              <td>
                <span class="text-dark-75 font-weight-bolder d-block font-size-lg">
                  {{ item.sales_managers_count || 0 }}
                </span>
              </td>
              <td class="pr-0 text-right">
                <div class="d-flex" v-if="!isReadonly">
                  <a
                    href="#"
                    class="text-hover-primary mx-1"
                    @click="handleEditProject(item)"
                  >
                    <span class="svg-icon svg-icon-md svg-icon-default">
                      <inline-svg
                        src="/media/svg/icons/Communication/Write.svg"
                      />
                    </span>
                  </a>
                  <a
                    href="#"
                    class="text-hover-primary mx-1"
                    @click="handleDeleteProject(item)"
                  >
                    <span class="svg-icon svg-icon-md svg-icon-default">
                      <inline-svg src="/media/svg/icons/General/Trash.svg" />
                    </span>
                  </a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import srx from '../../../functions.js';
import Swal from 'sweetalert2'

export default {
  name: "Projects",
  props: {
    projects: {
        type: Array,
        default: () => [],
    },
  },
  computed: {
    isReadonly() {
      return this.$route.name === 'companies.profile'
    },
  },
  methods: {
    customDate(dt) {
      return srx.customDate(dt)
    },
    handleEditProject(item) {
      this.$router.push({ name: 'companies.projects.edit', params: { companyId: item.company_id, projectId: item.id } })
    },
    handleDeleteProject(item) {
      Swal.fire({
        title: 'Вы действительно хотите удалить проект?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F64E60',
        confirmButtonText: 'Удалить',
        cancelButtonText: `Отмена`,
        preConfirm: () => {
          // return $this.$http.delete(`/api/companies/${item.id}/deactivate`)
          //   .catch((error) => {
          //     return error
          //   })
          return true
        }
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire('Проект был удален успешно', '', 'success')
        }
      })
    },
    showProject(item) {
      this.$router.push({
        name: 'companies.projects.edit',
        params: {
          projectId: item.id
        }
      })
    }
  }
};
</script>

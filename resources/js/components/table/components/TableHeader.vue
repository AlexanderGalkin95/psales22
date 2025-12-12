<script>
export default {
  name: "TableHeader",
  computed: {
    table() {
      return this.$parent
    }
  },
}
</script>

<template>
  <tr>
    <td :class="`vtable-plus ${table.getBreakpoint}`"></td>
    <template v-for="(col, cIdx) in table.columns">
      <th
        v-if="!col.hidden"
        :key="cIdx"
        :style="{ width: col.width }"
        :class="table.getHeaderClass(col)"
        @click="table.switchSort(col)"
      >
        {{ col.title }}
      </th>
    </template>
  </tr>
</template>

<style lang="scss" scoped>
tr {
  font-weight: 700;

  th {
    padding: 1.25rem;
  }

  th.sorting {
    background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATAgMAAAAPPt5fAAAACVBMVEUAAADIyMjl5eVIBBP/AAAAAXRSTlMAQObYZgAAAClJREFUCNdjwAYEwGQKiGDsBJFsTA5AUoJhAqZaiDhEDVg9RC/MHEwAANsMA91AQfd/AAAAAElFTkSuQmCC)
      no-repeat center right;
    cursor: pointer;
  }

  th.sorting_asc {
    background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATAQMAAABInqSPAAAABlBMVEUAAABVVVUT3Vn+AAAAAXRSTlMAQObYZgAAAB1JREFUCNdjwAkcgPgBAwPjBwYG5h8MDOx/GAgBAKRJBBCQbHkwAAAAAElFTkSuQmCC)
      no-repeat center right;
    cursor: pointer;
  }

  th.sorting_desc {
    background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAATAQMAAABInqSPAAAABlBMVEUAAABVVVUT3Vn+AAAAAXRSTlMAQObYZgAAABxJREFUCNdjIATY/zAwMP9gYGD8AOQ8AGIHnEoBkNkEEEbbutQAAAAASUVORK5CYII=)
      no-repeat center right;
    cursor: pointer;
  }
}
</style>
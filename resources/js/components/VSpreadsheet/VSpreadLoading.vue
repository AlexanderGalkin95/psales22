<template>
  <div class="overlay">
    <span class="spinner-border spinner-border-md spinner-danger"
          style="position: absolute;left: 50%;color: red;top: 150px;"
          role="status"
          aria-hidden="true"></span>
    <span class="sr-only">Loading...</span>
  </div>
</template>

<script>
export default {
  name: "VSpreadLoading"
}
</script>

<style scoped>

</style>
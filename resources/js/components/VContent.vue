<script>
export default {
    name: "VContent",
    props: {
        contentClass: {
            type: [String, Object],
            default: ''
        }
    }
}
</script>

<template>
    <div class="card-body d-flex flex-wrap">
        <slot/>
    </div>
</template>

<style scoped>

</style>
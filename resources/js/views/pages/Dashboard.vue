<template>
    <div class="col-md-12">
        <div class="card">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-center pt-8 sm:justify-start sm:pt-0" style="padding: 50px">
                    <div>Hello World!</div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: "Dashboard",
    mounted() {
        this.$parent.breadcrumbs = null;
        this.$parent.page = "Главная";
    }
}
</script>

<style scoped>

</style>

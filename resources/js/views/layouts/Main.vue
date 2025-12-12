<script>
import TopMenu from "./TopMenu";
import Breadcrumbs from "./Breadcrumbs";

export default {
    name: "Main",
    data() {
        return {
            breadcrumbs: null,
            page: null,
        }
    },
    components: {
        Breadcrumbs,
        TopMenu
    }
}
</script>


<template>
    <div>
        <TopMenu></TopMenu>
        <div class="br-pageheader">
            <Breadcrumbs :items="breadcrumbs" :title="page"></Breadcrumbs>
        </div>
        <div class="main" style="height: calc(100vh - 35px);">
            <RouterView :key="$route.fullPath"/>
        </div>

        <div  class="justify-center" style="position: absolute; height:55px; bottom: 0; width: 100%;text-align: center;padding-bottom: 14px;"><hr>2021 Pincher Sales Все права защищены</div>
    </div>
</template>

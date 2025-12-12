<script>

export default {
    name: "Breadcrumbs",
    props: {
        title: {
            type: String,
            default: '',
        },
        items: {
            type: Array,
            default: () => {
                return []
            },
        },
    }
}
</script>

<!--<template>-->
<!--    <nav class="breadcrumb mg-0 pd-0" style="font-size: 12px;">-->
<!--        <router-link class="breadcrumb-item text-secondary" to="/dashboard"><i class="fa fa-home" aria-hidden="true"></i></router-link>-->
<!--        <span class="breadcrumb-item active">{{ $t(title) }}</span>-->
<!--    </nav>-->
<!--</template>-->

<template>
    <!-- breadcrumb -->
    <ol class="breadcrumb">
        <router-link class="breadcrumb-item text-secondary" to="/dashboard"><i class="fa fa-home" aria-hidden="true"></i></router-link><span style="margin: 0 5px;">/</span>
        <li v-for="(item, index) in items" :key="item.text">
            <router-link class="breadcrumb-item text-secondary" :to="item.href">{{item.text}}</router-link>
            <span style="margin: 0 5px;">/</span>
        </li>
        <li>{{ $t(title) }}</li>
    </ol>
</template>

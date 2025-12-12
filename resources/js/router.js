import VueRouter from 'vue-router'
import Vue from 'vue'

Vue.use(VueRouter);

const routes = [
    {
        path: "/",
        component: () => import("./view/layout/Layout.vue"),
        children: [
            {
                path: "/dashboard",
                name: "dashboard",
                component: () => import("./view/pages/Dashboard.vue")
            },
            {
                path: "/users",
                name: "users",
                component: () => import('./views/pages/Users.vue')
            },
            {
                path: '/companies',
                component: {
                    render(h) {
                        return h('router-view')
                    },
                },
                children: [
                    {
                        path: '',
                        name: 'companies.list',
                        component: () => import('./views/pages/companies/Companies.vue'),
                    },
                    {
                        path: 'create',
                        name: 'companies.create',
                        component: () => import('./views/pages/companies/CompanyCreate.vue')
                    },
                    {
                        path: ':companyId/edit',
                        name: 'companies.edit',
                        component: () => import('./views/pages/companies/CompanyCreate.vue')
                    },
                    {
                        path: ':companyId/profile',
                        name: 'companies.profile',
                        component: () => import('./views/pages/companies/CompanyCreate.vue')
                    },
                    {
                        path: ':companyId/projects',
                        name: 'companies.projects.list',
                        component: () => import('./views/pages/Projects.vue')
                    },
                    {
                        path: ':companyId/projects/create',
                        name: 'companies.projects.create',
                        component: () => import('./views/pages/ProjectView.vue')
                    },
                    {
                        path: ':companyId/projects/:projectId',
                        name: 'companies.projects.edit',
                        component: () => import('./views/pages/ProjectView.vue')
                    },

                ]
            },
            {
                path: "/projects",
                name: "projects.list",
                component: () => import('./views/pages/Projects.vue')
            },
            {
                path: "/project/create",
                name: "projects.create",
                component: () => import('./views/pages/ProjectView.vue')
            },
            {
                path: "/projects/:projectId",
                name: "projects.edit",
                component: () => import('./views/pages/ProjectView.vue')
            },
            {
                path: "/projects/:projectId/spreadsheet",
                name: "projects.spreadsheet",
                component: () => import('./views/pages/Spreadsheet.vue')
            },
            {
                path: "/projects/:projectId/calls",
                name: "projects.calls",
                component: () => import('./views/pages/CallsPage.vue')
            },
            {
                path: "/support",
                name: "support",
                component: () => import('./views/pages/Support.vue')
            },
            {
                path: '/tasks',
                component: {
                    render(h) {
                        return h('router-view')
                    },
                },
                children: [
                    {
                        path: '',
                        component: () => import('./views/pages/tasks/TasksList.vue')
                    },
                    {
                        path: ':taskId(\\d+)',
                        component: () => import('./views/pages/tasks/profile/TaskProfile.vue')
                    }
                ]
            },
            {
                path: '/daily-reports',
                name: 'daily-reports',
                component: () => import('./views/pages/DailyReports.vue')
            },
            {
                path: '/log-events',
                name: 'log-events',
                component: () => import('./views/pages/LogEvents.vue')
            },
        ],
    },
    {
        path: '*',
        component: () => import('./view/pages/error/Error-1.vue')
    }
]

export default new VueRouter({
    mode: "history",
    routes
})

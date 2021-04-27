import Vue from "vue";
import VueRouter from "vue-router";
import store from "./store";
// require("./components");

Vue.use(VueRouter);

const router = new VueRouter({
    mode: "history",
    base: process.env.BASE_URL,
    routes: [
        {
            name: "Login",
            path: "/login",
            meta: { layout: "empty" },
            component: () => import("./components/pages/Login.vue")
        },
        // {
        //     name: "Logout",
        //     path: "/user/logout"
        //     meta: { layout: "empty" },
        //     component: () => import("./components/pages/Login.vue")
        // },
        {
            name: "Map",
            path: "/",
            meta: { layout: "main" },
            component: () => import("./components/pages/Map.vue")
        },
        {
            name: "Users",
            path: "/users",
            meta: { layout: "main", auth: true },
            component: () => import("./components/pages/Users.vue")
        },
        {
            name: "Layers",
            path: "/layers",
            meta: { layout: "main", auth: true },
            component: () => import("./components/pages/Layers.vue")
        },
        {
            name: "Roles",
            path: "/roles",
            meta: { layout: "main", auth: true },
            component: () => import("./components/pages/Roles.vue")
        },
        {
            name: "Permissions",
            path: "/permissions",
            meta: { layout: "main", auth: true },
            component: () => import("./components/pages/Permissions.vue")
        },
        {
            name: "AssigmentLayers",
            path: "/assigmentlayers",
            meta: { layout: "main", auth: true },
            component: () => import("./components/pages/AssigmentLayers.vue")
        },
        {
            name: "Categories",
            path: "/categories",
            meta: { layout: "main", auth: true },
            component: () => import("./components/pages/Categories.vue")
        },
        {
            name: "SubCategories",
            path: "/subcategories",
            meta: { layout: "main", auth: true },
            component: () => import("./components/pages/SubCategories.vue")
        }
    ]
});

router.beforeEach((to, from, next) => {
    // console.log(to);
    // console.log(to.path);

    // if (to.path == "/user/logout") {
    //     console.log(true);

    //     next("/login");
    // }

    axios.get("/user/init").then(response => {
        // console.log(response.data);
        if (response.data) {
            store.commit("updateUser", response.data);
            store.commit("updateLogged", true);
        }

        const isLogged = store.state.isLogged;

        const requireAuth = to.matched.some(record => record.meta.auth);

        if (requireAuth && !isLogged) {
            next("/login");
        } else {
            next();
        }
    });
});

export default router;

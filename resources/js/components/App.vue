<template>
    <div class="">
        <v-overlay :value="overlay">
            <v-progress-circular indeterminate size="64"></v-progress-circular>
        </v-overlay>

        <component v-if="!overlay" :is="layout"></component>
    </div>
</template>

<script>
import EmptyLayout from "./layouts/EmptyLayout";
import MainLayout from "./layouts/MainLayout";

export default {
    name: "App",
    data: () => ({
        overlay: false,
        initiated: false
    }),

    computed: {
        layout() {
            return (this.$route.meta.layout || "empty") + "-layout";
        }
    },
    methods: {
        init() {
            axios.get("/user/init").then(response => {
                // console.log(response.data);
                if (response.data) {
                    this.$store.commit("updateUser", response.data);
                    this.$store.commit("updateLogged", true);
                }
            });
        }
    },
    components: {
        EmptyLayout,
        MainLayout
    },
    created() {
        // this.init();
    },
    mounted() {}
};
</script>

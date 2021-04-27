<template>
    <v-content>
        <router-view style="flex: 1 0 auto;" />

        <app-footer />
    </v-content>
</template>

<script>
import AppFooter from "../core/AppFooter";

export default {
    name: "AppView",

    components: {
        AppFooter
    }
};
</script>

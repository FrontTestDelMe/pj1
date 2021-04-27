<template>
    <v-app>
        <app-bar />

        <drawer />

        <app-view />
    </v-app>
</template>
<script>
import AppBar from "../core/AppBar";
import Drawer from "../core/Drawer";
import AppView from "../core/AppView";

export default {
    name: "main-layout",
    data: () => ({
        expandOnHover: false
    }),
    components: {
        AppBar,
        Drawer,
        AppView
    }
};
</script>

<style>
.v-main__wrap {
    display: flex;
    flex-direction: column;
    min-height: 100%;
}
</style>

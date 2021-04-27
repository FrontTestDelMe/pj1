<template>
    <v-container>
        <v-row justify="center">
            <v-progress-circular
                :size="50"
                :color="color"
                indeterminate
            ></v-progress-circular>
        </v-row>
    </v-container>
</template>
<script>
export default {
    name: "Loader",
    computed: {
        color() {
            const colors = ["primary", "red", "purple", "green", "amber"];
            return colors[Math.floor(Math.random() * 3)];
        }
    }
};
</script>

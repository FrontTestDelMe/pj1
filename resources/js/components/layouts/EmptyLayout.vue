<template>
    <div class="empty-layout">
        <router-view />
    </div>
</template>
<script>
export default {
    name: "empty-layout",
    data: () => ({})
};
</script>
<style scoped>
.empty-layout {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

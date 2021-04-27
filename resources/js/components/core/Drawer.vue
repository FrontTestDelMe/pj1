<template>
    <v-navigation-drawer
        :right="$vuetify.rtl"
        mobile-break-point="960"
        app
        width="260"
        mini-variant-width="80"
        v-model="$store.state.drawer"
    >
        <v-list>
            <v-list-item class="px-2">
                <v-list-item-avatar tile width="100%">
                    <v-img src=""></v-img>
                </v-list-item-avatar>
            </v-list-item>

            <v-list-item link>
                <v-list-item-content>
                    <v-list-item-title class="title">
                        ЕМГИС
                    </v-list-item-title>
                    <v-list-item-subtitle
                        ><small>Тольятти</small></v-list-item-subtitle
                    >
                </v-list-item-content>
            </v-list-item>
        </v-list>

        <v-divider></v-divider>

        <v-list nav dense>
            <v-expansion-panels
                v-if="$store.state.user"
                v-model="panel"
                multiple
            >
                <v-expansion-panel>
                    <v-expansion-panel-header>
                        <v-icon medium color="blue darken-2" class="mr-3">
                            mdi-account-key
                        </v-icon>
                        Администрирование
                        пользователей</v-expansion-panel-header
                    >
                    <v-expansion-panel-content>
                        <v-list-item
                            :title="$t('USERS')"
                            v-if="can('read_users')"
                            link
                            to="/users"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-account-multiple</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>{{
                                $t("USERS")
                            }}</v-list-item-title>
                        </v-list-item>

                        <v-list-item
                            :title="$t('ROLES')"
                            v-if="can('read_roles')"
                            link
                            to="/roles"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-account-tie</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>{{
                                $t("ROLES")
                            }}</v-list-item-title>
                        </v-list-item>

                        <v-list-item
                            :title="$t('PERMISSIONS')"
                            v-if="can('read_permissions')"
                            link
                            to="/permissions"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-lock</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>{{
                                $t("PERMISSIONS")
                            }}</v-list-item-title>
                        </v-list-item>
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <v-expansion-panel>
                    <v-expansion-panel-header>
                        <v-icon medium color="blue darken-2" class="mr-3">
                            mdi-map-legend
                        </v-icon>
                        Администрирование карты
                    </v-expansion-panel-header>
                    <v-expansion-panel-content>
                        <v-list-item
                            :title="$t('LAYERS')"
                            v-if="can('read_layers')"
                            link
                            to="/layers"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-layers</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>{{
                                $t("LAYERS")
                            }}</v-list-item-title>
                        </v-list-item>

                        <v-list-item
                            :title="$t('ASSIGMENT_LAYERS')"
                            link
                            to="/assigmentLayers"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-file-lock</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>{{
                                $t("ASSIGMENT_LAYERS")
                            }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item
                            :title="$t('CATEGORIES')"
                            link
                            to="/categories"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-rectangle-outline</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>{{
                                $t("CATEGORIES")
                            }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item
                            :title="$t('SUBCATEGORIES')"
                            link
                            to="/subcategories"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-file-tree</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>{{
                                $t("SUBCATEGORIES")
                            }}</v-list-item-title>
                        </v-list-item>
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-expansion-panels>

            <v-list-item :title="$t('MAP')" link to="/">
                <v-list-item-icon>
                    <v-icon>mdi-map-outline</v-icon>
                </v-list-item-icon>
                <v-list-item-title>{{ $t("MAP") }}</v-list-item-title>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>

<script>
export default {
    data: () => ({
        panel: []
    }),
    mounted() {}
};
</script>

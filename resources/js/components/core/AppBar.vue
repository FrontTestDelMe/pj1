<template>
    <v-app-bar absolute app color="transparent" dense height="75">
        <!-- <v-btn class="mr-3" elevation="1" fab small>
            <v-icon>
                mdi-view-quilt
            </v-icon>

            <v-icon>
                mdi-dots-vertical
            </v-icon>
        </v-btn> -->

        <v-app-bar-nav-icon @click.stop="changeDrawer()"></v-app-bar-nav-icon>

        <v-toolbar-title
            class="hidden-sm-and-down font-weight-light"
            v-text="$route.name"
        />

        <v-spacer></v-spacer>

        <v-switch
            v-model="lang"
            flat
            :label="lang_name"
            @change="changeLang()"
            style="align-self:flex-end;"
        ></v-switch>

        <v-spacer></v-spacer>

        <v-switch
            v-model="$vuetify.theme.dark"
            inset
            label=""
            persistent-hint
            style="align-self:flex-end;"
        >
            <template v-slot:label>
                <v-icon>
                    mdi-weather-night
                </v-icon>
            </template>
        </v-switch>

        <v-spacer></v-spacer>

        <v-menu offset-y rounded="b-xl">
            <template v-slot:activator="{ on, attrs }">
                <v-btn plain color="primary" dark v-bind="attrs" v-on="on">
                    {{ $store.state.user ? $store.state.user.name : "Вход" }}
                </v-btn>
            </template>
            <v-list v-if="$store.state.user">
                <v-list-item
                    v-for="(item, index) in AuthItems"
                    :key="index"
                    @click="chooseMenu(item)"
                >
                    <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item>
            </v-list>
            <v-list v-if="!$store.state.user">
                <v-list-item
                    v-for="(item, index) in NotAuthItems"
                    :key="index"
                    @click="chooseMenu(item)"
                >
                    <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-menu>
        <h1>{{ user }}</h1>
        <!-- <v-btn icon>
                <v-icon>mdi-heart</v-icon>
            </v-btn>

            <v-btn icon>
                <v-icon>mdi-magnify</v-icon>
            </v-btn> -->
    </v-app-bar>
</template>
<script>
export default {
    name: "nav-bar",
    props: ["user"],
    data: () => ({
        AuthItems: [{ title: "Logout", link: "/user/logout" }],
        NotAuthItems: [{ title: "Login", link: "/user/login" }],
        auth: false,
        menu: "",
        lang_name: "Rus",
        lang: true
    }),

    mounted() {
        // console.log(this.user);
    },
    methods: {
        changeLang() {
            if (this.lang) {
                this.lang_name = "Rus";
                this.$i18n.locale = "ru";
            } else {
                this.lang_name = "Eng";
                this.$i18n.locale = "en";
            }
        },
        changeDrawer() {
            this.$store.commit("changeDrawer");
        },
        chooseMenu(item) {
            switch (item.title) {
                case "Logout":
                    this.logout();
                    break;
                case "Login":
                    this.$router.push("/login");
                    break;
            }
        },
        logout() {
            window.Laravel = null;
            this.$store.commit("logoutUser");

            axios
                .get("/user/logout")
                .then(response => {
                    // this.$toastr.s("");
                    // this.$router.push({ name: "Login" });
                    // this.$toastr.s("Успешно", "Вы вышли из системы");
                    // location.reload();
                })
                .catch(() => {
                    this.$toastr.e("Some error", "Error");
                });
        }
    }
};
</script>

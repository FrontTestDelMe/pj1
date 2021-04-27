<template>
    <v-card width="330">
        <v-card-title class="card__title">
            Единая муниципальная геоинформационная система г. о. Тольятти
        </v-card-title>
        <v-card-text>
            <form @submit.prevent="login()">
                <v-text-field
                    v-model="form.email"
                    :error-messages="emailErrors"
                    :counter="30"
                    label="E-mail"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    @input="$v.form.email.$touch()"
                    @blur="$v.form.email.$touch()"
                ></v-text-field>
                <v-text-field
                    v-model="form.password"
                    :error-messages="passwordErrors"
                    :type="showPass ? 'text' : 'password'"
                    :append-icon="showPass ? 'mdi-eye' : 'mdi-eye-off'"
                    label="Password"
                    name="password"
                    @click:append="showPass = !showPass"
                    required
                    @input="$v.form.password.$touch()"
                    @blur="$v.form.password.$touch()"
                ></v-text-field>
                <v-checkbox
                    v-model="form.remember"
                    label="Remember me"
                    name="remember"
                ></v-checkbox>
                <v-btn class="mr-4" type="submit">
                    Вход
                </v-btn>
            </form>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.card__title {
    word-break: normal;
    text-align: center;
}
</style>

<script>
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";

export default {
    mixins: [validationMixin],

    validations: {
        form: new Form({
            password: { required, maxLength: maxLength(30) },
            email: { required, email }
        })
    },

    name: "Login",
    data: () => ({
        showPass: false,
        form: new Form({
            email: "",
            password: "",
            remember: false
        })
    }),
    methods: {
        login() {
            // console.log(this.form);
            // return;
            this.form
                .post("/user/login")
                .then(response => {
                    console.log(response.data);
                    // this.$store.commit("updateUser", response.data);
                    this.$store.commit("updateUser", response.data);
                    this.$store.commit("updateLogged", true);
                    axios.post("/user/getJsPermissions").then(response => {
                        // console.log(JSON.parse(response.data.jsPermissions));

                        window.Laravel = {
                            jsPermissions: JSON.parse(
                                response.data.jsPermissions
                            ),
                            csrfToken: response.data.csrfToken
                        };
                        // window.Laravel.jsPermissions = JSON.parse(
                        //     response.data.jsPermissions
                        // );
                    });

                    this.$toastr.s("Успешно", "Добро пожаловать");
                    this.form.reset();
                    this.$router.push("/");
                })
                .catch(() => {
                    this.$toastr.e("Ошибка", "Что-то не так");
                });
        }
    },
    computed: {
        passwordErrors() {
            const errors = [];
            if (!this.$v.form.password.$dirty) return errors;
            !this.$v.form.password.maxLength &&
                errors.push("Password must be at most 30 characters long");
            !this.$v.form.password.required &&
                errors.push("Password is required");
            return errors;
        },
        emailErrors() {
            const errors = [];
            if (!this.$v.form.email.$dirty) return errors;
            !this.$v.form.email.email && errors.push("Must be valid e-mail");
            !this.$v.form.email.required && errors.push("E-mail is required");
            return errors;
        }
    },
    created() {
        if (this.$store.state.isLogged) {
            this.$router.push("/");
        }
    }
};
</script>

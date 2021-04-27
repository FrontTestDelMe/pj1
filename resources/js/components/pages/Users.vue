<template>
    <div class="">
        <v-toolbar flat>
            <v-toolbar-title>{{ $t("USERS") }}</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
            <v-text-field
                v-model="search"
                @change="getUsers(search)"
                @click:append="getUsers(search)"
                clearable
                append-icon="mdi-magnify"
                :label="$t('SEARCH')"
                single-line
                hide-details
            ></v-text-field>
            <v-spacer></v-spacer>
            <v-btn tile color="success" @click="newItem()">
                <v-icon left>
                    mdi-plus
                </v-icon>
                {{ $t("USER") }}
            </v-btn>
        </v-toolbar>
        <Loader v-if="loading" />
        <v-simple-table v-else>
            <template v-slot:default>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ $t("NAME") }}</th>
                        <th>{{ $t("ROLES") }}</th>
                        <th>{{ $t("EMAIL") }}</th>
                        <th>{{ $t("ACTION") }}</th>
                        <th>{{ $t("DATE_CREATED") }}</th>
                        <th>{{ $t("DATE_UPDATED") }}</th>
                    </tr>
                </thead>

                <tbody>
                    <h3 v-if="!users.length">{{ $t("NO_DATA") }}</h3>
                    <tr v-for="user in users" :key="user.id">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td v-if="user.roles.length">
                            {{
                                user.roles
                                    .map(role => {
                                        return role.name;
                                    })
                                    .join("; ")
                            }}
                        </td>
                        <td v-else>Отсутствует</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <v-btn
                                small
                                color="primary"
                                @click="viewItem(user)"
                            >
                                <v-icon left>
                                    mdi-eye
                                </v-icon>
                                {{ $t("READ") }}
                            </v-btn>
                            <v-btn
                                small
                                color="warning"
                                @click="editItem(user)"
                            >
                                <v-icon left>
                                    mdi-pencil
                                </v-icon>
                                {{ $t("EDIT") | crop_text(5, ".") }}
                            </v-btn>
                            <v-btn
                                small
                                color="error"
                                @click="deleteItem(user)"
                            >
                                <v-icon left>
                                    mdi-delete
                                </v-icon>
                                {{ $t("DELETE") }}
                            </v-btn>
                        </td>
                        <td>
                            {{ user.created_at | date }}
                        </td>
                        <td>
                            {{ user.updated_at | date }}
                        </td>
                    </tr>
                </tbody>

                <!-- Dialog View -->
                <v-dialog v-model="dialog" persistent max-width="600px">
                    <v-card>
                        <v-card-title>
                            <span class="headline">Профиль</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            :disabled="viewMode"
                                            v-model.trim="form.name"
                                            label="Name"
                                            hint="Name"
                                            :error-messages="nameErrors"
                                            :counter="30"
                                            required
                                            @input="$v.form.name.$touch()"
                                            @blur="$v.form.name.$touch()"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            :disabled="viewMode"
                                            v-model.trim="form.email"
                                            label="Email"
                                            hint="Email"
                                            :error-messages="emailErrors"
                                            :counter="30"
                                            required
                                            @input="$v.form.email.$touch()"
                                            @blur="$v.form.email.$touch()"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            :disabled="viewMode"
                                            v-model="form.password"
                                            :type="
                                                showPass ? 'text' : 'password'
                                            "
                                            :append-icon="
                                                showPass
                                                    ? 'mdi-eye'
                                                    : 'mdi-eye-off'
                                            "
                                            @click:append="showPass = !showPass"
                                            label="Password"
                                            hint="Password"
                                            :counter="30"
                                            required
                                            :error-messages="passwordErrors"
                                            @input="$v.form.password.$touch()"
                                            @blur="$v.form.password.$touch()"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <v-autocomplete
                                            :disabled="viewMode"
                                            v-model="form.roles"
                                            :items="roles"
                                            label="Roles"
                                            auto-select-first
                                            chips
                                            multiple
                                            small-chips
                                            @change="showRPermissions()"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <v-autocomplete
                                            :disabled="viewMode"
                                            v-model="form.permissions"
                                            :items="permissions"
                                            label="Permissions"
                                            auto-select-first
                                            chips
                                            clearable
                                            deletable-chips
                                            multiple
                                            small-chips
                                        ></v-autocomplete>
                                    </v-col>

                                    <v-col cols="12" v-if="!editMode">
                                        <v-item-group multiple>
                                            <v-subheader
                                                >Все разрешения</v-subheader
                                            >
                                            <v-item
                                                v-for="permission in form.AllPermissions"
                                                :key="permission.id"
                                            >
                                                <v-chip class="ma-1">
                                                    {{ permission.name }}
                                                </v-chip>
                                            </v-item>
                                        </v-item-group>
                                    </v-col>
                                    <v-col cols="12" v-else>
                                        <v-item-group multiple>
                                            <v-subheader
                                                >Разрешения от
                                                ролей</v-subheader
                                            >
                                            <v-item
                                                v-for="(item,
                                                index) in permissionsByRoles.permissions"
                                                :key="index"
                                            >
                                                <v-chip class="ma-1">
                                                    {{ item }}
                                                </v-chip>
                                            </v-item>
                                        </v-item-group>
                                    </v-col>
                                </v-row>
                            </v-container>
                            <!-- <small>*indicates required field</small> -->
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                color="blue darken-1"
                                text
                                @click="dialog = false"
                            >
                                Close
                            </v-btn>
                            <v-btn
                                v-show="!viewMode"
                                color="blue darken-1"
                                text
                                @click="!editMode ? createItem() : updateItem()"
                            >
                                Save
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <!-- /Dialog View -->
            </template>
        </v-simple-table>
    </div>
</template>
<script>
import Loader from "../core/Loader";
import { required, email, maxLength } from "vuelidate/lib/validators";

export default {
    name: "Users",
    data: () => ({
        action: "",
        search: "",
        editMode: false,
        viewMode: false,
        loading: false,
        dialog: false,
        showPass: false,
        load: true,
        // user: {},
        users: [],
        roles: [],
        permissions: [],
        permissionsByRoles: [],
        form: new Form({
            id: "",
            name: "",
            email: "",
            password: "",
            permissions: [],
            roles: [],
            AllPermissions: []
        })
    }),
    validations: {
        form: new Form({
            name: { required, maxLength: maxLength(30) },
            email: { email, required, maxLength: maxLength(30) },
            password: { required, maxLength: maxLength(30) }
        })
    },
    computed: {
        nameErrors() {
            const errors = [];
            if (!this.$v.form.name.$dirty) return errors;
            !this.$v.form.name.maxLength &&
                errors.push("Name must be at most 30 characters long");
            !this.$v.form.name.required && errors.push("Name is required.");
            return errors;
        },
        emailErrors() {
            const errors = [];
            if (!this.$v.form.email.$dirty) return errors;
            !this.$v.form.email.maxLength &&
                errors.push("Email must be at most 30 characters long");
            !this.$v.form.email.required && errors.push("Email is required.");
            return errors;
        },
        passwordErrors() {
            const errors = [];
            if (!this.$v.form.password.$dirty) return errors;
            !this.$v.form.password.maxLength &&
                errors.push("Password must be at most 30 characters long");
            !this.$v.form.password.required &&
                errors.push("Password is required.");
            return errors;
        }

        // formTitle() {
        //     return !this.editMode ? "New Item" : "Edit Item";
        // }
    },

    methods: {
        showRPermissions() {
            // console.log(this.form.roles);
            if (this.form.roles.length) {
                axios
                    .post("/role/getPermissionsByRoles", {
                        roles: this.form.roles
                    })
                    .then(response => {
                        this.loading = false;
                        // this.users = response.data.users;
                        // console.log(response.data);
                        this.permissionsByRoles = response.data;

                        // console.log(this.users);
                        // let tmp = this.users.map(item => {
                        //     return item.roles.map(role => {
                        //         return (role = role.name);
                        //     });
                        // });
                        // this.users.roles = tmp;

                        // console.log(tmp);
                        // console.log(this.users);
                    })
                    .catch(() => {
                        this.loading = false;
                        this.$toastr.e("Cannot load users", "Error");
                    });
            } else {
                this.permissionsByRoles = [];
            }
        },
        getUsers(search) {
            this.action = "Loading...";
            this.loading = true;
            axios
                .get("/user/getAll/", {
                    params: {
                        search
                    }
                })
                .then(response => {
                    this.loading = false;
                    this.users = response.data.users;
                    // console.log(this.users);
                    // let tmp = this.users.map(item => {
                    //     return item.roles.map(role => {
                    //         return (role = role.name);
                    //     });
                    // });
                    // this.users.roles = tmp;

                    // console.log(tmp);
                    // console.log(this.users);
                })
                .catch(() => {
                    this.loading = false;
                    this.$toastr.e("Cannot load users", "Error");
                });
        },
        viewItem(user) {
            this.editMode = false;
            this.viewMode = true;
            this.dialog = true;

            this.form.fill(user);

            this.form.roles = this.form.roles.map(role => {
                return role.name;
            });

            this.form.permissions = this.form.permissions.map(permission => {
                return permission.name;
            });

            //  this.form.AllPermissions = this.form.AllPermissions.map(permission => {
            //     return permission.name;
            // });
        },
        getRoles() {
            axios.get("/role/getAll").then(response => {
                this.roles = response.data.roles;
                this.roles = this.roles.map(item => {
                    return item.name;
                });
                // this.form.role = this.roles[0].id;
            });
        },
        getPermissions() {
            axios.get("/permission/getAll").then(response => {
                this.permissions = response.data.permissions;
                this.permissions = this.permissions.map(item => {
                    return item.name;
                });
            });
        },
        newItem() {
            this.viewMode = false;
            this.editMode = false;
            this.form.reset();
            this.dialog = true;
        },
        createItem() {
            this.$v.$touch();
            // console.log(this.$v);
            // console.log(this.$v.form);
            // console.log(this.$v.form.name);
            // console.log(this.$v.form.email);
            // console.log(this.$v.form.password);
            if (
                !this.$v.form.name.$error &&
                !this.$v.form.email.$error &&
                !this.$v.form.password.$error
            ) {
            } else {
                return;
            }

            this.action = "Creating user...";
            this.load = false;

            this.form
                .post("/account/create")
                .then(response => {
                    this.load = true;
                    this.$toastr.s("User created", "Created");
                    this.form.reset();
                    this.getUsers();
                })
                .catch(() => {
                    this.load = true;
                    this.$toastr.e("Cannot create user", "Error");
                });
        },
        editItem(user) {
            this.viewMode = false;
            this.editMode = true;
            this.dialog = true;
            this.form.reset();
            this.form.fill(user);
            // this.form.role = user.roles[0].id;

            this.form.roles = user.roles.map(role => {
                return role.name;
            });

            this.form.permissions = user.userPermissions;
            this.showRPermissions();
        },
        updateItem() {
            this.action = "Update user...";
            this.load = false;
            this.form
                .put("/account/update/" + this.form.id)
                .then(response => {
                    this.load = true;
                    this.$toastr.s(
                        "User information updated successfully",
                        "Created"
                    );
                    this.form.reset();
                    this.editMode = false;
                    this.getUsers();
                })
                .catch(() => {
                    this.load = true;
                    this.$toastr.e("Cannot update user information", "Error");
                });
        },
        deleteItem(user) {
            Swal.fire({
                title: "Are you sure?",
                text: user.name + " will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(result => {
                if (result.isConfirmed) {
                    axios
                        .delete("/user/delete/" + user.id)
                        .then(() => {
                            toast.fire(
                                "Deleted!",
                                user.name + " has been deleted sucessfully.",
                                "success"
                            );
                            this.getUsers();
                        })
                        .catch(() => {
                            toast.fire(
                                "Deleted!",
                                user.name +
                                    " was unable to be remove, try again later",
                                "error"
                            );
                        });
                }
            });
        }
    },
    created() {
        this.getUsers();
        this.getRoles();
        this.getPermissions();
    },
    components: { Loader }
};
</script>

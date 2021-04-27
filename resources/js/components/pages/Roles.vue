<template>
    <v-data-table
        :headers="headers"
        :items="roles"
        :loading="loading"
        :search="search"
        no-data-text="Нет ролей"
        sort-by="name"
        class="elevation-1"
    >
        <template v-slot:top>
            <v-toolbar flat>
                <v-toolbar-title>{{ $t("ROLES") }}</v-toolbar-title>
                <v-divider class="mx-4" inset vertical></v-divider>
                <v-spacer></v-spacer>
                <v-text-field
                    v-model="search"
                    append-icon="mdi-magnify"
                    :label="$t('SEARCH')"
                    single-line
                    hide-details
                ></v-text-field>
                <v-spacer></v-spacer>
                <v-dialog persistent v-model="createDialog" max-width="500px">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn
                            color="primary"
                            dark
                            class="mb-2"
                            v-bind="attrs"
                            v-on="on"
                        >
                            + {{ $t("ROLE") }}
                        </v-btn>
                    </template>
                    <v-card>
                        <v-card-title>
                            <span class="headline">{{ formTitle }}</span>
                        </v-card-title>

                        <v-card-text>
                            <v-container>
                                <form>
                                    <v-text-field
                                        v-model="form.name"
                                        :error-messages="nameErrors"
                                        :counter="30"
                                        label="Name"
                                        required
                                        @input="$v.form.name.$touch()"
                                        @blur="$v.form.name.$touch()"
                                    ></v-text-field>
                                    <v-autocomplete
                                        v-model="form.permissions"
                                        label="Permissions"
                                        :items="permissions"
                                        item-text="name"
                                        item-value="name"
                                        auto-select-first
                                        chips
                                        clearable
                                        deletable-chips
                                        multiple
                                        small-chips
                                    ></v-autocomplete>

                                    <v-btn
                                        class="mr-4"
                                        :loading="loading"
                                        @click="
                                            !editMode
                                                ? createItem()
                                                : updateItem()
                                        "
                                    >
                                        Submit
                                    </v-btn>
                                    <v-btn @click="closeCreate()">
                                        Close
                                    </v-btn>
                                </form>
                            </v-container>
                        </v-card-text>
                    </v-card>
                </v-dialog>
            </v-toolbar>
        </template>
        <template v-slot:item.actions="{ item }">
            <v-icon small class="mr-2" @click="editItem(item)">
                mdi-pencil
            </v-icon>
            <v-icon small @click="deleteItem(item)">
                mdi-delete
            </v-icon>
        </template>
        <template v-slot:no-data>
            <v-btn color="primary" @click="initialize">
                Reset
            </v-btn>
        </template>
    </v-data-table>
</template>

<script>
import { validationMixin } from "vuelidate";
import { required, maxLength } from "vuelidate/lib/validators";

export default {
    mixins: [validationMixin],

    validations: {
        form: new Form({
            name: { required, maxLength: maxLength(30) }
        })
    },

    data: () => ({
        search: "",
        editMode: false,
        createDialog: false,
        loading: false,
        role: {},
        roles: [],
        permissions: [],
        form: new Form({
            id: "",
            name: "",
            permissions: []
        }),
        headers: [
            {
                text: "ID",
                align: "start",
                sortable: false,
                value: "id"
            },
            { text: "Name", value: "name" },
            {
                text: "Permissions",
                value: "rolePermissions",
                sortable: false
            },
            { text: "Created", value: "created_at" },
            { text: "Updated", value: "updated_at" },
            { text: "Actions", value: "actions", sortable: false }
        ]
    }),

    computed: {
        selectErrors() {
            const errors = [];
            if (!this.$v.select.$dirty) return errors;
            !this.$v.select.required && errors.push("Item is required");
            return errors;
        },
        nameErrors() {
            const errors = [];
            if (!this.$v.form.name.$dirty) return errors;
            !this.$v.form.name.maxLength &&
                errors.push("Name must be at most 30 characters long");
            !this.$v.form.name.required && errors.push("Name is required.");
            return errors;
        },
        formTitle() {
            return !this.editMode ? "New Item" : "Edit Item";
        }
    },

    methods: {
        initialize() {
            this.getRoles();
            this.getPermissions();
        },
        getRoles() {
            this.loading = true;
            axios
                .get("/role/getAll")
                .then(response => {
                    this.roles = response.data.roles;
                    this.roles.forEach(item => {
                        item.created_at = moment(item.created_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                        item.updated_at = moment(item.updated_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                    });
                    this.loading = false;
                })
                .catch(() => {
                    this.$toastr.e("Cannot load roles", "Error");
                    this.loading = false;
                });
        },
        getPermissions() {
            this.loading = true;
            axios
                .get("/permission/getAll")
                .then(response => {
                    this.permissions = response.data.permissions;
                    this.loading = false;
                })
                .catch(() => {
                    this.$toastr.e("Cannot load permissions", "Error");
                    this.loading = false;
                });
        },
        createMode() {
            this.editMode = false;
            this.createDialog = true;
        },
        createItem() {
            this.$v.$touch();

            this.form
                .post("/role/create")
                .then(() => {
                    Swal.fire({
                        icon: "success",
                        title: "Role Created",
                        text: "Your Role has been created"
                    });
                    this.createDialog = false;
                    // this.$v.$reset();
                    this.initialize();
                })
                .catch(() => {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong!"
                    });
                });
        },
        editItem(role) {
            this.editMode = true;
            this.form.reset();
            this.form.fill(role);
            this.form.permissions = role.rolePermissions;
            // this.form.name = permission.name;
            // this.form.permissions = user.userPermissions;
            this.createDialog = true;
        },
        updateItem() {
            this.form
                .put("/role/update/" + this.form.id)
                .then(response => {
                    this.$toastr.s(
                        "Role information updated successfully",
                        "Updated"
                    );
                    this.createDialog = false;
                    this.form.reset();
                    this.initialize();
                })
                .catch(() => {
                    this.$toastr.e("Cannot update role information", "Error");
                });
        },
        deleteItem(role) {
            Swal.fire({
                title: "Are you sure?",
                text: role.name + " will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(result => {
                if (result.isConfirmed) {
                    axios
                        .delete("/role/delete/" + role.id)
                        .then(() => {
                            toast.fire(
                                "Deleted!",
                                role.name + " has been deleted sucessfully.",
                                "success"
                            );
                            // Fire.$emit("loadUser");
                            this.initialize();
                        })
                        .catch(() => {
                            toast.fire(
                                "Deleted!",
                                role.name +
                                    " was unable to be remove, try again later",
                                "error"
                            );
                        });
                }
            });
        },
        closeCreate() {
            this.createDialog = false;
            this.editMode = false;
            this.form.reset();
            this.$v.$reset();
        }
    },
    created() {
        this.loading = true;
        this.initialize();
        this.loading = false;
    }
};
</script>

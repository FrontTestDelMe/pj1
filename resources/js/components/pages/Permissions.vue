<template>
    <v-data-table
        :headers="headers"
        :items="permissions"
        :search="search"
        :loading="loadingTable"
        sort-by="name"
        class="elevation-1"
    >
        <template v-slot:top>
            <v-toolbar flat>
                <v-toolbar-title>Разрешения</v-toolbar-title>
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
                <v-btn color="primary" dark class="mb-2" @click="createMode">
                    + Разрешение
                </v-btn>
                <v-dialog v-model="createDialog" max-width="500px">
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
                                        label="Наименование"
                                        required
                                        @input="$v.form.name.$touch()"
                                        @blur="$v.form.name.$touch()"
                                    ></v-text-field>

                                    <v-btn
                                        class="mr-4"
                                        :loading="loading"
                                        @click="
                                            !editMode
                                                ? createItem()
                                                : updateItem()
                                        "
                                    >
                                        Сохранить
                                    </v-btn>
                                    <v-btn @click="closeCreate()">
                                        Закрыть
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
            <v-btn color="primary" :loading="loading" @click="initialize">
                Перезагрузить
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
        createDialog: false,
        permission: {},
        permissions: [],
        action: "",
        search: "",
        loading: false,
        loadingTable: false,
        loadingCreate: false,
        loadingDelete: false,
        loadingUpdate: false,

        editMode: false,
        form: new Form({
            id: "",
            name: ""
        }),
        headers: [
            {
                text: "ID",
                align: "start",
                sortable: false,
                value: "id"
            },
            { text: "Name", value: "name" },
            { text: "Created", value: "created_at" },
            { text: "Updated", value: "updated_at" },
            { text: "Actions", value: "actions", sortable: false }
        ]
    }),

    computed: {
        nameErrors() {
            const errors = [];
            if (!this.$v.form.name.$dirty) return errors;
            !this.$v.form.name.maxLength &&
                errors.push("Name must be at most 30 characters long");
            !this.$v.form.name.required && errors.push("Name is required.");
            return errors;
        },

        formTitle() {
            return !this.editMode ? "Создание" : "Редактирование";
        }
    },

    methods: {
        initialize() {
            this.action = "Loading...";
            this.loadingTable = true;
            axios
                .get("/permission/getAll")
                .then(response => {
                    this.permissions = response.data.permissions;
                    this.permissions.forEach(item => {
                        item.created_at = moment(item.created_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                        item.updated_at = moment(item.updated_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                    });
                    this.loadingTable = false;
                })
                .catch(() => {
                    this.$toastr.e("Cannot load permissions", "Error");
                    this.loadingTable = false;
                });
        },
        createMode() {
            this.editMode = false;
            this.createDialog = true;
        },
        createItem() {
            this.$v.$touch();
            this.loading = true;
            this.form
                .post("/permission/create")
                .then(() => {
                    Swal.fire({
                        icon: "success",
                        title: "Permission Created",
                        text: "Your Permission has been created"
                    });
                    this.createDialog = false;
                    this.loading = false;
                    // this.$v.$reset();
                    this.initialize();
                })
                .catch(() => {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong!"
                    });
                    this.loading = false;
                });
        },
        editItem(permission) {
            this.editMode = true;
            this.form.reset();
            this.form.fill(permission);
            // this.form.name = permission.name;
            // this.form.permissions = user.userPermissions;
            this.createDialog = true;
        },
        updateItem() {
            this.action = "Update permission...";
            this.loading = true;
            this.form
                .put("/permission/update/" + this.form.id)
                .then(response => {
                    this.loading = false;
                    this.$toastr.s(
                        "Permission information updated successfully",
                        "Updated"
                    );
                    this.createDialog = false;
                    this.form.reset();
                    this.loading = false;
                    this.initialize();
                })
                .catch(() => {
                    this.loading = false;
                    this.$toastr.e(
                        "Cannot update permission information",
                        "Error"
                    );
                });
        },
        deleteItem(permission) {
            Swal.fire({
                title: "Are you sure?",
                text: permission.name + " will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(result => {
                if (result.isConfirmed) {
                    axios
                        .delete("/permission/delete/" + permission.id)
                        .then(() => {
                            toast.fire(
                                "Deleted!",
                                permission.name +
                                    " has been deleted sucessfully.",
                                "success"
                            );
                            // Fire.$emit("loadUser");
                            this.initialize();
                        })
                        .catch(() => {
                            toast.fire(
                                "Deleted!",
                                permission.name +
                                    " was unable to be remove, try again later",
                                "error"
                            );
                        });
                }
            });
        },
        closeCreate() {
            this.createDialog = false;
            this.$v.$reset();
        }
    },
    created() {
        this.initialize();
    }
};
</script>

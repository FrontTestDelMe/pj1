<template>
    <v-data-table
        :headers="headers"
        :items="subCategories"
        :search="search"
        sort-by="name"
        class="elevation-1"
    >
        <template v-slot:top>
            <v-toolbar flat>
                <v-toolbar-title>{{ $t("SUBCATEGORIES") }}</v-toolbar-title>
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
                <v-dialog v-model="dialog" max-width="500px">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn
                            color="primary"
                            dark
                            class="mb-2"
                            v-bind="attrs"
                            v-on="on"
                        >
                            + {{ $t("SUBCATEGORY") }}
                        </v-btn>
                    </template>
                    <v-card>
                        <v-card-title>
                            <span class="headline">{{ formTitle }}</span>
                        </v-card-title>

                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            v-model="form.name"
                                            label="Name"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            v-model="form.priority"
                                            label="Priority"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-autocomplete
                                            v-model="form.category"
                                            label="Categories"
                                            :items="categories"
                                            item-text="name"
                                            item-value="id"
                                            auto-select-first
                                        ></v-autocomplete>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                class="mr-4"
                                :loading="loading"
                                @click="!editMode ? createItem() : updateItem()"
                            >
                                Submit
                            </v-btn>
                            <v-btn @click="dialog = !dialog">
                                Close
                            </v-btn>
                        </v-card-actions>
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

    data: () => ({
        name: "SubCategories",
        dialog: false,
        dialogDelete: false,
        loading: false,
        search: "",
        editMode: false,
        headers: [
            {
                text: "Name",
                align: "start",
                sortable: true,
                value: "name"
            },
            { text: "Priority", value: "priority", sortable: true },
            {
                text: "Category",
                value: "get_categories_relation[0].name",
                sortable: true
            },
            { text: "Created", value: "created_at", sortable: true },
            { text: "Updated", value: "updated_at", sortable: true },
            { text: "Actions", value: "actions", sortable: false }
        ],
        categories: [],
        categories_id: [],
        subCategories: [],
        editedIndex: -1,
        editedItem: {
            name: "",
            priority: 0,
            category: 0
        },
        form: new Form({
            id: "",
            name: "",
            priority: 0,
            category: 0
        }),
        defaultItem: {
            name: "",
            priority: 0,
            category: 0
        }
    }),

    validations: {
        form: new Form({
            name: { required, maxLength: maxLength(30) },
            priority: { required, maxLength: maxLength(3) },
            category: { required }
        })
    },

    computed: {
        formTitle() {
            return !this.editMode ? "New Item" : "Edit Item";
        },
        nameErrors() {
            const errors = [];
            if (!this.$v.form.name.$dirty) return errors;
            !this.$v.form.name.maxLength &&
                errors.push("Name must be at most 30 characters long");
            !this.$v.form.name.required && errors.push("Name is required.");
            return errors;
        },
        priorityErrors() {
            const errors = [];
            if (!this.$v.form.priority.$dirty) return errors;
            !this.$v.form.priority.maxLength &&
                errors.push("Priority must be at most 3 characters long");
            !this.$v.form.priority.required &&
                errors.push("Priority is required.");
            return errors;
        },
        categoryErrors() {
            const errors = [];
            if (!this.$v.form.category.$dirty) return errors;

            !this.$v.form.category.required &&
                errors.push("Category is required.");
            return errors;
        }
    },

    watch: {
        dialog(val) {
            val || this.close();
        },
        dialogDelete(val) {
            val || this.closeDelete();
        }
    },

    created() {
        this.initialize();
    },

    methods: {
        initialize() {
            this.getCategories();
            this.getSubCategories();
        },

        editItem(item) {
            // this.editedIndex = this.categories.indexOf(item);
            // this.editedItem = Object.assign({}, item);
            this.form.reset();
            this.form.fill(item);
            this.form.category = item.get_categories_relation[0].id;
            this.dialog = true;
            this.editMode = true;
        },
        updateItem() {
            this.loading = true;
            this.form
                .put("/subcategory/update/" + this.form.id)
                .then(response => {
                    this.loading = false;
                    this.$toastr.s(
                        "Category information updated successfully",
                        "Updated"
                    );
                    this.dialog = false;
                    this.editMode = false;
                    this.form.reset();
                    this.initialize();
                })
                .catch(() => {
                    this.loading = false;
                    this.$toastr.e(
                        "Cannot update category information",
                        "Error"
                    );
                });
        },
        deleteItem(item) {
            Swal.fire({
                title: "Are you sure?",
                text: item.name + " will be deleted category!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(result => {
                if (result.isConfirmed) {
                    axios
                        .delete("/subcategory/delete/" + item.id)
                        .then(() => {
                            toast.fire(
                                "Deleted!",
                                item.name + " has been deleted sucessfully.",
                                "success"
                            );
                            // Fire.$emit("loadUser");
                            this.initialize();
                        })
                        .catch(() => {
                            toast.fire(
                                "Deleted!",
                                item.name +
                                    " was unable to be remove, try again later",
                                "error"
                            );
                        });
                }
            });
        },

        deleteItemConfirm() {
            this.categories.splice(this.editedIndex, 1);
            this.closeDelete();
        },

        close() {
            this.dialog = false;
            this.editMode = false;
        },

        closeDelete() {
            this.dialogDelete = false;
        },

        createItem() {
            this.form
                .post("/subcategory/create")
                .then(() => {
                    Swal.fire({
                        icon: "success",
                        title: "Category Created",
                        text: "Your Category has been created"
                    });
                    this.dialog = false;
                    // this.$v.$reset();
                    this.initialize();
                })
                .catch(err => {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong!"
                    });
                });
        },
        getCategories() {
            this.loading = true;
            axios
                .get("/categories/getAll")
                .then(response => {
                    this.loading = false;
                    this.categories = response.data.categories;
                })
                .catch(err => {
                    this.loading = false;
                    this.$toastr.e("Cannot load categories", "Error");
                });
        },
        getSubCategories() {
            this.loading = true;
            axios
                .get("/subcategory/getAll")
                .then(response => {
                    this.loading = false;
                    this.subCategories = response.data.subCategory;
                    this.subCategories.forEach(item => {
                        item.created_at = moment(item.created_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                        item.updated_at = moment(item.updated_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                    });
                })
                .catch(err => {
                    this.loading = false;
                    this.$toastr.e("Cannot load subCategories", "Error");
                });
        }
    }
};
</script>

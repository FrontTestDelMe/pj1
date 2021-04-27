<template>
    <v-data-table
        :headers="headers"
        :items="layers"
        :search="search"
        :loading="loading"
        sort-by="calories"
        class="elevation-1"
    >
        <template v-slot:top>
            <v-toolbar flat>
                <v-toolbar-title>{{ $t("LAYERS") }}</v-toolbar-title>
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
                            + {{ $t("LAYER") }}
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
                                            :label="$t('NAME')"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            v-model="form.link"
                                            label="Link"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            v-model="form.description"
                                            label="Description"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-text-field
                                            v-model="form.priority"
                                            label="Priority"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-select
                                            v-model="selected_category"
                                            :items="categories"
                                            item-text="name"
                                            item-value="id"
                                            label="Категория"
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="4">
                                        <v-select
                                            v-model="form.selected_sub_category"
                                            :items="sub_category_choose"
                                            item-text="name"
                                            item-value="id"
                                            label="Подкатегория"
                                        ></v-select>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue darken-1" text @click="close">
                                {{ $t("CANCEL") }}
                            </v-btn>
                            <v-btn
                                color="blue darken-1"
                                text
                                :loading="loading"
                                @click="!editMode ? createItem() : updateItem()"
                            >
                                {{ $t("SAVE") }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-dialog v-model="dialogDelete" max-width="500px">
                    <v-card>
                        <v-card-title class="headline"
                            >Are you sure you want to delete this
                            item?</v-card-title
                        >
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                color="blue darken-1"
                                text
                                @click="closeDelete"
                            >
                                {{ $t("CANCEL") }}
                            </v-btn>
                            <v-btn
                                color="blue darken-1"
                                text
                                @click="deleteItemConfirm"
                                >OK</v-btn
                            >
                            <v-spacer></v-spacer>
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
                {{ $t("REFRESH") }}
            </v-btn>
        </template>
    </v-data-table>
</template>

<script>
export default {
    data: () => ({
        search: "",
        dialog: false,
        dialogDelete: false,
        editMode: false,
        loading: false,
        categories: [],
        sub_categories: [],
        sub_category_choose: [],
        selected_category: 0,
        headers: [
            {
                text: "ID",
                align: "start",
                sortable: false,
                value: "id"
            },
            { text: "Name", value: "name" },
            { text: "Link", value: "link" },
            { text: "Description", value: "description", sortable: false },
            { text: "Priority", value: "priority" },
            {
                text: "Подкатегория",
                value: "get_sub_categories_relation[0].name"
            },
            { text: "Created", value: "created_at" },
            { text: "Updated", value: "updated_at" },
            { text: "Actions", value: "actions", sortable: false }
        ],
        layers: [],
        editedIndex: -1,
        editedItem: {
            name: "",
            link: "",
            description: "",
            priority: 0,
            selected_sub_category: 0
        },
        defaultItem: {
            name: "",
            link: "",
            description: "",
            priority: 0,
            selected_sub_category: 0
        },
        form: new Form({
            name: "",
            link: "",
            description: "",
            priority: 0,
            selected_sub_category: 0
        })
    }),

    computed: {
        formTitle() {
            return this.editedIndex === -1
                ? this.$t("CREATE")
                : this.$t("EDIT");
        }
    },

    watch: {
        dialog(val) {
            val || this.close();
        },
        dialogDelete(val) {
            val || this.closeDelete();
        },
        selected_category(catId) {
            this.sub_category_choose = this.sub_categories.filter(sc => {
                return sc.get_categories_relation[0].id == catId;
            });
            // this.form.selected_sub_category = this.sub_category_choose[0].id;
        }
    },

    created() {
        // this.token = window.Laravel.csrfToken;
        this.initialize();
    },

    methods: {
        initialize() {
            this.loading = true;
            axios
                .get("/layer/get_layers")
                .then(response => {
                    this.layers = response.data.layers;
                    this.layers.forEach(item => {
                        item.created_at = moment(item.created_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                        item.updated_at = moment(item.updated_at).format(
                            "DD.MM.YYYY, hh:mm:ss"
                        );
                    });
                })
                .catch(() => {
                    this.$toastr.e("Cannot load layers", "Error");
                });

            this.getCategories();
            this.getSubCategories();
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
                    this.sub_categories = response.data.subCategory;
                })
                .catch(err => {
                    this.loading = false;
                    this.$toastr.e("Cannot load categories", "Error");
                });
        },

        editItem(item) {
            this.editedIndex = this.layers.indexOf(item);
            // console.log(item);
            this.form.name = item.name;
            this.form.link = item.link;
            this.form.description = item.description;
            this.form.priority = item.priority;
            this.form.id = item.id;

            this.selected_category =
                item.get_sub_categories_relation[0].get_categories_relation[0].id;
            this.form.selected_sub_category =
                item.get_sub_categories_relation[0].id;

            this.editMode = true;
            this.dialog = true;
        },

        deleteItem(item) {
            this.editedIndex = this.layers.indexOf(item);
            // this.form = Object.assign({}, item);

            Swal.fire({
                title: this.$t("DIALOG_DELETE"),
                text: item.name + this.$t("DELETED_PERMANENTLY"),
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: this.$t("DELETE")
            }).then(result => {
                if (result.isConfirmed) {
                    axios
                        .delete("/layer/delete/" + item.id)
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

            // this.dialogDelete = true;
        },

        deleteItemConfirm() {
            this.closeDelete();
        },

        close() {
            this.dialog = false;
            this.$nextTick(() => {
                this.form.reset();
                this.editedIndex = -1;
            });
            this.editMode = false;
        },

        closeDelete() {
            this.dialogDelete = false;
            this.$nextTick(() => {
                this.form.reset();
                this.editedIndex = -1;
            });
        },
        updateItem() {
            this.loading = true;
            this.form
                .put("/layer/update/" + this.form.id)
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
                    this.close();
                })
                .catch(() => {
                    this.loading = false;
                    this.$toastr.e(
                        "Cannot update category information",
                        "Error"
                    );
                });
        },

        createItem() {
            this.form
                .post("/layer/create")
                .then(response => {
                    this.$toastr.s("Layer created", "Created");
                    // Fire.$emit("loadLayer");
                    this.form.reset();
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        let msg = "";
                        for (var key in error.response.data.errors) {
                            msg += error.response.data.errors[key][0] + "</br>";
                        }

                        this.$toastr.e(msg, "Error");
                    } else {
                        this.$toastr.e("Cannot create layer", "Error");
                    }
                });

            this.initialize();
            this.close();
            this.editMode = false;
        }
    }
};
</script>

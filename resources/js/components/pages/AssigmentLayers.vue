<template>
    <div class="">
        <v-toolbar flat>
            <v-toolbar-title>{{ $t("ASSIGMENT_LAYERS") }}</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-col>
                <v-select
                    v-model="selectRole"
                    :items="roles"
                    item-text="name"
                    item-value="id"
                    label="Role"
                    @change="changeRole()"
                ></v-select>
            </v-col>

            <v-spacer></v-spacer>
            <v-btn
                tile
                color="success"
                :loading="loadingAdd"
                @click="newItem()"
            >
                <v-icon left>
                    mdi-plus
                </v-icon>
                {{ $t("CREATE") }}
            </v-btn>
        </v-toolbar>
        <Loader v-if="loadingTable" />
        <v-simple-table v-else>
            <template v-slot:default>
                <thead>
                    <tr>
                        <th>{{ $t("NAME") }}</th>
                        <th>{{ $t("READ") }}</th>
                        <th>{{ $t("ADDING") }}</th>
                        <th>{{ $t("EDITING") }}</th>
                        <th>{{ $t("DELETING") }}</th>
                        <th>{{ $t("ACTION") }}</th>
                    </tr>
                </thead>

                <tbody>
                    <h3 v-if="!layers.length">Таблица пуста</h3>
                    <tr v-for="layer in layers" :key="layer.id">
                        <td>{{ layer.name }}</td>
                        <td>
                            <v-checkbox
                                v-model="layer.read"
                                input-value="false"
                                color="primary"
                                value
                            ></v-checkbox>
                        </td>
                        <td>
                            <v-checkbox
                                v-model="layer.write"
                                color="success"
                                input-value="false"
                                value
                            ></v-checkbox>
                        </td>
                        <td>
                            <v-checkbox
                                v-model="layer.update"
                                color="warning"
                                input-value="false"
                                value
                            ></v-checkbox>
                        </td>
                        <td>
                            <v-checkbox
                                v-model="layer.delete"
                                input-value="false"
                                color="error"
                                value
                            ></v-checkbox>
                        </td>
                        <td>
                            <v-btn
                                class="mx-2"
                                fab
                                small
                                :loading="loadingDelete"
                                @click="deleteItem(layer)"
                            >
                                <v-icon dark>
                                    mdi-delete
                                </v-icon>
                            </v-btn>
                        </td>
                    </tr>
                </tbody>
            </template>
        </v-simple-table>
        <v-row justify="start">
            <v-col cols="3" class="ma-5">
                <v-btn
                    color="primary"
                    elevation="1"
                    :loading="loadingAssign"
                    @click="submit()"
                    >{{ $t("SAVE") }}</v-btn
                >
            </v-col>
        </v-row>

        <!-- Dialog -->
        <v-dialog v-model="dialog" scrollable max-width="300px">
            <v-card>
                <v-card-title>Добавить слои</v-card-title>
                <v-divider></v-divider>
                <v-card-text style="height: 300px;">
                    <v-autocomplete
                        v-model="addLayers"
                        :items="allLayers"
                        item-text="name"
                        item-value="id"
                        deletable-chips
                        clearable
                        chips
                        small-chips
                        label="Выберите слои"
                        multiple
                    ></v-autocomplete>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn color="blue darken-1" text @click="dialog = false">
                        Close
                    </v-btn>
                    <v-btn
                        color="blue darken-1"
                        text
                        :loading="loadingTable"
                        @click="addAssigmentLayers()"
                    >
                        Save
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- /Dialog -->
    </div>
</template>
<script>
import Loader from "../core/Loader";

export default {
    name: "AssigmentLayers",
    data: () => ({
        addLayers: [],
        allLayers: [],
        dialog: false,
        layers: [],
        headers: [
            {
                text: "ID",
                align: "start",
                sortable: false,
                value: "id"
            },
            { text: "layer name", value: "name" },
            {
                text: "Read",
                value: "read",
                sortable: false
            },
            {
                text: "Write",
                value: "write",
                sortable: false
            },
            {
                text: "Update",
                value: "update",
                sortable: false
            },
            {
                text: "Delete",
                value: "delete",
                sortable: false
            }
        ],
        roles: [],
        editMode: false,
        createDialog: false,
        selectRole: "",

        loadingTable: false,
        loadingAdd: false,
        loadingAssign: false,
        loadingDelete: false
    }),
    computed: {},
    methods: {
        newItem() {
            this.dialog = true;
        },
        editItem(item) {},
        deleteItem(layer) {
            Swal.fire({
                title: "Are you sure?",
                text: layer.name + " will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(result => {
                if (result.isConfirmed) {
                    this.loadingDelete = true;
                    axios
                        .post("/role/deleteLayer", {
                            id_role: this.selectRole,
                            id_layer: layer.id
                        })
                        .then(response => {
                            this.loadingDelete = false;
                            this.getAllLayers();
                            this.$toastr.s("Delete layer", "Assigment");
                        })
                        .catch(() => {
                            this.loadingDelete = false;
                            this.$toastr.e("Cannot delete layer", "Error");
                        });
                }
            });
        },
        getRoles() {
            // this.loadingTable = true;
            axios.get("/role/getAll").then(response => {
                this.roles = response.data.roles;
                if (response.data.roles.length) {
                    this.roles = this.roles.map(item => {
                        return { id: item.id, name: item.name };
                    });

                    this.selectRole = this.roles[0].id;
                }
                // this.loadingTable = false;
            });
        },
        getAllLayers() {
            this.loadingTable = true;
            axios.get("/layer/getAll").then(response => {
                this.allLayers = response.data.layers;

                this.changeRole();
            });
        },
        changeRole() {
            this.loadingTable = true;
            this.layers = [];
            axios
                .get("/role/getLayerPermissionsByRoleId/" + this.selectRole)
                .then(response => {
                    let tmp = response.data.rolesLayers[0].get_layers_relation;

                    let layer = {};
                    tmp.forEach(element => {
                        layer = JSON.parse("{" + element.permissions + "}");
                        layer.id = element.id_layer;
                        layer.name = element.name;

                        this.layers.push(layer);
                        layer = {};
                    });

                    this.addLayers = this.layers.map(item => {
                        return item.id;
                    });
                    this.loadingTable = false;
                })
                .catch(e => {
                    this.$toastr.e("Cannot get role`s layers", "Error");
                    // console.log(e);
                    this.loadingTable = false;
                });
        },
        submit(item) {
            this.loadingAssign = true;
            let data = JSON.stringify(this.layers);
            axios
                .post("/role/assigmentLayers", {
                    id_role: this.selectRole,
                    layers: data
                })
                .then(response => {
                    this.loadingAssign = false;
                    this.$toastr.s("Assigment layer", "Assigment");
                    this.dialog = false;
                })
                .catch(() => {
                    this.loadingAssign = false;
                    this.$toastr.e("Cannot assigment layer", "Error");
                });
        },
        addAssigmentLayers() {
            this.loadingAdd = true;
            let data = JSON.stringify(this.addLayers);
            axios
                .post("/role/addAssigmentLayers", {
                    id_role: this.selectRole,
                    layers: data
                })
                .then(response => {
                    this.getAllLayers();
                    this.$toastr.s("Add layer", "Assigment");
                    this.dialog = false;
                    this.loadingAdd = false;
                })
                .catch(() => {
                    this.loadingAdd = false;
                    this.$toastr.e("Cannot add layer(s)", "Error");
                });
        }
    },
    created() {
        this.getRoles();
        this.getAllLayers();
    },
    mounted() {},
    components: {
        Loader
    }
};
</script>

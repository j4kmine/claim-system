<template>
    <div>
        <CRow>
            <CCol md="4">
                <div role="group" class="single-file form-group">
                    <label for="uid-mxgq9rr79sk" class=""> Upload Warranty Prices </label>
                    <input id="uid-mxgq9rr79sk" type="file" name="file" class="form-control" @change="onFileChange">
                </div>
            </CCol>
            <CCol md="4">
                <CButton v-if="!importing" class="export-btn" color="success" @click="importPrices()">
                    Import
                </CButton>
                <CButton v-else class="export-btn" color="success" disabled>
                    <div class="spinner-grow text-light" role="status">
                    <span class="sr-only">Loading...</span>
                    </div>
                    Importing
                </CButton>
                <CButton v-if="!downloading" class="export-btn" color="primary" @click="exportPrices()">Download</CButton>
                <CButton v-else class="export-btn" color="primary" disabled>
                    <div class="spinner-grow text-light" role="status">
                    <span class="sr-only">Loading...</span>
                    </div>
                    Downloading
                </CButton>
            </CCol>
        </CRow>
        <CRow>
            <CCol>
                <hr style="margin-bottom: 30px;"></hr>
            </CCol>
        </CRow>
        <data-table
            ref="dataTable"
            :perPage="perPage"
            @on-table-props-changed="reloadTable"
            :key="tableKey"
            :columns="columns"
            order-by="id"
            :debounce-delay="debounceDelay"
            :data="data"
            order-dir="desc"
            :classes="classes"
            :headers="headers">
            <div slot="filters" slot-scope="{ tableFilters, perPage }">
                <div class="mb-3 row">
                    <div class="col-md-3">
                        <select class="form-control" v-model="tableFilters.length">
                            <option :key="page" v-for="page in perPage">{{ page }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <!--
                        <CButton color="danger" @click="deletePrices()">
                            Delete
                        </CButton>-->
                    </div>
                    <div class="col-md-3 offset-md-3">
                        <input
                            name="name"
                            class="form-control"
                            v-model="tableFilters.search"
                            placeholder="Search Table">
                    </div>
                </div>
            </div>
        </data-table>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],
      debounceDelay: 250,
      tableKey: 0,
      importing: false,
      downloading: false,
      file: null,
      url: "",

      data: {},
      tableProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      checkBoxes: [],
      classes: {
        table: {
          "table-striped": false,
        },
      },
      headers: {
        Authorization: window.axios.defaults.headers.common["Authorization"],
      },
      columns: [
        /*
                {
                    label: '',
                    orderable: false,
                    event: "click",
                    handler: this.selectRow,
                    component: 'CheckBoxCol',
                },*/
        {
          label: "Id",
          name: "id",
        },
        {
          label: "Make",
          name: "make",
        },
        {
          label: "Model",
          name: "model",
        },
        {
          label: "Capacity",
          name: "capacity",
        },
        {
          label: "Type",
          name: "type",
        },
        {
          label: "Fuel",
          name: "fuel",
        },
        {
          label: "Warranty Duration",
          name: "warranty_period",
        },
        {
          label: "Price",
          name: "format_price",
        },
        {
          label: "Max Claim",
          name: "format_max_claim",
        },
        {
          label: "Mileage Coverage",
          name: "format_mileage_coverage",
        },
        {
          label: "Package",
          name: "package",
        },
        {
          label: "Category",
          name: "category",
        },
        {
          label: "Status",
          name: "status",
          component: "StatusCol",
        },
        /*
                {
                    label: 'Action',
                    orderable: true,
                    component: 'ShowCol',
                    event: "click",
                    handler: this.displayRow,
                }*/
      ],
    };
  },
  methods: {
    onFileChange(e) {
      this.file = e.target.files[0];
    },
    deletePrices() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/warrantyPrices/delete";
      inputs.ids = this.checkBoxes;
      this.$store.dispatch("API", inputs).then((data) => {
        this.tableKey += 1;
      });
    },
    importPrices() {
      const formData = new FormData();
      formData.append("file", this.file);
      this.importing = true;
      var _this = this;
      axios.post("/api/warrantyPrices/import", formData).then(
        function (result) {
          _this.importing = false;
          _this.tableKey += 1;
          Vue.toasted.success(result.data.message);
        },
        function (err) {
          var error = "An error has occured.";
          if (err.response.status == 422) {
            if (err.response.data instanceof Array) {
              error = "";
              err.response.data.forEach((message) => {
                error += message + "<br>";
              });
            } else if (err.response.data.message != null) {
              error = err.response.data.message;
            }
          }
          _this.importing = false;
          Vue.toasted.error(error);
        }
      );
    },
    exportPrices() {
      this.downloading = true;
      var _this = this;
      axios({
        method: "post",
        url: "/api/warrantyPrices/export",
        responseType: "arraybuffer",
      })
        .then((response) => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "prices.xlsx"); //or any other extension
          document.body.appendChild(link);
          link.click();
          _this.downloading = false;
        })
        .catch((err) => {
          _this.downloading = false;
        });
    },
    displayRow(data) {
      this.$router.push({ path: "/warrantyPrices/edit/" + data.id });
    },
    getData(url = this.url, options = this.tableProps) {
      axios
        .get(url, {
          params: options,
        })
        .then((response) => {
          this.data = response.data;
        })
        .catch((errors) => {});
    },
    reloadTable(tableProps) {
      localStorage.setItem(`warrantyPrices`, JSON.stringify(tableProps));
      this.getData(this.url, tableProps);
    },
    selectRow(data) {
      this.checkBoxes[data.id] = data.select;
    },
  },
  mounted() {
    this.$refs.dataTable.tableProps.length = 20;
    console.log("test", this.$refs.dataTable);
    // this.$refs.dataTable.$children[0].tableData.length = 20;

    var getPage = JSON.parse(localStorage.getItem("warrantyPrices"));
    this.url =
      window.location.protocol +
      "//" +
      window.location.hostname +
      "/api/warrantyPrices";
    this.tableProps = getPage != null ? getPage : this.tableProps;
    this.getData(this.url, this.tableProps);
  },
};
</script>

<template>
  <data-table
    ref="dataTable"
    :perPage="perPage"
    :columns="vehicleType == 'new' ? columnNew : columnPreOwned"
    @on-table-props-changed="reloadTable"
    :data="data"
    order-by="id"
    order-dir="desc"
    :debounce-delay="debounceDelay"
    class="vehicledetail-datatable"
    :classes="classes"
    :headers="headers"
  >
  </data-table>
</template>
<script>
import warrantyGNew from "../../reports/warranty-g-new";
import warrantyGPreowned from "../../reports/warranty-g-preowned";
import { mapGetters } from "vuex";
export default {
  props: [
    "status",
    "vehicleType",
    "fromDate",
    "toDate",
    "dateType",
    "headers",
    "classes",
    "debounceDelay",
  ],
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],

      columnNew: warrantyGNew,
      columnPreOwned: warrantyGPreowned,
      data: {},
      url: "",
      newProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
      },
      PreownedProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
      },
    };
  },
  watch: {
    user: function (val) {},
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;

    this.url =
      window.location.protocol +
      "//" +
      window.location.hostname +
      "/api/reports-warranties";
    if (this.vehicleType == "") {
      this.vehicleType = "new";
    }

    var inputs = this.newProps;
    inputs.vehicle_type = this.vehicleType;
    inputs.status = this.status;
    inputs.fromDate = this.fromDate;
    inputs.toDate = this.toDate;
    inputs.type = this.dateType;
    this.getData(this.url, inputs);
  },
  methods: {
    getData(url = this.url, options = this.tableProps) {
      axios
        .get(url, {
          params: options,
        })
        .then((response) => {
          // this.key +=1;
          this.data = response.data;
        })
        .catch((errors) => {});
    },
    reloadTable(tableProps) {
      this.getData(this.url, tableProps);
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>

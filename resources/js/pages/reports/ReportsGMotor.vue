<template>
  <data-table
    ref="dataTable"
    :perPage="perPage"
    :columns="columns"
    @on-table-props-changed="reloadTable"
    :data="data"
    order-by="id"
    order-dir="desc"
    :classes="classes"
    :debounce-delay="debounceDelay"
    :headers="headers"
  >
  </data-table>
</template>
<script>
import motorG from "../../reports/motor-g";
import { mapGetters } from "vuex";
export default {
  props: ["status", "fromDate", "toDate", "dateType", "headers", "classes"],
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],

      debounceDelay: 1000,
      columns: motorG,
      data: {},
      url: "",
      tableProps: {
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
      "/api/reports-motors";
    var inputs = this.tableProps;
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

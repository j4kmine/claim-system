<template>
  <data-table
    ref="dataTable"
    :perPage="perPage"
    @row-clicked="displayRow"
    @on-table-props-changed="reloadTable"
    :columns="columns"
    order-by="id"
    order-dir="desc"
    :data="data"
    :debounce-delay="debounceDelay"
    :classes="classes"
    :headers="headers"
  >
  </data-table>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],

      data: {},
      debounceDelay: 250,
      url: "",
      tableProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      classes: {
        table: {
          "table-striped": false,
        },
      },
      headers: {
        Authorization: window.axios.defaults.headers.common["Authorization"],
      },
      columns: [
        {
          label: "Customer",
          name: "name",
          orderable: true,
          component: "UserCol",
        },
        {
          label: "NRIC",
          name: "nric_uen",
          orderable: true,
        },
        {
          label: "Phone",
          name: "phone",
          orderable: true,
        },
        {
          label: "Created At",
          name: "created_at",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
      ],
    };
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;

    var getPage = JSON.parse(localStorage.getItem("customerProfileMeta"));
    this.tableProps = getPage != null ? getPage : this.tableProps;
    this.url = this.url =
      window.location.protocol +
      "//" +
      window.location.hostname +
      "/api/customers/index";
    this.getData(this.url, this.tableProps);
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/customers/profiles/details/" + data.id });
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
      localStorage.setItem("customerProfileMeta", JSON.stringify(tableProps));
      this.getData(this.url, tableProps);
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>

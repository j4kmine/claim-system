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
export default {
  props: ["companyId", "companyUsers"],
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],

      debounceDelay: 250,
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
          label: "User",
          name: "name",
          orderable: true,
          component: "UserCol",
        },
        {
          label: "Company",
          name: "company_name",
          columnName: "companies.name",
          orderable: true,
        },
        {
          label: "Role",
          name: "role",
          orderable: true,
          component: "RoleCol",
        },
        {
          label: "Category",
          name: "category",
          orderable: true,
          component: "CategoryCol",
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

    var getPage = JSON.parse(localStorage.getItem("partnerUsers"));
    if (this.companyId == null) {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/users";
    } else {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/users/" +
        this.companyId;
    }
    this.tableProps = getPage != null ? getPage : this.tableProps;
    this.getData(this.url, this.tableProps);
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/users/edit/" + data.id });
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
      localStorage.setItem(`partnerUsers`, JSON.stringify(tableProps));
      this.getData(this.url, tableProps);
    },
  },
};
</script>

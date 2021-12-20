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
          label: "Car Plate",
          name: "registration_no",
          orderable: true,
        },
        {
          label: "User Access",
          name: "customers",
          orderable: false,
          component: "UserAccessVehicleCol",
        },
        {
          label: "Warranty Start",
          name: "warranty.format_start_date",
          columnName: "warranties.start_date",
          orderable: false,
        },
        {
          label: "Warranty End",
          name: "warranty.end_date",
          columnName: "warranties.end_date",
          orderable: false,
        },
        {
          label: "Insurance Start",
          name: "motor.format_start_date",
          columnName: "motors.start_date",
          orderable: false,
        },
        {
          label: "Insurance End",
          name: "motor.format_expiry_date",
          columnName: "motors.format_expiry_date",
          orderable: false,
        },
        {
          label: "Last Servicing",
          name: "services",
          orderable: false,
          component: "LastServicingCol",
        },
        {
          label: "COE Expiry",
          name: "coe_expiry_date",
          orderable: true,
        },
        {
          label: "Status",
          name: "motor.status",
          columnName: "motors.status",
          orderable: false,
          component: "MotorStatusCol",
        },
      ],
    };
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;

    if (this.companyId == null) {
      var getPage = JSON.parse(localStorage.getItem("customerVehicleMeta"));
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/customer/vehicles";
      this.tableProps = getPage != null ? getPage : this.tableProps;
      this.getData(this.url, this.tableProps);
    }
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/customers/vehicles/details/" + data.id });
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
      localStorage.setItem(`customerVehicleMeta`, JSON.stringify(tableProps));
      this.getData(this.url, tableProps);
    },
  },
};
</script>

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
    class="datatable-servicing"
    :classes="classes"
    :debounce-delay="debounceDelay"
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
          label: "Ref No.",
          name: "ref_no",
          orderable: true,
        },
        {
          label: "Car Plate",
          name: "vehicle.registration_no",
          columnName: "vehicles.registration_no",
          orderable: true,
        },
        {
          label: "Customer",
          name: "customer.name",
          columnName: "customers.name",
          orderable: true,
        },
        {
          label: "Phone",
          name: "customer.phone",
          columnName: "customers.phone",
          orderable: true,
        },
        {
          label: "Make",
          name: "vehicle.make",
          columnName: "vehicles.make",
          orderable: true,
        },
        {
          label: "Model",
          name: "vehicle.model",
          columnName: "vehicles.model",
          orderable: true,
        },
        {
          label: "Dealer",
          name: "workshop.name",
          columnName: "companies.name",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
        {
          label: "Created At",
          name: "created_at",
          orderable: true,
        },
      ],

      tableProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
    };
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;

    var getPage = JSON.parse(localStorage.getItem("accidentReportingMeta"));
    if (getPage != null) {
      if (getPage.page != 1) {
        this.tableProps = getPage;
        this.url =
          window.location.protocol +
          "//" +
          window.location.hostname +
          "/api/accidents";
        this.getData(this.url, this.tableProps);
      } else {
        this.url =
          window.location.protocol +
          "//" +
          window.location.hostname +
          "/api/accidents";
        this.getData(this.url);
      }
    } else {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/accidents";
      this.getData(this.url);
    }
  },
  watch: {
    user: function (val) {
      if (
        this.user.category == "all_cars" ||
        this.user.category == "workshop"
      ) {
        // do something if allowed
      } else {
        // redirect to dashboard if not allowed
        this.$router.push("/dashboard");
      }
    },
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/accidentReport/details/" + data.id });
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
      localStorage.setItem("accidentReportingMeta", JSON.stringify(tableProps));
      this.getData(this.url, tableProps);
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>

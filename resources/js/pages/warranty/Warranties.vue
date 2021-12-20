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
          label: "Ref No",
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
          name: "proposer.name",
          columnName: "proposers.name",
          orderable: true,
        },
        {
          label: "Phone",
          name: "proposer.phone",
          columnName: "proposers.phone",
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
          label: "Insurer",
          name: "insurer.name",
          columnName: "insurers.name",
        },
        {
          label: "Dealer",
          name: "dealer.name",
          columnName: "dealers.name",
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
      allProps: {
        search: "",
        length: 10,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      draftProps: {
        search: "",
        length: 10,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      archiveProps: {
        search: "",
        length: 10,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
    };
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;
  },
  watch: {
    $route(to, from) {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api" +
        to.path;
    },
    user: function (val) {
      var getPage = JSON.parse(
        localStorage.getItem(`warrantiesMeta_${this.$route.path}`)
      );
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api" +
        this.$route.path;
      if (this.$route.path.includes("draft")) {
        this.draftProps = getPage != null ? getPage : this.draftProps;
        this.getData(this.url, this.draftProps);
      } else if (this.$route.path.includes("archive")) {
        this.archiveProps = getPage != null ? getPage : this.archiveProps;
        this.getData(this.url, this.archiveProps);
      } else {
        this.allProps = getPage != null ? getPage : this.allProps;
        this.getData(this.url, this.allProps);
      }
    },
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/warranties/details/" + data.id });
    },
    getData(url = this.url, options = this.tableProps) {
      axios
        .get(url, {
          params: options,
        })
        .then((response) => {
          if (typeof response.data === "object") {
            this.data = response.data;
          }
        })
        .catch((errors) => {});
    },
    reloadTable(tableProps) {
      localStorage.setItem(
        `warrantiesMeta_${this.$route.path}`,
        JSON.stringify(tableProps)
      );
      this.getData(this.url, tableProps);
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>

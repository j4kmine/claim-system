<template>
  <data-table
    ref="dataTable"
    :perPage="perPage"
    @row-clicked="displayRow"
    @on-table-props-changed="reloadTable"
    :columns="columns"
    :data="data"
    order-by="id"
    order-dir="desc"
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

      lastSegment: "",
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
          label: "Name",
          name: "name",
          orderable: true,
        },
        {
          label: "Code",
          name: "code",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
        {
          label: "Users Count",
          name: "count",
          orderable: false,
        } /*
                {
                    label: '',
                    name: 'show',
                    orderable: false,
                    event: "click",
                    handler: this.displayRow,
                    component: 'ShowCol',
                },*/,
      ],
    };
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;

    var str = window.location.href;
    this.lastSegment = str.split("/").pop();
    var getPage = JSON.parse(
      localStorage.getItem(`partner_${this.lastSegment}`)
    );
    this.url =
      str.substring(0, str.lastIndexOf("/")) + "/api/" + this.lastSegment;
    this.tableProps = getPage != null ? getPage : this.tableProps;
    this.getData(this.url, this.tableProps);
  },
  watch: {
    $route(to, from) {},
    user: function (val) {
      var str = window.location.href;
      this.lastSegment = str.split("/").pop();
      var getPage = JSON.parse(
        localStorage.getItem(`partner_${this.lastSegment}`)
      );
      this.url =
        str.substring(0, str.lastIndexOf("/")) + "/api/" + this.lastSegment;
      this.tableProps = getPage != null ? getPage : this.tableProps;
      this.getData(this.url, this.tableProps);
    },
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/" + this.lastSegment + "/edit/" + data.id });
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
      localStorage.setItem(
        `partner_${this.lastSegment}`,
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

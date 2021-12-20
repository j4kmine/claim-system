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
    :debounce-delay="debounceDelay"
    :headers="headers"
    :key="key"
  >
  </data-table>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],

      key: 0,
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
          label: "III Claim No.",
          name: "insurer_ref_no",
          orderable: true,
        },
        {
          label: "Car Plate",
          name: "vehicle.registration_no",
        },
        // {
        //     label: 'Insurer',
        //     name: 'insurer_extend.company.name'
        // },
        {
          label: "Policy Certificate No.",
          name: "policy_certificate_no",
          orderable: true,
        },
        {
          label: "Claim Item (1)",
          name: "items",
          component: "ItemCol",
        },
        {
          label: "Total Claim Amount",
          name: "total_claim_amount",
          orderable: true,
        },
        {
          label: "Date of Loss",
          name: "format_date_of_loss",
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
        /*
                {
                    label: '',
                    name: 'show',
                    orderable: false,
                    event: "click",
                    handler: this.displayRow,
                    component: 'ShowCol',
                },*/
      ],
      allProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      draftProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      archiveProps: {
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
  },
  watch: {
    user: function (val) {
      if (
        this.$route.path.includes("archive") ||
        this.$route.path.includes("drafts")
      ) {
        if (
          this.user.category == "surveyor" ||
          this.user.category == "insurer" ||
          this.user.category == "dealer"
        ) {
          this.$router.push("/dashboard");
        }
      }

      var getPage = JSON.parse(
        localStorage.getItem(`claimsMeta_${this.$route.path}`)
      );

      var props = {};
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

      // dealer cant access all of claims
      if (this.user.category == "dealer") {
        this.$router.push("/dashboard");
      }
    },
    $route(to, from) {},
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/claims/details/" + data.id });
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
        `claimsMeta_${this.$route.path}`,
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

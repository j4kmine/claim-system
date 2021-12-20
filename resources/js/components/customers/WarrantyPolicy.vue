<template>
    <CCard>
        <CCardHeader class="font-weight-bold">Warranty Policy</CCardHeader>
        <CCardBody>
            <data-table
                :columns="columns"
                :url="url"
                order-by="id"
                order-dir="desc"
                class="vehicledetail-datatable"
                :classes="classes"
                :headers="headers">
            </data-table>
        </CCardBody>
    </CCard>
</template>
<script>
export default {
  props: ["idVehicle"],
  data() {
    return {
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
          label: "Insurer",
          name: "insurer.name",
          columnName: 'companies.name',
          orderable: true,
        },
        {
          label: "Policy No.",
          name: "policy_no",
          orderable: true,
        },
        {
          label: "Warranty Start",
          name: "format_start_date",
          columnName: "start_date",
          orderable: true,
        },
        {
          label: "Warranty End",
          name: "end_date",
          columnName: "expiry_date",
          orderable: true,
        },
        {
          label: "Amount",
          name: "format_price",
          columnName: "price",
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
  mounted() {},
  computed: {
    url() {
      if (this.idVehicle != null && this.idVehicle != "") {
        return (
          window.location.protocol +
          "//" +
          window.location.hostname +
          "/api/vehicles/" +
          this.idVehicle +
          "/warranty"
        );
      }
    },
  },
};
</script>
<style scoped>
.vehicledetail-datatable >>> .mb-3 {
  display: none;
}
</style>
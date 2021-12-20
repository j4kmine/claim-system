<template>
    <CCard>
        <CCardHeader class="font-weight-bold">Servicing History</CCardHeader>
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
          label: "Workshop",
          name: "workshop.name",
          columnName: 'companies.name',
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
          "/service-history"
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
<template>

    <CCard>
        <CCardHeader class="font-weight-bold">User Access</CCardHeader>
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
    props: ['idVehicle'],
    data(){
        return {
            classes: {
                "table": {
                    "table-striped": false,
                },
            },  
            headers: { 
                Authorization: window.axios.defaults.headers.common['Authorization'] 
            },
            columns: [
                {
                    label: 'User',
                    name: 'customer.name',
                    columnName: 'customers.name',
                    orderable: true,
                    component: 'UserCustomerCol', 
                },
                {
                    label: 'User Access',
                    name: 'granted_by',
                    orderable: true,
                    component: 'UserAccessCol'
                },
                {
                    label: 'Phone',
                    name: 'customer.phone',
                    columnName: 'customers.phone',
                    orderable: true,
                },
                {
                    label: 'Created At',
                    name: 'created_at',
                    orderable: true,
                },
                {
                    label: 'Status',
                    name: 'customer.status',
                    columnName: 'customers.status',
                    orderable: true,
                    component: 'StatusCustomerCol',
                }
            ],
        }
    },
    mounted(){
    },
    computed:{
        url(){
            if (this.idVehicle != null && this.idVehicle!=''){
                return window.location.protocol + "//" + window.location.hostname + '/api/vehicles/' + this.idVehicle + '/access';
            }
        }
    }
}
</script>
<style scoped>
.vehicledetail-datatable >>> .mb-3{
    display: none;
}
</style>
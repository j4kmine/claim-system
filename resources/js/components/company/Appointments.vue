<template>
    <data-table
        @row-clicked="displayRow"
        :columns="columns"
        :url="url"
        :classes="classes"
        :headers="headers">
    </data-table>
</template>
<script>
export default {
    props: ['companyId'],
    data() {
        return {
            lastSegment: "",
            url: "",
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
                    label: 'Name',
                    name: 'name',
                    orderable: true, 
                },
                {
                    label: 'Code',
                    name: 'code',
                    orderable: true,
                },
                {
                    label: 'Status',
                    name: 'status',
                    orderable: true,
                    component: 'StatusCol',
                },
                {
                    label: 'Users Count',
                    name: 'count',
                    orderable: false,
                },/*
                {
                    label: '',
                    name: 'show',
                    orderable: false,
                    event: "click",
                    handler: this.displayRow,
                    component: 'ShowCol', 
                },*/
            ]
        }
    },
    mounted(){
        this.url= window.location.protocol + "//" + window.location.hostname + '/api/appointments/' + this.companyId;
    },
    methods: {
        displayRow(data) {
            this.$router.push({ path: '/insurers/edit/' + data.id });
        },
    },
}
</script>
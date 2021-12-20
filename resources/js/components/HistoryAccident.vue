<template>
    <div>
    <data-table
        :columns="columns"
        :url="url"
        order-by="id"
        order-dir="desc"
        :classes="classes"
        :headers="headers">
    </data-table>
    </div>
</template>
<script>
export default {
    props: ['id', 'type'],
    data() {
        return {
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
                    label: 'Action',
                    name: 'log',
                    orderable: true
                },
                {
                    label: 'Created At',
                    name: 'created_at',
                    orderable: true
                },
            ]
        }
    },
    mounted(){
        if(this.id != null){
            this.url = window.location.protocol + "//" + window.location.hostname + '/api/dashboard/accidents/history/' + this.id;
        }
    }
}
</script>
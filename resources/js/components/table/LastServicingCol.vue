<template>
    <div>
        {{lastServicing}}
    </div>
</template>
<script>
export default {
    props: {
        data: {}
    },
    computed: {
        lastServicing(){
            if (this.data.services){
                var completed = this.data.services.filter(filterService => filterService.status == 'completed');
                completed.sort((a, b) => {return b.id - a.id;})
                console.log(completed);
                return completed[0].format_appointment_date;
            }
        }
    }
}
</script>
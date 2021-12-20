<template>
    <div>
        <CBadge class="badge-text" :color="status">{{ statusName }}</CBadge>
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    },
    computed: {
        status(){
            var badge = "";
            switch (this.data.status) {
                case 'inactive':
                    badge = "secondary";
                    break;
                case 'open':
                    badge = "warning";
                    break;
                case 'pending':
                    badge = "secondary";
                    break;
                case 'cancelled':
                    badge = "danger";
                    break;
                case 'completed':
                    badge = "success";
                    break;
                default:
                    badge = "success";
                    break;
            }
            return badge;
        },
        statusName(){
            var status = "";
            switch (this.data.status) {
                case 'open':
                    status = 'Servicing In Progress'
                    break;
                case 'upcoming':
                    status = 'Upcoming'
                    break;
                case 'pending':
                    status = 'Pending Collection'
                    break;
                case 'completed':
                    status = 'Completed'
                    break;
                default:
                    status = this.data.status;
                    break;
            }
            return status;
        }
    }
}
</script>
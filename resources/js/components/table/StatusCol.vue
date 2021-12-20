<template>
    <div>
        <CBadge class="badge-text" :color="status">{{ $helpers.unslugify(this.data.status == "repair_in_progress" ? "approved" : this.data.status) }}</CBadge>
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
        }
    }
}
</script>
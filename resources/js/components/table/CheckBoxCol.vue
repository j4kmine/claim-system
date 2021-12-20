<template>
    <div class="form-check">
        <input v-model="checkBox" @click="clickBox(data)" class="form-check-input" style="position: relative;" type="checkbox" value="" id="flexCheckDefault">
    </div>  
</template>

<script>
export default {
    props: {
        data: {},
        click: {
            type: Function,
            default: () => ({
            })
        },
        classes: {
            type: Object,
            default: () => ({
            }),
        },
    },
    data() {
        return {
            checkBox: false
        }
    },
    methods: {
        clickBox(data) {
            data.select = !this.checkBox;
            this.click(data);
        }
    }
}
</script>
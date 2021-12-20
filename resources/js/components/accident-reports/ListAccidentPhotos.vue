<template>
    <div>
        <label @click="toggle()"><CIcon name="cil-folder" style="margin-right: 5px;"></CIcon> {{type}}</label>
        <div v-show="isActive">
            <div v-for="document in documents" :key="document.id" class="value-content">
                <a :href="`${document.view}`" target="_blank">{{document.name}}</a>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['type', 'documents'],
    data() {
        return {
            isActive: false
        }
    },
    methods: {
        toggle(){
            this.isActive = !this.isActive
        }
    }
}
</script>
<style scoped>
.value-content{
    color: #007BFF;
    margin: 4px 0px;
    padding-left: 27px;
    height: 24px;
}
</style>
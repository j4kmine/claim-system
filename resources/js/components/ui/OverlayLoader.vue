<template>
  <div class="base-overlay-loader" v-show="loading" :style="customStyle">
    <CSpinner size="5xl" color="success" />
  </div>
</template>

<script>
export default {
  props: {
    loading: {
      type: Boolean,
    },
    customStyle: {
      type: Object,
      default: () => {
        return {
          width: "100%",
        };
      },
    },
  },
};
</script>

<style >
.base-overlay-loader {
  position: absolute;
  /* width: 100%; */
  height: 100%;
  text-align: center;
  margin-top: 100px;
  z-index: 9999;
}
</style>

<template>
  <div
    :style="{ backgroundColor: data.color }"
    style="max-width: 100px; border-radius: 8px; height: 20px"
  ></div>
</template>

<script>
export default {
  props: {
    data: {},
  },
};
</script>

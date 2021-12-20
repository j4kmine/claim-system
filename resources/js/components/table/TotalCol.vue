<template>
  <div class="user-container">
    {{ total }}
    <!-- {{ this.data.interval * this.data.slots_per_interval }} -->
  </div>
</template>

<script>
export default {
  props: {
    data: {},
  },
  computed: {
    total() {
      return this.data.slots_per_interval * this.hours;
    },
    session() {
      return 60 / this.data.interval;
    },
    hours() {
      var timeStart = new Date("01/01/2007 " + this.data.time_start).getHours();
      var timeEnd = new Date("01/01/2007 " + this.data.time_end).getHours();

      const hour = timeEnd - timeStart;

      return Math.trunc(hour * this.session);
    },
  },
};
</script>

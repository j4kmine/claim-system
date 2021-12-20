<template>
  <div>
    <table class="base-table">
      <thead>
        <tr>
          <th v-for="item in headers" :key="item.value">
            <span>
              {{ item.text }}
            </span>
          </th>
        </tr>
      </thead>
      <tbody v-show="!loading">
        <tr
          style="cursor: pointer"
          v-for="item in items"
          :key="item.id"
          @click="$emit('row-clicked', item)"
        >
          <td
            v-for="header in headers"
            :key="header.value"
            :width="header.width"
          >
            <span v-if="!header.sloted">
              <!-- if header nested -->
              {{ fetchHeader(header, item) }}
              <!-- {{ item[header.value] }} -->
            </span>
            <slot :name="header.value" v-bind="{ item, header }" />
          </td>
        </tr>
      </tbody>
      <tbody v-show="loading">
        <tr v-for="item in items" :key="item.id">
          <td
            v-for="header in headers"
            :key="header.value"
            :width="header.width"
          >
            <Loader />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  props: {
    url: {
      type: String,
    },
    headers: {
      type: Array,
    },
  },

  data() {
    return {
      items: [],
      loading: false,
    };
  },

  mounted() {
    this.loading = true;
    this.$store
      .dispatch("API", {
        url: `${this.url}`,
        method: "get",
      })
      .then((result) => {
        this.items = result;
        this.loading = false;
      });
  },
  methods: {
    fetchHeader(header, item) {
      if (header.value.includes(".")) {
        const arrayHeader = header.value.split(".");
        let value = null;
        for (let i = 0; i < arrayHeader.length; i++) {
          const element = arrayHeader[i];
          if (value) value = value[element];
          else value = item[element];
        }

        return value ?? "-";
      } else {
        return item[header.value] ?? "-";
      }
    },
  },
};
</script>

<style >
.base-table {
  width: 100%;
  border: 1px solid #c8ced3;
}

.base-table thead th {
  padding: 10px;
  border-bottom: 1px solid #c8ced3;
}

.base-table tbody td {
  padding: 10px;
  border-bottom: 1px solid #c8ced3;
}
</style>

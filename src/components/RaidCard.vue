<template>
  <router-link :to="'/raid/' + id" class="raid-card">
    <header class="raid-card__header">
      <div class="raid-card__container">
        <h3 class="raid-card__title">{{name}}</h3>
      </div>
      <div class="raid-card__container">
        <time class="raid-card__title">{{startDate}}</time>
      </div>
    </header>
  </router-link>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import moment from "moment";

export default defineComponent({
  name: "RaidCard",
  props: {
    name: String,
    faction: Number,
    Info: String,
    start: String,
    id: Number
  },
  setup({ start }) {
    let startDate = moment(start, "YYYY/MM/DD HH:mm").format("DD MMM");

    return { startDate };
  },
});
</script>

<style lang="scss" scoped>
.raid-card {
  display: flex;
  justify-content: center;
  padding: var(--edge-inset);
  border: 1px solid;
  box-shadow: 4px 6px 5px 0px rgba(0,0,0,0.4);
  text-decoration: none;
  border-color: rgba($gray-default, .5);
  
  &.declined {
    border-color: var(--color-declined);
  }

  &.pending {
    border-color: var(--color-pending);
  }

  &.accepted {
    border-color: var(--color-green-check);
  }

  &__header {
    display: flex;
    justify-content: space-between;
    width: 100%;
  }

  &__container {
    display: flex;
    align-content: center;
  }

  &__title {
    display: inline-block;
    margin: 0;
    font-size: var(--h3-size-default);
    font-weight: bold;
    color: var(--primary-title-color);
  }
}
</style>
<template>
  <section class="raid-dashboard">
    <div class="raid-dashboard__list">
      <div>
        <h2 class="raid-dashboard__title">My upcoming raids</h2>
      </div>
      <Suspense>
        <RaidList />
      </Suspense>
    </div>
    <div class="raid-dashboard__list">
      <div>
      <h2 class="raid-dashboard__title">Eligible raids</h2>
      </div>
      <Suspense>
        <RaidList />
      </Suspense>
    </div>
  </section>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import useRaids from "../state/raids";
import RaidList from "./RaidList.vue";

export default defineComponent({
  name: "RaidDashboard",
  components: {
    RaidList,
  },
  async setup() {
    const { raids, loadRaids, error } = useRaids();
    await loadRaids();
    console.log(raids.value);
    return { raids };
  },
});
</script>

<style lang="scss" scoped>
.raid-dashboard {
  margin: 0 -$edge-inset;
  display: flex;
  flex-wrap: wrap;

  &__title {
    margin-top: 0;
  }

  &__list {
    box-sizing: border-box;
    width: 100%;
    background-color: $black-lighter;
    @include desktop {
      padding: ($edge-inset * 2) $edge-inset;
      width: 50%;
    }
  }

  &__container {
    background-color: $black-lighter;
  }
}
</style>
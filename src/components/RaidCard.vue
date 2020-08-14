<template>
  <div class="raid-card">
    <header class="raid-card__header">
      <!-- <h3>{{raid.name}}</h3>
      <h3>{{raid.start}}</h3>-->
      <div class="raid-card__container">
        <img class="raid-card__faction" src="https://i.pinimg.com/originals/e7/a0/55/e7a0553e0470bdcdc54f8c2bb01932fa.png">
        <h3 class="raid-card__title">Raid name</h3>
      </div>
      <div class="raid-card__container">
        <time class="raid-card__title">23/09/2020</time>
      </div>
    </header>
  </div>
</template>

<script lang="ts">
import { defineComponent, reactive, ref } from "vue";

export default defineComponent({
  name: "RaidCard",
  props: { raid: Object },
});
</script>

<style scoped>
.raid-card {
  display: flex;
  justify-content: center;
}
.raid-card__header {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.raid-card__container {
    display: flex;
    align-content: center;
}

.raid-card__title {
    display: inline-block;
    margin: 0;
    font-size: var(--h3-size-default);
    font-weight: bold;
    color: var(--primary-title-color);
}

.raid-card__faction {
    width: 18px;
    height: auto;
    margin-right: .5rem;
}
</style>
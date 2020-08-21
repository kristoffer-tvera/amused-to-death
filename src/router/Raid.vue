<template>
  <Suspense>
    <router-view></router-view>
  </Suspense>
</template>


<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "RaidRoute",
});
</script>
<template>
<Navbar/>
    <router-view></router-view>
</template>

<script lang="ts">
import {defineComponent } from "vue";
import GlobalStyle from "./components/GlobalStyle.vue";
import Navbar from "./components/Navbar.vue";
import Characters from "./views/Characters.vue";

export default defineComponent({
  components: {
    GlobalStyle,
    Navbar,
  },
});

</script>

<style>


</style>

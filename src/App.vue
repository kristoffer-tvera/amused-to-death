<template>
    <GlobalStyle/>
    <Navbar/>
    <Suspense>
    <Dashboard/>
    </Suspense>
</template>

<script lang="ts">
import {defineComponent } from "vue";
import GlobalStyle from "./components/GlobalStyle.vue";
import Navbar from "./components/Navbar.vue";
import Characters from "./views/Characters.vue";
import Dashboard from "./views/Dashboard.vue";
// import {createRouter, createWebHistory} from "vue-router";

// const routes = [
//   {path: "/characters", component: Characters}
// ];

// const router = new VueRouter({
//   routes
// })

export default defineComponent({
  components: {
    GlobalStyle,
    Navbar,
    Dashboard
  },
});

</script>

<style>


</style>

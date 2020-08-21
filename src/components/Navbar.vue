
<template>
  <div class="nav__dummy"></div>
  <nav class="nav">
    <div class="nav__container container">
      <router-link class="nav__logo" to="/">
        <h2>Amused to death</h2>
      </router-link>
      <ul class="nav__links">
        <li class="nav__link">
          <router-link to="/">Home</router-link>
        </li>
        <li class="nav__link">
          <router-link to="/raid/create">Create</router-link>
        </li>
        <li class="nav__link">
          <router-link to="/raid/edit/1">Edit</router-link>
        </li>
      </ul>
    </div>
  </nav>
</template>


<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "Navbar",
  setup() {},
});
</script>

<style lang="scss" scoped>
.nav {
  position: fixed;
  display: flex;
  top: 0;
  width: 100%;
  align-items: center;
  min-height: 55px; // should equal the height of __dummy
  background-color: var(--overlay-background);

  &__dummy {
    min-height: 55px; // Should equal the height of .nav
  }

  &__links {
    display: flex;
    align-items: center;
    list-style: none;
    padding: 0;
    margin: 0;
  }

  &__link {
    margin-right: 0.5rem;

    &:last-of-type {
      margin-right: 0;
    }
  }

  &__container {
    display: flex;
    justify-content: space-between;
  }
}
</style>
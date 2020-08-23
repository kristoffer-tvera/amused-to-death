
<template>
  <div class="nav__dummy"></div>
  <nav class="nav">
    <div class="nav__content container">
      <div class="nav__container">
        <router-link class="nav__logo" to="/">
          <h2>Amused to death</h2>
        </router-link>
        <ul class="nav__links">
          <li class="nav__link">
            <router-link to="/raids">Raids</router-link>
          </li>
          <li class="nav__link">
            <router-link to="/characters">Characters</router-link>
          </li>
        </ul>
      </div>
      <Suspense>
        <UserMenu />
      </Suspense>
    </div>
  </nav>
</template>


<script lang="ts">
import { defineComponent } from "vue";
import UserMenu from "./UserMenu.vue";
export default defineComponent({
  name: "Navbar",
  components: {
    UserMenu,
  },
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

  &__content {
    display: flex;
  }

  &__logo {
    margin-right: $edge-inset * 2;
    text-decoration: none;
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

    & > a {
      text-decoration: none;
    }
  }

  &__wrapper {
    display: flex;
    justify-content: space-between;
  }

  &__container {
    display: flex;
    align-items: center;
    width: 50%;
    flex: 0 0 50%;
  }
}
</style>
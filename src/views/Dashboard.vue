<template>
  <main class="container">
    <div>
      <h1>Dashboard</h1>
    </div>
    <section>
      <header>
        <h2>Welcome</h2>
      </header>
      <article>
        If you have a character in the guild, log in through discord (!mudkip in the raider-only channel).
        If you dont, then please feel free to apply to become a part of the guild.
      </article>
      <footer></footer>
    </section>
    <!-- Split the row to Raids / Characters -->
    <section>
      <Suspense>
      <UserRaids />
      </Suspense>
    </section>
    <!-- <section>
      <header>
        <h2>My characters</h2>
        <Suspense>
          <div></div>
        </Suspense>
        <Suspense>
          <CharacterList />
        </Suspense>
      </header>
    </section> -->
  </main>
</template>
<script lang="ts">
import { defineComponent, reactive, ref } from "vue";
import usecharacters from "../utils/characters";
import CharacterList from "../components/CharacterList.vue";
import UserRaids from "../components/UserRaids.vue";

export default defineComponent({
  name: "Dashboard",
  components: {
    CharacterList,
    UserRaids,
  },
});
</script>
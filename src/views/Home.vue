<template>
  <main class="container">
    <section>
      <header>
        <h1>Welcome</h1>
      </header>
      <article>
        If you have a character in the guild, log in through discord (!mudkip in the raider-only channel).
        If you dont, then please feel free to apply to become a part of the guild.
      </article>
      <footer></footer>
    </section>
          <RaidDashboard />
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
import moment from "moment";
import RaidDashboard from "../components/RaidDashboard.vue";
import useRaids from "../state/useRaids";

export default defineComponent({
  name: "Home",
  components: {
    CharacterList,
    RaidDashboard
  },
  setup() {
    const { getRaids, raidsState} = useRaids();
    getRaids();
  }
});
</script>

<style lang="scss" scoped>



</style>
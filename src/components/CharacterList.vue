<template>
  <div class="character-list">
    <CharacterCard v-for="c in characters" :key="c.id" :character="c" />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from "vue";
import CharacterCard from "./CharacterCard.vue";

export default defineComponent({
  name: "Dashboard",
  components: {
    CharacterCard,
  },
  async setup() {
    let characters = ref(null);
    characters.value = await fetch(
      "http://localhost:3000/api/get/characters.php"
    )
      .then((res) => res.json())
      .then((data) => data);
    // console.log(characters);

    return { characters };
  },
});
</script>

<style scoped>
.character-list {
    display: flex;
    flex-wrap: wrap;
    width:100%;
}
</style>
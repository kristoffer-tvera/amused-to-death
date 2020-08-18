<template>
  <main class="raid-editor container">
    <div class="raid-editor__title">
      <h1>{{title}}</h1>
    </div>
    <form class="raid-editor__form" ref="raidConfig" @submit="submit">
      <input type="hidden" name="id" :value="state.raid.id" />
      <TextField name="name" text="Enter raid name" type="text" v-model:value="state.raid.name"  />
      <!-- TODO: Since input type converts the value to "string" anyway, the NumberField is a bit redundant. Replace with TextField later... -->
      <NumberField v-model:value="state.raid.gold" name="gold" text="Enter gold amount" />
      <DateField name="start" />
      <button type="submit">Submit me</button>
      <Btn type="submit" text="Submit" styles="red" />
    </form>
  </main>
</template>


<script lang="ts">
import { defineComponent, reactive, ref } from "vue";
import { useRoute } from "vue-router";
import TextField from "./TextField.vue";
import NumberField from "./NumberField.vue";
import DateField from "./DateField.vue";
import Btn from "./Btn.vue";

export default defineComponent({
  name: "RaidEditor",
  components: {
    TextField,
    NumberField,
    DateField,
    Btn,
  },
  async setup() {
    const {
      params: { id },
    } = useRoute();
    const state = reactive({
      raid: {},
      isDirty: false,
      error: null,
    });

    let title;

    if (id) {
      try {
        title = "Edit raid";
        await fetch("/api/raid.php?id=" + id)
          .then((res) => res.json())
          .then((json) => (state.raid = json));
      } catch (e) {
        state.error = e;
      }
    } else {
      title = "Create raid";
    }

    function submit(e: any) {
      e.preventDefault();
      let formData = new FormData(e.currentTarget);
      try {
        fetch("/api/raid.php", {
            method: "POST",
            mode: "cors",
            body: formData
        })
      }
      catch(e) {
        state.error = e;
      }
    }

    return {  submit, title, state };
  },
});
</script>


<style lang="scss" scoped>
.raid-editor {
  box-sizing: border-box;
  padding: var(--edge-inset);
  background-color: var(--overlay-background);

  &__form {
    & > * {
      margin: 1rem 0;
    }
  }

  /* &__title {

} */
}
</style>
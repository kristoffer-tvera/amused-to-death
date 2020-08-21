<template>
<div class="raid-editor__wrapper container">
  <div class="raid-editor">
    <div class="raid-editor__title">
      <h1>{{title}}</h1>
    </div>
    <form class="raid-editor__form" ref="raidConfig" @submit="submit">
      <input type="hidden" name="id" :value="state.raid.id" />
      <InputField name="name" text="Enter raid name" type="text" v-model:value="state.raid.name"  />
      <InputField  name="gold" type="number" v-model:value="state.raid.gold" text="Enter gold amount" min="0" />
      <InputField  name="start_date" type="datetime-local" v-model:value="state.raid.start"  />
      <Btn type="submit" text="Submit" />
    </form>
  </div>
</div>
</template>


<script lang="ts">
import { defineComponent, reactive, ref } from "vue";
import { useRoute } from "vue-router";
import InputField from "./InputField.vue";
import Btn from "./Btn.vue";

export default defineComponent({
  name: "RaidEditor",
  components: {
    InputField,
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

  &__wrapper {
    padding: 1rem;
    box-sizing: border-box;
  }

  /* &__title {

} */
}
</style>
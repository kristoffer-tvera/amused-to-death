<template>
  <div class="input__group">
    <label for="name">{{name}}</label>
    <input class="input" :type="type" :name="name" :placeholder="text" :value="value" @input="(event) => $emit('update:value', event.target.value)" :min="min" :max="max" />
  </div>
</template>
    

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "TextField",
  props: {
    name: String,
    text: String,
    type: String,
    value: String,
    min: String,
    max: String
  },
});
</script>

<style lang="scss" scoped>
.input {
  background-color: var(--background);
  color: var(--primary-title-color);
  box-sizing:border-box;
  padding: .5rem;
  border:none;
  &__group {
    display: flex;
    flex-direction: column;
    color: var(--primary-title-color);

    & label {
    margin-bottom: .5rem;
    font-size: 1rem;
    text-transform:capitalize;
    }
  }
}
</style>
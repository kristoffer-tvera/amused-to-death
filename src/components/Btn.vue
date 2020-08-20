<template>
    <button :type="type" class="button" :class="styles">{{text}}</button>
</template>

<script lang="ts">
import {defineComponent} from "vue";

export default defineComponent({
    name: "Btn",
    props: {
        text: String,
        type: String,
        styles: String,
    },
})

</script>

<style lang="scss" scoped>
.button {
    padding: .5rem 1rem;
}
</style>
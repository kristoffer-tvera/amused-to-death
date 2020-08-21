
<template>
<!-- TODO: Add new property to user raids - "accepted" - to show if the user is selected to participate in the raid -->
<div>
    <!-- Add an on click that shows more raid info when raid card is clicked -->
<ul class="list-unstyled">
    <li class="raid-list__item" v-for="r in raids" :key="r.id">
        <RaidCard :name="r.name" :start="r.start_date" :id="r.id" :raid="r" />
    </li>
</ul>
</div>
</template>

<script lang="ts">

import { defineComponent, reactive, ref } from "vue";
import RaidCard from "./RaidCard.vue";
import useRaids from "../state/raids";

export default defineComponent({
name: "UserRaids",
components: {
    RaidCard
},

async setup() {
    const { raids, loadRaids, error } = useRaids();
    await loadRaids();
    console.log(raids.value);
    return {raids}
}
});

</script>

<style lang="scss" scoped>
.raid-list {

    &__item {
        margin-bottom: .5rem;

        &:last-of-type {
            margin-bottom: 0;
        }
    }
}
</style>
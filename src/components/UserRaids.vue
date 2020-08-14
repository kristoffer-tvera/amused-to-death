
<template>
<!-- TODO: Add new property to user raids - "accepted" - to show if the user is selected to participate in the raid -->
<div>
    <!-- Add an on click that shows more raid info when raid card is clicked -->
<ul class="list-unstyled">
    <li>
        <RaidCard/>
    </li>
</ul>
</div>
</template>

<script lang="ts">

import { defineComponent, reactive, ref } from "vue";
import RaidCard from "./RaidCard.vue";

export default defineComponent({
name: "UserRaids",
components: {
    RaidCard
},

async setup() {
    let raids = ref(null);

}
});

</script>

<style scoped>

</style>
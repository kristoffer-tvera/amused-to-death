
<template>
    <nav class="nav">
        <div class="nav__container">
                <!-- <ul>
                    <li>
                        
                    </li>
                </ul> -->
        </div>
    </nav>
</template>


<script lang="ts">
import {defineComponent} from "vue";

export default defineComponent({
    name: "Navbar"
})

</script>

<style scoped>

.nav {
    display: flex;
    align-content: center;
    min-height: 55px;
    background-color: var(--overlay-background);
}

</style>
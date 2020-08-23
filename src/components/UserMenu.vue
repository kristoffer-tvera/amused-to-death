
<template>
    <div>
        <p>{{userState?.user?.alias}}</p>
    </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue'
import useUsers from "../state/useUsers";

export default defineComponent({
    name:"UserMenu",
    async setup() {
    const { getUser, userState } = useUsers();
    await getUser();

    console.log(userState);
    return {userState}
    }
})
</script>

<style lang="scss" scoped>
.user-menu {
    
}
</style>
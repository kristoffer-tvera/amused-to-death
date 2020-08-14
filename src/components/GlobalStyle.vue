
<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "GlobalStyle",
});
</script>

<style>
body {
  margin: 0;
  background-color: var(--background);
}

h1,h2,h3,h4,h5,h6, a {
  color: var(--primary-title-color);
}

h3 {
  font-size: var(--h3-size-default);
}

p, li, article {
  color: var(--primary-text-color);
}

:root {
  --background: #181818;
  --overlay-background: #202020;
  --primary-title-color: #fff;
  --primary-text-color: #aaaaaa;
  --edge-inset: 1rem;
  --h3-size-default: 1.2rem;
}

.w-100 {
  width: 100%;
}

.list-unstyled {
  list-style: none;
  padding: 0;
  margin:0;
}

.container {
  max-width: 1140px;
  width: 100%;
  margin:0 auto;
}

</style>
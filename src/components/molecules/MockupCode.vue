<template>
  <div class="mockup-code-custom bg-base-300">
    <Prism :language="language || 'bash'" :code="code" :plugins="plugins" />
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import Prism from '@/components/atoms/Prism.vue'
import 'prismjs/themes/prism-tomorrow.css'

@Options({
  components: {
    Prism,
  },
  props: {
    language: String,
    code: String,
    plugins: Array,
  },
})
export default class MockupCode extends Vue {
  code = ''
  plugins = []
}
</script>

<style scoped>
.mockup-code-custom {
  overflow: auto hidden;
  position: relative;
  padding-top: 1rem;
  border-radius: var(--rounded-box, 1rem);
}

.mockup-code-custom::before {
  content: '';
  border-radius: 9999px;
  display: block;
  height: 0.75rem;
  margin-bottom: 0.5rem;
  opacity: 0.3;
  width: 0.75rem;
  box-shadow: 1em 0, 2.4em 0, 3.8em 0;
}
</style>

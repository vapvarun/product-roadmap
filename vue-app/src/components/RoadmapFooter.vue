<template>
  <div class="roadmap-footer">
    <button 
      @click="$emit('suggest')"
      class="roadmap-btn roadmap-btn-primary"
    >
      {{ i18n.suggest || `Suggest ${i18n.item_singular || 'Feature'}` }}
    </button>
  </div>
</template>

<script setup>
import { inject } from 'vue'

defineEmits(['suggest'])

const config = inject('$roadmapConfig')
const i18n = config?.i18n || {}
</script>

<style lang="scss" scoped>
.roadmap-footer {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid var(--rm-border);
  text-align: center;
}
</style>
<template>
  <div class="roadmap-timeline">
    <h2>Timeline View (Pro Feature)</h2>
    <p>Timeline/Gantt chart view would be implemented here for Pro users.</p>
  </div>
</template>

<script setup>
defineProps({
  items: Array,
  mode: String
})

defineEmits(['update-item'])
</script>

<style lang="scss" scoped>
.roadmap-timeline {
  padding: 40px;
  text-align: center;
  background: #f5f5f5;
  border-radius: 8px;
}
</style>
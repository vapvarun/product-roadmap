<template>
  <div class="roadmap-footer">
    <button 
      @click="$emit('suggest')"
      class="roadmap-btn roadmap-btn-primary"
    >
      Suggest a Feature
    </button>
  </div>
</template>

<script setup>
defineEmits(['suggest'])
</script>

<style lang="scss" scoped>
.roadmap-footer {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid #ddd;
  text-align: center;
}

.roadmap-dark .roadmap-footer {
  border-top-color: #50575e;
}
</style>
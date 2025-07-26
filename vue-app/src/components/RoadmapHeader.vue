<template>
  <div class="roadmap-header">
    <h1>{{ productName }} Roadmap</h1>
    <div class="roadmap-header-actions">
      <input 
        type="search"
        :value="search"
        @input="$emit('update:search', $event.target.value)"
        placeholder="Search roadmap..."
        class="roadmap-form-control"
      >
      <button 
        v-if="canSuggest && enableSuggestions"
        @click="$emit('suggest')"
        class="roadmap-btn roadmap-btn-primary"
      >
        Suggest a Feature
      </button>
      <button 
        v-if="canManage"
        @click="$emit('update:mode', mode === 'view' ? 'manage' : 'view')"
        class="roadmap-btn roadmap-btn-secondary"
      >
        {{ mode === 'view' ? 'Manage Mode' : 'View Mode' }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  mode: String,
  search: String,
  canManage: Boolean,
  canSuggest: Boolean,
  enableSuggestions: Boolean,
  proFeatures: Object,
  productName: {
    type: String,
    default: 'Product'
  }
})

defineEmits(['update:mode', 'update:search', 'toggle-dark-mode', 'suggest'])
</script>

<style lang="scss" scoped>
.roadmap-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
  
  h1 {
    margin: 0;
  }
  
  &-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    
    input {
      width: 250px;
    }
  }
}

@media (max-width: 768px) {
  .roadmap-header {
    flex-direction: column;
    align-items: stretch;
    
    &-actions {
      width: 100%;
      
      input {
        flex: 1;
      }
    }
  }
}
</style>
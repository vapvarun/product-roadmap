<template>
  <div class="roadmap-filters">
    <select
      :value="status"
      @change="$emit('update:status', $event.target.value)"
      class="roadmap-form-control"
      :aria-label="config?.i18n?.filter_by_status || 'Filter by status'"
    >
      <option value="">{{ config?.i18n?.all_statuses || 'All Statuses' }}</option>
      <option 
        v-for="s in statuses"
        :key="s.id"
        :value="s.slug"
      >
        {{ s.name }}
      </option>
    </select>
    
    <select
      :value="category"
      @change="$emit('update:category', $event.target.value)"
      class="roadmap-form-control"
      :aria-label="config?.i18n?.filter_by_category || 'Filter by category'"
    >
      <option value="">{{ config?.i18n?.all_categories || 'All Categories' }}</option>
      <option 
        v-for="c in categories"
        :key="c.id"
        :value="c.slug"
      >
        {{ c.name }}
      </option>
    </select>
    
    <select 
      v-if="isPro && products.length > 0"
      :value="product"
      @change="$emit('update:product', $event.target.value)"
      class="roadmap-form-control"
      :aria-label="config?.i18n?.filter_by_product || 'Filter by product'"
    >
      <option value="">{{ config?.i18n?.all_products || 'All Products' }}</option>
      <option 
        v-for="p in products"
        :key="p.id"
        :value="p.slug"
      >
        {{ p.name }}
      </option>
    </select>
    
    <select
      :value="sortBy"
      @change="$emit('update:sortBy', $event.target.value)"
      class="roadmap-form-control roadmap-sort-control"
      :aria-label="config?.i18n?.sort_by || 'Sort by'"
    >
      <option value="">{{ config?.i18n?.sort_by || 'Sort By' }}</option>
      <option value="votes">{{ config?.i18n?.most_voted || 'Most Voted' }}</option>
      <option value="recent">{{ config?.i18n?.most_recent || 'Most Recent' }}</option>
      <option value="comments">{{ config?.i18n?.most_discussed || 'Most Discussed' }}</option>
      <option value="priority">{{ config?.i18n?.highest_priority || 'Highest Priority' }}</option>
    </select>
  </div>
</template>

<script setup>
import { inject } from 'vue'

const config = inject('$roadmapConfig', {})

defineProps({
  status: String,
  category: String,
  product: String,
  sortBy: String,
  statuses: Array,
  categories: Array,
  products: Array,
  isPro: Boolean
})

defineEmits(['update:status', 'update:category', 'update:product', 'update:sortBy'])
</script>

<style lang="scss" scoped>
.roadmap-filters {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  
  select {
    min-width: 150px;
    flex: 1;
  }
  
  .roadmap-sort-control {
    margin-left: auto;
    background-color: #f0f4f8;
    border-color: #e1e8ed;
    font-weight: 500;
    
    &:hover {
      background-color: #e1e8ed;
    }
  }
}

@media (max-width: 768px) {
  .roadmap-filters {
    flex-direction: column;
    
    select {
      width: 100%;
    }
    
    .roadmap-sort-control {
      margin-left: 0;
      order: -1; // Move sort to top on mobile
    }
  }
}
</style>
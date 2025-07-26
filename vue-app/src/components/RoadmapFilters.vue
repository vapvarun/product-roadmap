<template>
  <div class="roadmap-filters">
    <select 
      :value="status"
      @change="$emit('update:status', $event.target.value)"
      class="roadmap-form-control"
    >
      <option value="">All Statuses</option>
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
    >
      <option value="">All Categories</option>
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
    >
      <option value="">All Products</option>
      <option 
        v-for="p in products"
        :key="p.id"
        :value="p.slug"
      >
        {{ p.name }}
      </option>
    </select>
  </div>
</template>

<script setup>
defineProps({
  status: String,
  category: String,
  product: String,
  statuses: Array,
  categories: Array,
  products: Array,
  isPro: Boolean
})

defineEmits(['update:status', 'update:category', 'update:product'])
</script>

<style lang="scss" scoped>
.roadmap-filters {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  
  select {
    min-width: 150px;
  }
}

@media (max-width: 768px) {
  .roadmap-filters {
    flex-direction: column;
    
    select {
      width: 100%;
    }
  }
}
</style>
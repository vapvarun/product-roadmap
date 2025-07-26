<template>
  <div class="roadmap-modal" @click.self="$emit('close')">
    <div class="roadmap-modal-dialog">
      <div class="roadmap-modal-header">
        <h3>{{ item?.id ? 'Edit Item' : 'Add New Item' }}</h3>
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-icon">×</button>
      </div>
      
      <div class="roadmap-modal-body">
        <form @submit.prevent="handleSubmit">
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Title</label>
            <input 
              v-model="form.title"
              type="text"
              class="roadmap-form-control"
              required
            >
          </div>
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Description</label>
            <textarea 
              v-model="form.content"
              class="roadmap-form-control"
              rows="4"
              required
            ></textarea>
          </div>
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Status</label>
            <select v-model="form.status" class="roadmap-form-control">
              <option 
                v-for="status in statuses"
                :key="status.id"
                :value="status.slug"
              >
                {{ status.name }}
              </option>
            </select>
          </div>
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Categories</label>
            <div class="roadmap-checkbox-group">
              <label 
                v-for="category in categories"
                :key="category.id"
                class="roadmap-checkbox-label"
              >
                <input 
                  type="checkbox"
                  :value="category.id"
                  v-model="form.categories"
                >
                {{ category.name }}
              </label>
            </div>
          </div>
          
          <div v-if="isPro" class="roadmap-form-group">
            <label class="roadmap-form-label">Priority</label>
            <select v-model="form.priority" class="roadmap-form-control">
              <option value="">None</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="critical">Critical</option>
            </select>
          </div>
          
          <div v-if="isPro" class="roadmap-form-group">
            <label class="roadmap-form-label">Target Date</label>
            <input 
              v-model="form.target_date"
              type="date"
              class="roadmap-form-control"
            >
          </div>
        </form>
      </div>
      
      <div class="roadmap-modal-footer">
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-secondary">
          Cancel
        </button>
        <button @click="handleSubmit" class="roadmap-btn roadmap-btn-primary">
          Save
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  item: Object,
  statuses: Array,
  categories: Array,
  isPro: Boolean
})

const emit = defineEmits(['save', 'close'])

const form = reactive({
  title: '',
  content: '',
  status: 'planned',
  categories: [],
  priority: '',
  target_date: ''
})

watch(() => props.item, (newItem) => {
  if (newItem) {
    form.title = newItem.title || ''
    form.content = newItem.content || ''
    form.status = newItem.status?.[0]?.slug || 'planned'
    form.categories = newItem.categories?.map(cat => cat.id) || []
    form.priority = newItem.priority || ''
    form.target_date = newItem.target_date || ''
  } else {
    // Reset form for new item
    form.title = ''
    form.content = ''
    form.status = 'planned'
    form.categories = []
    form.priority = ''
    form.target_date = ''
  }
}, { immediate: true })

const handleSubmit = () => {
  const data = { ...form }
  if (props.item?.id) {
    data.id = props.item.id
  }
  emit('save', data)
}
</script>
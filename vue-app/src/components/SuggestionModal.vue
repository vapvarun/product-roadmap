<template>
  <div class="roadmap-modal" @click.self="$emit('close')">
    <div class="roadmap-modal-dialog">
      <div class="roadmap-modal-header">
        <h3>Suggest a Feature</h3>
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-icon">×</button>
      </div>
      
      <div class="roadmap-modal-body">
        <form @submit.prevent="handleSubmit">
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Feature Title</label>
            <input 
              v-model="form.title"
              type="text"
              class="roadmap-form-control"
              required
              placeholder="Brief title for your feature idea"
            >
          </div>
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Description</label>
            <textarea 
              v-model="form.description"
              class="roadmap-form-control"
              rows="6"
              required
              placeholder="Describe your feature idea in detail..."
            ></textarea>
          </div>
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Category (Optional)</label>
            <select v-model="form.category" class="roadmap-form-control">
              <option value="">Select a category</option>
              <option 
                v-for="category in categories"
                :key="category.id"
                :value="category.slug"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
        </form>
      </div>
      
      <div class="roadmap-modal-footer">
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-secondary">
          Cancel
        </button>
        <button @click="handleSubmit" class="roadmap-btn roadmap-btn-primary">
          Submit Suggestion
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  categories: Array
})

const emit = defineEmits(['submit', 'close'])

const form = reactive({
  title: '',
  description: '',
  category: ''
})

const handleSubmit = () => {
  if (form.title && form.description) {
    emit('submit', { ...form })
    // Reset form
    form.title = ''
    form.description = ''
    form.category = ''
  }
}
</script>
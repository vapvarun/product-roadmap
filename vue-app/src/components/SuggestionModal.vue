<template>
  <div class="roadmap-modal" @click.self="$emit('close')">
    <div class="roadmap-modal-dialog">
      <div class="roadmap-modal-header">
        <h3>{{ i18n.suggest || `Suggest ${i18n.item_singular || 'Feature'}` }}</h3>
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-icon">×</button>
      </div>
      
      <div class="roadmap-modal-body">
        <form @submit.prevent="handleSubmit">
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.item_singular || 'Feature' }} {{ i18n.title || 'Title' }}</label>
            <input 
              v-model="form.title"
              type="text"
              class="roadmap-form-control"
              required
              :placeholder="i18n.brief_title || 'Brief title for your feature idea'"
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
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Priority (Optional)</label>
            <select v-model="form.priority" class="roadmap-form-control">
              <option value="">Select priority</option>
              <option value="low">Low - Nice to have</option>
              <option value="medium">Medium - Important</option>
              <option value="high">High - Very important</option>
              <option value="critical">Critical - Must have</option>
            </select>
          </div>
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">External Link (Optional)</label>
            <input 
              v-model="form.external_link"
              type="url"
              class="roadmap-form-control"
              placeholder="Link to more details or examples"
            >
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
import { reactive, inject } from 'vue'

const props = defineProps({
  categories: Array
})

const emit = defineEmits(['submit', 'close'])

const config = inject('$roadmapConfig')
const i18n = config?.i18n || {}

const form = reactive({
  title: '',
  description: '',
  category: '',
  priority: '',
  external_link: ''
})

const handleSubmit = () => {
  if (form.title && form.description) {
    emit('submit', { ...form })
    // Reset form
    form.title = ''
    form.description = ''
    form.category = ''
    form.priority = ''
    form.external_link = ''
  }
}
</script>
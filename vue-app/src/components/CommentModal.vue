<template>
  <div class="roadmap-modal" @click.self="$emit('close')">
    <div class="roadmap-modal-dialog">
      <div class="roadmap-modal-header">
        <h3>Add Comment</h3>
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-icon">×</button>
      </div>
      
      <div class="roadmap-modal-body">
        <h4>{{ item.title }}</h4>
        <form @submit.prevent="handleSubmit">
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">Your Comment</label>
            <textarea 
              v-model="comment"
              class="roadmap-form-control"
              rows="4"
              required
              placeholder="Share your thoughts..."
            ></textarea>
          </div>
        </form>
      </div>
      
      <div class="roadmap-modal-footer">
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-secondary">
          Cancel
        </button>
        <button @click="handleSubmit" class="roadmap-btn roadmap-btn-primary">
          Post Comment
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  item: Object
})

const emit = defineEmits(['submit', 'close'])

const comment = ref('')

const handleSubmit = () => {
  if (comment.value.trim()) {
    emit('submit', {
      item_id: props.item.id,
      content: comment.value
    })
  }
}
</script>
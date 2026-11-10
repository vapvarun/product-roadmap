<template>
  <div class="roadmap-modal" @click.self="$emit('close')">
    <div class="roadmap-modal-dialog">
      <div class="roadmap-modal-header">
        <h3>{{ item?.id ? (i18n.edit_item || 'Edit Item') : (i18n.add_new_item || 'Add New Item') }}</h3>
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-icon">×</button>
      </div>
      
      <div class="roadmap-modal-body">
        <form @submit.prevent="handleSubmit">
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.title || 'Title' }}</label>
            <input
              v-model="form.title"
              type="text"
              class="roadmap-form-control"
              required
            >
          </div>

          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.description || 'Description' }}</label>
            <textarea
              v-model="form.content"
              class="roadmap-form-control"
              rows="4"
              required
            ></textarea>
          </div>

          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.status || 'Status' }}</label>
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
            <label class="roadmap-form-label">{{ i18n.categories || 'Categories' }}</label>
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
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.priority || 'Priority' }}</label>
            <select v-model="form.priority" class="roadmap-form-control">
              <option value="">{{ i18n.none || 'None' }}</option>
              <option value="low">{{ i18n.low || 'Low' }}</option>
              <option value="medium">{{ i18n.medium || 'Medium' }}</option>
              <option value="high">{{ i18n.high || 'High' }}</option>
              <option value="critical">{{ i18n.critical || 'Critical' }}</option>
            </select>
          </div>

          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.progress || 'Progress' }} (%)</label>
            <div class="roadmap-range-wrapper">
              <input 
                v-model="form.progress"
                type="range"
                min="0"
                max="100"
                step="5"
                class="roadmap-range-input"
                :style="{ '--roadmap-progress': `${form.progress}%` }"
              >
              <span class="roadmap-range-value">{{ form.progress }}%</span>
            </div>
          </div>
          
          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.target_date || 'Target Date' }}</label>
            <input
              v-model="form.target_date"
              type="date"
              class="roadmap-form-control"
            >
          </div>

          <div class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.external_link || 'External Link' }}</label>
            <input 
              v-model="form.external_link"
              type="url"
              class="roadmap-form-control"
              placeholder="https://example.com/feature-spec"
            >
          </div>
          
          <div v-if="isPro" class="roadmap-form-row">
            <div class="roadmap-form-group">
              <label class="roadmap-form-label">{{ i18n.start_date || 'Start Date' }}</label>
              <input
                v-model="form.start_date"
                type="date"
                class="roadmap-form-control"
              >
            </div>

            <div class="roadmap-form-group">
              <label class="roadmap-form-label">{{ i18n.end_date || 'End Date' }}</label>
              <input
                v-model="form.end_date"
                type="date"
                class="roadmap-form-control"
              >
            </div>
          </div>

          <div v-if="isPro" class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.effort_estimate || 'Effort Estimate' }}</label>
            <select v-model="form.effort_estimate" class="roadmap-form-control">
              <option value="">{{ i18n.select_effort || '-- Select Effort --' }}</option>
              <option value="xs">{{ i18n.effort_xs || 'XS - Very Small (1-2 days)' }}</option>
              <option value="s">{{ i18n.effort_s || 'S - Small (3-5 days)' }}</option>
              <option value="m">{{ i18n.effort_m || 'M - Medium (1-2 weeks)' }}</option>
              <option value="l">{{ i18n.effort_l || 'L - Large (2-4 weeks)' }}</option>
              <option value="xl">{{ i18n.effort_xl || 'XL - Extra Large (1-2 months)' }}</option>
              <option value="xxl">{{ i18n.effort_xxl || 'XXL - Huge (2+ months)' }}</option>
            </select>
          </div>

          <div v-if="isPro" class="roadmap-form-group">
            <label class="roadmap-form-label">{{ i18n.github_issue_url || 'GitHub Issue URL' }}</label>
            <input 
              v-model="form.github_issue"
              type="url"
              class="roadmap-form-control"
              placeholder="https://github.com/owner/repo/issues/123"
            >
          </div>
        </form>
      </div>
      
      <div class="roadmap-modal-footer">
        <button @click="$emit('close')" class="roadmap-btn roadmap-btn-secondary">
          {{ i18n.cancel || 'Cancel' }}
        </button>
        <button @click="handleSubmit" class="roadmap-btn roadmap-btn-primary">
          {{ i18n.save || 'Save' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, inject } from 'vue'

const config = inject('$roadmapConfig', {})
const i18n = config?.i18n || {}

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
  progress: 0,
  target_date: '',
  external_link: '',
  start_date: '',
  end_date: '',
  effort_estimate: '',
  github_issue: ''
})

watch(() => props.item, (newItem) => {
  if (newItem) {
    form.title = newItem.title || ''
    form.content = newItem.content || ''
    form.status = newItem.status?.[0]?.slug || 'planned'
    form.categories = newItem.categories?.map(cat => cat.id) || []
    form.priority = newItem.priority || ''
    form.progress = newItem.progress || 0
    form.target_date = newItem.target_date || ''
    form.external_link = newItem.external_link || ''
    form.start_date = newItem.start_date || ''
    form.end_date = newItem.end_date || ''
    form.effort_estimate = newItem.effort_estimate || ''
    form.github_issue = newItem.github_issue || ''
  } else {
    // Reset form for new item
    form.title = ''
    form.content = ''
    form.status = 'planned'
    form.categories = []
    form.priority = ''
    form.progress = 0
    form.target_date = ''
    form.external_link = ''
    form.start_date = ''
    form.end_date = ''
    form.effort_estimate = ''
    form.github_issue = ''
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

<style scoped>
.roadmap-form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.roadmap-range-wrapper {
  display: flex;
  align-items: center;
  gap: 15px;
}

.roadmap-range-input {
  flex: 1;
  height: 8px;
  -webkit-appearance: none;
  appearance: none;
  /* The track is the slider boundary (>= 3:1); themes add a generic input
     border, which a slider does not need. */
  background: linear-gradient(
    to right,
    var(--roadmap-accent) var(--roadmap-progress, 0%),
    var(--rm-control-border) var(--roadmap-progress, 0%)
  );
  border: 0;
  border-radius: 4px;
  outline: none;
}

.roadmap-range-input::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  background: var(--roadmap-accent);
  border-radius: 50%;
  cursor: pointer;
}

.roadmap-range-input::-moz-range-thumb {
  width: 20px;
  height: 20px;
  background: var(--roadmap-accent);
  border-radius: 50%;
  cursor: pointer;
  border: none;
}

.roadmap-range-value {
  min-width: 45px;
  font-weight: 600;
  color: var(--roadmap-accent-text);
  font-size: 14px;
}

@media (max-width: 640px) {
  .roadmap-form-row {
    grid-template-columns: 1fr;
  }
}
</style>
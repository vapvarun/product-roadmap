<template>
  <div class="roadmap-template-cards roadmap-cards">
    <div class="roadmap-cards-header">
      <div class="roadmap-cards-controls">
        <select v-model="columns" class="roadmap-form-control">
          <option value="auto">Auto Layout</option>
          <option value="2">2 Columns</option>
          <option value="3">3 Columns</option>
          <option value="4">4 Columns</option>
        </select>
        <select v-model="sortBy" class="roadmap-form-control">
          <option value="votes">Sort by Votes</option>
          <option value="created">Sort by Created</option>
          <option value="priority">Sort by Priority</option>
          <option value="title">Sort by Title</option>
        </select>
      </div>
    </div>

    <div 
      class="roadmap-cards-grid"
      :class="`roadmap-cards-columns-${columns}`"
    >
      <div v-if="sortedItems.length === 0" class="roadmap-cards-empty">
        <p>No roadmap items found</p>
      </div>
      
      <div 
        v-for="item in sortedItems" 
        :key="item.id"
        class="roadmap-card"
        @click="$emit('item-click', item)"
      >
        <div class="roadmap-card-header">
          <div class="roadmap-card-status">
            <span 
              v-for="status in item.status" 
              :key="status.id"
              class="roadmap-badge roadmap-badge-primary"
            >
              {{ status.name }}
            </span>
          </div>
          <div class="roadmap-card-votes">
            <span class="roadmap-vote-icon">👍</span>
            <span class="roadmap-vote-count">{{ item.votes || 0 }}</span>
          </div>
        </div>

        <h3 class="roadmap-card-title">{{ item.title }}</h3>
        
        <div class="roadmap-card-content">
          {{ item.excerpt || item.content.substring(0, 150) + '...' }}
        </div>

        <div 
          v-if="item.progress && item.progress > 0" 
          class="roadmap-card-progress"
        >
          <div class="roadmap-progress">
            <div 
              class="roadmap-progress-bar" 
              :style="{ width: item.progress + '%' }"
            ></div>
          </div>
          <span class="roadmap-progress-text">{{ item.progress }}%</span>
        </div>

        <div class="roadmap-card-footer">
          <div class="roadmap-card-meta">
            <span 
              v-if="item.priority" 
              class="roadmap-card-priority"
              :class="`roadmap-priority-${item.priority}`"
            >
              {{ item.priority }}
            </span>
            <span 
              v-if="item.target_date" 
              class="roadmap-card-date"
            >
              📅 {{ formatDate(item.target_date) }}
            </span>
          </div>
          
          <div class="roadmap-card-stats">
            <span class="roadmap-card-stat">💬 {{ item.comment_count || 0 }}</span>
            <div v-if="item.assignee" class="roadmap-card-assignee">
              <img 
                v-if="item.assignee.avatar" 
                :src="item.assignee.avatar" 
                :alt="item.assignee.name"
                class="roadmap-assignee-avatar"
              />
              <div 
                v-else 
                class="roadmap-assignee-initial"
              >
                {{ item.assignee.name.charAt(0).toUpperCase() }}
              </div>
            </div>
          </div>
        </div>

        <div 
          v-if="item.categories && item.categories.length" 
          class="roadmap-card-categories"
        >
          <span
            v-for="category in item.categories"
            :key="category.id"
            class="roadmap-category-tag"
          >
            {{ category.name }}
          </span>
        </div>

        <div v-if="mode === 'manage' && canEdit" class="roadmap-card-actions">
          <button
            @click.stop="$emit('edit-item', item)"
            class="roadmap-btn roadmap-btn-sm roadmap-btn-secondary"
            title="Edit"
          >
            ✏️ Edit
          </button>
          <button
            @click.stop="$emit('delete-item', item.id)"
            class="roadmap-btn roadmap-btn-sm roadmap-btn-danger"
            title="Delete"
          >
            🗑️ Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  statuses: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  mode: {
    type: String,
    default: 'view'
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  proFeatures: {
    type: Object,
    default: () => ({})
  }
})

defineEmits(['item-click', 'update-item', 'edit-item', 'delete-item'])

const columns = ref('auto')
const sortBy = ref('votes')

const sortedItems = computed(() => {
  return [...props.items].sort(getSortFunction())
})

const getSortFunction = () => {
  return (a, b) => {
    switch (sortBy.value) {
      case 'votes':
        return (b.votes || 0) - (a.votes || 0)
      case 'priority':
        const priorityOrder = { critical: 4, high: 3, medium: 2, low: 1 }
        return (priorityOrder[b.priority] || 0) - (priorityOrder[a.priority] || 0)
      case 'title':
        return a.title.localeCompare(b.title)
      case 'created':
      default:
        return new Date(b.created_at || 0) - new Date(a.created_at || 0)
    }
  }
}

const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  }) : ''
}
</script>

<style lang="scss" scoped>
.roadmap-cards-header {
  margin-bottom: 20px;
  display: flex;
  justify-content: flex-end;
}

.roadmap-cards-controls {
  display: flex;
  gap: 15px;

  select {
    min-width: 150px;
  }
}

.roadmap-cards-grid {
  display: grid;
  gap: 20px;

  &.roadmap-cards-columns-2 {
    grid-template-columns: repeat(2, 1fr);
  }

  &.roadmap-cards-columns-3 {
    grid-template-columns: repeat(3, 1fr);
  }

  &.roadmap-cards-columns-4 {
    grid-template-columns: repeat(4, 1fr);
  }

  &.roadmap-cards-columns-auto {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
}

.roadmap-cards-empty {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.roadmap-card {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;

  &:hover {
    border-color: #2271b1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }
}

.roadmap-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.roadmap-card-status {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.roadmap-card-votes {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #f0f0f0;
  padding: 4px 8px;
  border-radius: 15px;
  font-size: 14px;

  .roadmap-vote-count {
    font-weight: 600;
    color: #333;
  }

  .roadmap-vote-icon {
    font-size: 12px;
  }
}

.roadmap-card-title {
  margin: 0 0 12px;
  font-size: 18px;
  font-weight: 600;
  color: #333;
  line-height: 1.3;
}

.roadmap-card-content {
  margin: 0 0 15px;
  color: #666;
  font-size: 14px;
  line-height: 1.6;
  flex-grow: 1;
}

.roadmap-card-progress {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;

  .roadmap-progress {
    flex: 1;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    overflow: hidden;

    .roadmap-progress-bar {
      height: 100%;
      background: #46b450;
      transition: width 0.3s;
    }
  }

  .roadmap-progress-text {
    font-size: 12px;
    color: #666;
    min-width: 35px;
  }
}

.roadmap-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}

.roadmap-card-meta {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.roadmap-card-priority {
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 10px;
  font-weight: 500;

  &.roadmap-priority-low {
    background: #d4f4dd;
    color: #1e7e34;
  }

  &.roadmap-priority-medium {
    background: #fff3cd;
    color: #856404;
  }

  &.roadmap-priority-high {
    background: #ffeaa7;
    color: #e17055;
  }

  &.roadmap-priority-critical {
    background: #f8d7da;
    color: #721c24;
  }
}

.roadmap-card-date {
  font-size: 12px;
  color: #666;
}

.roadmap-card-stats {
  display: flex;
  gap: 10px;
  align-items: center;
}

.roadmap-card-stat {
  font-size: 13px;
  color: #666;
}

.roadmap-card-assignee {
  display: flex;
  align-items: center;

  .roadmap-assignee-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
  }

  .roadmap-assignee-initial {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #2271b1;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
  }
}

.roadmap-card-categories {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

.roadmap-card-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;

  button {
    font-size: 13px;
    padding: 6px 12px;
    flex: 1;
  }
}

.roadmap-category-tag {
  font-size: 11px;
  padding: 3px 8px;
  background: #f0f0f0;
  color: #666;
  border-radius: 10px;
}

// Dark mode
.roadmap-dark .roadmap-cards-grid .roadmap-card {
  background: #32373c;
  border-color: #50575e;

  &:hover {
    border-color: #4a90d9;
  }
}

.roadmap-dark .roadmap-cards-grid .roadmap-card-title {
  color: #e2e4e7;
}

.roadmap-dark .roadmap-cards-grid .roadmap-card-content {
  color: #aaa;
}

.roadmap-dark .roadmap-cards-grid .roadmap-card-votes {
  background: #23282d;

  .roadmap-vote-count {
    color: #e2e4e7;
  }
}

.roadmap-dark .roadmap-cards-grid .roadmap-card-date,
.roadmap-dark .roadmap-cards-grid .roadmap-card-stat {
  color: #999;
}

.roadmap-dark .roadmap-cards-grid .roadmap-card-categories {
  border-top-color: #50575e;
}

.roadmap-dark .roadmap-cards-grid .roadmap-card-actions {
  border-top-color: #50575e;
}

.roadmap-dark .roadmap-cards-grid .roadmap-category-tag {
  background: #23282d;
  color: #999;
}

// Responsive
@media (max-width: 1200px) {
  .roadmap-cards-grid.roadmap-cards-columns-4 {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 900px) {
  .roadmap-cards-grid.roadmap-cards-columns-3,
  .roadmap-cards-grid.roadmap-cards-columns-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .roadmap-cards-controls {
    flex-direction: column;
    width: 100%;

    select {
      width: 100%;
    }
  }

  .roadmap-cards-grid.roadmap-cards-columns-2,
  .roadmap-cards-grid.roadmap-cards-columns-3,
  .roadmap-cards-grid.roadmap-cards-columns-4 {
    grid-template-columns: 1fr;
  }
}
</style>
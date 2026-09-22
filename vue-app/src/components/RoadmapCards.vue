<template>
  <div class="roadmap-template-cards roadmap-cards">
    <div class="roadmap-cards-header">
      <div class="roadmap-cards-controls">
        <select v-model="columns" class="roadmap-form-control">
          <option value="auto">{{ i18n.auto_layout || 'Auto Layout' }}</option>
          <option value="2">2 {{ i18n.columns_label || 'Columns' }}</option>
          <option value="3">3 {{ i18n.columns_label || 'Columns' }}</option>
          <option value="4">4 {{ i18n.columns_label || 'Columns' }}</option>
        </select>
      </div>
    </div>

    <div
      class="roadmap-cards-grid"
      :class="`roadmap-cards-columns-${columns}`"
    >
      <div v-if="items.length === 0" class="roadmap-cards-empty">
        <p>{{ i18n.no_items || 'No roadmap items found' }}</p>
      </div>

      <div
        v-for="item in items"
        :key="item.id"
        class="roadmap-card"
        @click="$emit('item-click', item)"
      >
        <div class="roadmap-card-header">
          <div class="roadmap-card-status">
            <span
              v-for="status in item.status"
              :key="status.id"
              class="roadmap-badge"
              :class="'roadmap-status-' + (status.slug || 'default')"
            >
              {{ status.name }}
            </span>
          </div>
          <div v-if="showVotes" class="roadmap-card-votes">
            <span class="roadmap-vote-icon">👍</span>
            <span class="roadmap-vote-count">{{ item.votes || 0 }}</span>
          </div>
        </div>

        <h3 class="roadmap-card-title">
          {{ item.title }}
          <span
            v-if="item.is_suggestion"
            class="roadmap-card-source-badge"
            :title="i18n.user_suggested_title || 'Suggested by a user'"
          >
            {{ i18n.suggested || 'Suggested' }}
          </span>
        </h3>
        
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
            <span v-if="showComments" class="roadmap-card-stat">💬 {{ item.comment_count || 0 }}</span>
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
            :title="i18n.edit || 'Edit'"
          >
            ✏️ {{ i18n.edit || 'Edit' }}
          </button>
          <button
            @click.stop="$emit('delete-item', item.id)"
            class="roadmap-btn roadmap-btn-sm roadmap-btn-danger"
            :title="i18n.delete || 'Delete'"
          >
            🗑️ {{ i18n.delete || 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue'

const config = inject('$roadmapConfig', {})
const i18n = config?.i18n || {}

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

// Hide vote/comment stats entirely when the feature is off, matching the fix
// already shipped for the Minimal template (RoadmapMinimal.vue showVotes/showComments).
const showVotes = computed(() => config.settings?.enable_voting !== false)
const showComments = computed(() => config.settings?.enable_comments !== false)

const columns = ref('auto')

// This template used to have its own Sort By dropdown (votes/created/priority/
// title) that client-side re-sorted props.items independently of the shared
// filter bar's Sort By control above it (RoadmapFilters.vue, which already
// drives a real server-side `sort` REST param - see App.vue's selectedSort).
// Two identically-labeled "Sort By" controls was confusing on its own, and
// this one defaulted to 'votes' and silently overrode whatever sort the
// visitor had actually picked in the real filter. Removed; items render in
// the order the server already returned them.

const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  }) : ''
}
</script>

<style lang="scss" scoped>
.roadmap-card-source-badge {
  display: inline-block;
  margin-inline-start: 8px;
  padding: 2px 8px;
  font-size: 10px;
  font-weight: 600;
  line-height: 1.6;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  vertical-align: middle;
  border-radius: 10px;
  color: var(--rm-review, #7c3aed);
  background: color-mix(in srgb, var(--rm-review, #7c3aed) 14%, transparent);
}

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
  color: var(--rm-muted);
}

.roadmap-card {
  background: var(--rm-surface);
  border: 1px solid var(--rm-border);
  border-radius: 8px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;

  &:hover {
    border-color: var(--roadmap-accent);
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
  background: var(--rm-surface-alt);
  padding: 4px 8px;
  border-radius: 15px;
  font-size: 14px;

  .roadmap-vote-count {
    font-weight: 600;
    color: var(--rm-heading);
  }

  .roadmap-vote-icon {
    font-size: 12px;
  }
}

.roadmap-card-title {
  margin: 0 0 12px;
  font-size: 18px;
  font-weight: 600;
  color: var(--rm-heading);
  line-height: 1.3;
}

.roadmap-card-content {
  margin: 0 0 15px;
  color: var(--rm-muted);
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
    background: var(--rm-border);
    border-radius: 3px;
    overflow: hidden;

    .roadmap-progress-bar {
      height: 100%;
      background: var(--rm-done);
      transition: width 0.3s;
    }
  }

  .roadmap-progress-text {
    font-size: 12px;
    color: var(--rm-muted);
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
    background: var(--rm-priority-low-tint);
    color: var(--rm-priority-low);
  }

  &.roadmap-priority-medium {
    background: var(--rm-priority-medium-tint);
    color: var(--rm-priority-medium);
  }

  &.roadmap-priority-high {
    background: var(--rm-priority-high-tint);
    color: var(--rm-priority-high);
  }

  &.roadmap-priority-critical {
    background: var(--rm-priority-critical-tint);
    color: var(--rm-priority-critical);
  }
}

.roadmap-card-date {
  font-size: 12px;
  color: var(--rm-muted);
}

.roadmap-card-stats {
  display: flex;
  gap: 10px;
  align-items: center;
}

.roadmap-card-stat {
  font-size: 13px;
  color: var(--rm-muted);
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
    background: var(--roadmap-accent);
    color: var(--roadmap-accent-contrast, #fff);
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
  border-top: 1px solid var(--rm-border);
}

.roadmap-card-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid var(--rm-border);

  button {
    font-size: 13px;
    padding: 6px 12px;
    flex: 1;
  }
}

.roadmap-category-tag {
  font-size: 11px;
  padding: 3px 8px;
  background: var(--rm-surface-alt);
  color: var(--rm-muted);
  border-radius: 10px;
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
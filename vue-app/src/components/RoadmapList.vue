<template>
  <div class="roadmap-template-list roadmap-list">
    <div class="roadmap-list-header">
      <div class="roadmap-list-controls">
        <select v-model="groupBy" class="roadmap-form-control">
          <option value="status">{{ i18n.group_by_status || 'Group by Status' }}</option>
          <option value="category">{{ i18n.group_by_category || 'Group by Category' }}</option>
          <option value="priority">{{ i18n.group_by_priority || 'Group by Priority' }}</option>
          <option value="none">{{ i18n.no_grouping || 'No Grouping' }}</option>
        </select>
      </div>
    </div>

    <div class="roadmap-list-groups">
      <div 
        v-for="group in groupedItems" 
        :key="group.key"
        class="roadmap-list-group"
      >
        <div 
          class="roadmap-list-group-header"
          @click="toggleGroup(group.key)"
        >
          <h3>
            <span class="roadmap-list-toggle">
              {{ expandedGroups.includes(group.key) ? '▼' : '▶' }}
            </span>
            {{ group.name }}
            <span class="roadmap-list-count">({{ group.items.length }})</span>
          </h3>
        </div>
        
        <Transition name="list">
          <div 
            v-show="expandedGroups.includes(group.key)"
            class="roadmap-list-items"
          >
            <div 
              v-for="item in group.items" 
              :key="item.id"
              class="roadmap-list-item"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-list-item-main">
                <h4 class="roadmap-list-item-title">
                  {{ item.title }}
                  <span
                    v-if="item.is_suggestion"
                    class="roadmap-list-source-badge"
                    :title="i18n.user_suggested_title || 'Suggested by a user'"
                  >
                    {{ i18n.suggested || 'Suggested' }}
                  </span>
                </h4>
                <p class="roadmap-list-item-excerpt">
                  {{ item.excerpt || item.content.substring(0, 200) + '...' }}
                </p>
              </div>
              
              <div class="roadmap-list-item-meta">
                <div class="roadmap-list-item-badges">
                  <span
                    v-if="item.status && item.status.length"
                    class="roadmap-badge"
                    :class="'roadmap-status-' + (item.status[0].slug || 'default')"
                  >
                    {{ item.status[0].name }}
                  </span>
                  <span
                    v-if="item.priority"
                    class="roadmap-badge"
                    :class="`roadmap-priority-${item.priority}`"
                  >
                    {{ item.priority }}
                  </span>
                  <span
                    v-for="category in item.categories"
                    :key="category.id"
                    class="roadmap-badge roadmap-badge-secondary"
                  >
                    {{ category.name }}
                  </span>
                </div>

                <div class="roadmap-list-item-stats">
                  <div v-if="showVotes" class="roadmap-list-stat">
                    <i>👍</i> {{ item.votes || 0 }}
                  </div>
                  <div v-if="showComments" class="roadmap-list-stat">
                    <i>💬</i> {{ item.comment_count || 0 }}
                  </div>
                  <div v-if="item.target_date" class="roadmap-list-stat">
                    <i>📅</i> {{ formatDate(item.target_date) }}
                  </div>
                </div>
              </div>

              <div v-if="mode === 'manage' && canEdit" class="roadmap-list-item-actions">
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
              
              <div 
                v-if="item.progress && item.progress > 0" 
                class="roadmap-list-item-progress"
              >
                <div class="roadmap-progress">
                  <div 
                    class="roadmap-progress-bar" 
                    :style="{ width: item.progress + '%' }"
                  ></div>
                </div>
                <span class="roadmap-progress-text">{{ item.progress }}%</span>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'

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

const groupBy = ref('status')
// No local sort: items arrive in the board's sort (the owner's setting, or the
// Sort filter), applied by the server across all items, not just this page.
const expandedGroups = ref([])

const groupedItems = computed(() => {
  if (groupBy.value === 'none') {
    return [{
      key: 'all',
      name: i18n.all_items || 'All Items',
      items: props.items
    }]
  }

  const groups = {}
  
  props.items.forEach(item => {
    let groupKey = 'other'
    let groupName = i18n.other || 'Other'
    
    if (groupBy.value === 'status' && item.status && item.status.length) {
      groupKey = item.status[0].slug
      groupName = item.status[0].name
    } else if (groupBy.value === 'category' && item.categories && item.categories.length) {
      groupKey = item.categories[0].slug
      groupName = item.categories[0].name
    } else if (groupBy.value === 'priority' && item.priority) {
      groupKey = item.priority
      groupName = item.priority.charAt(0).toUpperCase() + item.priority.slice(1)
    }
    
    if (!groups[groupKey]) {
      groups[groupKey] = {
        key: groupKey,
        name: groupName,
        items: []
      }
    }
    
    groups[groupKey].items.push(item)
  })
  
  // Sort items within each group
  Object.values(groups).forEach(group => {
  })
  
  return Object.values(groups).sort((a, b) => {
    // Custom ordering for status groups
    if (groupBy.value === 'status') {
      const statusOrder = ['in-progress', 'planned', 'completed', 'cancelled', 'other']
      const aIndex = statusOrder.indexOf(a.key)
      const bIndex = statusOrder.indexOf(b.key)
      if (aIndex !== -1 && bIndex !== -1) {
        return aIndex - bIndex
      }
    }
    return a.name.localeCompare(b.name)
  })
})

const toggleGroup = (groupKey) => {
  const index = expandedGroups.value.indexOf(groupKey)
  if (index > -1) {
    expandedGroups.value.splice(index, 1)
  } else {
    expandedGroups.value.push(groupKey)
  }
}

const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  }) : ''
}

onMounted(() => {
  // Expand all groups by default
  expandedGroups.value = groupedItems.value.map(group => group.key)
})
</script>

<style lang="scss" scoped>
.roadmap-list-source-badge {
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
  color: var(--rm-review);
  background: color-mix(in srgb, var(--rm-review) 14%, transparent);
}

.roadmap-list {
  background: var(--rm-surface);
  border-radius: var(--rm-radius);
  padding: 20px;
}

.roadmap-list-header {
  margin-bottom: 20px;
}

.roadmap-list-controls {
  display: flex;
  gap: 15px;

  select {
    flex: 1;
    max-width: 200px;
  }
}

.roadmap-list-groups {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.roadmap-list-group {
  border: 1px solid var(--rm-border);
  border-radius: var(--rm-radius);
  overflow: hidden;
}

.roadmap-list-group-header {
  background: var(--rm-surface-alt);
  padding: 15px 20px;
  cursor: pointer;
  user-select: none;

  &:hover {
    background: var(--rm-border);
  }

  h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
  }
}

.roadmap-list-toggle {
  font-size: 12px;
  color: var(--rm-muted);
}

.roadmap-list-count {
  font-size: 14px;
  color: var(--rm-muted);
  font-weight: 400;
}

.roadmap-list-items {
  padding: 10px;
}

.roadmap-list-item {
  border: 1px solid var(--rm-border);
  border-radius: var(--rm-radius);
  padding: 15px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    border-color: var(--roadmap-accent);
    box-shadow: 0 2px 8px var(--rm-shadow);
    transform: translateY(-1px);
  }
}

.roadmap-list-item-main {
  margin-bottom: 12px;
}

.roadmap-list-item-title {
  margin: 0 0 8px;
  font-size: 16px;
  font-weight: 600;
  color: var(--rm-heading);
}

.roadmap-list-item-excerpt {
  margin: 0;
  color: var(--rm-muted);
  font-size: 14px;
  line-height: 1.5;
}

.roadmap-list-item-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.roadmap-list-item-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.roadmap-list-item-stats {
  display: flex;
  gap: 15px;
  font-size: 13px;
  color: var(--rm-muted);
}

.roadmap-list-item-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid var(--rm-border);

  button {
    font-size: 13px;
    padding: 6px 12px;
  }
}

.roadmap-list-item-progress {
  margin-top: 12px;
  display: flex;
  align-items: center;
  gap: 10px;

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

.roadmap-list-stat {
  display: flex;
  align-items: center;
  gap: 4px;

  i {
    font-style: normal;
  }
}

// Transitions
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.list-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

// Dark mode is handled purely by token swap (see main.scss .roadmap-app dark block).

// Responsive
@media (max-width: 768px) {
  .roadmap-list {
    padding: 15px;
  }

  .roadmap-list-controls {
    flex-direction: column;

    select {
      max-width: 100%;
    }
  }

  .roadmap-list-item-meta {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
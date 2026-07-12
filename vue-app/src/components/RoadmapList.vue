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
        <select v-model="sortBy" class="roadmap-form-control">
          <option value="created">{{ i18n.sort_by_created || 'Sort by Created Date' }}</option>
          <option value="votes">{{ i18n.sort_by_votes || 'Sort by Votes' }}</option>
          <option value="priority">{{ i18n.sort_by_priority || 'Sort by Priority' }}</option>
          <option value="title">{{ i18n.sort_by_title || 'Sort by Title' }}</option>
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
                <h4 class="roadmap-list-item-title">{{ item.title }}</h4>
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
                    :class="`roadmap-badge-${getPriorityClass(item.priority)}`"
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
                  <div class="roadmap-list-stat">
                    <i>👍</i> {{ item.votes || 0 }}
                  </div>
                  <div class="roadmap-list-stat">
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

const groupBy = ref('status')
const sortBy = ref('created')
const expandedGroups = ref([])

const groupedItems = computed(() => {
  if (groupBy.value === 'none') {
    return [{
      key: 'all',
      name: i18n.all_items || 'All Items',
      items: sortedItems.value
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
    group.items.sort(getSortFunction())
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

const toggleGroup = (groupKey) => {
  const index = expandedGroups.value.indexOf(groupKey)
  if (index > -1) {
    expandedGroups.value.splice(index, 1)
  } else {
    expandedGroups.value.push(groupKey)
  }
}

const getPriorityClass = (priority) => {
  switch (priority) {
    case 'critical': return 'danger'
    case 'high': return 'warning'
    case 'medium': return 'primary'
    case 'low': return 'success'
    default: return 'secondary'
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
.roadmap-list {
  background: var(--rm-surface);
  border-radius: 8px;
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
  border-radius: 6px;
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
  border-radius: 6px;
  padding: 15px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    border-color: var(--roadmap-accent);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
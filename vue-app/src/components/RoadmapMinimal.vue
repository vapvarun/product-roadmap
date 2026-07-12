<template>
  <div class="roadmap-template-minimal roadmap-minimal">
    <div class="roadmap-minimal-filters">
      <div 
        v-for="filter in filters" 
        :key="filter.key"
        class="roadmap-minimal-filter"
        :class="{ active: activeFilter === filter.key }"
        @click="activeFilter = filter.key"
      >
        {{ filter.name }}
        <span class="roadmap-minimal-count">{{ filter.count }}</span>
      </div>
    </div>

    <div class="roadmap-minimal-timeline">
      <div 
        v-for="group in filteredGroups" 
        :key="group.key"
        class="roadmap-minimal-group"
      >
        <h2 class="roadmap-minimal-group-title">
          {{ group.name }}
          <span class="roadmap-minimal-group-count">({{ group.items.length }})</span>
        </h2>
        
        <div class="roadmap-minimal-items">
          <div 
            v-for="item in group.items" 
            :key="item.id"
            class="roadmap-minimal-item"
            @click="$emit('item-click', item)"
          >
            <div class="roadmap-minimal-item-indicator">
              <div 
                class="roadmap-minimal-dot"
                :class="`roadmap-status-${getStatusSlug(item)}`"
              ></div>
            </div>
            
            <div class="roadmap-minimal-item-content">
              <h3 class="roadmap-minimal-item-title">
                {{ item.title }}
                <span 
                  v-if="item.priority" 
                  class="roadmap-minimal-priority"
                  :title="`${i18n.priority || 'Priority'}: ${item.priority}`"
                >
                  {{ getPriorityIcon(item.priority) }}
                </span>
              </h3>
              
              <p class="roadmap-minimal-item-description">
                {{ item.excerpt || item.content.substring(0, 120) + '...' }}
              </p>
              
              <div class="roadmap-minimal-item-meta">
                <div class="roadmap-minimal-votes">
                  <span class="roadmap-minimal-vote-icon">👍</span>
                  {{ item.votes || 0 }}
                </div>
                <div class="roadmap-minimal-comments">
                  <span class="roadmap-minimal-comment-icon">💬</span>
                  {{ item.comment_count || 0 }}
                </div>
                <div 
                  v-if="item.categories && item.categories.length" 
                  class="roadmap-minimal-category"
                >
                  {{ item.categories[0].name }}
                </div>
                <div 
                  v-if="item.target_date" 
                  class="roadmap-minimal-date"
                >
                  📅 {{ formatDate(item.target_date) }}
                </div>
              </div>
              <div v-if="mode === 'manage' && canEdit" class="roadmap-minimal-item-actions">
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
      </div>
    </div>

    <div class="roadmap-minimal-footer">
      <div class="roadmap-minimal-toggle">
        <input
          type="checkbox"
          id="show-completed"
          v-model="showCompleted"
        />
        <label for="show-completed" contenteditable="false">{{ i18n.show_completed_items || 'Show completed items' }}</label>
      </div>
      
      <div class="roadmap-minimal-stats">
        <span>{{ totalItems }} {{ i18n.total_items || 'total items' }}</span>
        <span>{{ inProgressItems }} {{ i18n.in_progress || 'in progress' }}</span>
        <span v-if="showCompleted">{{ completedItems }} {{ i18n.completed || 'completed' }}</span>
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

const activeFilter = ref('all')
const showCompleted = ref(true)

const filters = computed(() => {
  const all = { key: 'all', name: i18n.all || 'All', count: props.items.length }
  const byStatus = {}

  props.items.forEach(item => {
    const statusSlug = getStatusSlug(item)
    if (!byStatus[statusSlug]) {
      byStatus[statusSlug] = {
        key: statusSlug,
        name: getStatusName(item) || statusSlug.replace('-', ' '),
        count: 0
      }
    }
    byStatus[statusSlug].count++
  })

  // Filter out completed statuses if showCompleted is false
  let statusFilters = Object.values(byStatus)
  if (!showCompleted.value) {
    statusFilters = statusFilters.filter(filter =>
      !['completed', 'released', 'cancelled'].includes(filter.key)
    )
  }

  return [all, ...statusFilters]
})

const filteredItems = computed(() => {
  let items = props.items

  // Filter by status
  if (activeFilter.value !== 'all') {
    items = items.filter(item => getStatusSlug(item) === activeFilter.value)
  }

  // Filter completed items
  if (!showCompleted.value) {
    items = items.filter(item => {
      const statusSlug = getStatusSlug(item)
      return !['completed', 'released', 'cancelled'].includes(statusSlug)
    })
  }

  return items
})

const filteredGroups = computed(() => {
  const now = new Date()
  const currentQuarter = Math.floor(now.getMonth() / 3) + 1
  const currentYear = now.getFullYear()

  const groups = {
    current: {
      key: 'current',
      name: `Q${currentQuarter} ${currentYear} - ${i18n.now || 'Now'}`,
      items: []
    },
    next: {
      key: 'next',
      name: `Q${currentQuarter === 4 ? 1 : currentQuarter + 1} ${currentQuarter === 4 ? currentYear + 1 : currentYear} - ${i18n.next || 'Next'}`,
      items: []
    },
    future: {
      key: 'future',
      name: i18n.future_later || 'Future - Later',
      items: []
    }
  }

  filteredItems.value.forEach(item => {
    const statusSlug = getStatusSlug(item)
    
    if (['in-progress', 'development', 'testing'].includes(statusSlug)) {
      groups.current.items.push(item)
    } else if (['planned', 'approved', 'scheduled'].includes(statusSlug)) {
      groups.next.items.push(item)
    } else {
      groups.future.items.push(item)
    }
  })

  return Object.values(groups).filter(group => group.items.length > 0)
})

const totalItems = computed(() => props.items.length)
const inProgressItems = computed(() => {
  return props.items.filter(item => {
    const statusSlug = getStatusSlug(item)
    return ['in-progress', 'development', 'testing'].includes(statusSlug)
  }).length
})
const completedItems = computed(() => {
  return props.items.filter(item => {
    const statusSlug = getStatusSlug(item)
    return ['completed', 'released'].includes(statusSlug)
  }).length
})

const getStatusSlug = (item) => {
  return item.status?.[0]?.slug || 'unknown'
}

const getStatusName = (item) => {
  return item.status?.[0]?.name || (i18n.unknown || 'Unknown')
}

const getPriorityIcon = (priority) => {
  switch (priority) {
    case 'critical': return '🔴'
    case 'high': return '🟠'
    case 'medium': return '🟡'
    case 'low': return '🟢'
    default: return ''
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
.roadmap-minimal {
  max-width: 800px;
  margin: 0 auto;
}

.roadmap-minimal-filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid var(--rm-border);
}

.roadmap-minimal-filter {
  background: var(--rm-surface-alt);
  border: 1px solid var(--rm-border);
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  color: var(--rm-heading);

  &:hover {
    background: var(--rm-border);
  }

  &.active {
    background: var(--roadmap-accent);
    color: #fff;
    border-color: var(--roadmap-accent);
  }
}

.roadmap-minimal-count {
  font-size: 12px;
  opacity: 0.7;
  margin-left: 4px;
}

.roadmap-minimal-timeline,
.roadmap-minimal-group {
  margin-bottom: 40px;
}

.roadmap-minimal-group-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--rm-heading);
  margin: 0 0 20px;
  padding-left: 30px;
}

.roadmap-minimal-group-count {
  font-size: 16px;
  font-weight: 400;
  color: var(--rm-muted);
}

.roadmap-minimal-items {
  position: relative;

  &:before {
    content: '';
    position: absolute;
    left: 12px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--rm-border);
  }
}

.roadmap-minimal-item {
  display: flex;
  margin-bottom: 20px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover .roadmap-minimal-item-content {
    background: var(--rm-surface-alt);
  }

  &:hover .roadmap-minimal-item-title {
    color: var(--roadmap-accent);
  }
}

.roadmap-minimal-item-indicator {
  width: 30px;
  flex-shrink: 0;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 4px;
}

.roadmap-minimal-item-content {
  flex: 1;
  background: var(--rm-surface);
  border: 1px solid var(--rm-border);
  border-radius: 6px;
  padding: 15px;
  transition: all 0.2s;
}

.roadmap-minimal-item-title {
  margin: 0 0 8px;
  font-size: 16px;
  font-weight: 500;
  color: var(--rm-heading);
  display: flex;
  align-items: center;
  gap: 8px;
}

.roadmap-minimal-item-description {
  margin: 0 0 10px;
  color: var(--rm-muted);
  font-size: 14px;
  line-height: 1.5;
}

.roadmap-minimal-item-meta {
  display: flex;
  gap: 15px;
  align-items: center;
  flex-wrap: wrap;
  font-size: 13px;
  color: var(--rm-muted);
}

.roadmap-minimal-item-actions {
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

.roadmap-minimal-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--rm-muted);
  border: 2px solid var(--rm-surface);
  box-shadow: 0 0 0 2px var(--rm-border);
  position: relative;
  z-index: 1;

  &.roadmap-status-planned {
    background: var(--rm-planned);
  }

  &.roadmap-status-in-progress {
    background: var(--rm-progress);
  }

  &.roadmap-status-completed {
    background: var(--rm-done);
  }

  &.roadmap-status-cancelled {
    background: var(--rm-cancelled);
  }

  &.roadmap-status-under-review {
    background: var(--rm-review);
  }
}

.roadmap-minimal-votes,
.roadmap-minimal-comments,
.roadmap-minimal-date {
  display: flex;
  align-items: center;
  gap: 4px;
}

.roadmap-minimal-vote-icon,
.roadmap-minimal-comment-icon {
  font-size: 12px;
}

.roadmap-minimal-category {
  font-size: 12px;
  padding: 2px 8px;
  background: var(--rm-surface-alt);
  border-radius: 10px;
}

.roadmap-minimal-priority {
  font-size: 14px;
}

.roadmap-minimal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 20px;
  border-top: 2px solid var(--rm-border);
}

.roadmap-minimal-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
  color: var(--rm-muted);
  user-select: none;

  input {
    cursor: pointer;
  }

  label {
    user-select: none;
  }
}

.roadmap-minimal-stats {
  display: flex;
  gap: 20px;
  font-size: 14px;
  color: var(--rm-muted);
}

// Responsive
@media (max-width: 768px) {
  .roadmap-minimal-filters {
    gap: 8px;
  }

  .roadmap-minimal-filter {
    font-size: 13px;
    padding: 6px 12px;
  }

  .roadmap-minimal-group-title {
    font-size: 18px;
    padding-left: 20px;
  }

  .roadmap-minimal-item-content {
    padding: 12px;
  }

  .roadmap-minimal-item-title {
    font-size: 15px;
  }

  .roadmap-minimal-item-meta {
    font-size: 12px;
    gap: 10px;
  }

  .roadmap-minimal-footer {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }
}
</style>
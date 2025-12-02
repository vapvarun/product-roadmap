<template>
  <div class="roadmap-template-ideabox roadmap-ideabox">
    <!-- Sort Controls -->
    <div class="roadmap-ideabox-header">
      <div class="roadmap-ideabox-tabs">
        <button
          v-for="tab in sortTabs"
          :key="tab.key"
          class="roadmap-ideabox-tab"
          :class="{ active: sortBy === tab.key }"
          @click="sortBy = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>
      <div class="roadmap-ideabox-count">
        {{ filteredItems.length }} {{ filteredItems.length === 1 ? 'idea' : 'ideas' }}
      </div>
    </div>

    <!-- Ideas List -->
    <div class="roadmap-ideabox-list">
      <div
        v-for="item in sortedItems"
        :key="item.id"
        class="roadmap-ideabox-item"
        :class="{ 'roadmap-ideabox-item-voted': item.user_voted }"
      >
        <!-- Vote Section (Left) -->
        <div class="roadmap-ideabox-vote">
          <button
            class="roadmap-ideabox-vote-btn"
            :class="{ 'voted': item.user_voted }"
            @click.stop="handleVote(item)"
            :disabled="voting === item.id"
            :title="item.user_voted ? 'Remove your vote' : 'Vote for this idea'"
          >
            <svg viewBox="0 0 24 24" class="roadmap-ideabox-vote-icon">
              <path d="M12 4l-8 8h5v8h6v-8h5z" fill="currentColor"/>
            </svg>
          </button>
          <span class="roadmap-ideabox-vote-count">{{ item.votes || 0 }}</span>
          <span class="roadmap-ideabox-vote-label">votes</span>
        </div>

        <!-- Content Section (Middle) -->
        <div class="roadmap-ideabox-content" @click="$emit('item-click', item)">
          <h3 class="roadmap-ideabox-title">{{ item.title }}</h3>
          <p class="roadmap-ideabox-excerpt">
            {{ item.excerpt || truncateContent(item.content, 150) }}
          </p>

          <!-- Tags -->
          <div class="roadmap-ideabox-tags">
            <span
              v-if="item.status && item.status.length"
              class="roadmap-ideabox-tag roadmap-ideabox-tag-status"
              :style="getStatusStyle(item.status[0])"
            >
              {{ item.status[0].name }}
            </span>
            <span
              v-for="category in item.categories.slice(0, 3)"
              :key="category.id"
              class="roadmap-ideabox-tag roadmap-ideabox-tag-category"
            >
              {{ category.name }}
            </span>
            <span
              v-if="item.priority"
              class="roadmap-ideabox-tag"
              :class="`roadmap-ideabox-tag-${getPriorityClass(item.priority)}`"
            >
              {{ item.priority }}
            </span>
          </div>

          <!-- Meta Info -->
          <div class="roadmap-ideabox-meta">
            <span class="roadmap-ideabox-meta-item">
              <svg viewBox="0 0 20 20" width="14" height="14">
                <path fill="currentColor" d="M10 2c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zM10 16.5c-3.59 0-6.5-2.91-6.5-6.5s2.91-6.5 6.5-6.5 6.5 2.91 6.5 6.5-2.91 6.5-6.5 6.5z"/>
                <path fill="currentColor" d="M10.25 5h-1.5v5.5l4.5 2.75.75-1.25-3.75-2.25z"/>
              </svg>
              {{ formatDate(item.date) }}
            </span>
            <span class="roadmap-ideabox-meta-item">
              <svg viewBox="0 0 20 20" width="14" height="14">
                <path fill="currentColor" d="M10 2C5.582 2 2 5.582 2 10s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zm0 14c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/>
                <circle fill="currentColor" cx="10" cy="10" r="2"/>
              </svg>
              {{ item.author }}
            </span>
            <span v-if="item.comment_count > 0" class="roadmap-ideabox-meta-item roadmap-ideabox-meta-comments">
              <svg viewBox="0 0 20 20" width="14" height="14">
                <path fill="currentColor" d="M10 1C4.477 1 0 4.806 0 9.5c0 2.327 1.133 4.415 2.953 5.862L1.5 19l4.667-2.333C7.146 17.055 8.555 17.25 10 17.25c5.523 0 10-3.806 10-8.5S15.523 1 10 1z"/>
              </svg>
              {{ item.comment_count }}
            </span>
          </div>
        </div>

        <!-- Actions Section (Right) -->
        <div v-if="mode === 'manage' && canEdit" class="roadmap-ideabox-actions">
          <button
            @click.stop="$emit('edit-item', item)"
            class="roadmap-ideabox-action-btn"
            title="Edit"
          >
            <svg viewBox="0 0 20 20" width="16" height="16">
              <path fill="currentColor" d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828zM15 4l1 1-8.5 8.5H6v-1.5L14.5 3.5 15 4z"/>
            </svg>
          </button>
          <button
            @click.stop="$emit('delete-item', item.id)"
            class="roadmap-ideabox-action-btn roadmap-ideabox-action-danger"
            title="Delete"
          >
            <svg viewBox="0 0 20 20" width="16" height="16">
              <path fill="currentColor" d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredItems.length === 0" class="roadmap-ideabox-empty">
        <svg viewBox="0 0 24 24" width="64" height="64" class="roadmap-ideabox-empty-icon">
          <path fill="currentColor" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
        <h3>No ideas yet</h3>
        <p>Be the first to suggest a feature!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue'

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

const emit = defineEmits(['item-click', 'vote', 'edit-item', 'delete-item', 'update-item'])

// Get config from parent
const config = inject('$roadmapConfig', { settings: {} })

// State
const sortBy = ref('votes')
const voting = ref(null)

// Sort tabs
const sortTabs = [
  { key: 'votes', label: 'Top Voted' },
  { key: 'recent', label: 'Newest' },
  { key: 'active', label: 'Active' }
]

// Status colors mapping
const statusColors = {
  'planned': { bg: '#e8f4fd', text: '#1d4ed8' },
  'in-progress': { bg: '#fef3c7', text: '#b45309' },
  'completed': { bg: '#d1fae5', text: '#059669' },
  'cancelled': { bg: '#fee2e2', text: '#dc2626' },
  'under-review': { bg: '#f3e8ff', text: '#7c3aed' }
}

// Computed
const filteredItems = computed(() => {
  return props.items || []
})

const sortedItems = computed(() => {
  const items = [...filteredItems.value]

  switch (sortBy.value) {
    case 'votes':
      return items.sort((a, b) => (b.votes || 0) - (a.votes || 0))
    case 'recent':
      return items.sort((a, b) => new Date(b.date) - new Date(a.date))
    case 'active':
      // Sort by most recent comment activity or vote
      return items.sort((a, b) => {
        const aActivity = Math.max(
          new Date(a.modified || a.date).getTime(),
          (a.comment_count || 0)
        )
        const bActivity = Math.max(
          new Date(b.modified || b.date).getTime(),
          (b.comment_count || 0)
        )
        return bActivity - aActivity
      })
    default:
      return items
  }
})

// Methods
const handleVote = async (item) => {
  if (voting.value === item.id) return

  voting.value = item.id

  try {
    // Toggle vote: if already voted, remove vote (0), otherwise add vote (1)
    const voteValue = item.user_voted ? 0 : 1
    emit('vote', item.id, voteValue)
  } finally {
    setTimeout(() => {
      voting.value = null
    }, 500)
  }
}

const getStatusStyle = (status) => {
  const colors = statusColors[status.slug] || { bg: '#f3f4f6', text: '#374151' }
  return {
    backgroundColor: colors.bg,
    color: colors.text
  }
}

const getPriorityClass = (priority) => {
  switch (priority) {
    case 'critical': return 'critical'
    case 'high': return 'high'
    case 'medium': return 'medium'
    case 'low': return 'low'
    default: return 'default'
  }
}

const truncateContent = (content, length) => {
  if (!content) return ''
  const stripped = content.replace(/<[^>]*>/g, '')
  if (stripped.length <= length) return stripped
  return stripped.substring(0, length) + '...'
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diff = now - d
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days === 0) return 'Today'
  if (days === 1) return 'Yesterday'
  if (days < 7) return `${days} days ago`
  if (days < 30) return `${Math.floor(days / 7)} weeks ago`
  if (days < 365) return `${Math.floor(days / 30)} months ago`
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style lang="scss" scoped>
.roadmap-ideabox {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.roadmap-ideabox-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.roadmap-ideabox-tabs {
  display: flex;
  gap: 4px;
}

.roadmap-ideabox-tab {
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #6b7280;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;

  &:hover {
    background: #e5e7eb;
    color: #374151;
  }

  &.active {
    background: #2271b1;
    color: #fff;
  }
}

.roadmap-ideabox-count {
  font-size: 14px;
  color: #6b7280;
}

.roadmap-ideabox-list {
  display: flex;
  flex-direction: column;
}

.roadmap-ideabox-item {
  display: flex;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  transition: background-color 0.2s ease;

  &:last-child {
    border-bottom: none;
  }

  &:hover {
    background: #f9fafb;
  }

  &.roadmap-ideabox-item-voted {
    .roadmap-ideabox-vote-btn {
      background: #2271b1;
      color: #fff;
      border-color: #2271b1;
    }
  }
}

.roadmap-ideabox-vote {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 70px;
  padding-right: 20px;
  text-align: center;
}

.roadmap-ideabox-vote-btn {
  width: 48px;
  height: 48px;
  border: 2px solid #d1d5db;
  background: #fff;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: all 0.2s ease;

  &:hover:not(:disabled):not(.voted) {
    border-color: #10b981;
    color: #10b981;
    background: #f0fdf4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  &.voted {
    background: #10b981;
    border-color: #10b981;
    color: #fff;

    &:hover:not(:disabled) {
      background: #f97316;
      border-color: #f97316;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
    }
  }
}

.roadmap-ideabox-vote-icon {
  width: 24px;
  height: 24px;
}

.roadmap-ideabox-vote-count {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin-top: 8px;
  line-height: 1;
}

.roadmap-ideabox-vote-label {
  font-size: 11px;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 2px;
}

.roadmap-ideabox-content {
  flex: 1;
  cursor: pointer;
  min-width: 0;
}

.roadmap-ideabox-title {
  margin: 0 0 8px;
  font-size: 17px;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.4;
  transition: color 0.2s ease;

  &:hover {
    color: #2271b1;
  }
}

.roadmap-ideabox-excerpt {
  margin: 0 0 12px;
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
}

.roadmap-ideabox-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.roadmap-ideabox-tag {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 500;
  border-radius: 20px;
  background: #f3f4f6;
  color: #374151;
}

.roadmap-ideabox-tag-status {
  font-weight: 600;
}

.roadmap-ideabox-tag-category {
  background: #e5e7eb;
  color: #4b5563;
}

.roadmap-ideabox-tag-critical {
  background: #fee2e2;
  color: #dc2626;
}

.roadmap-ideabox-tag-high {
  background: #fef3c7;
  color: #b45309;
}

.roadmap-ideabox-tag-medium {
  background: #e0f2fe;
  color: #0369a1;
}

.roadmap-ideabox-tag-low {
  background: #d1fae5;
  color: #059669;
}

.roadmap-ideabox-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  font-size: 13px;
  color: #9ca3af;
}

.roadmap-ideabox-meta-item {
  display: flex;
  align-items: center;
  gap: 5px;

  svg {
    opacity: 0.7;
  }
}

.roadmap-ideabox-meta-comments {
  color: #6b7280;
  font-weight: 500;
}

.roadmap-ideabox-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-left: 16px;
  border-left: 1px solid #e5e7eb;
  margin-left: 16px;
}

.roadmap-ideabox-action-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #d1d5db;
  background: #fff;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: all 0.2s ease;

  &:hover {
    background: #f3f4f6;
    color: #374151;
  }

  &.roadmap-ideabox-action-danger:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fecaca;
  }
}

.roadmap-ideabox-empty {
  text-align: center;
  padding: 60px 20px;
  color: #9ca3af;

  h3 {
    margin: 16px 0 8px;
    color: #6b7280;
    font-size: 18px;
  }

  p {
    margin: 0;
    font-size: 14px;
  }
}

.roadmap-ideabox-empty-icon {
  opacity: 0.4;
}

// Dark mode
.roadmap-dark .roadmap-ideabox {
  background: #1f2937;
}

.roadmap-dark .roadmap-ideabox-header {
  background: #111827;
  border-bottom-color: #374151;
}

.roadmap-dark .roadmap-ideabox-tab {
  color: #9ca3af;

  &:hover {
    background: #374151;
    color: #e5e7eb;
  }

  &.active {
    background: #2271b1;
    color: #fff;
  }
}

.roadmap-dark .roadmap-ideabox-item {
  border-bottom-color: #374151;

  &:hover {
    background: #111827;
  }
}

.roadmap-dark .roadmap-ideabox-vote-btn {
  background: #374151;
  border-color: #4b5563;
  color: #d1d5db;

  &:hover:not(:disabled):not(.voted) {
    border-color: #10b981;
    color: #10b981;
    background: #064e3b;
  }

  &.voted {
    background: #10b981;
    border-color: #10b981;
    color: #fff;

    &:hover:not(:disabled) {
      background: #f97316;
      border-color: #f97316;
    }
  }
}

.roadmap-dark .roadmap-ideabox-vote-count {
  color: #e5e7eb;
}

.roadmap-dark .roadmap-ideabox-title {
  color: #f3f4f6;

  &:hover {
    color: #60a5fa;
  }
}

.roadmap-dark .roadmap-ideabox-excerpt {
  color: #9ca3af;
}

.roadmap-dark .roadmap-ideabox-tag {
  background: #374151;
  color: #d1d5db;
}

.roadmap-dark .roadmap-ideabox-actions {
  border-left-color: #374151;
}

.roadmap-dark .roadmap-ideabox-action-btn {
  background: #374151;
  border-color: #4b5563;
  color: #d1d5db;

  &:hover {
    background: #4b5563;
    color: #f3f4f6;
  }
}

// Responsive
@media (max-width: 768px) {
  .roadmap-ideabox-header {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }

  .roadmap-ideabox-item {
    flex-wrap: wrap;
    gap: 16px;
  }

  .roadmap-ideabox-vote {
    flex-direction: row;
    min-width: auto;
    padding-right: 0;
    gap: 8px;
    order: -1;
    width: 100%;
    justify-content: flex-start;
  }

  .roadmap-ideabox-vote-btn {
    width: 40px;
    height: 40px;
  }

  .roadmap-ideabox-vote-count {
    font-size: 16px;
    margin-top: 0;
  }

  .roadmap-ideabox-vote-label {
    margin-top: 0;
  }

  .roadmap-ideabox-content {
    width: 100%;
  }

  .roadmap-ideabox-actions {
    flex-direction: row;
    width: 100%;
    border-left: none;
    border-top: 1px solid #e5e7eb;
    padding-left: 0;
    padding-top: 12px;
    margin-left: 0;
    margin-top: 0;
  }

  .roadmap-ideabox-meta {
    gap: 12px;
  }

  .roadmap-ideabox-tabs {
    width: 100%;
    overflow-x: auto;
  }

  .roadmap-ideabox-tab {
    padding: 6px 12px;
    font-size: 13px;
    white-space: nowrap;
  }
}
</style>

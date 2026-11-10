<template>
  <div class="roadmap-template-ideabox roadmap-ideabox">
    <!-- Sort Controls -->
    <div class="roadmap-ideabox-header">
      <div class="roadmap-ideabox-tabs">
        <button
          v-for="tab in sortTabs"
          :key="tab.key"
          class="roadmap-ideabox-tab"
          :class="{ active: sort === tab.key }"
          :aria-pressed="sort === tab.key ? 'true' : 'false'"
          @click="$emit('update:sort', tab.key)"
        >
          {{ tab.label }}
        </button>
      </div>
      <div class="roadmap-ideabox-count">
        {{ filteredItems.length }} {{ filteredItems.length === 1 ? (i18n.idea || 'idea') : (i18n.ideas || 'ideas') }}
      </div>
    </div>

    <!-- Ideas List -->
    <div class="roadmap-ideabox-list">
      <div
        v-for="item in filteredItems"
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
            :title="item.user_voted ? (i18n.remove_vote || 'Remove your vote') : (i18n.vote_for_this || 'Vote for this idea')"
          >
            <svg viewBox="0 0 24 24" class="roadmap-ideabox-vote-icon">
              <path d="M12 4l-8 8h5v8h6v-8h5z" fill="currentColor"/>
            </svg>
          </button>
          <span class="roadmap-ideabox-vote-count">{{ item.votes || 0 }}</span>
          <span class="roadmap-ideabox-vote-label">{{ i18n.votes || 'votes' }}</span>
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
              :class="`roadmap-status-${item.status[0].slug}`"
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
              class="roadmap-ideabox-tag roadmap-ideabox-tag-priority"
              :class="`roadmap-priority-${item.priority}`"
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
            :title="i18n.edit || 'Edit'"
          >
            <svg viewBox="0 0 20 20" width="16" height="16">
              <path fill="currentColor" d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828zM15 4l1 1-8.5 8.5H6v-1.5L14.5 3.5 15 4z"/>
            </svg>
          </button>
          <button
            @click.stop="$emit('delete-item', item.id)"
            class="roadmap-ideabox-action-btn roadmap-ideabox-action-danger"
            :title="i18n.delete || 'Delete'"
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
        <h3>{{ i18n.no_ideas_yet || 'No ideas yet' }}</h3>
        <p>{{ i18n.be_first_suggest || 'Be the first to suggest a feature!' }}</p>
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
  },
  // The board's sort (owner setting, then visitor choice). The tabs set it;
  // the server applies it across all items.
  sort: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['item-click', 'vote', 'edit-item', 'delete-item', 'update-item', 'update:sort'])

// Get config from parent
const config = inject('$roadmapConfig', { settings: {} })
const i18n = config?.i18n || {}

// State
const voting = ref(null)

// Sort tabs
const sortTabs = [
  { key: 'votes', label: i18n.top_voted || 'Top Voted' },
  { key: 'recent', label: i18n.newest || 'Newest' },
  { key: 'comments', label: i18n.most_discussed || 'Most Discussed' }
]

// Computed
const filteredItems = computed(() => {
  return props.items || []
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

  if (days === 0) return i18n.today || 'Today'
  if (days === 1) return i18n.yesterday || 'Yesterday'
  if (days < 7) return `${days} ${i18n.days_ago || 'days ago'}`
  if (days < 30) return `${Math.floor(days / 7)} ${i18n.weeks_ago || 'weeks ago'}`
  if (days < 365) return `${Math.floor(days / 30)} ${i18n.months_ago || 'months ago'}`
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style lang="scss" scoped>
.roadmap-ideabox {
  background: var(--rm-surface);
  border-radius: var(--rm-radius);
  overflow: hidden;
}

.roadmap-ideabox-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--rm-border);
  background: var(--rm-surface-alt);
}

.roadmap-ideabox-tabs {
  display: flex;
  gap: 4px;
}

.roadmap-ideabox-tab {
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: var(--rm-muted);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border-radius: var(--rm-radius-button);
  transition: all 0.2s ease;

  &:hover {
    background: var(--rm-border);
    color: var(--rm-heading);
  }

  &.active {
    background: var(--roadmap-accent);
    color: var(--roadmap-accent-contrast);
  }
}

.roadmap-ideabox-count {
  font-size: 14px;
  color: var(--rm-muted);
}

.roadmap-ideabox-list {
  display: flex;
  flex-direction: column;
}

.roadmap-ideabox-item {
  display: flex;
  padding: 20px;
  border-bottom: 1px solid var(--rm-border);
  transition: background-color 0.2s ease;

  &:last-child {
    border-bottom: none;
  }

  &:hover {
    background: var(--rm-surface-alt);
  }

  &.roadmap-ideabox-item-voted {
    .roadmap-ideabox-vote-btn {
      background: var(--roadmap-accent);
      color: var(--roadmap-accent-contrast);
      border-color: var(--roadmap-accent);
      padding: 0;
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
  padding: 0;
  border: 2px solid var(--rm-control-border);
  background: var(--rm-surface);
  border-radius: var(--rm-radius-button);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--rm-muted);
  transition: all 0.2s ease;

  &:hover:not(:disabled):not(.voted) {
    border-color: var(--rm-done);
    color: var(--rm-done);
    background: var(--rm-done-tint);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--rm-shadow);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  &.voted {
    background: var(--rm-done);
    border-color: var(--rm-done);
    color: var(--rm-done-tint);

    &:hover:not(:disabled) {
      background: var(--rm-progress);
      border-color: var(--rm-progress);
      color: var(--rm-progress-tint);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px var(--rm-shadow);
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
  color: var(--rm-heading);
  margin-top: 8px;
  line-height: 1;
}

.roadmap-ideabox-vote-label {
  font-size: 11px;
  color: var(--rm-muted);
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
  color: var(--rm-heading);
  line-height: 1.4;
  transition: color 0.2s ease;

  &:hover {
    color: var(--roadmap-accent-text);
  }
}

.roadmap-ideabox-excerpt {
  margin: 0 0 12px;
  font-size: 14px;
  color: var(--rm-muted);
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
  background: var(--rm-surface-alt);
  color: var(--rm-text);
}

.roadmap-ideabox-tag-status {
  font-weight: 600;
  background: var(--rm-status-tint);
  color: var(--rm-status);
}

.roadmap-ideabox-tag-priority {
  background: var(--rm-priority-tint, var(--rm-surface-alt));
  color: var(--rm-priority-color, var(--rm-text));
}

.roadmap-ideabox-tag-category {
  background: var(--rm-border);
  color: var(--rm-text);
}

.roadmap-ideabox-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  font-size: 13px;
  color: var(--rm-muted);
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
  color: var(--rm-muted);
  font-weight: 500;
}

.roadmap-ideabox-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-left: 16px;
  border-left: 1px solid var(--rm-border);
  margin-left: 16px;
}

.roadmap-ideabox-action-btn {
  width: 36px;
  height: 36px;
  border: 1px solid var(--rm-control-border);
  background: var(--rm-surface);
  border-radius: var(--rm-radius-button);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--rm-muted);
  transition: all 0.2s ease;

  &:hover {
    background: var(--rm-surface-alt);
    color: var(--rm-heading);
  }

  &.roadmap-ideabox-action-danger:hover {
    background: var(--rm-cancelled-tint);
    color: var(--rm-cancelled);
    border-color: var(--rm-cancelled);
  }
}

.roadmap-ideabox-empty {
  text-align: center;
  padding: 60px 20px;
  color: var(--rm-muted);

  h3 {
    margin: 16px 0 8px;
    color: var(--rm-muted);
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
    border-top: 1px solid var(--rm-border);
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

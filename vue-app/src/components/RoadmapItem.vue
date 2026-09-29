<template>
  <div 
    class="roadmap-item"
    :class="{ 'roadmap-item-draggable': draggable, 'roadmap-item-clickable': true }"
    :draggable="draggable"
    @dragstart="$emit('dragstart', $event)"
    @click="handleItemClick"
  >
    <div class="roadmap-item-header">
      <h4 class="roadmap-item-title">
        {{ item.title }}
        <span
          v-if="item.is_suggestion"
          class="roadmap-item-source-badge"
          :title="i18n.user_suggested_title || 'Suggested by a user'"
        >
          {{ i18n.suggested || 'Suggested' }}
        </span>
      </h4>
      <!-- aria-live: the delete button's label change is announced. -->
      <div v-if="canEdit" class="roadmap-item-actions" aria-live="polite">
        <button
          @click.stop="$emit('edit')"
          class="roadmap-btn roadmap-btn-sm roadmap-btn-icon"
          :title="i18n.edit || 'Edit'"
          :aria-label="i18n.edit || 'Edit'"
        >
          <span aria-hidden="true">✏️</span>
        </button>
        <!-- Two-step delete: the first click arms it, a second within 5 seconds deletes. -->
        <button
          v-if="mode === 'manage'"
          @click.stop="onDelete"
          class="roadmap-btn roadmap-btn-sm roadmap-btn-icon roadmap-btn-danger"
          :title="confirmingDelete ? i18n.confirm_delete : (i18n.delete || 'Delete')"
          :aria-label="confirmingDelete ? i18n.confirm_delete_label : (i18n.delete || 'Delete')"
        >
          <span v-if="confirmingDelete">{{ i18n.confirm_delete_label || 'Confirm delete' }}</span>
          <span v-else aria-hidden="true">🗑️</span>
        </button>
      </div>
    </div>
    
    <div class="roadmap-item-content">
      <p>{{ item.excerpt || item.content }}</p>
    </div>
    
    <div class="roadmap-item-meta">
      <div class="roadmap-item-tags">
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
          class="roadmap-badge roadmap-badge-primary"
        >
          {{ category.name }}
        </span>
      </div>
      
      <div v-if="item.target_date" class="roadmap-item-date">
        📅 {{ formatDate(item.target_date) }}
      </div>
    </div>
    
    <div class="roadmap-item-footer">
      <div class="roadmap-item-stats">
        <button
          v-if="showVotes"
          @click="handleVote"
          class="roadmap-item-vote"
          :class="{ 'voted': item.user_voted }"
          :disabled="!canVote"
          :aria-pressed="item.user_voted ? 'true' : 'false'"
          :title="item.user_voted ? (i18n.you_have_voted || 'You have voted for this item') : (i18n.vote_for_this || 'Vote for this item')"
        >
          <span class="roadmap-vote-icon" aria-hidden="true">{{ item.user_voted ? '✓' : '👍' }}</span>
          {{ item.votes || 0 }} {{ item.votes === 1 ? (i18n.vote || 'Vote') : (i18n.votes || 'Votes') }}
          <span v-if="item.user_voted" class="roadmap-vote-tag">{{ i18n.voted || 'Voted' }}</span>
        </button>

        <a
          v-if="showComments && item.link"
          :href="item.link"
          class="roadmap-item-comment"
          @click.stop
          :title="i18n.view_comments || 'View comments'"
        >
          💬 {{ item.comment_count || 0 }} {{ item.comment_count === 1 ? (i18n.comment || 'Comment') : (i18n.comments || 'Comments') }}
        </a>
        <button
          v-else-if="showComments"
          @click="$emit('comment')"
          class="roadmap-item-comment"
          :disabled="!canComment"
        >
          💬 {{ item.comment_count || 0 }} {{ item.comment_count === 1 ? (i18n.comment || 'Comment') : (i18n.comments || 'Comments') }}
        </button>
      </div>
      
      <div v-if="item.assignee" class="roadmap-item-assignee">
        <img 
          v-if="item.assignee.avatar"
          :src="item.assignee.avatar"
          :alt="item.assignee.name"
          class="roadmap-avatar"
        >
        <span>{{ item.assignee.name }}</span>
      </div>
    </div>
    
    <div v-if="item.progress > 0" class="roadmap-item-progress">
      <div class="roadmap-progress">
        <div 
          class="roadmap-progress-bar"
          :style="{ width: `${item.progress}%` }"
        ></div>
      </div>
      <span class="roadmap-progress-text">{{ item.progress }}%</span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref, onBeforeUnmount } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  mode: {
    type: String,
    default: 'view'
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  draggable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update', 'delete', 'vote', 'comment', 'dragstart', 'click', 'edit'])

// Inject config
const config = inject('$roadmapConfig', {})
const i18n = config?.i18n || {}

const confirmingDelete = ref(false)
let deleteTimer = null
const onDelete = () => {
  if (confirmingDelete.value) {
    clearTimeout(deleteTimer)
    confirmingDelete.value = false
    emit('delete')
    return
  }
  confirmingDelete.value = true
  deleteTimer = setTimeout(() => { confirmingDelete.value = false }, 5000)
}
onBeforeUnmount(() => clearTimeout(deleteTimer))

// Computed
// A guest has no config.user, so can_vote (computed server-side only for a
// logged-in user) is never set for them. Fall back to the guest-voting flag
// the same way App.vue's submitVote() and single-vote.js already do, so the
// button isn't disabled for a guest when guest voting is on.
const canVote = computed(() => {
  if (!config.settings?.enable_voting) return false
  return config.user ? !!config.user.can_vote : !!config.settings?.allow_guest_voting
})
const canComment = computed(() => config.user?.can_comment && config.settings?.enable_comments)
// Hide the whole stat, not just disable it, when the feature is off - matches
// the fix already shipped for the Minimal template (RoadmapMinimal.vue).
const showVotes = computed(() => config.settings?.enable_voting !== false)
const showComments = computed(() => config.settings?.enable_comments !== false)

// Methods
const handleVote = () => {
  const voteValue = props.item.user_voted ? 0 : 1
  emit('vote', voteValue)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const handleItemClick = (event) => {
  // Don't trigger click when clicking on buttons
  if (event.target.closest('button')) {
    return
  }
  emit('click', props.item)
}
</script>

<style lang="scss" scoped>
.roadmap-item-source-badge {
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

.roadmap-item {
  background: var(--rm-surface);
  border: 1px solid var(--rm-border);
  border-radius: 6px;
  padding: 16px;
  transition: all 0.2s ease;
  
  &:hover {
    box-shadow: 0 2px 8px var(--rm-shadow);
  }
  
  &-clickable {
    cursor: pointer;
  }
  
  &-draggable {
    cursor: move;
  }
  
  &-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
  }
  
  &-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.4;
    flex: 1;
  }
  
  &-actions {
    display: flex;
    gap: 4px;
    margin-left: 12px;
  }
  
  &-content {
    margin-bottom: 12px;
    
    p {
      margin: 0;
      font-size: 14px;
      color: var(--rm-muted);
      line-height: 1.5;
    }
  }
  
  &-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
  }
  
  &-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }
  
  &-date {
    font-size: 13px;
    color: var(--rm-muted);
  }
  
  &-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid var(--rm-border);
  }
  
  &-stats {
    display: flex;
    gap: 12px;
  }
  
  &-vote,
  &-comment {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    font-size: 13px;
    // Set, not inherited: themes paint bare buttons with their own colour.
    color: var(--rm-text);
    background: transparent;
    border: 1px solid var(--rm-border);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
      background: var(--rm-surface-alt);
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    &.voted {
      background: var(--roadmap-accent-tint);
      border-color: var(--roadmap-accent);
      // Lightness-clamped accent text so the voted state stays readable on the
      // tint under a pale theme accent.
      color: var(--roadmap-accent-text);
    }
  }

  // Voted affordance carried in content, not background, so it survives themes
  // (e.g. BuddyX) that repaint every bare <button> with their own accent and
  // would otherwise flatten the .voted background. currentColor keeps the pill
  // legible on both a solid themed button (white text) and the tinted state.
  &-vote {
    .roadmap-vote-icon {
      font-style: normal;
      line-height: 1;
    }

    &.voted .roadmap-vote-tag {
      margin-inline-start: 4px;
      padding: 1px 6px;
      border: 1px solid currentColor;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.02em;
      text-transform: uppercase;
      line-height: 1.4;
    }
  }
  
  // Style for comment link when it's an anchor
  a.roadmap-item-comment {
    text-decoration: none;
    color: inherit;
    
    &:hover {
      background: var(--rm-surface-alt);
      color: var(--roadmap-accent);
    }
  }
  
  &-assignee {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--rm-muted);
  }
  
  &-progress {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
}

.roadmap-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
}

.roadmap-progress {
  flex: 1;
  height: 6px;
  background: var(--rm-border);
  border-radius: 3px;
  overflow: hidden;

  &-bar {
    height: 100%;
    background: var(--rm-done);
    transition: width 0.3s ease;
  }

  &-text {
    font-size: 12px;
    color: var(--rm-muted);
    font-weight: 500;
  }
}
</style>
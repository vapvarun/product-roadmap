<template>
  <div 
    class="roadmap-item"
    :class="{ 'roadmap-item-draggable': draggable, 'roadmap-item-clickable': true }"
    :draggable="draggable"
    @dragstart="$emit('dragstart', $event)"
    @click="handleItemClick"
  >
    <div class="roadmap-item-header">
      <h4 class="roadmap-item-title">{{ item.title }}</h4>
      <div v-if="canEdit" class="roadmap-item-actions">
        <button 
          @click.stop="$emit('edit')"
          class="roadmap-btn roadmap-btn-sm roadmap-btn-icon"
          title="Edit"
        >
          ✏️
        </button>
        <button 
          v-if="mode === 'manage'"
          @click.stop="$emit('delete')"
          class="roadmap-btn roadmap-btn-sm roadmap-btn-icon roadmap-btn-danger"
          title="Delete"
        >
          🗑️
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
          :class="`roadmap-badge-${getPriorityClass(item.priority)}`"
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
          @click="handleVote"
          class="roadmap-item-vote"
          :class="{ 'voted': item.user_voted }"
          :disabled="!canVote"
          :aria-pressed="item.user_voted ? 'true' : 'false'"
          :title="item.user_voted ? 'You have voted for this item' : 'Vote for this item'"
        >
          <span class="roadmap-vote-icon" aria-hidden="true">{{ item.user_voted ? '✓' : '👍' }}</span>
          {{ item.votes || 0 }} {{ item.votes === 1 ? 'Vote' : 'Votes' }}
          <span v-if="item.user_voted" class="roadmap-vote-tag">Voted</span>
        </button>
        
        <a 
          v-if="item.link"
          :href="item.link"
          class="roadmap-item-comment"
          @click.stop
          title="View comments"
        >
          💬 {{ item.comment_count || 0 }} {{ item.comment_count === 1 ? 'Comment' : 'Comments' }}
        </a>
        <button 
          v-else
          @click="$emit('comment')"
          class="roadmap-item-comment"
          :disabled="!canComment"
        >
          💬 {{ item.comment_count || 0 }} {{ item.comment_count === 1 ? 'Comment' : 'Comments' }}
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
import { computed, inject } from 'vue'

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

// Computed
const canVote = computed(() => config.user?.can_vote && config.settings?.enable_voting)
const canComment = computed(() => config.user?.can_comment && config.settings?.enable_comments)

// Methods
const handleVote = () => {
  const voteValue = props.item.user_voted ? 0 : 1
  emit('vote', voteValue)
}

const getPriorityClass = (priority) => {
  const map = {
    low: 'success',
    medium: 'warning',
    high: 'danger',
    critical: 'danger'
  }
  return map[priority] || 'primary'
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
.roadmap-item {
  background: var(--rm-surface);
  border: 1px solid var(--rm-border);
  border-radius: 6px;
  padding: 16px;
  transition: all 0.2s ease;
  
  &:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
      background: var(--roadmap-accent-tint, #e3f2fd);
      border-color: var(--roadmap-accent, #2196f3);
      color: var(--roadmap-accent, #2196f3);
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
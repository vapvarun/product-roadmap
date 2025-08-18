<template>
  <div class="roadmap-modal" @click.self="$emit('close')">
    <div class="roadmap-modal-dialog roadmap-modal-lg">
      <div class="roadmap-modal-header">
        <h3>{{ item.title }}</h3>
        <button 
          @click="$emit('close')"
          class="roadmap-btn roadmap-btn-icon"
        >
          ×
        </button>
      </div>
      
      <div class="roadmap-modal-body">
        <!-- Item Status and Priority -->
        <div class="roadmap-detail-meta">
          <div class="roadmap-detail-status">
            <strong>Status:</strong>
            <span 
              v-for="status in item.status" 
              :key="status.id"
              class="roadmap-badge roadmap-badge-primary"
            >
              {{ status.name }}
            </span>
          </div>
          
          <div v-if="item.priority" class="roadmap-detail-priority">
            <strong>Priority:</strong>
            <span 
              class="roadmap-badge"
              :class="`roadmap-badge-${getPriorityClass(item.priority)}`"
            >
              {{ item.priority }}
            </span>
          </div>
          
          <div v-if="item.target_date" class="roadmap-detail-date">
            <strong>Target Date:</strong>
            {{ formatDate(item.target_date) }}
          </div>
        </div>
        
        <!-- Description -->
        <div class="roadmap-detail-section">
          <h4>Description</h4>
          <div class="roadmap-detail-content" v-html="item.content"></div>
        </div>
        
        <!-- Categories -->
        <div v-if="item.categories && item.categories.length > 0" class="roadmap-detail-section">
          <h4>Categories</h4>
          <div class="roadmap-detail-tags">
            <span 
              v-for="category in item.categories"
              :key="category.id"
              class="roadmap-badge roadmap-badge-primary"
            >
              {{ category.name }}
            </span>
          </div>
        </div>
        
        <!-- Progress -->
        <div v-if="item.progress > 0" class="roadmap-detail-section">
          <h4>Progress</h4>
          <div class="roadmap-progress-detail">
            <div class="roadmap-progress">
              <div 
                class="roadmap-progress-bar"
                :style="{ width: `${item.progress}%` }"
              ></div>
            </div>
            <span class="roadmap-progress-text">{{ item.progress }}%</span>
          </div>
        </div>
        
        <!-- Assignee -->
        <div v-if="item.assignee" class="roadmap-detail-section">
          <h4>Assigned To</h4>
          <div class="roadmap-detail-assignee">
            <img 
              v-if="item.assignee.avatar"
              :src="item.assignee.avatar"
              :alt="item.assignee.name"
              class="roadmap-avatar-lg"
            >
            <span>{{ item.assignee.name }}</span>
          </div>
        </div>
        
        <!-- Stats -->
        <div class="roadmap-detail-stats">
          <div class="roadmap-stat-item">
            <span class="roadmap-stat-icon">👍</span>
            <span class="roadmap-stat-value">{{ item.votes || 0 }}</span>
            <span class="roadmap-stat-label">Votes</span>
          </div>
          
          <div class="roadmap-stat-item">
            <span class="roadmap-stat-icon">💬</span>
            <span class="roadmap-stat-value">{{ commentCount }}</span>
            <span class="roadmap-stat-label">Comments</span>
          </div>
          
          <div v-if="item.view_count" class="roadmap-stat-item">
            <span class="roadmap-stat-icon">👁️</span>
            <span class="roadmap-stat-value">{{ item.view_count }}</span>
            <span class="roadmap-stat-label">Views</span>
          </div>
        </div>
        
      </div>
      
      <div class="roadmap-modal-footer">
        <button 
          v-if="canVote"
          @click="$emit('vote')"
          class="roadmap-btn"
          :class="item.user_voted ? 'roadmap-btn-primary' : 'roadmap-btn-secondary'"
        >
          {{ item.user_voted ? 'Remove Vote' : 'Vote for This' }}
        </button>
        
        
        <a 
          v-if="item.link"
          :href="item.link"
          class="roadmap-btn roadmap-btn-primary"
        >
          View Details & Comments
        </a>
        
        <button 
          @click="$emit('close')"
          class="roadmap-btn roadmap-btn-secondary"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'vote', 'comment'])

// Inject config
const config = inject('$roadmapConfig', {})

// State
const commentCount = ref(props.item.comment_count || 0)

// Computed
const canVote = computed(() => config.user?.can_vote && config.settings?.enable_voting)
const canComment = computed(() => config.user?.can_comment && config.settings?.enable_comments)

// Methods
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
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const handleCommentAdded = () => {
  commentCount.value++
}
</script>

<style lang="scss" scoped>
.roadmap-modal-lg {
  max-width: 800px;
}

.roadmap-detail {
  &-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    
    > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    strong {
      font-weight: 600;
      color: #666;
    }
  }
  
  &-section {
    margin-bottom: 30px;
    
    h4 {
      margin: 0 0 15px 0;
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }
  }
  
  &-content {
    font-size: 15px;
    line-height: 1.6;
    color: #555;
    
    :deep(p) {
      margin: 0 0 15px 0;
      
      &:last-child {
        margin-bottom: 0;
      }
    }
    
    :deep(ul),
    :deep(ol) {
      margin: 0 0 15px 0;
      padding-left: 30px;
    }
  }
  
  &-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  
  &-assignee {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
  }
  
  &-stats {
    display: flex;
    gap: 40px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #eee;
  }
}

.roadmap-progress-detail {
  display: flex;
  align-items: center;
  gap: 15px;
  
  .roadmap-progress {
    flex: 1;
    max-width: 300px;
    height: 10px;
    background: #eee;
    border-radius: 5px;
    overflow: hidden;
    
    &-bar {
      height: 100%;
      background: #4caf50;
      transition: width 0.3s ease;
    }
  }
  
  .roadmap-progress-text {
    font-size: 16px;
    font-weight: 600;
    color: #4caf50;
  }
}

.roadmap-stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  
  &-icon {
    font-size: 24px;
  }
  
  &-value {
    font-size: 20px;
    font-weight: 600;
    color: #333;
  }
  
  &-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
}

.roadmap-avatar-lg {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

// Dark mode
.roadmap-dark {
  .roadmap-detail {
    &-meta {
      border-bottom-color: #50575e;
      
      strong {
        color: #ccc;
      }
    }
    
    &-section h4 {
      color: #e2e4e7;
    }
    
    &-content {
      color: #ccc;
    }
    
    &-stats {
      border-top-color: #50575e;
    }
  }
  
  .roadmap-stat-item {
    &-value {
      color: #e2e4e7;
    }
    
    &-label {
      color: #aaa;
    }
  }
  
  .roadmap-progress-detail {
    .roadmap-progress {
      background: #50575e;
    }
  }
}
</style>
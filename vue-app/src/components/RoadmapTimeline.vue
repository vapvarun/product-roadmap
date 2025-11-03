<template>
  <div class="roadmap-template-timeline roadmap-timeline">
    <div class="roadmap-timeline-header">
      <h2 class="roadmap-timeline-title">Product Roadmap Timeline</h2>
      <div class="roadmap-timeline-filters">
        <select v-model="currentView" class="roadmap-select">
          <option value="current">Current Quarter</option>
          <option value="6months">Next 6 Months</option>
          <option value="year">Next Year</option>
          <option value="all">All Time</option>
        </select>
      </div>
    </div>

    <div class="roadmap-timeline-content">
      <div class="roadmap-timeline-sections">
        <!-- Current/In Progress Section -->
        <div class="roadmap-timeline-section">
          <div class="roadmap-timeline-section-header">
            <h3>
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="M10 6V10L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Now
            </h3>
            <span class="roadmap-timeline-period">{{ currentPeriod }}</span>
          </div>
          <div class="roadmap-timeline-items">
            <div v-if="currentItems.length === 0" class="roadmap-timeline-empty">
              <p>No items in progress</p>
            </div>
            <div 
              v-for="item in currentItems" 
              :key="item.id"
              class="roadmap-timeline-item"
              :class="`status-${getStatusSlug(item)}`"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-timeline-item-content">
                <h4>{{ item.title }}</h4>
                <p>{{ item.excerpt || item.content.substring(0, 100) + '...' }}</p>
                <div class="roadmap-timeline-item-footer">
                  <div class="roadmap-timeline-item-meta">
                    <span class="roadmap-timeline-status">{{ getStatusName(item) }}</span>
                    <span v-if="item.progress" class="roadmap-timeline-progress">
                      <div class="roadmap-progress-bar">
                        <div class="roadmap-progress-fill" :style="{ width: item.progress + '%' }"></div>
                      </div>
                      {{ item.progress }}%
                    </span>
                  </div>
                  <div class="roadmap-timeline-item-stats">
                    <span class="roadmap-timeline-votes">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 2L10.5 7L16 7.5L12 11.5L13 16L8 13.5L3 16L4 11.5L0 7.5L5.5 7L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                      </svg>
                      {{ item.votes || 0 }}
                    </span>
                    <span class="roadmap-timeline-comments">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M2 2H14V10H8L4 14V10H2V2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                      </svg>
                      {{ item.comment_count || 0 }}
                    </span>
                  </div>
                </div>
                <div v-if="mode === 'manage' && canEdit" class="roadmap-timeline-item-actions">
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
              <div v-if="item.start_date && item.end_date" class="roadmap-timeline-item-dates">
                <span>{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Next/Upcoming Section -->
        <div class="roadmap-timeline-section">
          <div class="roadmap-timeline-section-header">
            <h3>
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M2 10H18M18 10L13 5M18 10L13 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Next
            </h3>
            <span class="roadmap-timeline-period">{{ nextPeriod }}</span>
          </div>
          <div class="roadmap-timeline-items">
            <div v-if="upcomingItems.length === 0" class="roadmap-timeline-empty">
              <p>No upcoming items</p>
            </div>
            <div 
              v-for="item in upcomingItems" 
              :key="item.id"
              class="roadmap-timeline-item"
              :class="`status-${getStatusSlug(item)}`"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-timeline-item-content">
                <h4>{{ item.title }}</h4>
                <p>{{ item.excerpt || item.content.substring(0, 100) + '...' }}</p>
                <div class="roadmap-timeline-item-footer">
                  <div class="roadmap-timeline-item-meta">
                    <span class="roadmap-timeline-status">{{ getStatusName(item) }}</span>
                    <span v-if="item.categories && item.categories.length" class="roadmap-timeline-category">
                      {{ item.categories[0].name }}
                    </span>
                  </div>
                  <div class="roadmap-timeline-item-stats">
                    <span class="roadmap-timeline-votes">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M8 2L10.5 7L16 7.5L12 11.5L13 16L8 13.5L3 16L4 11.5L0 7.5L5.5 7L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                      </svg>
                      {{ item.votes || 0 }}
                    </span>
                    <span class="roadmap-timeline-comments">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M2 2H14V10H8L4 14V10H2V2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                      </svg>
                      {{ item.comment_count || 0 }}
                    </span>
                  </div>
                </div>
                <div v-if="mode === 'manage' && canEdit" class="roadmap-timeline-item-actions">
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
              <div v-if="item.start_date && item.end_date" class="roadmap-timeline-item-dates">
                <span>{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Future/Backlog Section -->
        <div class="roadmap-timeline-section">
          <div class="roadmap-timeline-section-header">
            <h3>
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Later
            </h3>
            <span class="roadmap-timeline-period">{{ futurePeriod }}</span>
          </div>
          <div class="roadmap-timeline-items">
            <div v-if="futureItems.length === 0" class="roadmap-timeline-empty">
              <p>No items in backlog</p>
            </div>
            <div 
              v-for="item in futureItems" 
              :key="item.id"
              class="roadmap-timeline-item"
              :class="`status-${getStatusSlug(item)}`"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-timeline-item-content">
                <h4>{{ item.title }}</h4>
                <p>{{ item.excerpt || item.content.substring(0, 100) + '...' }}</p>
                <div class="roadmap-timeline-item-footer">
                  <div class="roadmap-timeline-item-meta">
                    <span class="roadmap-timeline-status">{{ getStatusName(item) }}</span>
                    <span v-if="item.categories && item.categories.length" class="roadmap-timeline-category">
                      {{ item.categories[0].name }}
                    </span>
                  </div>
                  <div class="roadmap-timeline-item-stats">
                    <span class="roadmap-timeline-votes">👍 {{ item.votes || 0 }}</span>
                    <span class="roadmap-timeline-comments">💬 {{ item.comment_count || 0 }}</span>
                  </div>
                </div>
                <div v-if="mode === 'manage' && canEdit" class="roadmap-timeline-item-actions">
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
        </div>
      </div>

      <!-- Visual Timeline -->
      <div class="roadmap-visual-timeline">
        <div class="roadmap-timeline-markers">
          <div class="roadmap-timeline-marker">
            <div class="roadmap-timeline-marker-label">Past</div>
          </div>
          <div class="roadmap-timeline-marker active">
            <div class="roadmap-timeline-marker-label">Now</div>
          </div>
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

const currentView = ref('current')
const now = new Date()
const currentQuarter = Math.floor(now.getMonth() / 3) + 1
const currentYear = now.getFullYear()

const currentPeriod = computed(() => {
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
  return `${months[now.getMonth()]} - ${months[now.getMonth() + 2] || months[0]} ${currentYear}`
})

const nextPeriod = computed(() => {
  const nextQ = currentQuarter === 4 ? 1 : currentQuarter + 1
  const nextY = currentQuarter === 4 ? currentYear + 1 : currentYear
  return `Q${nextQ} ${nextY}`
})

const futurePeriod = computed(() => {
  return `${currentYear + 1} & Beyond`
})

const currentItems = computed(() => {
  const inProgressStatuses = ['in-progress', 'development', 'testing', 'in-development']
  return props.items.filter(item => {
    const statusSlug = item.status?.[0]?.slug
    return inProgressStatuses.includes(statusSlug) || 
           (item.start_date && new Date(item.start_date) <= now && (!item.end_date || new Date(item.end_date) >= now))
  })
})

const upcomingItems = computed(() => {
  const upcomingStatuses = ['planned', 'approved', 'scheduled']
  const futureDate = new Date()
  futureDate.setMonth(futureDate.getMonth() + 3)
  
  return props.items.filter(item => {
    const statusSlug = item.status?.[0]?.slug
    return upcomingStatuses.includes(statusSlug) || 
           (item.start_date && new Date(item.start_date) > now && new Date(item.start_date) <= futureDate)
  })
})

const futureItems = computed(() => {
  const futureStatuses = ['idea', 'under-review', 'considering', 'backlog']
  const futureDate = new Date()
  futureDate.setMonth(futureDate.getMonth() + 3)
  
  return props.items.filter(item => {
    const statusSlug = item.status?.[0]?.slug
    return futureStatuses.includes(statusSlug) || 
           (!item.start_date || (item.start_date && new Date(item.start_date) > futureDate))
  })
})

const getStatusSlug = (item) => {
  return item.status?.[0]?.slug || 'default'
}

const getStatusName = (item) => {
  return item.status?.[0]?.name || 'Unknown'
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
.roadmap-timeline {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.roadmap-timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;

  .roadmap-timeline-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
  }

  .roadmap-select {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;

    option {
      background: #667eea;
      color: #fff;
    }
  }
}

.roadmap-timeline-content {
  padding: 2rem;
}

.roadmap-timeline-sections {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.roadmap-timeline-section {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;

  .roadmap-timeline-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;

    h3 {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0;
      font-size: 1.25rem;
      font-weight: 600;
      color: #333;

      svg {
        color: #667eea;
      }
    }

    .roadmap-timeline-period {
      font-size: 13px;
      color: #666;
      background: white;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
    }
  }
}

.roadmap-timeline-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.roadmap-timeline-empty {
  text-align: center;
  padding: 2rem;
  color: #999;

  p {
    margin: 0;
    font-size: 14px;
  }
}

.roadmap-timeline-item {
  background: white;
  border-radius: 8px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
  position: relative;
  overflow: hidden;

  &:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #e0e0e0;
    transition: all 0.2s ease;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #667eea;

    &:before {
      width: 6px;
    }
  }

  &.status-in-progress:before,
  &.status-development:before {
    background: #ff9800;
  }

  &.status-planned:before,
  &.status-approved:before {
    background: #2196f3;
  }

  &.status-completed:before,
  &.status-released:before {
    background: #4caf50;
  }

  &.status-idea:before,
  &.status-under-review:before {
    background: #9c27b0;
  }

  .roadmap-timeline-item-content {
    h4 {
      margin: 0 0 0.5rem;
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }

    p {
      margin: 0 0 1rem;
      font-size: 14px;
      color: #666;
      line-height: 1.5;
    }
  }

  .roadmap-timeline-item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .roadmap-timeline-item-meta {
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .roadmap-timeline-status {
        font-size: 12px;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        background: #e3f2fd;
        color: #1976d2;
        font-weight: 500;
      }

      .roadmap-timeline-category {
        font-size: 12px;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        background: #f3e5f5;
        color: #7b1fa2;
      }

      .roadmap-timeline-progress {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 12px;
        color: #666;

        .roadmap-progress-bar {
          width: 60px;
          height: 4px;
          background: #e0e0e0;
          border-radius: 2px;
          overflow: hidden;

          .roadmap-progress-fill {
            height: 100%;
            background: #4caf50;
            transition: width 0.3s ease;
          }
        }
      }
    }

    .roadmap-timeline-item-stats {
      display: flex;
      gap: 1rem;

      span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 13px;
        color: #999;

        svg {
          width: 16px;
          height: 16px;
        }
      }
    }
  }

  .roadmap-timeline-item-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;

    button {
      font-size: 13px;
      padding: 6px 12px;
    }
  }

  .roadmap-timeline-item-dates {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f0f0f0;

    span {
      font-size: 12px;
      color: #999;
    }
  }
}

.roadmap-visual-timeline {
  position: relative;
  margin-top: 3rem;
  padding: 2rem 0;

  .roadmap-timeline-markers {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 1;

    .roadmap-timeline-marker {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;

      &:before {
        content: '';
        width: 20px;
        height: 20px;
        background: white;
        border: 3px solid #667eea;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      &.active:before {
        background: #667eea;
        width: 24px;
        height: 24px;
      }

      .roadmap-timeline-marker-label {
        font-size: 13px;
        font-weight: 600;
        color: #666;
        background: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      }
    }
  }
}

// Dark mode
.roadmap-dark .roadmap-timeline {
  background: #1e1e1e;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);

  .roadmap-timeline-header {
    background: linear-gradient(135deg, #4c5270 0%, #5a4a6d 100%);
  }

  .roadmap-timeline-section {
    background: #2a2a2a;

    h3 {
      color: #f0f0f0;
    }

    .roadmap-timeline-period {
      background: #333;
      color: #ccc;
    }
  }

  .roadmap-timeline-item {
    background: #333;

    &:hover {
      border-color: #8b92ff;
    }

    h4 {
      color: #f0f0f0;
    }

    p {
      color: #ccc;
    }

    .roadmap-timeline-status {
      background: #1a237e;
      color: #82b1ff;
    }

    .roadmap-timeline-category {
      background: #4a148c;
      color: #e1bee7;
    }
  }

  .roadmap-visual-timeline {
    .roadmap-timeline-marker {
      &:before {
        background: #1e1e1e;
        border-color: #8b92ff;
      }

      &.active:before {
        background: #8b92ff;
      }

      .roadmap-timeline-marker-label {
        background: #2a2a2a;
        color: #f0f0f0;
      }
    }
  }
}

// Responsive
@media (max-width: 768px) {
  .roadmap-timeline-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .roadmap-timeline-sections {
    grid-template-columns: 1fr;
  }

  .roadmap-visual-timeline {
    display: none;
  }
}
</style>
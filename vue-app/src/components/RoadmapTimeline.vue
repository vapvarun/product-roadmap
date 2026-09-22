<template>
  <div
    class="roadmap-template-timeline roadmap-timeline"
    :class="{ 'roadmap-timeline--hide-votes': !showVotes, 'roadmap-timeline--hide-comments': !showComments }"
  >
    <div class="roadmap-timeline-header">
      <h2 class="roadmap-timeline-title">{{ i18n.roadmap_timeline || 'Product Roadmap Timeline' }}</h2>
      <div class="roadmap-timeline-filters">
        <select v-model="currentView" class="roadmap-select">
          <option value="current">{{ i18n.current_quarter || 'Current Quarter' }}</option>
          <option value="6months">{{ i18n.next_6_months || 'Next 6 Months' }}</option>
          <option value="year">{{ i18n.next_year || 'Next Year' }}</option>
          <option value="all">{{ i18n.all_time || 'All Time' }}</option>
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
              {{ i18n.now || 'Now' }}
            </h3>
            <span class="roadmap-timeline-period">{{ currentPeriod }}</span>
          </div>
          <div class="roadmap-timeline-items">
            <div v-if="currentItems.length === 0" class="roadmap-timeline-empty">
              <p>{{ i18n.no_items_in_progress || 'No items in progress' }}</p>
            </div>
            <div 
              v-for="item in currentItems" 
              :key="item.id"
              class="roadmap-timeline-item"
              :class="`status-${getStatusSlug(item)}`"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-timeline-item-content">
                <h4>
                  {{ item.title }}
                  <span
                    v-if="item.is_suggestion"
                    class="roadmap-timeline-source-badge"
                    :title="i18n.user_suggested_title || 'Suggested by a user'"
                  >
                    {{ i18n.suggested || 'Suggested' }}
                  </span>
                </h4>
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
              {{ i18n.next || 'Next' }}
            </h3>
            <span class="roadmap-timeline-period">{{ nextPeriod }}</span>
          </div>
          <div class="roadmap-timeline-items">
            <div v-if="upcomingItems.length === 0" class="roadmap-timeline-empty">
              <p>{{ i18n.no_upcoming_items || 'No upcoming items' }}</p>
            </div>
            <div 
              v-for="item in upcomingItems" 
              :key="item.id"
              class="roadmap-timeline-item"
              :class="`status-${getStatusSlug(item)}`"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-timeline-item-content">
                <h4>
                  {{ item.title }}
                  <span
                    v-if="item.is_suggestion"
                    class="roadmap-timeline-source-badge"
                    :title="i18n.user_suggested_title || 'Suggested by a user'"
                  >
                    {{ i18n.suggested || 'Suggested' }}
                  </span>
                </h4>
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
              {{ i18n.later || 'Later' }}
            </h3>
            <span class="roadmap-timeline-period">{{ futurePeriod }}</span>
          </div>
          <div class="roadmap-timeline-items">
            <div v-if="futureItems.length === 0" class="roadmap-timeline-empty">
              <p>{{ i18n.no_items_in_backlog || 'No items in backlog' }}</p>
            </div>
            <div 
              v-for="item in futureItems" 
              :key="item.id"
              class="roadmap-timeline-item"
              :class="`status-${getStatusSlug(item)}`"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-timeline-item-content">
                <h4>
                  {{ item.title }}
                  <span
                    v-if="item.is_suggestion"
                    class="roadmap-timeline-source-badge"
                    :title="i18n.user_suggested_title || 'Suggested by a user'"
                  >
                    {{ i18n.suggested || 'Suggested' }}
                  </span>
                </h4>
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

        <!-- Done/Completed Section -->
        <div class="roadmap-timeline-section roadmap-timeline-done" v-if="completedItems.length > 0">
          <div class="roadmap-timeline-section-header">
            <h3>
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M7 10L9 12L13 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
              </svg>
              {{ i18n.done || 'Done' }}
            </h3>
            <span class="roadmap-timeline-period">{{ completedItems.length }} {{ i18n.completed || 'completed' }}</span>
          </div>
          <div class="roadmap-timeline-items">
            <div
              v-for="item in completedItems"
              :key="item.id"
              class="roadmap-timeline-item status-completed"
              @click="$emit('item-click', item)"
            >
              <div class="roadmap-timeline-item-content">
                <h4>
                  {{ item.title }}
                  <span
                    v-if="item.is_suggestion"
                    class="roadmap-timeline-source-badge"
                    :title="i18n.user_suggested_title || 'Suggested by a user'"
                  >
                    {{ i18n.suggested || 'Suggested' }}
                  </span>
                </h4>
                <p>{{ item.excerpt || item.content.substring(0, 100) + '...' }}</p>
                <div class="roadmap-timeline-item-footer">
                  <div class="roadmap-timeline-item-meta">
                    <span class="roadmap-timeline-status roadmap-status-completed">{{ getStatusName(item) }}</span>
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

      <!-- Visual Timeline -->
      <div class="roadmap-visual-timeline">
        <div class="roadmap-timeline-markers">
          <div class="roadmap-timeline-marker">
            <div class="roadmap-timeline-marker-label">{{ i18n.past || 'Past' }}</div>
          </div>
          <div class="roadmap-timeline-marker active">
            <div class="roadmap-timeline-marker-label">{{ i18n.now || 'Now' }}</div>
          </div>
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
// Applied as a class on the root element (below) rather than per-section v-if,
// since the same markup repeats across the Now/Next/Later/Done sections.
const showVotes = computed(() => config.settings?.enable_voting !== false)
const showComments = computed(() => config.settings?.enable_comments !== false)

// Seeded from the owner's Timeline setting instead of being hardcoded.
// The setting and this selector were written with different vocabularies
// (month/quarter/year vs current/6months/year/all), so map rather than assume
// they line up - an unmapped value falls back to the previous default.
const timelineViewFromSetting = (value) => {
  switch (value) {
    case 'month':
    case 'quarter':
    case 'current':
      return 'current'
    case '6months':
      return '6months'
    case 'year':
      return 'year'
    case 'all':
      return 'all'
    default:
      return 'current'
  }
}

const currentView = ref(
  timelineViewFromSetting(
    props.proFeatures?.timeline_default_view || props.proFeatures?.timeline_scale
  )
)
const now = new Date()

// These three labels used to be hardcoded to a fixed 3-month/quarter span
// regardless of currentView, so switching Templates -> Timeline Scale to
// Year (or picking a different period from the dropdown) correctly changed
// which items were bucketed but left the header text always reading a
// 3-month "Now"/one-quarter "Next" range. Derive them from the same overall
// window getDateFilteredItems() actually uses, split into three chunks.
const viewSpanMonths = computed(() => {
  switch (currentView.value) {
    case '6months':
      return 6
    case 'year':
      return 12
    case 'all':
      return null // open-ended - no fixed window to describe
    default:
      return 3
  }
})

const monthLabel = (date) => {
  const months = i18n.months || ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
  return months[date.getMonth()]
}

const addMonths = (date, count) => {
  const d = new Date(date)
  d.setMonth(d.getMonth() + count)
  return d
}

const currentPeriod = computed(() => {
  if (viewSpanMonths.value === null) {
    return i18n.all_time || 'All Time'
  }
  const chunk = Math.max(1, Math.round(viewSpanMonths.value / 3))
  const end = addMonths(now, chunk - 1)
  return `${monthLabel(now)} - ${monthLabel(end)} ${end.getFullYear()}`
})

const nextPeriod = computed(() => {
  if (viewSpanMonths.value === null) {
    return i18n.all_time || 'All Time'
  }
  const chunk = Math.max(1, Math.round(viewSpanMonths.value / 3))
  const start = addMonths(now, chunk)
  const end = addMonths(now, chunk * 2 - 1)
  return `${monthLabel(start)} - ${monthLabel(end)} ${end.getFullYear()}`
})

const futurePeriod = computed(() => {
  if (viewSpanMonths.value === null) {
    return i18n.all_time || 'All Time'
  }
  const chunk = Math.max(1, Math.round(viewSpanMonths.value / 3))
  const start = addMonths(now, chunk * 2)
  return `${monthLabel(start)} ${start.getFullYear()} ${i18n.and_beyond || '& Beyond'}`
})

// Filter items based on selected time period
const getDateFilteredItems = () => {
  if (currentView.value === 'all') {
    return props.items
  }

  const now = new Date()
  let endDate = new Date()

  switch (currentView.value) {
    case 'current':
      // Current quarter (3 months)
      endDate.setMonth(endDate.getMonth() + 3)
      break
    case '6months':
      endDate.setMonth(endDate.getMonth() + 6)
      break
    case 'year':
      endDate.setFullYear(endDate.getFullYear() + 1)
      break
  }

  return props.items.filter(item => {
    const itemDate = item.target_date || item.start_date || item.end_date
    if (!itemDate) return true // Include items without dates

    const targetDate = new Date(itemDate)
    return targetDate >= now && targetDate <= endDate
  })
}

const inProgressStatuses = ['in-progress', 'development', 'testing', 'in-development']
const upcomingStatuses = ['planned', 'approved', 'scheduled']

const currentItems = computed(() => {
  const filteredItems = getDateFilteredItems()

  return filteredItems.filter(item => {
    const statusSlug = item.status?.[0]?.slug
    return inProgressStatuses.includes(statusSlug) ||
           (item.start_date && new Date(item.start_date) <= now && (!item.end_date || new Date(item.end_date) >= now))
  })
})

const upcomingItems = computed(() => {
  const futureDate = new Date()
  futureDate.setMonth(futureDate.getMonth() + 3)
  const filteredItems = getDateFilteredItems()

  return filteredItems.filter(item => {
    const statusSlug = item.status?.[0]?.slug
    return upcomingStatuses.includes(statusSlug) ||
           (item.start_date && new Date(item.start_date) > now && new Date(item.start_date) <= futureDate)
  })
})

const futureItems = computed(() => {
  const futureStatuses = ['idea', 'under-review', 'considering', 'backlog']
  const completedStatuses = ['completed', 'released', 'done', 'shipped']
  const futureDate = new Date()
  futureDate.setMonth(futureDate.getMonth() + 3)
  const filteredItems = getDateFilteredItems()

  return filteredItems.filter(item => {
    const statusSlug = item.status?.[0]?.slug
    // Exclude completed items from Later section
    if (completedStatuses.includes(statusSlug)) return false
    // Do not swallow items an earlier section has already claimed. The old
    // predicate ended in `!item.start_date`, so every dateless item landed in
    // Later on top of wherever its status had already placed it - most items
    // have no start date, so most of the board rendered twice and every count
    // on the view was inflated.
    if (inProgressStatuses.includes(statusSlug)) return false
    if (upcomingStatuses.includes(statusSlug)) return false
    return futureStatuses.includes(statusSlug) ||
           (!item.start_date || (item.start_date && new Date(item.start_date) > futureDate))
  })
})

const completedItems = computed(() => {
  const completedStatuses = ['completed', 'released', 'done', 'shipped']
  const filteredItems = getDateFilteredItems()

  return filteredItems.filter(item => {
    const statusSlug = item.status?.[0]?.slug
    return completedStatuses.includes(statusSlug)
  })
})

const getStatusSlug = (item) => {
  return item.status?.[0]?.slug || 'default'
}

const getStatusName = (item) => {
  return item.status?.[0]?.name || (i18n.unknown || 'Unknown')
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
.roadmap-timeline--hide-votes .roadmap-timeline-votes,
.roadmap-timeline--hide-comments .roadmap-timeline-comments {
  display: none;
}

.roadmap-timeline-source-badge {
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

.roadmap-timeline {
  background: var(--rm-surface);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.roadmap-timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, var(--roadmap-accent) 0%, var(--roadmap-accent-hover) 100%);
  color: var(--roadmap-accent-contrast, #fff);

  .roadmap-timeline-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    // The gradient header is a light/brand accent surface even under a dark
    // theme, so its text must follow accent-contrast. !important is deliberate:
    // it overrides the broad dark-mode heading rule (which forces every
    // [class*="-title"] to a light colour) that would otherwise wash the title
    // out on a pale theme accent (Reign).
    color: var(--roadmap-accent-contrast, #fff) !important;
  }

  .roadmap-select {
    // Translucent version of the contrast colour so the control adapts: a subtle
    // dark tint on light accents, a subtle light tint on dark ones - never the
    // invisible white-on-pale-blue it was before.
    background: color-mix(in srgb, var(--roadmap-accent-contrast, #fff) 15%, transparent);
    color: var(--roadmap-accent-contrast, #fff);
    border: 1px solid color-mix(in srgb, var(--roadmap-accent-contrast, #fff) 35%, transparent);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;

    option {
      background: var(--roadmap-accent);
      color: var(--roadmap-accent-contrast, #fff);
    }
  }
}

.roadmap-timeline-content {
  padding: 2rem;
}

.roadmap-timeline-sections {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
  align-items: start;
}

.roadmap-timeline-section {
  background: var(--rm-surface-alt);
  border-radius: 12px;
  padding: 1.5rem;
  min-height: 120px;

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
      color: var(--rm-heading);

      svg {
        color: var(--roadmap-accent);
      }
    }

    .roadmap-timeline-period {
      font-size: 13px;
      color: var(--rm-muted);
      background: var(--rm-surface);
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
    }
  }

  &.roadmap-timeline-done {
    background: var(--rm-done-tint);
    border: 1px solid var(--rm-done-tint);

    .roadmap-timeline-section-header h3 svg {
      color: var(--rm-done);
    }

    .roadmap-timeline-period {
      background: var(--rm-done);
      color: white;
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
  color: var(--rm-muted);

  p {
    margin: 0;
    font-size: 14px;
  }
}

.roadmap-timeline-item {
  background: var(--rm-surface);
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
    background: var(--rm-border);
    transition: all 0.2s ease;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: var(--roadmap-accent);

    &:before {
      width: 6px;
    }
  }

  &.status-in-progress:before,
  &.status-development:before {
    background: var(--rm-progress);
  }

  &.status-planned:before,
  &.status-approved:before {
    background: var(--rm-planned);
  }

  &.status-completed:before,
  &.status-released:before,
  &.status-done:before,
  &.status-shipped:before {
    background: var(--rm-done);
  }

  .roadmap-status-completed {
    background: var(--rm-done-tint) !important;
    color: var(--rm-done) !important;
  }

  &.status-idea:before,
  &.status-under-review:before {
    background: var(--rm-review);
  }

  .roadmap-timeline-item-content {
    h4 {
      margin: 0 0 0.5rem;
      font-size: 16px;
      font-weight: 600;
      color: var(--rm-heading);
    }

    p {
      margin: 0 0 1rem;
      font-size: 14px;
      color: var(--rm-muted);
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
        background: var(--rm-planned-tint);
        color: var(--rm-planned);
        font-weight: 500;
      }

      .roadmap-timeline-category {
        font-size: 12px;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        background: var(--rm-review-tint);
        color: var(--rm-review);
      }

      .roadmap-timeline-progress {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 12px;
        color: var(--rm-muted);

        .roadmap-progress-bar {
          width: 60px;
          height: 4px;
          background: var(--rm-border);
          border-radius: 2px;
          overflow: hidden;

          .roadmap-progress-fill {
            height: 100%;
            background: var(--rm-done);
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
        color: var(--rm-muted);

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
    border-top: 1px solid var(--rm-border);

    button {
      font-size: 13px;
      padding: 6px 12px;
    }
  }

  .roadmap-timeline-item-dates {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--rm-border);

    span {
      font-size: 12px;
      color: var(--rm-muted);
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
        background: var(--rm-surface);
        border: 3px solid var(--roadmap-accent);
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      &.active:before {
        background: var(--roadmap-accent);
        width: 24px;
        height: 24px;
      }

      .roadmap-timeline-marker-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--rm-muted);
        background: var(--rm-surface);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
<template>
  <div class="roadmap-changelog" :class="{ 'roadmap-dark': darkMode }">
    <div class="roadmap-changelog-container">
      <!-- Loading State -->
      <div v-if="loading" class="roadmap-changelog-loading">
        <LoadingSpinner />
        <p>Loading changelog...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="roadmap-changelog-error">
        <p>{{ error }}</p>
        <button @click="fetchChangelogs" class="roadmap-btn roadmap-btn-primary">
          Retry
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="changelogs.length === 0" class="roadmap-changelog-empty">
        <p>No changelog entries found.</p>
      </div>

      <!-- Changelog List -->
      <div v-else class="roadmap-changelog-list">
        <div
          v-for="entry in changelogs"
          :key="entry.id"
          class="roadmap-changelog-entry"
          :class="{ 'roadmap-changelog-entry-expanded': expandedEntries.includes(entry.id) }"
        >
          <!-- Entry Header -->
          <div
            class="roadmap-changelog-entry-header"
            @click="collapsible ? toggleEntry(entry.id) : null"
            :style="{ cursor: collapsible ? 'pointer' : 'default' }"
          >
            <div class="roadmap-changelog-entry-info">
              <h3 class="roadmap-changelog-version">
                <span v-if="collapsible" class="roadmap-changelog-toggle">
                  {{ expandedEntries.includes(entry.id) ? '▼' : '▶' }}
                </span>
                {{ entry.title || `v${entry.version}` }}
              </h3>
              <div class="roadmap-changelog-meta">
                <span v-if="entry.version" class="roadmap-changelog-version-badge">
                  v{{ entry.version }}
                </span>
                <span v-if="showDate && entry.formatted_date" class="roadmap-changelog-date">
                  {{ entry.formatted_date }}
                </span>
                <span
                  v-for="product in entry.products"
                  :key="product.id"
                  class="roadmap-changelog-product"
                >
                  {{ product.name }}
                </span>
              </div>
            </div>
            <div class="roadmap-changelog-entry-stats">
              <span class="roadmap-changelog-item-count">
                {{ entry.items.length }} {{ entry.items.length === 1 ? 'change' : 'changes' }}
              </span>
            </div>
          </div>

          <!-- Entry Content -->
          <Transition name="changelog-slide">
            <div
              v-show="!collapsible || expandedEntries.includes(entry.id)"
              class="roadmap-changelog-entry-content"
            >
              <!-- Description if any -->
              <div
                v-if="entry.content"
                class="roadmap-changelog-description"
                v-html="entry.content"
              ></div>

              <!-- Change Items -->
              <ul class="roadmap-changelog-items">
                <li
                  v-for="(item, index) in entry.items"
                  :key="index"
                  class="roadmap-changelog-item"
                >
                  <span
                    class="roadmap-changelog-badge"
                    :style="{ backgroundColor: item.type_color }"
                  >
                    {{ item.type_name }}
                  </span>
                  <span class="roadmap-changelog-item-text">{{ item.description }}</span>
                </li>
              </ul>
            </div>
          </Transition>
        </div>
      </div>

      <!-- Load More -->
      <div v-if="hasMore && !loading" class="roadmap-changelog-load-more">
        <button @click="loadMore" class="roadmap-btn roadmap-btn-secondary">
          Load More
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import LoadingSpinner from './LoadingSpinner.vue'

const props = defineProps({
  config: {
    type: Object,
    required: true
  },
  product: {
    type: String,
    default: ''
  },
  limit: {
    type: Number,
    default: 10
  },
  showDate: {
    type: Boolean,
    default: true
  },
  collapsible: {
    type: Boolean,
    default: false
  }
})

// State
const loading = ref(true)
const error = ref(null)
const changelogs = ref([])
const expandedEntries = ref([])
const currentPage = ref(1)
const totalEntries = ref(0)
const darkMode = ref(false)

// Computed
const hasMore = computed(() => changelogs.value.length < totalEntries.value)

// API helper
const apiCall = async (endpoint) => {
  const url = `${props.config.api_url}${endpoint}`
  const response = await fetch(url, {
    headers: {
      'X-WP-Nonce': props.config.nonce,
      'Content-Type': 'application/json'
    }
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({ message: 'Network error' }))
    throw new Error(errorData.message || 'API call failed')
  }

  return response.json()
}

// Methods
const fetchChangelogs = async () => {
  loading.value = true
  error.value = null

  try {
    const params = new URLSearchParams({
      limit: props.limit * currentPage.value
    })

    if (props.product) {
      params.append('product', props.product)
    }

    const response = await apiCall(`/changelog?${params}`)
    changelogs.value = response.changelogs || []
    totalEntries.value = response.total || 0

    // If collapsible, expand the first entry by default
    if (props.collapsible && changelogs.value.length > 0 && expandedEntries.value.length === 0) {
      expandedEntries.value = [changelogs.value[0].id]
    }
  } catch (err) {
    error.value = err.message || 'Failed to load changelog'
  } finally {
    loading.value = false
  }
}

const toggleEntry = (entryId) => {
  const index = expandedEntries.value.indexOf(entryId)
  if (index > -1) {
    expandedEntries.value.splice(index, 1)
  } else {
    expandedEntries.value.push(entryId)
  }
}

const loadMore = () => {
  currentPage.value++
  fetchChangelogs()
}

// Lifecycle
onMounted(() => {
  fetchChangelogs()
})

// Expose for external use
defineExpose({
  refresh: fetchChangelogs
})
</script>

<style lang="scss" scoped>
.roadmap-changelog {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
  line-height: 1.6;
}

.roadmap-changelog-container {
  max-width: 800px;
  margin: 0 auto;
}

.roadmap-changelog-loading,
.roadmap-changelog-error,
.roadmap-changelog-empty {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.roadmap-changelog-error {
  color: #dc3545;
}

.roadmap-changelog-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.roadmap-changelog-entry {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
  transition: box-shadow 0.2s ease;

  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
}

.roadmap-changelog-entry-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
  border-bottom: 1px solid #e0e0e0;
  user-select: none;

  &:hover {
    background: linear-gradient(135deg, #f0f1f2 0%, #f8f9fa 100%);
  }
}

.roadmap-changelog-entry-info {
  flex: 1;
}

.roadmap-changelog-version {
  margin: 0 0 8px;
  font-size: 20px;
  font-weight: 600;
  color: #1a1a1a;
  display: flex;
  align-items: center;
  gap: 8px;
}

.roadmap-changelog-toggle {
  font-size: 12px;
  color: #666;
  transition: transform 0.2s ease;
}

.roadmap-changelog-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.roadmap-changelog-version-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  background: #2271b1;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  border-radius: 20px;
}

.roadmap-changelog-date {
  font-size: 14px;
  color: #666;
}

.roadmap-changelog-product {
  display: inline-flex;
  align-items: center;
  padding: 3px 8px;
  background: #e8f0fe;
  color: #1967d2;
  font-size: 12px;
  font-weight: 500;
  border-radius: 4px;
}

.roadmap-changelog-entry-stats {
  text-align: right;
}

.roadmap-changelog-item-count {
  font-size: 13px;
  color: #888;
}

.roadmap-changelog-entry-content {
  padding: 24px;
}

.roadmap-changelog-description {
  margin-bottom: 20px;
  color: #444;
  font-size: 15px;
  line-height: 1.7;

  p:last-child {
    margin-bottom: 0;
  }
}

.roadmap-changelog-items {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.roadmap-changelog-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 16px;
  background: #f9fafb;
  border-radius: 8px;
  transition: background-color 0.2s ease;

  &:hover {
    background: #f3f4f6;
  }
}

.roadmap-changelog-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 90px;
  padding: 4px 12px;
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 4px;
  flex-shrink: 0;
}

.roadmap-changelog-item-text {
  color: #333;
  font-size: 14px;
  line-height: 1.5;
  flex: 1;
}

.roadmap-changelog-load-more {
  text-align: center;
  padding: 20px 0;

  .roadmap-btn {
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .roadmap-btn-secondary {
    background: #f0f0f0;
    color: #333;
    border: 1px solid #ddd;

    &:hover {
      background: #e5e5e5;
    }
  }
}

// Transitions
.changelog-slide-enter-active,
.changelog-slide-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.changelog-slide-enter-from {
  opacity: 0;
  max-height: 0;
}

.changelog-slide-enter-to {
  opacity: 1;
  max-height: 1000px;
}

.changelog-slide-leave-from {
  opacity: 1;
  max-height: 1000px;
}

.changelog-slide-leave-to {
  opacity: 0;
  max-height: 0;
}

// Dark mode
.roadmap-dark {
  .roadmap-changelog-entry {
    background: #23282d;
    border-color: #3c434a;
  }

  .roadmap-changelog-entry-header {
    background: linear-gradient(135deg, #32373c 0%, #23282d 100%);
    border-bottom-color: #3c434a;

    &:hover {
      background: linear-gradient(135deg, #3a4046 0%, #32373c 100%);
    }
  }

  .roadmap-changelog-version {
    color: #e2e4e7;
  }

  .roadmap-changelog-date,
  .roadmap-changelog-item-count {
    color: #a0a5aa;
  }

  .roadmap-changelog-description {
    color: #c3c4c7;
  }

  .roadmap-changelog-item {
    background: #32373c;

    &:hover {
      background: #3a4046;
    }
  }

  .roadmap-changelog-item-text {
    color: #d4d4d4;
  }

  .roadmap-changelog-loading,
  .roadmap-changelog-empty {
    color: #a0a5aa;
  }
}

// Responsive
@media (max-width: 768px) {
  .roadmap-changelog-entry-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 20px;
  }

  .roadmap-changelog-entry-stats {
    text-align: left;
  }

  .roadmap-changelog-version {
    font-size: 18px;
  }

  .roadmap-changelog-entry-content {
    padding: 16px 20px;
  }

  .roadmap-changelog-item {
    flex-direction: column;
    gap: 8px;
    padding: 10px 14px;
  }

  .roadmap-changelog-badge {
    min-width: auto;
    align-self: flex-start;
  }
}
</style>

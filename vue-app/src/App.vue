<template>
  <div class="roadmap-app" :class="{ 'roadmap-dark': darkMode, 'roadmap-pro': config.is_pro }">
    <div class="roadmap-container">
      <!-- Header -->
      <RoadmapHeader 
        v-model:mode="currentMode"
        v-model:search="searchQuery"
        :can-manage="canManage"
        :can-suggest="canSuggest"
        :enable-suggestions="config.settings.enable_suggestions"
        :pro-features="config.pro_features"
        :product-name="currentProductName"
        @toggle-dark-mode="toggleDarkMode"
        @suggest="openSuggestionModal"
        :show-title="config.show_title !== false"
      />
      
      <!-- Filters -->
      <RoadmapFilters
        v-if="config.show_filters !== false"
        v-model:status="selectedStatus"
        v-model:category="selectedCategory"
        v-model:product="selectedProduct"
        v-model:sortBy="selectedSort"
        :statuses="statuses"
        :categories="categories"
        :products="products"
        :is-pro="config.is_pro"
      />
      
      <!-- Main Content -->
      <div class="roadmap-content">
        <!-- Loading State -->
        <div v-if="loading" class="roadmap-loading">
          <LoadingSpinner />
          <p>{{ config.i18n.loading }}</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="roadmap-error">
          <p>{{ error }}</p>
          <button @click="fetchItems" class="roadmap-btn roadmap-btn-primary">
            {{ config.i18n.retry || 'Retry' }}
          </button>
        </div>
        
        <!-- Minimal View (Default for Free) -->
        <RoadmapMinimal
          v-if="viewMode === 'minimal'"
          :items="filteredItems"
          :statuses="statuses"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          :show-votes="config.settings.enable_voting !== false"
          :show-comments="config.settings.enable_comments !== false"
          @update-item="updateItem"
          @delete-item="deleteItem"
          @vote="submitVote"
          @comment="openCommentModal"
          @item-click="openDetailModal"
          @edit-item="openItemModal"
        />
        
        <!-- Board View (Pro) -->
        <RoadmapBoard
          v-else-if="viewMode === 'board' && config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          :show-votes="config.settings.enable_voting !== false"
          :show-comments="config.settings.enable_comments !== false"
          @update-item="updateItem"
          @delete-item="deleteItem"
          @vote="submitVote"
          @comment="openCommentModal"
          @item-click="openDetailModal"
          @edit-item="openItemModal"
        />
        
        <!-- Timeline View (Pro) -->
        <RoadmapTimeline
          v-else-if="viewMode === 'timeline' && config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :categories="categories"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          @item-click="openDetailModal"
          @update-item="updateItem"
          @edit-item="openItemModal"
          @delete-item="deleteItem"
        />
        
        <!-- List View (Pro) -->
        <RoadmapList
          v-else-if="viewMode === 'list' && config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :categories="categories"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          @item-click="openDetailModal"
          @update-item="updateItem"
          @edit-item="openItemModal"
          @delete-item="deleteItem"
        />
        
        <!-- Cards View (Pro) -->
        <RoadmapCards
          v-else-if="viewMode === 'cards' && config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :categories="categories"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          @item-click="openDetailModal"
          @update-item="updateItem"
          @edit-item="openItemModal"
          @delete-item="deleteItem"
        />

        <!-- Idea Box View (Pro) -->
        <RoadmapIdeaBox
          v-else-if="viewMode === 'ideabox' && config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :categories="categories"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          @item-click="openDetailModal"
          @vote="submitVote"
          @edit-item="openItemModal"
          @delete-item="deleteItem"
          @update-item="updateItem"
        />

        <!-- Fallback to Minimal for Pro templates when not Pro -->
        <RoadmapMinimal
          v-else-if="['grid', 'timeline', 'list', 'cards', 'board', 'ideabox'].includes(viewMode) && !config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          :show-votes="config.settings.enable_voting !== false"
          :show-comments="config.settings.enable_comments !== false"
          @update-item="updateItem"
          @delete-item="deleteItem"
          @vote="submitVote"
          @comment="openCommentModal"
          @item-click="openDetailModal"
          @edit-item="openItemModal"
        />
        
        <!-- No Items -->
        <div v-else-if="filteredItems.length === 0" class="roadmap-empty">
          <p>{{ config.i18n.no_items }}</p>
          <button
            v-if="canEdit && currentMode === 'manage'"
            @click="openItemModal()"
            class="roadmap-btn roadmap-btn-primary"
          >
            {{ config.i18n.add_item }}
          </button>
        </div>

        <!-- Load more -->
        <div
          v-if="!loading && totalItems > items.length"
          class="roadmap-load-more"
          style="text-align:center; margin:24px 0;"
        >
          <p class="roadmap-load-more__count" style="margin:0 0 8px; opacity:.7;">
            {{ items.length }} / {{ totalItems }}
          </p>
          <button
            @click="loadMore"
            :disabled="loadingMore"
            class="roadmap-btn roadmap-btn-primary roadmap-load-more__btn"
          >
            {{ loadingMore ? (config.i18n.loading || 'Loading…') : (config.i18n.load_more || 'Load more') }}
          </button>
        </div>
      </div>

      <!-- Footer Actions -->
      <RoadmapFooter
        v-if="config.settings.enable_suggestions && canSuggest"
        @suggest="openSuggestionModal"
      />
    </div>
    
    <!-- Modals -->
    <ItemModal
      v-if="showItemModal"
      :item="editingItem"
      :statuses="statuses"
      :categories="categories"
      :is-pro="config.is_pro"
      @save="saveItem"
      @close="closeItemModal"
    />
    
    <CommentModal
      v-if="showCommentModal"
      :item="commentingItem"
      @submit="submitComment"
      @close="closeCommentModal"
    />
    
    <SuggestionModal
      v-if="showSuggestionModal"
      :categories="categories"
      @submit="submitSuggestion"
      @close="closeSuggestionModal"
    />
    
    <ItemDetailModal
      v-if="showDetailModal"
      :item="detailItem"
      @vote="handleDetailVote"
      @comment="handleDetailComment"
      @close="closeDetailModal"
    />
    
    <!-- Simple Notification -->
    <Transition name="notification">
      <div 
        v-if="notification.show"
        class="roadmap-notification"
        :class="`roadmap-notification-${notification.type}`"
      >
        {{ notification.message }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, provide } from 'vue'

// Components
import RoadmapHeader from '@/components/RoadmapHeader.vue'
import RoadmapFilters from '@/components/RoadmapFilters.vue'
import RoadmapBoard from '@/components/RoadmapBoard.vue'
import RoadmapTimeline from '@/components/RoadmapTimeline.vue'
import RoadmapList from '@/components/RoadmapList.vue'
import RoadmapCards from '@/components/RoadmapCards.vue'
import RoadmapMinimal from '@/components/RoadmapMinimal.vue'
import RoadmapIdeaBox from '@/components/RoadmapIdeaBox.vue'
import RoadmapFooter from '@/components/RoadmapFooter.vue'
import ItemModal from '@/components/ItemModal.vue'
import CommentModal from '@/components/CommentModal.vue'
import SuggestionModal from '@/components/SuggestionModal.vue'
import ItemDetailModal from '@/components/ItemDetailModal.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

// Props
const props = defineProps({
  config: {
    type: Object,
    required: true
  }
})

// Provide config to child components
provide('$roadmapConfig', props.config)

// State
const loading = ref(true)
const loadingMore = ref(false)
const error = ref(null)
const items = ref([])
const currentPage = ref(1)
const totalItems = ref(0)
const totalPages = ref(0)
// Bumped on every fresh fetch (e.g. product/filter change) so a slow in-flight
// loadMore response for the previous product is discarded instead of appended.
const fetchGeneration = ref(0)
const statuses = ref([])
const categories = ref([])
const products = ref([])

// UI State
const currentMode = ref(props.config.mode || 'view')
const viewMode = ref(props.config.template || 'minimal')
const darkMode = ref(false)
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedCategory = ref('')
const selectedProduct = ref(props.config.product || '')
const selectedSort = ref(props.config.default_sort || '')

// Modal State
const showItemModal = ref(false)
const editingItem = ref(null)
const showCommentModal = ref(false)
const commentingItem = ref(null)
const showSuggestionModal = ref(false)
const showDetailModal = ref(false)
const detailItem = ref(null)

// Notification state
const notification = ref({
  show: false,
  message: '',
  type: 'success'
})

// Computed
const canEdit = computed(() => props.config.user?.can_edit || false)
const canManage = computed(() => props.config.user?.can_manage || false)
const canSuggest = computed(() => {
  // Logged-in users can suggest if they have permission
  if (props.config.user?.can_suggest) {
    return true
  }

  // Guest users can suggest if Pro is active and guest suggestions are enabled
  if (!props.config.user && props.config.is_pro && props.config.settings?.enable_guest_suggestions) {
    return true
  }

  return false
})

const currentProductName = computed(() => {
  // Get the current product
  const productSlug = selectedProduct.value || props.config.product
  
  // Find the product in the products list
  const product = products.value.find(p => p.slug === productSlug)
  
  if (product) {
    return product.name
  }
  
  // Default names for common products
  if (productSlug === 'main') {
    return 'Main'
  }
  
  // Fallback: capitalize the slug
  return productSlug.charAt(0).toUpperCase() + productSlug.slice(1)
})

const filteredItems = computed(() => {
  // The server applies product, status, category, search and sort, so this is
  // just the loaded list. Kept as a computed so the templates that bind to it
  // do not all have to change.
  return items.value
})

const apiCall = async (endpoint, options = {}) => {
  const url = `${props.config.api_url}${endpoint}`
  const defaultOptions = {
    headers: {
      'X-WP-Nonce': props.config.nonce,
      'Content-Type': 'application/json'
    }
  }

  const response = await fetch(url, {
    ...defaultOptions,
    ...options,
    headers: {
      ...defaultOptions.headers,
      ...options.headers
    }
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: props.config.i18n.network_error || 'Network error' }))
    throw new Error(error.message || props.config.i18n.api_call_failed || 'API call failed')
  }

  return response.json()
}

// Methods
const fetchItemsPage = async (page) => {
  // Filters and sort go to the server. Filtering the loaded page in the browser
  // silently produced partial results on any board larger than one page - a
  // "Completed" filter showed only the completed items that happened to be in
  // the first 100, with no indication anything was missing.
  const params = new URLSearchParams({
    product: selectedProduct.value || props.config.product,
    per_page: props.config.settings?.items_per_page || 100,
    page: page
  })
  if (selectedStatus.value) params.set('status', selectedStatus.value)
  if (selectedCategory.value) params.set('category', selectedCategory.value)
  if (searchQuery.value) params.set('search', searchQuery.value)
  if (selectedSort.value) params.set('sort', selectedSort.value)
  return await apiCall(`/items?${params}`)
}

const fetchItems = async () => {
  loading.value = true
  error.value = null
  currentPage.value = 1
  const gen = ++fetchGeneration.value

  try {
    const response = await fetchItemsPage(1)
    if (gen !== fetchGeneration.value) return // superseded by a newer fetch
    items.value = response.items || []
    totalItems.value = response.total ?? items.value.length
    totalPages.value = response.pages ?? 1
  } catch (err) {
    if (gen === fetchGeneration.value) error.value = err.message || props.config.i18n.error
  } finally {
    if (gen === fetchGeneration.value) loading.value = false
  }
}

// Append the next page of items (Load more), preserving the loaded list.
const loadMore = async () => {
  if (loadingMore.value || items.value.length >= totalItems.value) {
    return
  }
  loadingMore.value = true
  const gen = fetchGeneration.value
  try {
    const next = currentPage.value + 1
    const response = await fetchItemsPage(next)
    if (gen !== fetchGeneration.value) return // product/filter changed mid-flight; discard
    const fetched = response.items || []
    if (fetched.length) {
      items.value = [...items.value, ...fetched]
      currentPage.value = next
      totalItems.value = response.total ?? totalItems.value
      totalPages.value = response.pages ?? totalPages.value
    }
    // Stop showing the button once the server runs out of rows, even if the
    // reported total disagrees with the number of rows actually returned
    // (deleted items, or a pro filter that makes count and rows disagree).
    const perPage = props.config.settings?.items_per_page || 100
    if (fetched.length < perPage || next >= totalPages.value) {
      totalItems.value = items.value.length
    }
  } catch (err) {
    if (gen === fetchGeneration.value) error.value = err.message || props.config.i18n.error
  } finally {
    loadingMore.value = false
  }
}

const fetchSettings = async () => {
  try {
    const response = await apiCall('/settings')
    statuses.value = response.statuses || []
    categories.value = response.categories || []
    
    if (props.config.is_pro && response.products) {
      products.value = response.products
    } else {
      // For free version, ensure we have at least the main product
      // This will be fetched from server but we can provide a fallback
      products.value = response.products || []
    }
  } catch (err) {
    // Error fetching settings
  }
}

const updateItem = async (itemId, updates) => {
  try {
    const response = await apiCall(`/items/${itemId}`, {
      method: 'PUT',
      body: JSON.stringify(updates)
    })
    const index = items.value.findIndex(item => item.id === itemId)
    if (index !== -1) {
      items.value[index] = response
    }
    showNotification(props.config.i18n.item_updated)
  } catch (err) {
    showNotification(err.message || props.config.i18n.error, 'error')
  }
}

const deleteItem = async (itemId) => {
  if (!confirm(props.config.i18n.confirm_delete)) {
    return
  }
  
  try {
    await apiCall(`/items/${itemId}`, {
      method: 'DELETE'
    })
    items.value = items.value.filter(item => item.id !== itemId)
    showNotification(props.config.i18n.item_deleted)
  } catch (err) {
    showNotification(err.message || props.config.i18n.error, 'error')
  }
}

const saveItem = async (itemData) => {
  try {
    // Add current product to item data
    const dataToSave = {
      ...itemData,
      product: selectedProduct.value || props.config.product
    }
    
    if (itemData.id) {
      // Update existing
      const response = await apiCall(`/items/${itemData.id}`, {
        method: 'PUT',
        body: JSON.stringify(dataToSave)
      })
      const index = items.value.findIndex(item => item.id === itemData.id)
      if (index !== -1) {
        items.value[index] = response
      }
      showNotification(props.config.i18n.item_updated)
    } else {
      // Create new
      const response = await apiCall('/items', {
        method: 'POST',
        body: JSON.stringify(dataToSave)
      })
      items.value.unshift(response)
      showNotification(props.config.i18n.item_added)
    }
    closeItemModal()
  } catch (err) {
    showNotification(err.message || props.config.i18n.error, 'error')
  }
}

const submitVote = async (itemId, voteValue) => {
  // Gate on the capability, not on the presence of a user. The server has
  // always accepted guest votes when allow_guest_voting is on (can_vote() in
  // class-roadmap-api.php); this guard was blocking the request before the
  // setting was ever consulted, so the feature was unreachable.
  if (!props.config.user && !props.config.settings?.allow_guest_voting) {
    showNotification(props.config.i18n.login_required)
    return
  }
  
  try {
    const response = await apiCall('/vote', {
      method: 'POST',
      body: JSON.stringify({
        item_id: itemId,
        vote_value: voteValue
      })
    })
    
    // Update local vote count
    const item = items.value.find(i => i.id === itemId)
    if (item) {
      item.votes = response.vote_count
      item.user_voted = voteValue
    }
    
    showNotification(
      voteValue === 0
        ? (props.config.i18n.vote_removed || 'Your vote has been removed')
        : props.config.i18n.vote_submitted,
      'success'
    )
  } catch (err) {
    showNotification(err.message || props.config.i18n.error, 'error')
  }
}

const submitComment = async (commentData) => {
  try {
    await apiCall('/comment', {
      method: 'POST',
      body: JSON.stringify(commentData)
    })
    
    // Update local comment count
    const item = items.value.find(i => i.id === commentData.item_id)
    if (item) {
      item.comment_count++
    }
    
    showNotification(props.config.i18n.comment_submitted)
    closeCommentModal()
  } catch (err) {
    showNotification(err.message || props.config.i18n.error, 'error')
  }
}

const submitSuggestion = async (suggestionData) => {
  try {
    // Check if user is logged in or guest
    const isGuest = !props.config.user || !props.config.user.ID
    const isPro = props.config.is_pro
    const guestSuggestionsEnabled = props.config.settings?.enable_guest_suggestions

    let endpoint = '/suggest'
    let payload = {
      title: suggestionData.title,
      description: suggestionData.description,
      category: suggestionData.category || '',
      priority: suggestionData.priority || 'medium',
      // Honeypot value (empty for real users) — the server rejects if filled.
      website: suggestionData.website || '',
      // Associate the suggestion with the product whose roadmap it was made on.
      product: selectedProduct.value || props.config.product || 'main'
    }

    // If guest and Pro with guest suggestions enabled
    if (isGuest && isPro && guestSuggestionsEnabled) {
      endpoint = '/guest-suggest'
      payload.guest_name = suggestionData.guest_name || ''
      payload.guest_email = suggestionData.guest_email || ''
      payload.product = props.config.product || 'main'
    } else if (isGuest) {
      // Guest but no Pro or guest suggestions not enabled
      showNotification(props.config.i18n.login_required || 'Please login to submit suggestions', 'error')
      return
    }

    const response = await apiCall(endpoint, {
      method: 'POST',
      body: JSON.stringify(payload)
    })

    showNotification(response.message || props.config.i18n.suggestion_submitted || 'Your feature suggestion has been submitted!', 'success')
    closeSuggestionModal()

    // Only refresh if user can view pending items (admin)
    if (props.config.user?.can_edit) {
      await fetchItems()
    }
  } catch (err) {
    showNotification(err.message || err.response?.data?.message || props.config.i18n.error, 'error')
  }
}

// Modal handlers
const openItemModal = (item = null) => {
  editingItem.value = item
  showItemModal.value = true
}

const closeItemModal = () => {
  editingItem.value = null
  showItemModal.value = false
}

const openCommentModal = (item) => {
  commentingItem.value = item
  showCommentModal.value = true
}

const closeCommentModal = () => {
  commentingItem.value = null
  showCommentModal.value = false
}

const openSuggestionModal = () => {
  showSuggestionModal.value = true
}

const closeSuggestionModal = () => {
  showSuggestionModal.value = false
}

const openDetailModal = (item) => {
  detailItem.value = item
  showDetailModal.value = true
}

const closeDetailModal = () => {
  detailItem.value = null
  showDetailModal.value = false
}

const handleDetailVote = async () => {
  if (detailItem.value) {
    await submitVote(detailItem.value.id, detailItem.value.user_voted ? 0 : 1)
    // Update the detail item
    const updatedItem = items.value.find(i => i.id === detailItem.value.id)
    if (updatedItem) {
      detailItem.value = updatedItem
    }
  }
}

const handleDetailComment = () => {
  closeDetailModal()
  openCommentModal(detailItem.value)
}

const toggleDarkMode = () => {
  darkMode.value = !darkMode.value
}

// Show notification helper
const showNotification = (message, type = 'success') => {
  notification.value = {
    show: true,
    message,
    type
  }
  
  // Auto hide after 3 seconds
  setTimeout(() => {
    notification.value.show = false
  }, 3000)
}

// Watchers
watch(selectedProduct, () => {
  if (props.config.is_pro) {
    fetchItems()
  }
})

// Status, category and sort are server-side now, so changing one has to
// re-query rather than re-filter what is already loaded.
watch([selectedStatus, selectedCategory, selectedSort], () => {
  fetchItems()
})

// Search is debounced so typing does not fire a request per keystroke.
let searchDebounceTimer = null
watch(searchQuery, () => {
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    searchDebounceTimer = null
    fetchItems()
  }, 350)
})

// Open single item for editing
const openSingleItemModal = async (itemId) => {
  // Fetch the item data if not already loaded
  if (!items.value.length) {
    await fetchItems()
  }
  
  const item = items.value.find(i => i.id === parseInt(itemId))
  if (item) {
    openItemModal(item)
  } else {
    // If item not in current list, fetch it directly
    try {
      const response = await apiCall(`/items/${itemId}`)
      if (response) {
        openItemModal(response)
      }
    } catch (error) {
      showNotification(props.config.i18n.load_item_failed || 'Failed to load item for editing', 'error')
    }
  }
}

// Lifecycle
onMounted(() => {
  fetchSettings()
  fetchItems()
  
  // Check if we should open an item for editing on mount
  if (props.config.single_edit_mode && props.config.edit_item_id) {
    setTimeout(() => {
      openSingleItemModal(props.config.edit_item_id)
    }, 500)
  }
})

// Expose for external use
defineExpose({
  refresh: fetchItems,
  openItemModal,
  openSuggestionModal,
  openSingleItemModal
})
</script>

<style lang="scss">
@use './assets/css/main.scss' as *;

// Professional notification styles
.roadmap-notification {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0.8);
  padding: 20px 30px;
  background: #fff;
  color: #333;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  font-size: 16px;
  font-weight: 500;
  text-align: center;
  min-width: 250px;
  z-index: 99999;
  
  &-success {
    color: #4caf50;
    border: 2px solid #4caf50;
    
    &::before {
      content: "✓ ";
      font-size: 20px;
      font-weight: bold;
      margin-right: 8px;
    }
  }
  
  &-error {
    color: #f44336;
    border: 2px solid #f44336;
    
    &::before {
      content: "✕ ";
      font-size: 20px;
      font-weight: bold;
      margin-right: 8px;
    }
  }
  
  &-warning {
    color: #ff9800;
    border: 2px solid #ff9800;
    
    &::before {
      content: "⚠ ";
      font-size: 20px;
      font-weight: bold;
      margin-right: 8px;
    }
  }
  
  &-info {
    color: #2196f3;
    border: 2px solid #2196f3;
    
    &::before {
      content: "ℹ ";
      font-size: 20px;
      font-weight: bold;
      margin-right: 8px;
    }
  }
}

// Notification transition
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  transform: translate(-50%, -50%) scale(0.5);
  opacity: 0;
}

.notification-leave-to {
  transform: translate(-50%, -50%) scale(0.5);
  opacity: 0;
}
</style>
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
      />
      
      <!-- Filters -->
      <RoadmapFilters
        v-model:status="selectedStatus"
        v-model:category="selectedCategory"
        v-model:product="selectedProduct"
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
            Retry
          </button>
        </div>
        
        <!-- Board View -->
        <RoadmapBoard
          v-if="viewMode === 'board'"
          :items="filteredItems"
          :statuses="statuses"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
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
        />
        
        <!-- Minimal View (Pro) -->
        <RoadmapMinimal
          v-else-if="viewMode === 'minimal' && config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :categories="categories"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
          @item-click="openDetailModal"
          @update-item="updateItem"
        />
        
        <!-- Fallback to Board for Pro templates when not Pro -->
        <RoadmapBoard
          v-else-if="['timeline', 'list', 'cards', 'minimal'].includes(viewMode) && !config.is_pro"
          :items="filteredItems"
          :statuses="statuses"
          :mode="currentMode"
          :can-edit="canEdit"
          :pro-features="config.pro_features"
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
const error = ref(null)
const items = ref([])
const statuses = ref([])
const categories = ref([])
const products = ref([])

// UI State
const currentMode = ref(props.config.mode || 'view')
const viewMode = ref(props.config.template || 'board')
const darkMode = ref(false)
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedCategory = ref('')
const selectedProduct = ref(props.config.product || '')

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
const canSuggest = computed(() => props.config.user?.can_suggest || false)

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
  let filtered = items.value
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(item => 
      item.title.toLowerCase().includes(query) ||
      item.content.toLowerCase().includes(query)
    )
  }
  
  // Status filter
  if (selectedStatus.value) {
    filtered = filtered.filter(item => 
      item.status.some(s => s.slug === selectedStatus.value)
    )
  }
  
  // Category filter
  if (selectedCategory.value) {
    filtered = filtered.filter(item => 
      item.categories.some(c => c.slug === selectedCategory.value)
    )
  }
  
  // Product filter (Pro)
  if (selectedProduct.value && props.config.is_pro) {
    filtered = filtered.filter(item => 
      item.product && Array.isArray(item.product) && 
      item.product.some(p => p.slug === selectedProduct.value)
    )
  }
  
  return filtered
})

// API setup
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
    const error = await response.json().catch(() => ({ message: 'Network error' }))
    throw new Error(error.message || 'API call failed')
  }
  
  return response.json()
}

// Methods
const fetchItems = async () => {
  loading.value = true
  error.value = null
  
  try {
    const params = new URLSearchParams({
      product: selectedProduct.value || props.config.product
    })
    
    const response = await apiCall(`/items?${params}`)
    items.value = response.items || []
    
    // Items fetched successfully
  } catch (err) {
    error.value = err.message || props.config.i18n.error
  } finally {
    loading.value = false
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
  if (!props.config.user) {
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
    
    showNotification(props.config.i18n.vote_submitted, 'success')
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
    // Find category ID from slug
    let categoryIds = []
    if (suggestionData.category) {
      const category = categories.value.find(c => c.slug === suggestionData.category)
      if (category) {
        categoryIds = [category.id]
      }
    }
    
    // Create a roadmap item with "suggested" or "under-review" status
    const itemData = {
      title: suggestionData.title,
      content: suggestionData.description,
      status: 'under-review', // This status already exists from our earlier setup
      categories: categoryIds,
      product: props.config.product || 'main',
      priority: 'medium'
    }
    
    await saveItem(itemData)
    showNotification('Your feature suggestion has been submitted!', 'success')
    closeSuggestionModal()
    
    // Refresh the items to show the new suggestion
    await fetchItems()
  } catch (err) {
    showNotification(err.response?.data?.message || props.config.i18n.error, 'error')
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
      showNotification('Failed to load item for editing', 'error')
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
@import './assets/css/main.scss';

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
<template>
  <div class="roadmap-board">
    <div class="roadmap-board-columns">
      <div 
        v-for="status in statuses"
        :key="status.id"
        class="roadmap-board-column"
      >
        <div class="roadmap-board-header">
          <h3>{{ status.name }}</h3>
          <span class="roadmap-board-count">{{ getItemsByStatus(status.slug).length }}</span>
        </div>
        
        <div 
          class="roadmap-board-items"
          :data-status="status.slug"
          @drop="handleDrop($event, status.slug)"
          @dragover.prevent
          @dragenter.prevent
        >
          <RoadmapItem
            v-for="item in getItemsByStatus(status.slug)"
            :key="item.id"
            :item="item"
            :mode="mode"
            :can-edit="canEdit"
            :draggable="mode === 'manage' && proFeatures?.dragdrop_enabled"
            @update="$emit('update-item', item.id, $event)"
            @delete="$emit('delete-item', item.id)"
            @vote="$emit('vote', item.id, $event)"
            @comment="$emit('comment', item)"
            @dragstart="handleDragStart($event, item)"
            @click="$emit('item-click', item)"
            @edit="$emit('edit-item', item)"
          />
          
          <div 
            v-if="getItemsByStatus(status.slug).length === 0"
            class="roadmap-board-empty"
          >
            <p class="roadmap-board-empty-text">No items in {{ status.name.toLowerCase() }}</p>
            <p class="roadmap-board-empty-hint">
              <template v-if="mode === 'manage' && canEdit">
                Drag items here or create new ones
              </template>
              <template v-else>
                Items will appear here when added
              </template>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import RoadmapItem from './RoadmapItem.vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  statuses: {
    type: Array,
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
  proFeatures: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update-item', 'delete-item', 'vote', 'comment', 'item-click', 'edit-item'])

// Methods
const getItemsByStatus = (statusSlug) => {
  const filtered = props.items.filter(item => 
    item.status && Array.isArray(item.status) && item.status.some(s => s.slug === statusSlug)
  )
  
  // Debug logging
  if (statusSlug === 'planned' && props.items.length > 0) {
    // Filtering items by status
  }
  
  return filtered
}

// Drag and Drop handlers
let draggedItem = null

const handleDragStart = (event, item) => {
  draggedItem = item
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/html', event.target.innerHTML)
}

const handleDrop = (event, newStatus) => {
  event.preventDefault()
  
  if (!draggedItem || !props.proFeatures?.dragdrop_enabled) {
    return
  }
  
  // Emit update event to change item status
  emit('update-item', draggedItem.id, { status: newStatus })
  
  draggedItem = null
}
</script>

<style lang="scss" scoped>
.roadmap-board {
  &-columns {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
  }
  
  &-column {
    background: var(--rm-surface-alt);
    border-radius: 8px;
    padding: 15px;
    min-height: 400px;
  }
  
  &-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    
    h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
    }
  }
  
  &-count {
    background: var(--rm-border);
    color: var(--rm-muted);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
  }
  
  &-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 300px;
    
    &.drag-over {
      background: var(--roadmap-accent-tint);
      border: 2px dashed var(--roadmap-accent);
      border-radius: 4px;
    }
  }
  
  &-empty {
    text-align: center;
    padding: 60px 20px;
    
    &-text {
      margin: 0 0 8px 0;
      font-size: 16px;
      color: var(--rm-muted);
      font-weight: 500;
    }

    &-hint {
      margin: 0;
      font-size: 14px;
      color: var(--rm-muted);
    }
  }
}
</style>
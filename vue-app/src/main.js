import { createApp } from 'vue'
import App from './App.vue'
import './assets/css/main.scss'

// This will be the global mount function for WordPress
window.RoadmapApp = {
  mount(container, config) {
    const app = createApp(App, {
      config: config
    })
    
    // Make config globally available
    app.config.globalProperties.$roadmapConfig = config
    
    const instance = app.mount(container)
    
    // Store the instance for external access
    container.__vue_app__ = instance
    
    return app
  }
}

// Development mode - mount to #app
if (import.meta.env.DEV) {
  const devConfig = {
    api_url: '/wp-json/roadmap/v1',
    nonce: 'dev-nonce',
    mode: 'view',
    allow_frontend_edit: true,
    product: 'main',
    is_pro: true,
    settings: {
      enable_voting: true,
      enable_comments: true,
      enable_suggestions: true,
      items_per_page: 100,
    },
    user: {
      id: 1,
      name: 'Dev User',
      email: 'dev@example.com',
      can_vote: true,
      can_comment: true,
      can_suggest: true,
      can_edit: true,
      can_manage: true,
    },
    i18n: {
      loading: 'Loading...',
      error: 'An error occurred',
      no_items: 'No roadmap items found',
      vote: 'Vote',
      votes: 'votes',
      comment: 'Comment',
      comments: 'comments',
      suggest: 'Suggest Feature',
      search: 'Search...',
      filter_by_status: 'Filter by Status',
      filter_by_category: 'Filter by Category',
      all_statuses: 'All Statuses',
      all_categories: 'All Categories',
      view_mode: 'View Mode',
      manage_mode: 'Manage Mode',
      add_item: 'Add Item',
      edit_item: 'Edit Item',
      delete_item: 'Delete Item',
      save: 'Save',
      cancel: 'Cancel',
      confirm_delete: 'Are you sure you want to delete this item?',
      item_added: 'Item added successfully',
      item_updated: 'Item updated successfully',
      item_deleted: 'Item deleted successfully',
      vote_submitted: 'Your vote has been submitted',
      comment_submitted: 'Your comment has been submitted',
      suggestion_submitted: 'Your suggestion has been submitted',
      login_required: 'Please login to perform this action',
      title: 'Title',
      description: 'Description',
      status: 'Status',
      category: 'Category',
      priority: 'Priority',
      target_date: 'Target Date',
      low: 'Low',
      medium: 'Medium',
      high: 'High',
      critical: 'Critical',
    },
    pro_features: {
      timeline_enabled: true,
      dragdrop_enabled: true,
      export_enabled: true,
      analytics_enabled: true,
      timeline_default_view: 'month',
    },
    products: [],
  }
  
  window.RoadmapApp.mount('#app', devConfig)
}
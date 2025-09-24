/**
 * Roadmap Single Item Edit Handler
 * 
 * This script handles the edit functionality for single roadmap items
 */
(function($) {
    'use strict';
    
    // Wait for DOM ready
    $(document).ready(function() {
        
        // Initialize edit functionality
        function initEditHandler() {
            
            // Check if edit button exists
            var $editBtn = $('.roadmap-edit-btn');
            if (!$editBtn.length) {
                return;
            }
            
            
            // Handle edit button click
            $editBtn.on('click', function(e) {
                e.preventDefault();
                
                var itemId = $(this).data('item-id');
                
                // Get container
                var container = document.getElementById('roadmap-edit-app');
                if (!container) {
                    alert('Edit functionality not available. Container missing.');
                    return;
                }
                
                
                // Check if RoadmapApp exists
                if (!window.RoadmapApp) {
                    alert('Edit functionality not available. Please refresh the page.');
                    return;
                }
                
                
                // Initialize or use existing app
                if (!container._vueAppMounted) {
                    
                    try {
                        // Parse config
                        var configStr = container.dataset.config;
                        if (!configStr) {
                            throw new Error('No config data found');
                        }
                        
                        var config = JSON.parse(configStr);
                        
                        // Mount the app
                        var app = window.RoadmapApp.mount(container, config);
                        container._vueAppMounted = true;
                        
                        // Show container
                        container.style.display = 'block';
                        
                        // Try to open modal after mount
                        setTimeout(function() {
                            tryOpenModal(container, itemId);
                        }, 200);
                        
                    } catch (error) {
                        alert('Failed to initialize editor: ' + error.message);
                    }
                } else {
                    tryOpenModal(container, itemId);
                }
            });
        }
        
        // Try to open the edit modal
        function tryOpenModal(container, itemId) {
            
            // Multiple ways to access the Vue instance
            var instance = container.__vue_app__ || 
                          container._instance || 
                          (container._vueApp && container._vueApp._instance);
            
            if (!instance) {
                alert('Unable to open editor. Please refresh and try again.');
                return;
            }
            
            
            // Try to call the method
            if (instance.openSingleItemModal) {
                instance.openSingleItemModal(itemId);
            } else if (instance.$refs && instance.$refs.app && instance.$refs.app.openSingleItemModal) {
                instance.$refs.app.openSingleItemModal(itemId);
            } else {
                alert('Edit functionality not properly initialized.');
            }
        }
        
        // Initialize
        initEditHandler();
        
    });
    
})(jQuery);
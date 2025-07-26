/**
 * Roadmap Single Item Edit Handler
 * 
 * This script handles the edit functionality for single roadmap items
 */
(function($) {
    'use strict';
    
    // Wait for DOM ready
    $(document).ready(function() {
        // Debug helper
        function debugLog(message, data) {
            if (window.console && window.console.log) {
                console.log('[Roadmap Edit]', message, data || '');
            }
        }
        
        // Initialize edit functionality
        function initEditHandler() {
            debugLog('Initializing edit handler');
            
            // Check if edit button exists
            var $editBtn = $('.roadmap-edit-btn');
            if (!$editBtn.length) {
                debugLog('No edit button found');
                return;
            }
            
            debugLog('Edit button found', $editBtn);
            
            // Handle edit button click
            $editBtn.on('click', function(e) {
                e.preventDefault();
                debugLog('Edit button clicked');
                
                var itemId = $(this).data('item-id');
                debugLog('Item ID:', itemId);
                
                // Get container
                var container = document.getElementById('roadmap-edit-app');
                if (!container) {
                    debugLog('ERROR: No Vue container found');
                    alert('Edit functionality not available. Container missing.');
                    return;
                }
                
                debugLog('Container found', container);
                
                // Check if RoadmapApp exists
                if (!window.RoadmapApp) {
                    debugLog('ERROR: RoadmapApp not loaded');
                    alert('Edit functionality not available. Please refresh the page.');
                    return;
                }
                
                debugLog('RoadmapApp available');
                
                // Initialize or use existing app
                if (!container._vueAppMounted) {
                    debugLog('Mounting new Vue app instance');
                    
                    try {
                        // Parse config
                        var configStr = container.dataset.config;
                        if (!configStr) {
                            throw new Error('No config data found');
                        }
                        
                        var config = JSON.parse(configStr);
                        debugLog('Config parsed', config);
                        
                        // Mount the app
                        var app = window.RoadmapApp.mount(container, config);
                        container._vueAppMounted = true;
                        debugLog('Vue app mounted successfully');
                        
                        // Show container
                        container.style.display = 'block';
                        
                        // Try to open modal after mount
                        setTimeout(function() {
                            tryOpenModal(container, itemId);
                        }, 200);
                        
                    } catch (error) {
                        debugLog('ERROR mounting app:', error);
                        alert('Failed to initialize editor: ' + error.message);
                    }
                } else {
                    debugLog('Using existing Vue app instance');
                    tryOpenModal(container, itemId);
                }
            });
        }
        
        // Try to open the edit modal
        function tryOpenModal(container, itemId) {
            debugLog('Trying to open modal for item:', itemId);
            
            // Multiple ways to access the Vue instance
            var instance = container.__vue_app__ || 
                          container._instance || 
                          (container._vueApp && container._vueApp._instance);
            
            if (!instance) {
                debugLog('ERROR: No Vue instance found on container');
                debugLog('Container properties:', Object.keys(container));
                alert('Unable to open editor. Please refresh and try again.');
                return;
            }
            
            debugLog('Vue instance found', instance);
            
            // Try to call the method
            if (instance.openSingleItemModal) {
                debugLog('Calling openSingleItemModal');
                instance.openSingleItemModal(itemId);
            } else if (instance.$refs && instance.$refs.app && instance.$refs.app.openSingleItemModal) {
                debugLog('Calling via $refs.app');
                instance.$refs.app.openSingleItemModal(itemId);
            } else {
                debugLog('ERROR: openSingleItemModal method not found');
                debugLog('Available methods:', Object.keys(instance).filter(k => typeof instance[k] === 'function'));
                alert('Edit functionality not properly initialized.');
            }
        }
        
        // Initialize
        initEditHandler();
        
        // Also expose globally for debugging
        window.RoadmapEditDebug = {
            initEditHandler: initEditHandler,
            tryOpenModal: tryOpenModal,
            getContainer: function() {
                return document.getElementById('roadmap-edit-app');
            },
            getVueInstance: function() {
                var container = this.getContainer();
                return container && (container.__vue_app__ || container._instance);
            }
        };
    });
    
})(jQuery);
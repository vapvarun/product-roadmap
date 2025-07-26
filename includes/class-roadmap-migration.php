<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Migration {
    
    /**
     * Run migration to convert product meta to taxonomy terms
     */
    public static function migrate_products_to_taxonomy() {
        // Get all roadmap items
        $items = get_posts([
            'post_type' => 'roadmap_item',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ]);
        
        $migrated = 0;
        $errors = 0;
        
        foreach ($items as $item) {
            // Get the product from meta
            $product_meta = get_post_meta($item->ID, '_roadmap_product', true);
            
            if (empty($product_meta)) {
                continue;
            }
            
            // Check if this item already has a product term
            $existing_terms = wp_get_object_terms($item->ID, 'roadmap_product');
            
            if (!empty($existing_terms)) {
                // Already has a term, skip
                continue;
            }
            
            // Get or create the term
            $term = get_term_by('slug', $product_meta, 'roadmap_product');
            
            if (!$term) {
                // Create the term
                $term_data = wp_insert_term(
                    ucfirst($product_meta),
                    'roadmap_product',
                    ['slug' => $product_meta]
                );
                
                if (is_wp_error($term_data)) {
                    $errors++;
                    continue;
                }
                
                $term_id = $term_data['term_id'];
            } else {
                $term_id = $term->term_id;
            }
            
            // Assign the term to the post
            $result = wp_set_object_terms($item->ID, (int)$term_id, 'roadmap_product');
            
            if (!is_wp_error($result)) {
                $migrated++;
                // Optionally remove the meta field after successful migration
                // delete_post_meta($item->ID, '_roadmap_product');
            } else {
                $errors++;
            }
        }
        
        return [
            'total' => count($items),
            'migrated' => $migrated,
            'errors' => $errors,
        ];
    }
    
    /**
     * Check if migration is needed
     */
    public static function needs_migration() {
        global $wpdb;
        
        // Check if any items have the old meta field but no taxonomy term
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'roadmap_product'
            WHERE p.post_type = 'roadmap_item'
            AND pm.meta_key = '_roadmap_product'
            AND tt.term_taxonomy_id IS NULL
        ");
        
        return $count > 0;
    }
}
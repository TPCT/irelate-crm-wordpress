<?php

/**
 * This file will create Custom Post Types.
 */
class IRCRM_Post_Types
{

    public function __construct()
    {
        add_action('init', [$this, 'create_custom_post_types']);
    }

    public function create_custom_post_types()
    {
        register_post_type('ircrm_contact', array(
            'label' => 'Contacts',
            'description' => 'iRelate User Contacts',
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_rest' => false,
            'hierarchical' => false,
            'capability_type' => 'contact',
            'query_var' => false,
            'supports' => array('title', 'editor', 'author', 'custom-fields'),
            'rewrite' => array('slug'=>'contact'),
            'taxonomies' => array('ircrm_ranking'),
            'can_export' => false,
        ));
    }
}
new IRCRM_Post_Types();

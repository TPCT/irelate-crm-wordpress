<?php

/**
 * This file will create Custom Taxonomies.
 */

class IRCRM_Taxonomies
{

    public function __construct()
    {
        add_action('init', [$this, 'create_custom_taxonomies']);
    }

    public function create_custom_taxonomies()
    {
        register_taxonomy(
            'ircrm_ranking',
            array('ircrm_contact'),
            array(
                'label' => 'Categories',
                'description' => 'Categories for IRCRM User Contacts',
                'public' => false,
                'hierarchical' => false,
                'query_var' => false,
            )
        );
    }
}
new IRCRM_Taxonomies();

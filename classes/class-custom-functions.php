<?php

/**
 * This file contains functions used within the plugin.
 */

class IRCRM_Functions
{

    public function __construct()
    {
    }
    public static function is_crm_page() {
        if ( is_singular('ircrm_contact')
            || is_page($GLOBALS['ircrmvars']['frontend_dashboard_id'])
            || is_page($GLOBALS['ircrmvars']['frontend_contacts_id'])
            || is_page($GLOBALS['ircrmvars']['frontend_campaign_id'])
            || is_page($GLOBALS['ircrmvars']['frontend_profile_id'])
            ){
            return true;
        }
        return false;
    }

    public static function table_columns() {
        $table_cols = ['', 'User ID', 'Newsletter', 'Category', 'Mailing Name', 'Primary First Name', 'Primary Last Name', 'Secondary First Name', 'Secondary Last Name', 'Address 1', 'Address 2', 'City', 'State', 'Zipcode', 'Phone 1', 'Phone 1 Description', 'Phone 2', 'Phone 2 Description', 'Phone 3', 'Phone 3 Description', 'Email Address 1', 'Email Address 1 Description', 'Email Address 2', 'Email Address 2 Description', 'Notes', 'Date Added'];
        for ($i = 1; $i <= 7; $i++){
            $table_cols[] = 'Key Date ' . $i;
            $table_cols[] = 'Key Date Description ' . $i;
            $table_cols[] = 'Key Date Reminder ' . $i;
        }
        return $table_cols;
    }

    public static function get_notes($meta) {
        if(is_array($meta)) {
            $meta = array_filter($meta, function($key) {
                return strpos($key, 'note_') === 0;
            }, ARRAY_FILTER_USE_KEY);
            return $meta;
        }
        return false;
    }

    public static function get_keydates($meta) {
        if(is_array($meta)) {
            $meta = array_filter($meta, function($key) {
                return strpos($key, 'keydate_') === 0;
            }, ARRAY_FILTER_USE_KEY);
            return $meta;
        }
        return [];
    }

    public static function get_keydates_desc($meta){
        if(is_array($meta)) {
            $meta = array_filter($meta, function($key) {
                return strpos($key, 'keydatedesc_') === 0;
            }, ARRAY_FILTER_USE_KEY);
            return $meta;
        }
        return [];
    }

    public static function get_keydates_reminder($meta){
        if(is_array($meta)) {
            $meta = array_filter($meta, function($key) {
                return strpos($key, 'keydaterem_') === 0;
            }, ARRAY_FILTER_USE_KEY);
            return $meta;
        }
        return [];
    }

    public static function get_keydate_labels() {
        $labels = array(
            'anniversary' => 'Anniversary',
            'birthday' => 'Birthday',
            'home_purchase' => 'Home Purchase',
        );

        return $labels;
    }

    public static function get_value($get) {
        if(isset($get) && $get!='' && $get!=null) {
            return $get;
        }
        return false;
    }

    public static function get_post_by_meta($meta_value=false, $post_type='page') {
        if(! $meta_value)
            return false;
        $args = array(
            'post_type' => $post_type,
            'meta_value' => $meta_value,
            'meta_key' => '_ircrm_page_type',
            'posts_per_page' => 1,
        );
        $result = get_posts($args);
        if($result) {
            return $result[0];
        }
        return false;
    }

    public static function create_default_page($post_title, $post_meta, $content, $slug=false, $post_type='page') {
        $page = IRCRM_Functions::get_post_by_meta($post_meta, $post_type);
        if($page) {
            $args = array(
                'ID' => $page->ID,
                'post_content' => $content,
            );
            if($slug) $args['post_name'] = $slug;
            wp_update_post($args);
        }
        else {
            $args = array(
                'post_type' => $post_type,
                'post_title' => $post_title,
                'post_content' => $content,
                'post_status' => 'publish',
                'meta_input'   => array(
                    '_ircrm_page_type' => $post_meta,
                )
            );
            if($slug) $args['post_name'] = $slug;
            wp_insert_post($args);
        }
    }

    public static function phoneFormatter($phone){
        $phone = str_replace('-', '', $phone);
        $phone = substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
        return $phone;
    }
}
new IRCRM_Functions();

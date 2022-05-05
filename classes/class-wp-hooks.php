<?php

/**
 * This file will fire custom functions for action and filter hooks.
 */

class IRCRM_Hooks
{

    public function __construct()
    {
        add_action('init', [$this, 'global_variables']);
        add_action('init', [$this, 'redirect_realtors']);
        add_action('init', [$this, 'register_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'load_frontend_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);
        add_action('admin_head-user-edit.php', [$this, 'profile_subject_start'], 1);
        add_action('admin_footer-user-edit.php', [$this, 'profile_subject_end'], 1);
        add_action('admin_head-profile.php', [$this, 'profile_subject_start'], 1);
        add_action('admin_footer-profile.php', [$this, 'profile_subject_end'], 1);

        add_filter('pre_site_transient_update_core', [$this, 'disable_core_updates']);
        add_filter('pre_site_transient_update_plugins', [$this, 'disable_core_updates']);
        add_filter('pre_site_transient_update_themes', [$this, 'disable_core_updates']);
        add_filter('ajax_query_attachmenargs', [$this, 'show_current_user_attachments']);
        add_filter('show_admin_bar', [$this, 'show_admin_bar']);
        add_filter('login_redirect', [$this, 'login_redirect'], 10, 3);
        add_filter('body_class', [$this, 'body_class']);
    }

    public function global_variables() {
      
        global $ircrmvars;

        $profile_page = IRCRM_Functions::get_post_by_meta('ircrm_profile');
        $ircrmvars['frontend_profile_id'] = $profile_page->ID;
        $ircrmvars['frontend_profile_url'] = get_permalink($profile_page->ID);

        $dashboard_page = IRCRM_Functions::get_post_by_meta('ircrm_dashboard');
        $ircrmvars['frontend_dashboard_id'] = $dashboard_page->ID;
        $ircrmvars['frontend_dashboard_url'] = get_permalink($dashboard_page->ID);

        $dashboard_page = IRCRM_Functions::get_post_by_meta('ircrm_contacts');
        $ircrmvars['frontend_contacts_id'] = $dashboard_page->ID;
        $ircrmvars['frontend_contacts_url'] = get_permalink($dashboard_page->ID);

        $dashboard_page = IRCRM_Functions::get_post_by_meta('ircrm_campaign');
        $ircrmvars['frontend_campaign_id'] = $dashboard_page->ID;
        $ircrmvars['frontend_campaign_url'] = get_permalink($dashboard_page->ID);

        $new_contact = IRCRM_Functions::get_post_by_meta('ircrm_newcontact', 'ircrm_contact');
        $ircrmvars['new_contact_id'] = $new_contact->ID;
        $ircrmvars['new_contact_url'] = get_permalink($new_contact->ID);
    }

    public function body_class($classes) {
        if ( IRCRM_Functions::is_crm_page() ) {
            $classes[] = 'ircrm-page';
        }
        if ( current_user_can('is_ircrm_realtor') ) {
            $classes[] = 'ircrm-realtor';
        }
        return $classes;
    }

    public function redirect_realtors() {
        if ( is_admin() && current_user_can( 'ircrm_realtor' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            wp_redirect( $GLOBALS['ircrmvars']['frontend_contacts_url'] );
            exit;
        }
    }

    public function show_admin_bar($show_admin_bar) {
        if(! is_admin() && current_user_can('is_ircrm_realtor')){
            $show_admin_bar = false;
        }
        return $show_admin_bar;
    }

    public function profile_subject_start() {
        ob_start([$this,'remove_personal_options']);
    }

    public function remove_personal_options($options) {
        if(current_user_can('is_custom_user')){
            $options = preg_replace('#<h2>'. __("Personal Options") .'</h2>.+?/table>#s', '', $options, 1);
            $options = preg_replace('#<h2>'. __("About Yourself") .'</h2>.+?/table>#s', '', $options, 1);
            $options = preg_replace('#<h2>'. __("About the user") .'</h2>.+?/table>#s', '', $options, 1);
            $options = preg_replace('#<h2>Account Management</h2>#s', '', $options, 1);
            $options = preg_replace('#<h2>Name</h2>#s', '', $options, 1);
            $options = preg_replace('#<h2>Contact Info</h2>#s', '', $options, 1);
            $options = preg_replace('#<tr class="user-display-name-wrap.+?/tr>#s', '', $options, 1);
            $options = preg_replace('#<tr class="user-url-wrap.+?/tr>#s', '', $options, 1);
        }
        return $options;
    }

    public function profile_subject_end() {
        ob_end_flush();
    }

    public function disable_core_updates(){
        if(current_user_can('is_custom_user')) {
            global $wp_version;
            remove_action('admin_color_scheme_picker','admin_color_scheme_picker');
            return(object) array('last_checked'=> time(),'version_checked'=> $wp_version);
        }
    }

    public function show_current_user_attachments($query) {
        $user_id = get_current_user_id();
        if ($user_id && current_user_can('is_custom_user')) {
            $query['author'] = $user_id;
        }
        return $query;
    }

    /**
     * Redirect custom users after login
     */

    public function login_redirect($redirect_to, $request, $user) {
        if (isset($user->roles) && is_array($user->roles)) {
            $roles = $user->roles;
            if (in_array('ircrm_admin', $roles) || in_array('ircrm_staff', $roles) || in_array('ircrm_realtor', $roles)) {
                $redirect_to = IRCRM_DASHBOARD;
            }
        }
        return $redirect_to;
    }

    /**
     * Register plugins scripts
     */

    public function register_scripts()
    {
        wp_register_style('jquery-ui-css', IRCRM_URL . 'assets/css/jquery-ui.css');
        wp_register_style('jquery-dataTables', IRCRM_URL . 'assets/js/jquery.dataTables/css/jquery.dataTables.min.css');
        wp_register_style('buttons-dataTables', IRCRM_URL . 'assets/js/jquery.dataTables/css/buttons.dataTables.min.css');
        wp_register_style('select-dataTables', IRCRM_URL . 'assets/js/jquery.dataTables/css/select.dataTables.min.css');
        wp_register_style('jquery-validationEngine', IRCRM_URL . 'assets/js/jquery.validationEngine/css/validationEngine.jquery.css');
        wp_register_style('timepicker', IRCRM_URL . 'assets/css/jquery-ui-timepicker-addon.css');
        wp_register_style('bootstrap', IRCRM_URL . 'assets/css/bootstrap.min.css');
        wp_register_style('font-awesome', IRCRM_URL . 'assets/css/font-awesome.min.css');
        wp_register_style('ircrm-style', IRCRM_URL . 'assets/css/main.css');
        wp_register_style('ircrm-frontend-style', IRCRM_URL . 'assets/css/frontend.css');

        wp_register_script('jquery-dataTables', IRCRM_URL . 'assets/js/jquery.dataTables/js/jquery.dataTables.min.js', array('jquery', 'jquery-ui-core'), '', true);
        wp_register_script('dataTables-buttons', IRCRM_URL . 'assets/js/jquery.dataTables/js/dataTables.buttons.min.js', array('jquery', 'jquery-dataTables'), '', true);
        wp_register_script('buttons-html5', IRCRM_URL . 'assets/js/jquery.dataTables/js/buttons.html5.min.js', array('jquery', 'jquery-dataTables'), '', true);
        wp_register_script('dataTables.select', IRCRM_URL . 'assets/js/jquery.dataTables/js/dataTables.select.min.js', array('jquery', 'jquery-dataTables'), '', true);
        wp_register_script('jquery-validationEngine-languages', IRCRM_URL . 'assets/js/jquery.validationEngine/languages/jquery.validationEngine-en.js', array('jquery'), '', true);
        wp_register_script('jquery-validationEngine', IRCRM_URL . 'assets/js/jquery.validationEngine/jquery.validationEngine.js', array('jquery'), '', true);
        wp_register_script('timepicker', IRCRM_URL . 'assets/js/jquery-ui-timepicker-addon.js', array('jquery','jquery-ui-core', 'jquery-ui-datepicker'), '', true);
        wp_register_script('bootstrap', IRCRM_URL . 'assets/js/bootstrap.min.js', array('jquery'), '', true);
        wp_register_script('ircrm-script', IRCRM_URL . 'assets/js/main.js', ['jquery', 'wp-element'], wp_rand(), true);
        wp_localize_script('ircrm-script', 'appLocalizer', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'token' => wp_create_nonce('ircrm_token'),
            'dashboard' => IRCRM_DASHBOARD,
        ]);
    }

    /**
     * Loading admin scripts
     */

    public function load_admin_scripts()
    {
        wp_enqueue_style('jquery-ui-css');
        wp_enqueue_style('jquery-dataTables');
        wp_enqueue_style('buttons-dataTables');
        wp_enqueue_style('select-dataTables');
        wp_enqueue_style('jquery-validationEngine');
        wp_enqueue_style('timepicker');
        wp_enqueue_style('bootstrap');
        wp_enqueue_style('font-awesome');
        wp_enqueue_style('ircrm-style');
        wp_enqueue_style('ircrm-frontend-style');

        wp_enqueue_script('jquery-dataTables');
        wp_enqueue_script('dataTables-buttons');
        wp_enqueue_script('buttons-html5');
        wp_enqueue_script('dataTables.select');
        wp_enqueue_script('jquery-validationEngine-languages');
        wp_enqueue_script('jquery-validationEngine');
        wp_enqueue_script('timepicker');
        wp_enqueue_script('bootstrap');
        wp_enqueue_script('ircrm-script');
    }

    /**
     * Loading admin scripts
     */

    public function load_frontend_scripts()
    {
        if(IRCRM_Functions::is_crm_page()) {

            if(is_page($GLOBALS['ircrmvars']['frontend_contacts_id'])) {
                wp_enqueue_style('jquery-ui-css');
                wp_enqueue_style('jquery-dataTables');
                wp_enqueue_style('buttons-dataTables');
                wp_enqueue_style('select-dataTables');

                wp_enqueue_script('jquery-dataTables');
                wp_enqueue_script('dataTables-buttons');
                wp_enqueue_script('buttons-html5');
                wp_enqueue_script('dataTables.select');
            }

            wp_enqueue_style('jquery-validationEngine');
            wp_enqueue_style('timepicker');
            wp_enqueue_style('bootstrap');
            wp_enqueue_style('font-awesome');
            wp_enqueue_style('ircrm-style');
            wp_enqueue_style('ircrm-frontend-style');

            wp_enqueue_script('jquery-validationEngine-languages');
            wp_enqueue_script('jquery-validationEngine');
            wp_enqueue_script('timepicker');
            wp_enqueue_script('bootstrap');
            wp_enqueue_script('ircrm-script');
        }
    }

}
new IRCRM_Hooks();

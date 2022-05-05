<?php

/**
 * This file is for showing front-facing pages for Users.
 */

class IRCRM_Frontend_Pages
{

    public function __construct()
    {
        add_filter('the_content', [$this, 'frontend_content'], 99);
    }

    public function frontend_content($content) {

        $is_logged      = is_user_logged_in();
        $is_realtor     = current_user_can('is_ircrm_realtor');
        $is_crm_page    = IRCRM_Functions::is_crm_page();
        $is_admin_page  = is_admin();
        $dashboard_url  = $GLOBALS['ircrmvars']['frontend_dashboard_url'];
        $campaign_url   = $GLOBALS['ircrmvars']['frontend_campaign_url'];
        $contacts_id    = $GLOBALS['ircrmvars']['frontend_contacts_id'];
        $contacts_url   = $GLOBALS['ircrmvars']['frontend_contacts_url'];
        $profile_id     = $GLOBALS['ircrmvars']['frontend_profile_id'];
        $profile_url    = $GLOBALS['ircrmvars']['frontend_profile_url'];
        $post_id        = get_the_ID();
        $user_id        = get_current_user_id();
        $user           = get_user_by('id', $user_id);
        $realtor_status = get_user_meta($user_id, 'realtor_status', true);
        $deactivated    = $realtor_status == 'inactive' ? true : false;

        if($is_realtor && is_singular('ircrm_contact')) :
            $html           = '';
            $admin_page     = new IRCRM_Admin_Pages();
            $btn            = '<a href="'. $contacts_url .'" class="btn btn-light-gray">Back</a> <button type="button" class="btn btn-red btn-delete" data-id="' . $post_id . '" data-type="contact" data-redirect="'. $contacts_url .'">Delete</button>';
            $title          = is_single('new') ? 'Add Contact' : 'Edit Contact';

            $html .= $admin_page->page_top($title, $btn);

            if(! $deactivated) {
                $html .= do_shortcode('[ircrm-edit-contact id="'. $post_id .'" frontend="true"]');
            }
            else {
                $html .= '<p>Sorry, you are not allowed to view this page. Please contact support.</p>';
            }

            $html .= $admin_page->page_bottom();

            $content = $html;

        endif;

        if($is_realtor && $post_id == $contacts_id) {
            $content = do_shortcode('[ircrm-contacts]');
        }

        if($is_realtor && $post_id == $profile_id) {
            $content = do_shortcode('[ircrm-edit-profile]');
        }

        if($is_crm_page) {
            $wp_login_url = wp_login_url();
            $login_url = $is_logged ? wp_logout_url($wp_login_url) : wp_login_url($dashboard_url);
            $login_text = $is_logged ? 'Sign Out' : 'Sign In';
            $content .= '
            <div class="ircrm-topbar">
                <div class="wrap">
                    <div class="row">
                        <div class="col-md-8">
                            <h1><a href="'. home_url() .'">iRelate CRM</a></h1>
                            <nav class="main-nav">
                                <a href="'. $dashboard_url .'">Dashboard</a>
                                <a href="'. $contacts_url .'">Contacts</a>
                                <a href="'. $campaign_url .'">Campaign</a>
                            </nav>
                        </div>
                        <div class="col-md-4">
                            <nav class="utility-nav t-right">
                                <a href="'. $login_url .'">'. $login_text .'</a>
                                <a href="'. $profile_url .'"><i class="fa fa-user"></i></a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            ';
        }

        return $content;
    }

}
new IRCRM_Frontend_Pages();

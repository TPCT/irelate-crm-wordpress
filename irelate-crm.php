<?php

/**
 * Plugin Name: iRelate CRM
 * Author: Carlos Reyes
 * Author URI:
 * Version: 1.0.0
 * Description: A Customer Relationship Management system for iRelate
 * Text-Domain: irelate-crm
 */

// No direct access allowed.
if (!defined('ABSPATH')) : exit();
endif;

/**
 * Activation/Deactivation Hooks
 */

register_activation_hook(__FILE__, 'ircrm_plugin_activation');
register_deactivation_hook(__FILE__, 'ircrm_plugin_deactivation');

function ircrm_plugin_activation()
{
    ircrm_create_pages();
    ircrm_add_new_roles();
    ircrm_add_role_caps();
    flush_rewrite_rules();
}

function ircrm_plugin_deactivation()
{
    flush_rewrite_rules();
}

function ircrm_add_new_roles()
{

    remove_role('ircrm_admin');
    remove_role('ircrm_staff');
    remove_role('ircrm_realtor');

    add_role(
        'ircrm_admin',
        __('IRCRM Admin'),
        array(
            'read' => true,
            'upload_files' => true,
            'use_irelatecrm' => true,
            'manage_staffs' => true,
            'manage_realtors' => true,
            'manage_contacts' => true,
            'is_custom_user' => true,
            'is_ircrm_admin' => true,
            'read_contact' => true,
            'edit_contact' => true,
            'delete_contact' => true,
            'read_contacts' => true,
            'edit_contacts' => true,
            'delete_contacts' => true,
            'read_note' => true,
            'edit_note' => true,
            'delete_note' => true,
            'read_notes' => true,
            'edit_notes' => true,
            'delete_notes' => true,
        )
    );

    add_role(
        'ircrm_staff',
        __('IRCRM Staff'),
        array(
            'read' => true,
            'upload_files' => true,
            'use_irelatecrm' => true,
            'manage_realtors' => true,
            'manage_contacts' => true,
            'is_custom_user' => true,
            'is_ircrm_staff' => true,
        )
    );

    add_role(
        'ircrm_realtor',
        __('IRCRM User'),
        array(
            'read' => true,
            'upload_files' => true,
            'use_irelatecrm' => true,
            'manage_contacts' => true,
            'is_custom_user' => true,
            'is_ircrm_realtor' => true,
            'read_contact' => true,
            'edit_contact' => true,
            'delete_contact' => true,
        )
    );
}

function ircrm_add_role_caps()
{
    $admin = get_role('administrator');
    $admin->add_cap('use_irelatecrm');
    $admin->add_cap('manage_staffs');
    $admin->add_cap('manage_realtors');
    $admin->add_cap('manage_contacts');
    $admin->add_cap('read_contact');
    $admin->add_cap('edit_contact');
    $admin->add_cap('delete_contact');
    $admin->add_cap('read_contacts');
    $admin->add_cap('edit_contacts');
    $admin->add_cap('delete_contacts');
    $admin->add_cap('read_note');
    $admin->add_cap('edit_note');
    $admin->add_cap('delete_note');
    $admin->add_cap('read_notes');
    $admin->add_cap('edit_notes');
    $admin->add_cap('delete_notes');
}

function ircrm_create_pages() {
    IRCRM_Functions::create_default_page('Profile', 'ircrm_profile', '', 'ircrm-profile');
    IRCRM_Functions::create_default_page('Dashboard', 'ircrm_dashboard', '', 'ircrm-dashboard');
    IRCRM_Functions::create_default_page('Contacts', 'ircrm_contacts', '', 'ircrm-contacts');
    IRCRM_Functions::create_default_page('Campaign', 'ircrm_campaign', '', 'ircrm-campaign');
    IRCRM_Functions::create_default_page('New Contact', 'ircrm_newcontact', '', 'new', 'ircrm_contact');
}

/**
 * Define Plugins Contants
 */
define('IRCRM_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('IRCRM_URL', trailingslashit(plugins_url('/', __FILE__)));
define('IRCRM_DASHBOARD', admin_url('admin.php?page=ircrm-dashboard'));

require_once IRCRM_PATH . 'classes/class-custom-functions.php';
require_once IRCRM_PATH . 'classes/class-wp-hooks.php';
require_once IRCRM_PATH . 'classes/class-admin-pages.php';
require_once IRCRM_PATH . 'classes/class-frontend-pages.php';
require_once IRCRM_PATH . 'classes/class-ajax-post.php';
require_once IRCRM_PATH . 'classes/class-post-types.php';
require_once IRCRM_PATH . 'classes/class-post-taxonomies.php';
require_once IRCRM_PATH . 'classes/class-shortcodes.php';

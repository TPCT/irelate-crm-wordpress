<?php

/**
 * This file will create admin menu page.
 */

class IRCRM_Admin_Pages
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'create_admin_menu']);
    }

    public function create_admin_menu()
    {
        if (current_user_can('is_custom_user')) {
            remove_menu_page('separator1');
            remove_menu_page('separator2');
            remove_menu_page('separator-last');
            remove_menu_page('index.php');
            remove_menu_page('upload.php');
            remove_menu_page('tools.php');
            remove_menu_page('users.php');
        }
        add_menu_page(
            __('iRelate CRM', 'irelate-crm'),
            __('iRelate CRM', 'irelate-crm'),
            'use_irelatecrm',
            'ircrm-dashboard',
            [$this, 'ircrm_dashboard'],
            'dashicons-groups',
            7
        );
        add_submenu_page(
            'ircrm-dashboard',
            __('Contacts', 'irelate-crm'),
            __('Contacts', 'irelate-crm'),
            'use_irelatecrm',
            'ircrm-dashboard',
            [$this, 'ircrm_dashboard'],
        );
        add_submenu_page(
            'ircrm-dashboard',
            __('Users', 'irelate-crm'),
            __('Users', 'irelate-crm'),
            'manage_realtors',
            'ircrm-realtors',
            [$this, 'ircrm_realtors'],
        );
        add_submenu_page(
            'ircrm-dashboard',
            __('Settings', 'irelate-crm'),
            __('Settings', 'irelate-crm'),
            'manage_staffs',
            'ircrm-settings',
            [$this, 'ircrm_settings'],
        );
        add_menu_page(
            __('Edit Contact', 'irelate-crm'),
            __('Edit Contact', 'irelate-crm'),
            'use_irelatecrm',
            'ircrm-edit-contact',
            [$this, 'ircrm_edit_contact'],
            'dashicons-groups',
            7
        );
        remove_menu_page('ircrm-edit-contact');
    }

    public function ircrm_edit_contact()
    {
        $id    = IRCRM_Functions::get_value($_GET['id']);
        $btn   = '<a href="'. IRCRM_DASHBOARD .'" class="btn btn-light-gray">Back</a>';
        if(! current_user_can('is_ircrm_staff')) {
            $btn  .= ' <button type="button" class="btn btn-red btn-delete" data-id="' . $id . '" data-type="contact" data-redirect="'. IRCRM_DASHBOARD .'">Delete</button>';
        }
        $title = $id=='new' ? 'Add Contact' : 'Edit Contact';
        $html  = $this->page_top($title, $btn);
        $html .= do_shortcode('[ircrm-edit-contact id="'. $id .'"]');        
        $html .= $this->page_bottom();
        echo $html;
    }

    public function ircrm_dashboard()
    {
        $add_btn        = '';
        $deactivated    = false;
        $is_realtor     = false;
        $is_admin       = current_user_can('manage_staffs');
        $is_staff       = current_user_can('is_ircrm_staff');
        $thead_cols     = IRCRM_Functions::table_columns();
//         $keydate_label  = IRCRM_Functions::get_value($_GET['keydate_label']);
//         $date_from      = IRCRM_Functions::get_value($_GET['date_from']);
//         $date_to        = IRCRM_Functions::get_value($_GET['date_to']);
        $user_id        = IRCRM_Functions::get_value($_GET['user_id']);
        $user_ids       = IRCRM_Functions::get_value($_GET['user_ids']);
        $userids_arr    = $user_ids ? explode(',',$user_ids) : array();
        $edit_base_url  = admin_url('admin.php?page=ircrm-edit-contact');
        $user_id        = $user_id ? absint($user_id) : null;
        $user           = get_user_by('id', $user_id);

        $realtor_dropdown = '
        <span><select name="realtorid" class="realtorid">
            <option value="">'. (! $user_id ? 'Choose User' : 'All Users') .'</option>';
            $args = array(
                'role' => 'ircrm_realtor',
            );
            $realtors = get_users($args);
            if ($realtors) :
                foreach ($realtors as $realtor) {
                    $realtor_dropdown .= '<option value="' . $realtor->ID . '" ' . ($user_id == $realtor->ID ? 'selected' : '') . '>' . $realtor->user_login . '</option>';
                }
            endif;
            $realtor_dropdown .= '
        </select></span>';


        if (current_user_can('manage_realtors')) {
            $actn_btn = $realtor_dropdown;
            $actn_btn .= '<a class="btn-advancedsearch" href="" >User Advanced Search</a>';
        }
        $html = $this->page_top($actn_btn);

        include_once IRCRM_PATH . 'includes/form-advanced-search.php';

        if ($is_admin) {
            $add_btn .= '<a href="'. $edit_base_url .'&id=new" class="btn btn-blue">Add New</a> <button type="button" class="btn btn-red btn-deleteselected">Delete Selected</button>';
        }
		
        if ($is_admin && $user_id) {
            wp_enqueue_media();
            $add_btn .= '&nbsp;<button type="button" class="btn btn-light-gray btn-importcontacts" data-id="'. $user_id .'">Import Contacts</button>';
        }
        $html .= '<h2><span>Contacts</span>&nbsp;&nbsp;'. $add_btn .'</h2>';

        $title_prefix       = ! $user_id ? 'All Users' : $user->user_login;
        $export_all         = implode(',',range(1,count($thead_cols)-1));
        $export_mailing     = '1,2,3,10,11,12,13,14,25';
        $export_newsletter  = '1,5,10,11,12,13,14,25';
        $column_filter      = '1,2,5,10,11,12,13,14,25';
        $column_visible     = '0,1,2,5,10,11,12,13,14,25';
        $table_props        = 'id="contacts-list" class="ircrm-data-table" data-length="50" data-customsearch="true" data-filter="true" data-colfiltertrigger="Contact Advanced Search" data-colfilter="['. $column_filter .']" data-visiblecols="['. $column_visible .']" data-selectable="true" data-exporttitle="'. $title_prefix .' - " data-exportcol="true" data-exportmailing="' . $export_mailing . '" data-exportnewsletter="' . $export_newsletter . '" data-exportall="' . $export_all . '" data-dom="fBr<\'table-wrapper\'t><\'table-footer clearfix\'pl>"';

        $args = array(
            'post_type' => 'ircrm_contact',
            'post_status' => 'publish',
            'exclude' => array($GLOBALS['ircrmvars']['new_contact_id']),
            'posts_per_page' => -1,
        );
        if ($user_id) {
            $args['author'] = $user_id;
        }
        else if($user_ids) {
            $args['author'] = $user_ids;
        }
        if($keydate_label) {
            $args['meta_query'] = array(
                array(
                    'key' => 'keydate_'. $keydate_label,
                    'value' => array($date_from, $date_to),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ),
            );
        }
        $contacts = get_posts($args);

        $html .= $this->table_head($thead_cols, $table_props);
        $html .= $this->contact_rows($contacts, $edit_base_url .'&id=');
        $html .= $this->table_foot($thead_cols);

        $html .= $this->page_bottom();

        echo $html;
    }


    private function generate_date_keys($key_dates, $key_date_desc, $key_date_rem){
        $html = '';
        for($i = 0; $i < 7; $i++){
            $html .= "
                <td>{$key_dates['keydate_' . $i][0]}</td>
                <td>{$key_date_desc['keydatedesc_'.$i][0]}</td>
                <td>{$key_date_rem['keydaterem_'.$i][0]}</td>
            ";
        }
        return $html;
    }

    public function contact_rows($contacts, $edit_base_url, $is_relator=false) {

        $html = '';

        if ($contacts && ! empty($contacts)) :
            foreach ($contacts as $contact) :
                $id             = $contact->ID;
                $mailing_name   = $contact->post_title;
//                $author         = $contact->post_author;
                $rank_obj       = wp_get_object_terms($id, 'ircrm_ranking');
//                $rank_id        = $rank_obj[0]->term_id;
                $rank           = $rank_obj[0]->name;
                $meta           = get_post_meta($id);
                $realtor_id     = $meta['realtor_id'][0];
                $newsletter     = $meta['newsletter'][0];
                $pfirstname     = $meta['primary_first_name'][0];
                $plastname      = $meta['primary_last_name'][0];
                $sfirstname     = $meta['secondary_first_name'][0];
                $slastname      = $meta['secondary_last_name'][0];
                $address1       = trim($meta['address1'][0]);
                $city           = $meta['city'][0];
                $state          = $meta['state'][0];
                $zipcode        = $meta['zipcode'][0];
                $phone1         = IRCRM_Functions::phoneFormatter($meta['phone1'][0]);
                $phone2         = IRCRM_Functions::phoneFormatter($meta['phone2'][0]);
                $phone3         = IRCRM_Functions::phoneFormatter($meta['phone3'][0]);
                $phone1_desc    = $meta['phone1_desc'][0];
                $phone2_desc    = $meta['phone2_desc'][0];
                $phone3_desc    = $meta['phone3_desc'][0];
                $email1         = $meta['email1'][0];
                $email2         = $meta['email2'][0];
                $email1_desc    = $meta['email1_desc'][0];
                $email2_desc    = $meta['email2_desc'][0];
//                 $rank_desc      = $meta['rank_desc'][0];
				$date_added		= $meta['date_added'][0] ?? 0;

                $keydates       = IRCRM_Functions::get_keydates($meta);
                $keydatesdesc   = IRCRM_Functions::get_keydates_desc($meta);
                $keydatesrem    = IRCRM_Functions::get_keydates_reminder($meta);

                $notes          = IRCRM_Functions::get_notes($meta);
//                $keydates_list  = '';
                $notes_list     = '';

                if (!$is_relator)
                    $address2   = $meta['address2'][0];
                else {
                    $split_position = strpos($address1, '#');
                    $temp_address = $address1;
                    if ($split_position){
                        $address1 = substr($temp_address, 0, $split_position);
                        $address2 = trim(substr($temp_address, $split_position + 1));
                    }else{
                        $address2 = "";
                    }
                    $zipcode = substr((string)$zipcode, 0, 5);
                    $state   = substr($state, 0, 2);
                }

//                if(! empty($keydates)) {
//                    krsort($keydates);
//                    foreach ($keydates as $key => $value) {
//                        $label = ucwords(str_replace('_', ' ', substr($key, 8)));
//                        $keydates_list .= '<b>'. $label .':</b> '. $value[0] .'<br>';
//                    }
//                }

                if(! empty($notes)) {
                    krsort($notes);
                    foreach ($notes as $key => $value) {
                        $note_date = date('Y-m-d',substr($key, 5));
                        $notes_list .= '<b>'. $note_date .'</b><br>'. $value[0] .'<br><br>';
                    }
                }

                $html .= '
                <tr id="item-' . $id . '" '. ($newsletter=='Yes' ? 'class="newsletter-yes"' : '') .'>
                    <td></td>
                    <td>' . $realtor_id . '</td>
                    <td>' . $newsletter . '</td>
                    <td>' . $rank . '</td>' .
//                     '<td>' . $rank_desc . '</td> '
                    '<td><a href="'. $edit_base_url . $id .'">' . $mailing_name . '</a></td> 
                    <td>' . $pfirstname . '</td>
                    <td>' . $plastname . '</td>
                    <td>' . $sfirstname . '</td>
                    <td>' . $slastname . '</td>
                    <td>' . $address1 . '</td>
                    <td>' . $address2 . '</td>
                    <td>' . $city . '</td>
                    <td>' . $state . '</td>
                    <td>' . $zipcode . '</td>
                    <td>' . $phone1 . '</td>
                    <td>' . $phone1_desc . '</td>
                    <td>' . $phone2 . '</td>
                    <td>' . $phone2_desc . '</td>
                    <td>' . $phone3 . '</td>
                    <td>' . $phone3_desc . '</td>
                    <td>' . $email1 . '</td>
                    <td>' . $email1_desc . '</td>
                    <td>' . $email2 . '</td>
                    <td>' . $email2_desc . '</td>
                    <td>' . $notes_list . '</td>
					<td>' . strftime("%D %T", $date_added) . '</td>'
                    . $this->generate_date_keys($keydates, $keydatesdesc, $keydatesrem) .
                '</tr>            
                ';
            endforeach;
        endif;

        return $html;
    }

    public function ircrm_realtors()
    {
        $add_btn = '';
        $is_admin = current_user_can('manage_staffs');
        if ($is_admin) {
            $add_btn = '<button type="button" class="btn btn-blue btn-newrealtor">Add New</button>';
        }

        $thead_cols = ['Username', 'First Name', 'Last Name', 'Email Address', 'Status', 'Actions'];
        $table_props = 'id="reators-list" class="ircrm-data-table" data-length="50" data-filter="true"';

        $html  = $this->page_top('Users', $add_btn);
        $html .= $this->table_head($thead_cols, $table_props);

        $args = array(
            'role' => 'ircrm_realtor',
        );
        $users = get_users($args);
        if ($users) :
            foreach ($users as $user) {
                $user_id     = $user->ID;
                $user_login  = $user->user_login;
                $user_email  = $user->user_email;
                $meta        = get_user_meta($user_id);
                $first_name  = $meta['first_name'][0];
                $last_name   = $meta['last_name'][0];
                $realtor_id  = $meta['realtor_id'][0];
                $newsletters = $meta['newsletters'][0];
                $status      = !isset($meta['realtor_status'][0]) ? 'inactive' : $meta['realtor_status'][0];
                $status_text = $status == 'inactive' ? 'In-active' : 'Active';
                $btn_text    = $status == 'inactive' ? 'Activate' : 'Deactivate';

                $html .= '
                <tr id="item-' . $user_id . '">
                    <td>' . $user_login . '</td>
                    <td>' . $first_name . '</td>
                    <td>' . $last_name . '</td>
                    <td>' . $user_email . '</td>
                    <td class="status">' . $status_text . '</td>
                    <td>';
                if ($is_admin) {
                    $html .= '    
                        <button type="button" class="btn btn-blue btn-editrealtor" 
                            data-id="' . $user_id . '" 
                            data-username="' . $user_login . '"
                            data-firstname="' . $first_name . '"
                            data-lastname="' . $last_name . '"
                            data-email="' . $user_email . '"
                            data-realtorid="' . $realtor_id . '"
                            data-status="' . $status . '"
                            data-newsletters="' . $newsletters . '"
                            >Edit</button>
                        <button type="button" class="btn btn-red btn-delete" data-id="' . $user_id . '" data-type="realtor">Delete</button>
                        <button type="button" class="btn btn-light-gray btn-updatestatus" data-id="' . $user_id . '">' . $btn_text . '</button>';
                }
                $html .= '                        
                        <a role="button" class="btn btn-light-gray btn-viewcontacts" href="' . IRCRM_DASHBOARD .'&user_id=' . $user_id . '">View Contacts</button>
                    </td>
                </tr>            
                ';
            }
        endif;

        $html .= $this->table_foot();
        $html .= $this->page_bottom();

        if ($is_admin) {
            $popup_content = '
                <form method="post" action="" id="form-save-realtor" name="form-save-realtor" class="validate">
                    <input type="hidden" name="user-id" id="user-id" value="" />
                    <p>Username <i>(Usernames cannot be changed)</i><br />
                        <input type="text" name="user-login" id="user-login" value="" class="validate[required]" /></p>
                    <p>Password <i>(Leave it blank if you do not want to change password)</i><br />
                        <input type="password" name="user-pass" id="user-pass" value="" class="validate[required]" /></p>
                    <p>Email Address<br />
                        <input type="text" name="user-email" id="user-email" value="" class="validate[required,custom[email]]" /></p>      
                    <p>First Name<br />
                        <input type="text" name="first-name" id="first-name" value="" /></p>
                    <p>Last Name<br />
                        <input type="text" name="last-name" id="last-name" value="" /></p>
                    <p>Status<br />
                        <select name="realtor-status" id="realtor-status">
                            <option value="active">Active</option>
                            <option value="inactive">In-active</option>
                        </select></p>  
                    <p class="newsletter-delivery">Newsletter<br />
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Jan" checked>Jan</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Feb" checked>Feb</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Mar" checked>Mar</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Apr" checked>Apr</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="May" checked>May</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Jun" checked>Jun</label>
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Jul" checked>Jul</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Aug" checked>Aug</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Sep" checked>Sep</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Oct" checked>Oct</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Nov" checked>Nov</label> 
                        <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Dec" checked>Dec</label> 
                    </p>        
                    <div class="t-right"><input type="submit" value="Save" class="btn btn-blue"></div>
                </form>        
            ';
            $html .= $this->modal_popup('popup-save-realtor', 'Add User', $popup_content);
        }
        echo $html;
    }

    public function ircrm_settings()
    {
        $html  = $this->page_top('Settings');

        $html .= '<div class="row"><div class="col-md-6 col-sm-12">';

        $html .= $this->page_subheading('Categories','<button type="button" class="btn btn-blue btn-newranking">Add New</button>');
        $thead_cols = ['Title', 'Description', 'Actions'];
        $table_props = 'id="ranking-list" class="ircrm-data-table" data-length="50" data-filter="true"';
        $html .= $this->table_head($thead_cols, $table_props);

            $rankings = get_terms(array(
                'taxonomy' => 'ircrm_ranking',
                'hide_empty' => false,
            ));

            if ($rankings) :
                foreach ($rankings as $ranking) {
                    $ranking_id     = $ranking->term_id;
                    $ranking_title  = $ranking->name;
                    $ranking_desc   = $ranking->description;

                    $html .= '
                    <tr id="item-' . $ranking_id . '">
                        <td>' . $ranking_title . '</td>
                        <td>' . $ranking_desc . '</td>
                        <td>
                            <button type="button" class="btn btn-blue btn-editranking" 
                                data-id="' . $ranking_id . '" 
                                data-title="' . $ranking_title . '"
                                data-description="' . $ranking_desc . '"
                                >Edit</button>
                            <button type="button" class="btn btn-red btn-delete" data-id="' . $ranking_id . '" data-type="ranking">Delete</button>
                        </td>
                    </tr>            
                    ';
                }
            endif;

        $html .= $this->table_foot();

        $html .= '</div><div class="col-md-6 col-sm-12">';

        // $html .= $this->page_subheading('Key Date Labels','<button type="button" class="btn btn-blue btn-newlabel">Add New</button>');
        // $thead_cols = ['Label', 'Description', 'Actions'];
        // $table_props = 'id="label-list" class="ircrm-data-table" data-length="50" data-filter="true"';
        // $html .= $this->table_head($thead_cols, $table_props);

        //     $rankings = get_terms(array(
        //         'taxonomy' => 'ircrm_ranking',
        //         'hide_empty' => false,
        //     ));

        //     if ($rankings) :
        //         foreach ($rankings as $ranking) {
        //             $ranking_id     = $ranking->term_id;
        //             $ranking_title  = $ranking->name;
        //             $ranking_desc   = $ranking->description;

        //             $html .= '
        //             <tr id="item-' . $ranking_id . '">
        //                 <td>' . $ranking_title . '</td>
        //                 <td>' . $ranking_desc . '</td>
        //                 <td>
        //                     <button type="button" class="btn btn-blue btn-editranking"
        //                         data-id="' . $ranking_id . '"
        //                         data-title="' . $ranking_title . '"
        //                         data-description="' . $ranking_desc . '"
        //                         >Edit</button>
        //                     <button type="button" class="btn btn-red btn-delete" data-id="' . $ranking_id . '" data-type="ranking">Delete</button>
        //                 </td>
        //             </tr>
        //             ';
        //         }
        //     else :
        //         $tds = '';
        //         for ($x = 1; $x < count($thead_cols); $x++) {
        //             $tds .= '<td></td>';
        //         }
        //         $html .= '<tr><td>No Categorys Found</td>' . $tds . '</tr>';
        //     endif;

        // $html .= $this->table_foot();

        $html .= '</div></div';

        $html .= $this->page_bottom();

        $popup_content = '
            <form method="post" action="" id="form-save-ranking" name="form-save-ranking" class="validate">
                <input type="hidden" name="ranking-id" id="ranking-id" value="" />
                <p>Title<br />
                    <input type="text" name="ranking-title" id="ranking-title" value="" class="validate[required]" /></p>
                <p>Description<br />
                    <textarea row="10" name="ranking-description" id="ranking-description"></textarea></p>
                <div class="t-right"><input type="submit" value="Save" class="btn btn-blue"></div>
            </form>        
        ';
        $html .= $this->modal_popup('popup-save-ranking', 'Add Category', $popup_content);
        echo $html;
    }

    public function page_subheading($title, $button = '')
    {
        return '
            <h2 class="ircrm-subheading">' . $title . ' ' . $button . '</h2>
        ';
    }    

    public function page_top($title, $button = '')
    {
        return '
        <div class="wrap" id="ircrm-admin-app">
            <h1 class="wp-heading ircrm-heading">' . $title . ' ' . $button . '</h1>
            <div class="inner-wrap">
        ';
    }

    public function page_bottom()
    {
        return '
            </div>
        </div>';
    }

    public function table_head($columns = array(), $props = '')
    {
        $html = '
        <table ' . $props . ' >';
        if ($columns) {
            $html .= '
            <thead>
                <tr>';
            foreach ($columns as $column) {
                $html .= '<th>' . $column . '</th>';
            }
            $html .= '
                </tr>
            </thead>';
        }
        return $html;
    }

    public function table_foot($columns = array())
    {
        $html = '';
        if ($columns) {
            $html .= '
            <tfoot>
                <tr>';
            foreach ($columns as $column) {
                $html .= '<th>' . $column . '</th>';
            }
            $html .= '
                </tr>
            </tfoot>';
        }
        $html .= '
        </table>';
        return $html;
    }

    public function modal_popup($id = 'popup-form', $title = '', $content = '')
    {
        return '
        <div id="' . $id . '" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">' . $title . '</h4>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-notice"></div>
                        ' . $content . '
                    </div>
                </div>
            </div>
        </div>';
    }
}
new IRCRM_Admin_Pages();

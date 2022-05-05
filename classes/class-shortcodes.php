<?php

/**
 * This file contains shortcodes.
 */

class IRCRM_Shortcodes
{

    public function __construct()
    {
        add_shortcode('ircrm-edit-profile', [$this, 'edit_profile']);
        add_shortcode('ircrm-dashboard', [$this, 'dashboard']);
        add_shortcode('ircrm-contacts', [$this, 'contacts']);
        add_shortcode('ircrm-edit-contact', [$this, 'edit_contact']);
    }

    public function edit_profile() {

        ob_start();

        $html           = '';
      	$user           = wp_get_current_user();
        $user_id        = $user->ID;
        $is_realtor     = current_user_can('is_ircrm_realtor');
        $admin_page     = new IRCRM_Admin_Pages();

        $html .= $admin_page->page_top('Profile');

        if($is_realtor) {

        $html .= '
            <form method="post" action="" name="form-update-profile" id="form-update-profile" class="validate">
          		<input type="hidden" name="user_id" id="user_id" value="'. $user_id .'">
          		<table class="form-table" role="presentation">
          			<tbody>
          				<tr class="user-user-login-wrap has-description">
          					<th><label for="user_login">Username</label></th>
          					<td><input type="text" name="user_login" id="user_login" value="'. $user->user_login .'" disabled="disabled" class="regular-text">
          					<span class="description">Usernames cannot be changed.</span></td>
          				</tr>
          				<tr class="user-first-name-wrap">
          					<th><label for="first_name">First Name</label></th>
          					<td><input type="text" name="first_name" id="first_name" value="'. $user->first_name .'" class="regular-text"></td>
          				</tr>
          				<tr class="user-last-name-wrap">
          					<th><label for="last_name">Last Name</label></th>
          					<td><input type="text" name="last_name" id="last_name" value="'. $user->last_name .'" class="regular-text"></td>
          				</tr>
          				<tr class="user-email-wrap">
          					<th><label for="user_email">Email <span class="description">(required)</span></label></th>
          					<td><input type="email" name="user_email" id="user_email" aria-describedby="email-description" value="'. $user->user_email .'" class="regular-text ltr" required>
          					</td>
          				</tr>
          				<tr class="user-pass1-wrap has-description">
          					<th><label for="pass1">New Password</label></th>
          					<td><input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off">
          					<span class="description">Leave it blank if you\'re not changing your password.</span></td>
          				</tr>
          				<tr class="user-pass2-wrap has-description">
          					<th><label for="pass2">Repeat New Password</label></th>
          					<td><input type="password" name="pass2" id="pass2" class="regular-text" value="" autocomplete="off">
          					<span class="description">Leave it blank if you\'re not changing your password.</span></td>
          				</tr>
          				<tr class="submit-wrap">
          					<th scope="row">&nbsp;</th>
          					<td><input type="submit" class="btn btn-blue" value="Update Profile"></td>
          				</tr>
          			</tbody>
          		</table>
          	</form>
        ';

        }

        $html .= $admin_page->page_bottom();

        echo $html;

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function dashboard() {

        ob_start();

        $html           = '';
        $deactivated    = false;
        $is_realtor     = current_user_can('is_ircrm_realtor');
        $admin_page     = new IRCRM_Admin_Pages();

        $html .= $admin_page->page_top('Dashboard');


        $html .= $admin_page->page_bottom();

        echo $html;

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function contacts() {

        ob_start();

        $html           = '';
        $deactivated    = false;
        $is_realtor     = current_user_can('is_ircrm_realtor');
        $admin_page     = new IRCRM_Admin_Pages();
        $thead_cols     = IRCRM_Functions::table_columns();
        $keydate_labels = IRCRM_Functions::get_keydate_labels();
        $keydate_label  = IRCRM_Functions::get_value($_GET['keydate_label']);
        $date_from      = IRCRM_Functions::get_value($_GET['date_from']);
        $date_to        = IRCRM_Functions::get_value($_GET['date_to']);
        $edit_base_url  = home_url('contact/');
        $user_id        = get_current_user_id();
        $user           = get_user_by('id', $user_id);
        $realtor_status = get_user_meta($user_id, 'realtor_status', true);
        $user_ids       = null;

        if ($realtor_status == 'inactive') {
            $deactivated = true;
        }

        if ($is_realtor)
            $thead_cols[2] = "NL";

        $action_btn = $is_realtor ? '<a href="'. $edit_base_url .'new" class="btn btn-blue">Add New</a> <button type="button" class="btn btn-red btn-deleteselected">Delete Selected</button>' : '';
        //$action_btn .= ' <a class="btn-advancedsearch" href="" >Advanced Search</a>';
        $html      .= $admin_page->page_top('Contacts', $action_btn);

        include_once IRCRM_PATH . 'includes/form-advanced-search.php';

        if ($is_realtor && ! $deactivated) :

            $args = array(
                'post_type' => 'ircrm_contact',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'author' => $user_id,
            );
            $contacts = get_posts($args);

            $export_all         = implode(',',range(2,count($thead_cols)-1));
            $export_mailing     = '2,3,10,11,12,13,14';
            $column_filter      = '2,3,4,9,10,11,12,13';
            $column_visible     = '0,2,3,4,9,10,11,12,13';
            $table_props        = 'id="contacts-list" class="ircrm-data-table" data-length="50" data-contactcustomsearch="true" data-customsearch="true" data-filter="true" data-colfiltertrigger="Contact Advanced Search" data-colfilter="['. $column_filter .']" data-visiblecols="['. $column_visible .']" data-selectable="true" data-exportcol="true" data-exportmailing="' . $export_mailing . '" data-exportall="' . $export_all . '" data-dom="fBr<\'table-wrapper\'t><\'table-footer clearfix\'pl>"';

            $html .= $admin_page->table_head($thead_cols, $table_props);
            $html .= $admin_page->contact_rows($contacts, $edit_base_url, true);
            $html .= $admin_page->table_foot($thead_cols);
        else:
            $html .= '<p>Sorry, you are not allowed to view this page. Please contact support.</p>';
            $html .= !is_user_logged_in() ? '<p>Click <a href="'. wp_login_url() .'">here</a> to login</p>' : '';
        endif;

        $html .= $admin_page->page_bottom();

        echo $html;

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function edit_contact($atts) {

        ob_start();

        $html           = '';
        $is_admin       = current_user_can('manage_staffs');
        $is_staff       = current_user_can('manage_realtors');
        $is_realtor     = current_user_can('is_ircrm_realtor');
        $is_logged      = is_user_logged_in();
        $user_id        = get_current_user_id();
        $newcontact_id  = absint($GLOBALS['ircrmvars']['new_contact_id']);
        $id             = $atts['id'] && $atts['id']!='new' ? absint($atts['id']) : $newcontact_id;
        $contact        = get_post($id);
        $contact_id     = $contact->ID;
        $mailing_name   = $contact->post_title;
        $author         = $contact->post_author;
        $rank_obj       = wp_get_object_terms($contact_id, 'ircrm_ranking');
        $rank_id        = $rank_obj[0]->term_id;
        $rank           = $rank_obj[0]->name;
        $meta           = get_post_meta($contact_id);
        $newsletter     = $meta['newsletter'][0];
        $pfirstname     = $meta['primary_first_name'][0];
        $plastname      = $meta['primary_last_name'][0];
        $sfirstname     = $meta['secondary_first_name'][0];
        $slastname      = $meta['secondary_last_name'][0];
        $address1       = $meta['address1'][0];
        $address2       = $meta['address2'][0];
        $city           = $meta['city'][0];
        $state          = $meta['state'][0];
        $zipcode        = $meta['zipcode'][0];
        $phone1         = $meta['phone1'][0];
        $phone2         = $meta['phone2'][0];
        $phone3         = $meta['phone3'][0];
        $phone1_desc    = $meta['phone1_desc'][0];
        $phone2_desc    = $meta['phone2_desc'][0];
        $phone3_desc    = $meta['phone3_desc'][0];
        $email1         = $meta['email1'][0];
        $email2         = $meta['email2'][0];
        $email1_desc    = $meta['email1_desc'][0];
        $email2_desc    = $meta['email2_desc'][0];
        $rank_desc      = $meta['rank_desc'][0];
        $keydates_list  = '<div id="keydates-'. $contact_id .'" class="keydates-list"></div>';
        $notes_list     = '<div id="notes-'. $contact_id .'" class="notes-list"></div>';
        $keydates       = IRCRM_Functions::get_keydates($meta);
        $keydates_desc  = IRCRM_Functions::get_keydates_desc($meta);
        $keydates_rem   = IRCRM_Functions::get_keydates_reminder($meta);
        $notes          = IRCRM_Functions::get_notes($meta);
        $updated        = IRCRM_Functions::get_value($_GET['updated']);

        $new            = $newcontact_id==$id ? true : false;
        $can_view       = $is_logged && ($is_staff || $user_id==$author || $new) ? true : false;
        $can_edit       = current_user_can('edit_contact') && ($is_admin || $user_id==$author || $new) ? true : false;

        if($can_view) {

            if(! empty($keydates)) {
                krsort($keydates);
                $keydates_list = '<div id="keydates-'. $contact_id .'" class="keydates-list">';
                foreach ($keydates as $key => $value) {
                    $label = ucwords(str_replace('_', ' ', substr($key, 8)));
                    $keydates_list .= '<span class="'. $key .'_'. $contact_id .'"><b>'. $label .':</b> '. $value[0] .' '. (!$is_staff ? '<i class="fa fa-times t-red btn-delete-meta" data-id="'. $contact_id .'" data-key="'. $key .'"></i>' : '') .'<br></span>';
                }
                $keydates_list .= '</div>';
            }

            if(! empty($notes)) {
                krsort($notes);
                $notes_list = '<div id="notes-'. $contact_id .'" class="notes-list">';
                foreach ($notes as $key => $value) {
                    $note_date = date('Y-m-d',substr($key, 5));
                    $notes_list .= '<span class="'. $key .'_'. $contact_id .'"><b>'. $note_date .'</b> '. (! $is_staff ? '<i class="fa fa-times t-red btn-delete-meta" data-id="'. $contact_id .'" data-key="'. $key .'"></i>' : '') .'<br>'. $value[0] .'<br><br></span>';
                }
                $notes_list .= '</div>';
            }

            $rankings = get_terms(array(
                'taxonomy' => 'ircrm_ranking',
                'hide_empty' => false,
            ));

            if ($rankings) {
                $ranks = '';
                foreach ($rankings as $ranking) {
                    $ranking_id     = $ranking->term_id;
                    $ranking_title  = $ranking->name;
                    $ranks .= '<option value="' . $ranking_id . '" '. ( $ranking_id==$rank_id || ( $new && $ranking_title=='B' ) ? 'selected' : '' ) .'>' . $ranking_title . '</option>';
                }
            }

            $html .= '
            <form method="post" action="" id="form-save-contact" name="form-save-contact" class="validate">';
                if($updated) {
                    $html .= '
                    <div class="row">
                        <div class="col-md-4 col-sm-12"></div>
                        <div class="col-md-8 col-sm-12"><span class="t-green">Contact was successfully saved.</span></div>
                    </div>';
                }
                if($is_admin && $new) {
                    $realtor_dropdown = '
                    <span><select name="realtorid" class="realtorid validate[required]">
                        <option value="">Choose User</option>';
                        $args = array(
                            'role' => 'ircrm_realtor',
                        );
                        $realtors = get_users($args);
                        if ($realtors) :
                            foreach ($realtors as $realtor) {
                                $rid = $realtor->ID;
                                $realtor_dropdown .= '<option value="' . $rid . '" '. ( $author==$rid ? 'selected' : '' ) .'>' . $realtor->user_login . '</option>';
                            }
                        endif;
                        $realtor_dropdown .= '
                    </select></span>';
                    $html .= '
                    <div class="row">
                        <div class="col-md-4 col-sm-12">User ID</div>
                        <div class="col-md-8 col-sm-12">' . $realtor_dropdown . '</div>
                    </div>';
                }

                $html .= '
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Receive Newsletter?</div>
                        <div class="col-md-8 col-sm-12">
                            <label><input type="radio" name="newsletter" id="newletter1" value="Yes" '. ( $newsletter=='Yes' || $new ? 'checked' : '' ) .' '. ( ! $can_edit ? 'disabled' : '' ) .' /> Yes</label>&nbsp;&nbsp;&nbsp;
                            <label><input type="radio" name="newsletter" id="newletter2" value="No" '. ( $newsletter=='No' ? 'checked' : '' ) .' '. ( ! $can_edit ? 'disabled' : '' ) .' /> No</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Category</div>
                        <div class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <select name="rank" id="rank" '. ( ! $can_edit ? 'disabled' : '' ) .'>
                                        <option value="">Choose Category</option>
                                        ' . $ranks . '
                                    </select>
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="text" placeholder="Description" name="rank_desc" id="rank_desc" value="'. $rank_desc .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 col-sm-12">Primary First Name</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="pfirstname" id="pfirstname" value="'. $pfirstname .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Primary Last Name</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="plastname" id="plastname" value="'. $plastname .'" class="validate[required]" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Secondary First Name</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="sfirstname" id="sfirstname" value="'. $sfirstname .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Secondary Last Name (If Different)</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="slastname" id="slastname" value="'. $slastname .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Address 1</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="address1" id="address1" value="'. $address1 .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Address 2</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="address2" id="address2" value="'. $address2 .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">City</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="city" id="city" value="'. $city .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">State</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="state" id="state" value="'. $state .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Zipcode</div>
                        <div class="col-md-8 col-sm-12">
                            <input type="text" name="zipcode" id="zipcode" value="'. $zipcode .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Phone 1</div>
                        <div class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <input type="tel" placeholder="000-000-0000" name="phone1" id="phone1" value="'. $phone1 .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="text" placeholder="Description" name="phone1_desc" id="phone1_desc" value="'. $phone1_desc .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Phone 2</div>
                        <div class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <input type="tel" placeholder="000-000-0000" name="phone2" id="phone2" value="'. $phone2 .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="text" placeholder="Description" name="phone2_desc" id="phone2_desc" value="'. $phone2_desc .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Phone 3</div>
                        <div class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <input type="tel" placeholder="000-000-0000" name="phone3" id="phone3" value="'. $phone3 .'" class="validate[custom[phone]]" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="text" placeholder="Description" name="phone3_desc" id="phone3_desc" value="'. $phone3_desc .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Email Address 1</div>
                        <div class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <input type="email" placeholder="user@example.com" name="email1" id="email1" value="'. $email1 .'" class="validate[required,custom[email]]" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="text" placeholder="Description" name="email1_desc" id="email1_desc" value="'. $email1_desc .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Email Address 2</div>
                        <div class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <input type="email" placeholder="user@example.com" name="email2" id="email2" value="'. $email2 .'" class="validate[custom[email]]" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="text" placeholder="Description" name="email2_desc" id="email2_desc" value="'. $email2_desc .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                            </div>
                        </div>
                    </div>
                ';

                for ($i = 1; $i <= 7; $i++){
                    $html .= '
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Key Date ' . $i . '</div>
                        <div class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-5 col-sm-12">
                                    <input type="date" name="key_dates[]" id="key_date_'. $i .'" value="'. $keydates['keydate_' . ($i-1)][0] .'" class="validate[custom[date]]" '. ( ! $can_edit ? 'disabled' : '' ) .' min="' . strftime("%Y-%m-%d") . '"/>
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="text" placeholder="Description" name="key_date_desc[]" id="key_date_desc_' . $i .'" value="' . $keydates_desc['keydatedesc_'. ($i-1)][0] . '" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                                <div class="col-md-7 col-sm-12">
                                    <input type="checkbox" name="key_date_reminder[]" id="key_date_reminder_' . $i . '" ' . ($keydates_rem['keydaterem_'.($i-1)][0] !== 'false' ? 'checked="checked"' : '') . (!$can_edit ? 'disabled': '') . ' />
                                    <label for="key_date_reminder_' . $i . '">Reminder</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    ';
                }

                if($new) {
                    $html .= '
                    <div class="row">
                        <div class="col-md-4 col-sm-12">Notes</div>
                        <div class="col-md-8 col-sm-12">
                            <textarea name="notes" id="notes" rows="5" /></textarea>
                        </div>
                    </div>';
                }

                if($can_edit) {
                    $html .= '
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <input type="hidden" name="id" id="id" value="'. ( $new ? '' : $id ) .'" />
                            <input type="hidden" name="frontend" id="frontend" value="'. ( $atts['frontend'] ? true : false ) .'" />
                            <input type="submit" value="Save Changes" class="btn btn-blue">
                        </div>
                    </div>
                    ';
                }

            $html .= '
            </form>';

            if(!$new) {
                $notes_list = '
                <div id="notes-list" class="notes-list">
                    <h3>Notes</h3><br>';
                    if($can_edit) {
                        $notes_list .='
                        <div class="row note-item" id="new-note">
                            <div class="col-md-3 col-sm-12">
                                <input type="text" name="note-date" class="note-date datetime-picker" value="" placeholder="MM/DD/YYYY" />
                            </div>
                            <div class="col-md-'. ( $can_edit ? '7' : '9' ) .' col-sm-12">
                                <textarea name="note-content" class="note-content" rows="5" placeholder="Type your notes here..." /></textarea>
                            </div>';
                            if($can_edit) {
                                $notes_list .='
                                <div class="col-md-2 col-sm-12">
                                    <button type="button" class="btn btn-blue btn-savenote" data-id="'. $id .'" data-key="new" />Save</button>
                                </div>';
                            }
                            $notes_list .='
                        </div>';
                    }

                    $notes = IRCRM_Functions::get_notes($meta);
                    if(! empty($notes)) {
                        krsort($notes);
                        foreach ($notes as $key => $value) {
                            $note_date = date('m/d/Y h:i a',substr($key, 5));
                            $notes_list .='
                            <div class="row note-item '. $key .'_'. $id .'">
                                <div class="col-md-3 col-sm-12">
                                    <input type="text" name="note-date" class="note-date datetime-picker" value="'. $note_date .'" '. ( ! $can_edit ? 'disabled' : '' ) .' />
                                </div>
                                <div class="col-md-'. ( $can_edit ? '7' : '9' ) .' col-sm-12">
                                    <textarea name="note-content" class="note-content" '. ( ! $can_edit ? 'disabled' : '' ) .' />'. $value[0] .'</textarea>
                                </div>';
                                if($can_edit) {
                                    $notes_list .='
                                        <div class="col-md-2 col-sm-12 action-buttons">
                                            <button class="btn btn-blue btn-savenote" data-id="'. $id .'" data-key="'. $key .'" />Save</button>
                                            <button class="btn btn-red btn-delete-meta" data-id="'. $id .'" data-key="'. $key .'" />Delete</button>
                                        </div>';
                                }
                                $notes_list .='
                            </div>';
                        }
                    }
                $notes_list .= '
                </div>';

                $html .= $notes_list;
            }

        } else {
            $html .= 'Sorry, you cannot view this contact.';
        }

        echo $html;

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

}
new IRCRM_Shortcodes();

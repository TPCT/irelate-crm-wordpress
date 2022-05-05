<?php

/**
 * This file will create Custom Rest API End Points.
 */
class IRCRM_Ajax_Post
{

    public function __construct()
    {
        add_action('init', [$this, 'ajax_post_init']);
    }

    public function ajax_post_init()
    {
        add_action('wp_ajax_save_contact', [$this, 'save_contact']);
        add_action('wp_ajax_save_realtor', [$this, 'save_realtor']);
        add_action('wp_ajax_save_ranking', [$this, 'save_ranking']);
        add_action('wp_ajax_update_status', [$this, 'update_status']);
        add_action('wp_ajax_delete_item', [$this, 'delete_item']);
        add_action('wp_ajax_delete_meta', [$this, 'delete_meta']);
        add_action('wp_ajax_import_contacts', [$this, 'import_contacts']);
        add_action('wp_ajax_save_note', [$this, 'save_note']);
        add_action('wp_ajax_save_keydate', [$this, 'save_keydate']);
        add_action('wp_ajax_advanced_search', [$this, 'advanced_search']);
    }

    private function array_text_sanitizer($array){
        for ($i = 0; $i < count($array); $i++)
            $array[$i] = sanitize_text_field($array[$i]);
        return $array;
    }

    public function save_contact()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $frontend       = sanitize_text_field($_POST['frontend']);
            $action         = sanitize_text_field($_POST['action']);
            $id             = absint(sanitize_text_field($_POST['id']));
            $realtorid      = absint(sanitize_text_field($_POST['realtorid']));
            $rank           = absint(sanitize_text_field($_POST['rank']));
            $rank_desc      = sanitize_text_field($_POST['rank_desc']);
            $newsletter     = sanitize_text_field($_POST['newsletter']);
            $pfirstname     = sanitize_text_field($_POST['pfirstname']);
            $plastname      = sanitize_text_field($_POST['plastname']);
            $sfirstname     = sanitize_text_field($_POST['sfirstname']);
            $slastname      = sanitize_text_field($_POST['slastname']);
            $address1       = sanitize_text_field($_POST['address1']);
            $address2       = sanitize_text_field($_POST['address2']);
            $city           = sanitize_text_field($_POST['city']);
            $state          = sanitize_text_field($_POST['state']);
            $zipcode        = sanitize_text_field($_POST['zipcode']);
            $phone1         = IRCRM_Functions::phoneFormatter($_POST['phone1']);
            $phone2         = IRCRM_Functions::phoneFormatter($_POST['phone2']);
            $phone3         = IRCRM_Functions::phoneFormatter($_POST['phone3']);
            $phone1_desc    = sanitize_text_field($_POST['phone1_desc']);
            $phone2_desc    = sanitize_text_field($_POST['phone2_desc']);
            $phone3_desc    = sanitize_text_field($_POST['phone3_desc']);
            $email1         = sanitize_email($_POST['email1']);
            $email2         = sanitize_email($_POST['email2']);
            $email1_desc    = sanitize_text_field($_POST['email1_desc']);
            $email2_desc    = sanitize_text_field($_POST['email2_desc']);
            $notes          = sanitize_textarea_field($_POST['notes']);
            $key_dates      = $this->array_text_sanitizer($_POST['key_dates']);
            $key_dates_desc = $this->array_text_sanitizer($_POST['key_date_desc']);
            $key_dates_reminder = $this->array_text_sanitizer($_POST['key_date_reminder']);
			$date_added 	= time();
            $has_error      = false;

            $slastname = $slastname ?: $plastname;

            $response = array(
                'success' => true,
                'reload' => true,
                'message' => array(),
                'action' => $action,
            );
		
            if (current_user_can('manage_contacts') && !current_user_can('is_ircrm_staff')) :

                if (current_user_can('is_ircrm_realtor')) {
                    $realtorid = get_current_user_id();
                    $realtor_status = get_user_meta($realtorid, 'realtor_status', true);
                    if($realtor_status=='inactive') die();
                }

                $args = array(
                    'post_title' => $pfirstname . ' ' . ($sfirstname ? 'and ' . $sfirstname . ' ' : '') . $plastname,
                    'post_type' => 'ircrm_contact',
                    'post_status' => 'publish',
                    'post_content' => '[ircrm-edit-contact]',
                );

                if ($id) {
                    $args['ID'] = $id;
                    $args['post_name'] = $id;
                    $id = wp_update_post($args, true);
                } else {
                    $args['post_author'] = $realtorid;
                    $id = wp_insert_post($args, true);
                    $args['ID'] = $id;
                    $args['post_name'] = $id;
                    wp_update_post($args, true);
                }

                if (!is_wp_error($id)) {
                    wp_set_object_terms($id, $rank, 'ircrm_ranking');
                    if($realtorid) {
                        $realtor = get_user_by('id', $realtorid);
                        update_post_meta($id, 'realtor_id', $realtor->user_login);
                    }
                    if($notes){
                        update_post_meta($id, 'note_'. time(), $notes);
                    }

                    if (!$slastname)
                        $slastname = $plastname;

                    update_post_meta($id, 'newsletter', $newsletter);
                    update_post_meta($id, 'primary_first_name', $pfirstname);
                    update_post_meta($id, 'primary_last_name', $plastname);
                    update_post_meta($id, 'secondary_first_name', $sfirstname);
                    update_post_meta($id, 'secondary_last_name', $slastname);
                    update_post_meta($id, 'address1', $address1);
                    update_post_meta($id, 'address2', $address2);
                    update_post_meta($id, 'city', $city);
                    update_post_meta($id, 'state', $state);
                    update_post_meta($id, 'zipcode', $zipcode);
                    update_post_meta($id, 'phone1', $phone1);
                    update_post_meta($id, 'phone2', $phone2);
                    update_post_meta($id, 'phone3', $phone3);
                    update_post_meta($id, 'phone1_desc', $phone1_desc);
                    update_post_meta($id, 'phone2_desc', $phone2_desc);
                    update_post_meta($id, 'phone3_desc', $phone3_desc);
                    update_post_meta($id, 'email1', $email1);
                    update_post_meta($id, 'email2', $email2);
                    update_post_meta($id, 'email1_desc', $email1_desc);
                    update_post_meta($id, 'email2_desc', $email2_desc);
                    update_post_meta($id, 'rank_desc', $rank_desc);
					update_post_meta($id, 'date_added', $date_added);

                    if ($key_dates){
                        for($i = 0; $i < 7; $i++) {
                            update_post_meta($id, 'keydate_' . $i, $key_dates[$i]);
                            update_post_meta($id, 'keydatedesc_' . $i, $key_dates_desc[$i]);
                            update_post_meta($id, 'keydaterem_' . $i, $key_dates_reminder[$i] ?? 'false');
                        }
                    }


                    $response['id'] = $id;
                    $response['reload'] = false;
                    $response['message'] = 'Contact was successfully saved.';
                    $response['redirect'] = false;

                    if($frontend) {
                        //$response['redirect'] = get_permalink($id) .'?updated=true';
                    }
                    else {
                        //$response['redirect'] = admin_url('admin.php?page=ircrm-edit-contact&id='. $id .'&update=true');
                    }

                } else {
                    $response['message'] = $id->errors;
                    $has_error = true;
                }

            endif;

            if ($has_error == true) {
                $response['success'] = false;
            }

            echo json_encode($response);

        endif;

        die();
    }

    public function import_contacts()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $action    = sanitize_text_field($_POST['action']);
            $user_id   = absint(sanitize_text_field($_POST['id']));
            $file      = esc_url($_POST['attachment']);

            $response = array(
                'success' => true,
                'message' => array(),
                'action' => $action,
                'id' => $user_id,
				'data' => []
            );
            if (current_user_can('manage_staffs')) :
                
                $handle = fopen($file,"r");
                
                if ($handle !== FALSE) {

                    $count=0;
                    //'User ID', 'Newsletter', 'Category', 'Mailing Name', 'Primary First Name', 'Primary Last Name', 'Secondary First Name', 'Secondary Last Name', 'Address 1', 'Address 2', 'City', 'State', 'Zipcode', 'Phone 1', 'Phone 2', 'Phone 3', 'Email Address 1', 'Email Address 2'
                    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
						$response['data'][] = $data;
                        $count++;
                        $realtorid      = $data[0];
                        $newsletter     = $data[1];
                        $category       = $data[2];
                        $mailing_name   = $data[3];
                        $pfirstname     = $data[4];
                        $plastname      = $data[5];
                        $sfirstname     = $data[6];
                        $slastname      = $data[7];
                        $address1       = $data[8];
                        $address2       = $data[9];
                        $city           = $data[10];
                        $state          = $data[11];
                        $zipcode        = $data[12];
                        $phone1         = IRCRM_Functions::phoneFormatter($data[13]);
                        $phone1_desc    = $data[14];
                        $phone2         = IRCRM_Functions::phoneFormatter($data[15]);
                        $phone2_desc    = $data[16];
                        $phone3         = IRCRM_Functions::phoneFormatter($data[17]);
                        $phone3_desc    = $data[18];
                        $email1         = $data[19];
                        $email1_desc    = $data[20];
                        $email2         = $data[21];
                        $email2_desc    = $data[22];
                        $date_added     = $data[23];
                        $datekeys       = [];
                        $datekeysdesc   = [];
                        $datekeysrem    = [];

                        for ($i=1; $i < 8; $i++){
                            $datekeys[] = $data[23 + $i];
                            $datekeysdesc[] = $data[23 + $i + 1];
                            $datekeysrem[]  = $data[23 + $i + 2];
                        }
            
                        if($count===1) continue;

                        $realtor = get_user_by('login', $realtorid);
                        
                        if($realtor) :

                            $args = array(
                                'post_title' => $mailing_name,
                                'post_type' => 'ircrm_contact',
                                'post_status' => 'publish',
                                'post_author' => $realtor->ID,
                            );
                            
                            $id = wp_insert_post($args, true);

                            if (!is_wp_error($id)) {
                                update_post_meta($id, 'realtor_id', $realtorid);
                                update_post_meta($id, 'newsletter', $newsletter);
                                update_post_meta($id, 'primary_first_name', $pfirstname);
                                update_post_meta($id, 'primary_last_name', $plastname);
                                update_post_meta($id, 'secondary_first_name', $sfirstname);
                                update_post_meta($id, 'secondary_last_name', $slastname);
                                update_post_meta($id, 'address1', $address1);
                                update_post_meta($id, 'address2', $address2);
                                update_post_meta($id, 'city', $city);
                                update_post_meta($id, 'state', $state);
                                update_post_meta($id, 'zipcode', $zipcode);
                                update_post_meta($id, 'phone1', $phone1);
                                update_post_meta($id, 'phone2', $phone2);
                                update_post_meta($id, 'phone3', $phone3);
                                update_post_meta($id, 'email1', $email1);
                                update_post_meta($id, 'email2', $email2);
                                update_post_meta($id, 'phone1_desc', $phone1_desc);
                                update_post_meta($id, 'phone2_desc', $phone2_desc);
                                update_post_meta($id, 'phone3_desc', $phone3_desc);
                                update_post_meta($id, 'email1_desc', $email1_desc);
                                update_post_meta($id, 'email2_desc', $email2_desc);
								update_post_meta($id, 'date_added', $date_added);

                                for ($i = 0; $i < 7; $i++){
                                    update_post_meta($id, 'keydate_' . $i, $datekeys[$i]);
                                    update_post_meta($id, 'keydatedesc_' . $i, $datekeysdesc[$i]);
                                    update_post_meta($id, 'keydaterem_' . $i, $datekeysrem[$i]);
                                }
                            }
                        endif;
                    }
                    fclose($handle);
                }                
                
            endif;

            echo json_encode($response);

        endif;

        die();
    }

    public function save_realtor()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $action         = sanitize_text_field($_POST['action']);
            $user_id        = absint(sanitize_text_field($_POST['user-id']));
            $user_login     = sanitize_text_field($_POST['user-login']);
            $user_pass      = sanitize_text_field($_POST['user-pass']);
            $user_email     = sanitize_text_field($_POST['user-email']);
            $first_name     = sanitize_text_field($_POST['first-name']);
            $last_name      = sanitize_text_field($_POST['last-name']);
            $realtor_id     = sanitize_text_field($_POST['realtor-id']);
            $realtor_status = sanitize_text_field($_POST['realtor-status']);
            $newsletters    = $_POST['user-newsletter'];
            $has_error      = false;

            $response = array(
                'success' => true,
                'reload' => true,
                'message' => array(),
                'action' => $action,
            );
            if (current_user_can('manage_staffs')) :

                $args = array(
                    'user_email' => $user_email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                );

                if ($user_id) {
                    $args['ID'] = $user_id;
                    if ($user_pass) $args['user_pass'] = $user_pass;
                    $user_id = wp_update_user($args);
                } else {
                    $args['role'] = 'ircrm_realtor';
                    $args['show_admin_bar_front'] = false;
                    $args['user_login'] = $user_login;
                    $args['user_pass'] = $user_pass;
                    $user_id = wp_insert_user($args);
                }

                if (!is_wp_error($user_id)) {
                    update_user_meta($user_id, 'realtor_id', $realtor_id);
                    update_user_meta($user_id, 'realtor_status', $realtor_status);
                    update_user_meta($user_id, 'newsletters', implode(',',$newsletters));
                    $response['newsletters'] = $newsletters;
                    $response['user_id'] = $user_id;
                } else {
                    $response['message'] = $user_id->errors;
                    $has_error = true;
                }

            endif;

            if ($has_error == true) {
                $response['success'] = false;
            }

            echo json_encode($response);

        endif;

        die();
    }

    public function save_ranking()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $action         = sanitize_text_field($_POST['action']);
            $ranking_id     = absint(sanitize_text_field($_POST['ranking-id']));
            $ranking_title  = sanitize_text_field($_POST['ranking-title']);
            $ranking_desc   = sanitize_text_field($_POST['ranking-description']);
            $has_error      = false;

            $response = array(
                'success' => true,
                'message' => array(),
                'action' => $action,
            );
            if (current_user_can('manage_staffs')) :

                $args = array(
                    'description' => $ranking_desc,
                );

                if ($ranking_id) {
                    $args['name'] = $ranking_title;
                    $ranking = wp_update_term($ranking_id, 'ircrm_ranking', $args);
                } else {
                    $ranking = wp_insert_term($ranking_title, 'ircrm_ranking', $args);
                }

                if (!is_wp_error($ranking)) {
                    $response['ranking_id'] = $ranking->term_id;
                } else {
                    $response['message'] = $ranking->errors;
                    $has_error = true;
                }

            endif;

            if ($has_error == true) {
                $response['success'] = false;
            }

            echo json_encode($response);

        endif;

        die();
    }

    public function update_status()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $action    = sanitize_text_field($_POST['action']);
            $user_id   = absint(sanitize_text_field($_POST['id']));

            $response = array(
                'success' => true,
                'message' => array(),
                'action' => $action,
                'id' => $user_id,
            );
            if (current_user_can('manage_staffs')) :

                $status = get_user_meta($user_id, 'realtor_status', true);
                $response['status'] = $status;
                if ($status == 'active') {
                    update_user_meta($user_id, 'realtor_status', 'inactive');
                    $response['status_text'] = 'In-active';
                    $response['btn_text'] = 'Activate';
                } else {
                    update_user_meta($user_id, 'realtor_status', 'active');
                    $response['status_text'] = 'Active';
                    $response['btn_text'] = 'Deactivate';
                }

            endif;

            echo json_encode($response);

        endif;

        die();
    }

    public function delete_item()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $is_admin  = current_user_can('manage_staffs');
            $action    = sanitize_text_field($_POST['action']);
            $id        = sanitize_text_field($_POST['id']);
            $type      = sanitize_text_field($_POST['type']);
            $redirect  = sanitize_url($_POST['redirect']);

            $response = array(
                'success' => true,
                'reload' => true,
                'redirect' => $redirect,
                'message' => array(),
                'action' => $action,
                'id' => $id,
            );
            $has_error = false;

            if ($type == 'selected_contacts') {
                $ids = explode(',',$id);
                foreach ($ids as $i) {
                    $i = absint(str_replace('item-','',$i));
                    $contact = get_post($i);
                    if($is_admin || $contact->post_author == get_current_user_id()) {
                        wp_delete_post($i, true);
                    }
                }
            } 

            if ($type == 'contact') {
                $id = absint($id);
                $contact = get_post($id);
                if($is_admin || $contact->post_author == get_current_user_id()) {
                    $response['reload'] = false;
                    wp_delete_post($id, true);
                }
                else {
                    $has_error = true;
                }
            } 
            else if ($type == 'ranking' && $is_admin) {
                $id = absint($id);
                wp_delete_term($id, 'ircrm_ranking');
            } 
            else if ($type == 'realtor' && $is_admin) {
                $id = absint($id);
                wp_delete_user($id);
            }

            if($has_error==true) {
                $response['success'] = false;
                $response['message'][] = 'You do not have permission to delete this item';
            }

            echo json_encode($response);

        endif;

        die();
    }

    public function delete_meta()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $action    = sanitize_text_field($_POST['action']);
            $id        = absint(sanitize_text_field($_POST['id']));
            $key       = sanitize_text_field($_POST['key']);

            $response = array(
                'success' => true,
                'reload' => false,
                'message' => array(),
                'action' => $action,
                'id' => $id,
                'key' => $key,
            );

            $contact = get_post($id);
            if($contact && (current_user_can('manage_staffs') || $contact->post_author == get_current_user_id())) {
                $delete = delete_post_meta($id, $key);
            }

            if(! $delete) {
                $response['success'] = false;
                $response['message'] = $delete;
            }

            echo json_encode($response);

        endif;

        die();
    }

    public function save_note()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $action  = sanitize_text_field($_POST['action']);
            $id      = absint(sanitize_text_field($_POST['contact-id']));
            $note    = sanitize_textarea_field($_POST['note-content']);
            $date    = sanitize_text_field($_POST['note-date']);
            $stamp   = $date ? strtotime($date) : time();
            $key     = sanitize_text_field($_POST['note-key']);
            $newkey  = 'note_'. $stamp;

            $response = array(
                'success' => true,
                'message' => array(),
                'action' => $action,
                'id' => $id,
                'date' => date('m/d/Y h:i a',$stamp),
                'note' => $note,
                'key' => $key,
                'newkey' => $newkey,
            );

            if($key!='new') {
                delete_post_meta($id, $key);
            }
            
            $newnote = update_post_meta($id, $newkey, $note);

            if(! $newnote) {
                $response['success'] = false;
                $response['message'] = 'Error: Note was not added.';
            }

            echo json_encode($response);

        endif;

        die();
    }

    public function save_keydate()
    {
        if ($_POST) :

            check_ajax_referer('ircrm_token', 'token');

            $action  = sanitize_text_field($_POST['action']);
            $id      = absint(sanitize_text_field($_POST['contact-id']));
            $date    = sanitize_text_field($_POST['keydate-date']);
            $label   = sanitize_text_field($_POST['keydate-label']);

            $response = array(
                'success' => true,
                'message' => array(),
                'action' => $action,
                'id' => $id,
                'date' => $date,
                'label' => $label,
                'label_text' => ucwords(str_replace('_', ' ', $label)),
            );

            $newkeydate = update_post_meta($id, 'keydate_'. $label, $date);

            if(! $newkeydate) {
                $response['success'] = false;
                $response['message'] = 'Error: Key date was not added.';
            }

            echo json_encode($response);

        endif;

        die();
    }

    public function advanced_search()
    {
        if ($_POST){

            check_ajax_referer('ircrm_token', 'token');

            $action             = sanitize_text_field($_POST['action']);
            $user_ids           = $_POST['user-ids'];
			$user_news_letter   = sanitize_text_field($_POST['user-newsletter']);
		
            $response = array(
                'success' => true,
                'message' => array(),
                'action' => $action,
                'user_ids' => $user_ids,
				'news_letters' => $user_news_letter
            );

            $params = '';

            if($user_ids) {
                $user_ids = implode(',',$user_ids);
                $params .= '&user_ids='. $user_ids;
            }
			
			if ($user_news_letter){
				$user_news_letter = implode(',', $user_news_letter);
				$params .= "&news_letters=" . $user_news_letter;
			}

            $response['redirect_url'] = IRCRM_DASHBOARD . $params;

            echo json_encode($response);

		}

        die();
    }

}
new IRCRM_Ajax_Post();

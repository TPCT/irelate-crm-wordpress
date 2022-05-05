<?php
$advanced_search = '';
$advsearch_open = IRCRM_Functions::get_value($_GET['advancedsearch']);
$keydate_labels = IRCRM_Functions::get_keydate_labels();
$keydate_select_options = '';
if($keydate_labels) {
    foreach ($keydate_labels as $key => $value) {
        $keydate_select_options .= '<option value="'. $key .'" '. ($keydate_label == $key ? 'selected' : '') .'>'. $value .'</option>';
    }
}  

if(! $is_realtor) :
    
    $reset_url = IRCRM_DASHBOARD .'&advancedsearch=true';

    $realtor_checkboxes = '<ul>';
    if ($realtors) :
        foreach ($realtors as $realtor) {
            $realtor_id = $realtor->ID;
            $newsletters = get_user_meta($realtor_id, 'newsletters', true);
            $realtor_checkboxes .= '<li><label><input type="checkbox" name="user-ids[]" class="user-ids" data-newsletters="'. $newsletters .'" value="' . $realtor_id . '" ' . ((! empty($userids_arr) && in_array($realtor_id, $userids_arr)) || empty($userids_arr) ? 'checked' : '') . ' />' . $realtor->user_login . '</label></li>';
        }
    endif;
    $realtor_checkboxes .= '</ul>';
else:
    $reset_url  = $GLOBALS['ircrmvars']['frontend_contacts_url'] .'?advancedsearch=true';

endif;

$advanced_search .= '
    <form method="post" action="" id="form-advanced-search" name="form-advanced-search" class="validate" '. ($user_ids || $keydate_label || $advsearch_open ? 'style="display: block;"' : '') .' >';
        if(! $is_realtor) :
        $advanced_search .= '
        <p class="newsletter-delivery">
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Jan" >Jan</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Feb" >Feb</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Mar" >Mar</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Apr" >Apr</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="May" >May</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Jun" >Jun</label>
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Jul" >Jul</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Aug" >Aug</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Sep" >Sep</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Oct" >Oct</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Nov" >Nov</label> 
            <label><input type="checkbox" name="user-newsletter[]" class="user-newsletter" value="Dec" >Dec</label> 
        </p>
        <p><b>Users</b> <a href="" class="btn btn-light-gray btn-unselect-allusers">Unselect All</a><br>'. $realtor_checkboxes .'</p>';
        endif;
        $advanced_search .= '    
        <div class="t-left">
            <input type="submit" value="Search" class="btn btn-blue">&nbsp;&nbsp;&nbsp;<a href="'. $reset_url .'">Reset</a>
        </div>
    </form>        
';
$html .= $advanced_search;

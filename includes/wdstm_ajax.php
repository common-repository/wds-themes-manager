<?php

add_action('wp_ajax_wdstm_create_filter', 'wdstm_create_filter');

function wdstm_create_filter() {

    $filter = sanitize_text_field($_POST['filter']);

    $data = array();

    switch ($filter) {
        case 'day':
            $data['filter'] = wdstm_day_filter();
            break;
        case 'days-period':
            $data['filter'] = wdstm_day_periods_filter();
            break;
        case 'time':
            $data['filter'] = wdstm_time_filter();
            break;
        case 'seasons':
            $data['filter'] = wdstm_seasons_filter();
            break;
        case 'devices':
            $data['filter'] = wdstm_devices_filter();
            break;
        case 'os':
            $data['filter'] = wdstm_os_filter();
            break;
        case 'browsers':
            $data['filter'] = wdstm_browser_filter();
            break;
        case 'countries':
            $data['filter'] = wdstm_country_filter();
            break;
        case 'languages':
            $data['filter'] = wdstm_languages_filter();
            break;
        case 'range_ip':
            $data['filter'] = wdstm_range_ip_filter();
            break;
        case 'taxonomy':
            $data['filter'] = wdstm_taxonomy_filter();
            break;
	    case 'typepage':
            $data['filter'] = wdstm_typepage_filter();
            break;
        case 'phone':
            $data['filter'] = wdstm_phone_filter();
            break;
        default:
            break;
    }

    echo json_encode($data);
    die();
}

add_action('wp_ajax_wdstm_save_filter', 'wdstm_save_filter');
/**
 * save timed coupon
 */
function wdstm_save_filter() {

    global $wpdb;

    $question = esc_html__( 'Success', 'wdsc' );
    $result = esc_html__( 'updated', 'wdsc' );
    $table = wdstm_sign_get_table_name();

    $filterID   = esc_sql(absint(intval($_POST['filter_id'])));
    $operation     = sanitize_text_field($_POST['operation']);
    $editTitle     = sanitize_text_field($_POST['edit_title']);

    $days       = ($_POST['days'] != '')            ? serialize(array_map('sanitize_text_field', wp_unslash($_POST['days'])))    : '';
    $theme      = ($_POST['select-theme'] != '')    ? htmlspecialchars(sanitize_text_field($_POST['select-theme']))     : '';
    $period     = ($_POST['period'] != '')          ? serialize(array_map('sanitize_text_field', wp_unslash($_POST['period'])))  : '';
    $time       = ($_POST['time'] != '')            ? serialize(array_map('sanitize_text_field', wp_unslash($_POST['time'])))    : '';
    $seasons    = ($_POST['seasons'] != '')         ? serialize(array_map('sanitize_text_field', wp_unslash($_POST['seasons'])))  : '';
    $os         = ($_POST['os'] != '')              ? serialize(array_map('sanitize_text_field', wp_unslash($_POST['os'])))       : '';
    $device     = ($_POST['device'] != '')          ? htmlspecialchars(sanitize_text_field($_POST['device']))           : '';
    $typepage   = ($_POST['type_page'] != '')       ? htmlspecialchars(sanitize_text_field($_POST['type_page']))        : '';
    $browser    = ($_POST['browser'] != '')         ? htmlspecialchars(sanitize_text_field($_POST['browser']))          : '';
    $phone      = ($_POST['phone'] != '')           ? htmlspecialchars(sanitize_text_field($_POST['phone']))            : '';
    $br_inc_exc = ($_POST['br_include'] != '')      ? htmlspecialchars(sanitize_text_field($_POST['br_include']))       : '';
    $c_inc_exc  = ($_POST['c_include'] != '')       ? htmlspecialchars(sanitize_text_field($_POST['c_include']))        : '';
    $l_inc_exc  = ($_POST['l_include'] != '')       ? htmlspecialchars(sanitize_text_field($_POST['l_include']))        : '';
    $p_inc_exc  = ($_POST['p_include'] != '')       ? htmlspecialchars(sanitize_text_field($_POST['p_include']))        : '';
    $country    = ($_POST['country'] != '')         ? htmlspecialchars(sanitize_text_field($_POST['country']))          : '';
    $language   = ($_POST['language'] != '')        ? htmlspecialchars(sanitize_text_field($_POST['language']))         : '';
    $range_ip   = ($_POST['range_ip'] != '')        ? serialize(array_map('sanitize_text_field', wp_unslash($_POST['range_ip'])))  : '';
    $title      = ($_POST['title'] != '')           ? htmlspecialchars(sanitize_text_field($_POST['title']))            : '';
    $taxonomy   = ($_POST['taxonomy'] != '')        ? htmlspecialchars(sanitize_text_field($_POST['taxonomy']))         : '';
    $repeater   = ($_POST['repeater'] != '')        ? sanitize_text_field($_POST['repeater'])                           : '';
    $on_off     = ($_POST['on_off'] != '')          ? sanitize_text_field($_POST['on_off'])                             : esc_html('on');


    $data = array(
        'title'             => $title,
        'days'              => $days,
        'theme'             => $theme,
        'period_days'       => $period,
        'repeater'          => $repeater,
        'time'              => $time,
        'seasons'           => $seasons,
        'os'                => $os,
        'device_type'       => $device,
        'typepage'          => $typepage,
        'browser'           => $browser,
        'phone'             => $phone,
        'br_includ'         => $br_inc_exc,
        'c_includ'          => $c_inc_exc,
        'l_includ'          => $l_inc_exc,
        'p_includ'          => $p_inc_exc,
        'country'           => $country,
        'language'          => $language,
        'range_ip'          => $range_ip,
        'taxonomy'          => $taxonomy,
        'on_off'            => $on_off,
        'notes'             => ''
    );

    if($operation == 'save') {

        $r = $wpdb->insert(
            $table,
            $data,
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        if ($r === FALSE) {
	        $question = __( 'ERORR', 'wdsc');
            $result = 'error';
        } else {
            $last_id = $wpdb->insert_id;
            $filters_order = json_decode(get_option('wdstm_order_filter'));
            if(!is_array($filters_order)) {
                $filters_order = array();
            }
            $filters_order[] = $last_id;
            update_option('wdstm_order_filter', json_encode($filters_order));

        }
    } else {

        $data['title'] = $editTitle;

        $r = $wpdb->update(
            $table,
            $data,
            array( 'id' => "$filterID" ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
	            '%s'
            )
        );

        if ($r === FALSE) {
	        $question = __( 'ERORR', 'wdsc');
            $result = 'error';
        }
    }

    $data = array('question' => $question, 'result' => $result );
    echo json_encode($data);
    die();


}

add_action('wp_ajax_wdstm_edit_filter', 'wdstm_edit_filter');
/**
 * edit filter
 */
function wdstm_edit_filter() {

    global $wpdb;
    $id = esc_sql(absint(intval($_POST['id'])));
    $table = wdstm_sign_get_table_name();

    $editResourse = $wpdb->get_row( /** @lang text */
	    "SELECT * FROM $table WHERE id = '$id'", OBJECT);

    $result = wdstm_create_filters($editResourse, 'edit');

    $data = array('filter' => $result, 'id' => $id );

    echo json_encode($data);
    die();


}

add_action('wp_ajax_wdstm_deleter_filter', 'wdstm_deleter_filter');
/**
 * delete filter from database
 */
function wdstm_deleter_filter() {

    global $wpdb;
    $table = wdstm_sign_get_table_name();
    $response = __( 'Success', 'wdsc' );
    $id = abs(intval($_POST['id']));

    $r = $wpdb->delete( $table, array( 'id' => $id ), array('%d') );

    if($r) {
        $filters_order = json_decode(get_option('wdstm_order_filter'));
        if(is_array($filters_order)) {
            if(($key = array_search($id, $filters_order)) !== FALSE){
                array_splice($filters_order, $key, 1);
            }
            update_option('wdstm_order_filter', json_encode($filters_order));
        }
    }

    $data = array('response' => $response, 'result' => $r, 'id' => $id );

    echo json_encode($data);
    die();

}

add_action('wp_ajax_wdstm_activate_filters', 'wdstm_activate_filters');
/**
 * activate plugin
 */
function wdstm_activate_filters() {

    if(get_option('wdstm-activate-plugin') == 'checked' ) {
        update_option('wdstm-activate-plugin', '');
        $result = __( 'Plugin disabled', 'wdsc' );
    } else {
        update_option('wdstm-activate-plugin', 'checked');
        $result = __( 'Plugin enabled', 'wdsc' );
    }

    $data = array('result' => $result );

    echo json_encode($data);
    die();

}

add_action('wp_ajax_wdstm_save_default_theme', 'wdstm_save_default_theme');
/**
 * save default theme
 */
function wdstm_save_default_theme() {

    $default_themes = sanitize_text_field($_POST['theme']);
    $result = esc_html__( 'Saved', 'wdsc' );

    update_option('wdstm_default_theme', $default_themes);

    if(get_option('wdstm_default_theme') == '' ) {
        $result = esc_html__( 'Error', 'wdsc' );
    }

    $data = array('result' => $result );

    echo json_encode($data);
    die();

}

add_action('wp_ajax_wdstm_save_order', 'wdstm_save_order');
/**
 * save order
 */
function wdstm_save_order() {

    $result = esc_html__( 'Success', 'wdsc' );
    $order = htmlspecialchars(sanitize_text_field($_POST['order']));
    update_option('wdstm_order_filter', $order);

    if(get_option('wdstm_order_filter') == '') {
        $result = esc_html__( 'Error', 'wdsc' );
    }

    $data = array('result' => $result );

    echo json_encode($data);
    die();

}

add_action('wp_ajax_wdstm_on_off_filter', 'wdstm_on_off_filter');


/**
 * on/off filter
 */
function wdstm_on_off_filter() {
	global $wpdb;
	$table = wdstm_sign_get_table_name();
	$question = esc_html__( 'Success', 'wdsc' );
	$result = esc_html__( 'updated', 'wdsc' );;
	$id = esc_sql(abs(intval($_POST['id'])));
	$on_off = sanitize_text_field($_POST['on_off']);


	$r = $wpdb->update( $table,
		array( 'on_off' => $on_off),
		array( 'ID' => "$id" ),
		array( '%s' )
	);

	if ($r === FALSE) {
		$question = esc_html__( 'ERORR', 'wdsc');
		$result = esc_html__( 'error', 'wdsc');;
	}

	$data = array('question' => $question, 'result' => $result );
	echo json_encode($data);
	die();
}

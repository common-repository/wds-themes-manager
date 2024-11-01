<?php

use Sinergi\BrowserDetector\Os;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Language;

/**
 * get list of themes
 * @return array
 */
function wdstm_get_themes_list() {
    $themes = wp_get_themes();
    $wp_themes = array();

    foreach ( $themes as $theme ) {
        $name = $theme->get('Name');
        if ( isset( $wp_themes[ $name ] ) )
            $wp_themes[ $name . '/' . $theme->get_stylesheet() ] = $theme;
        else
            $wp_themes[ $name ] = $theme;
    }
    return $wp_themes;
}

/**
 * get stylesheet theme
 * @param $stylesheet
 * @return mixed
 */
function wdstm_get_selected_theme() {

    require_once WDSTM_PLUGIN_DIR . '/includes/mobile-detect/Mobile_Detect.php';

    $filters_order = json_decode(get_option('wdstm_order_filter'));

    $filters_array = wdstm_get_filters();
    $user_theme = '';

    if(is_array($filters_order) && count($filters_order) > 0 ) {
        foreach ($filters_order as $value) {

        	$on_off_filter = $filters_array[$value]->on_off;

        	if($on_off_filter == 'off') {
		        $user_theme = '';
		        continue;
	        }


            //days
            if (($days = $filters_array[$value]->days) != '') {
                $today = wdstm_get_today_day();
                $days = unserialize($days);

                if (in_array($today, $days)) {
                    $user_theme = $filters_array[$value]->theme;
                } else {
                    $user_theme = '';
                    continue;
                }
            }

            //period days
            if (($period = $filters_array[$value]->period_days) != '') {

                $period = unserialize($period);
                $repeater = $filters_array[$value]->repeater;
                $start = $period['from'];
                $end = $period['to'];

                $periodEnd = $start > $end ? $start : $end;
                $periodStart = $start < $end ? $start : $end;

                if ($repeater == '') {
                    $nowDate = date('d-m-Y');
                    if ($periodStart <= $nowDate && $nowDate < $periodEnd) {
                        $user_theme = $filters_array[$value]->theme;
                    } else {
                        $user_theme = '';
                        continue;
                    }
                } else {
                    $nowDate = date('d-m');
                    if (substr($periodStart, 0, -5) <= $nowDate && $nowDate < substr($periodEnd, 0, -5)) {
                        $user_theme = $filters_array[$value]->theme;
                    } else {
                        $user_theme = '';
                        continue;
                    }
                }
            }

            //time
            if (($time = $filters_array[$value]->time) != '') {
                $time = unserialize($time);
                $time_from = strtotime($time["from"]);
                $time_to = strtotime($time["to"]);
                $now_time = strtotime('now');

                $max = $time_from > $time_to ? $time_from : $time_to;
                $min = $time_from < $time_to ? $time_from : $time_to;

                if ($min < $now_time && $now_time < $max) {
                    $user_theme = $filters_array[$value]->theme;
                } else {
                    $user_theme = '';
                    continue;
                }
            }

            //seasons
            if (($seasons = $filters_array[$value]->seasons) != '') {
                $seasons = unserialize($seasons);
                $current_month = wdstm_get_seasons(date('n'));
                if (in_array($current_month, $seasons)) {
                    $user_theme = $filters_array[$value]->theme;
                } else {
                    $user_theme = '';
                    continue;
                }
            }

            //device
            if (($device = $filters_array[$value]->device_type) != '') {
                $detect = new Mobile_Detect;
                $targetDevice = 'desktop';
                if ($detect->isMobile() && !$detect->isTablet()) {
                    $targetDevice = 'mobile';
                } elseif ($detect->isTablet()) {
                    $targetDevice = 'tablet';
                }

                if ($device == $targetDevice) {
                    $user_theme = $filters_array[$value]->theme;
                } else {
                    $user_theme = '';
                    continue;
                }
                
            }

            //Os
            
            if (($os = $filters_array[$value]->os) != '') {

                $detect = new Os();

                $os = unserialize($os);
                
                $targetDevice = $detect->getName();
                if (in_array($targetDevice, $os)) {
                    $user_theme = $filters_array[$value]->theme;
                } else {
                    $user_theme = '';
                    continue;
                }
            }

            //Browsers
            if (($browsers = $filters_array[$value]->browser) != '') {

                $browsers = explode(',', $browsers);

                $detect = new Browser();
                $targetDevice = $detect->getName();

                if (in_array($targetDevice, $browsers) && $filters_array[$value]->br_includ == '1') {

                    $user_theme = $filters_array[$value]->theme;

                } elseif (!in_array($targetDevice, $browsers) && $filters_array[$value]->br_includ == '0') {

                    $user_theme = $filters_array[$value]->theme;

                } else {
                    $user_theme = '';
                    continue;
                }
            }

            //phones
            if (($phones = $filters_array[$value]->phone) != '') {

                $phones = explode(',', $phones);
                $detect_phone = new Mobile_Detect;

                if ($detect_phone->isiPhone()) {
                    $targetDevice = 'iphone';
                } elseif ($detect_phone->isiPad()) {
                	$targetDevice = 'ipad';
	            } elseif ($detect_phone->isBlackBerry()) {
                    $targetDevice = 'blackberry';
                } elseif ($detect_phone->isSamsung()) {
                    $targetDevice = 'samsung';
                } elseif ($detect_phone->isSamsungTablet()) {
                	$targetDevice = 'samsungtablet';
	            } elseif ($detect_phone->isKindle()) {
                    $targetDevice = 'kindle';
                } elseif ($detect_phone->isMotorola()) {
                    $targetDevice = 'motorola';
                } elseif ($detect_phone->isLGTablet()) {
                	$targetDevice = 'lgtablet';
	            } elseif ($detect_phone->isLG()) {
                    $targetDevice = 'lg';
                } elseif ($detect_phone->isSony()) {
                    $targetDevice = 'sony';
                } elseif ($detect_phone->isNokiaLumia()) {
                    $targetDevice = 'nokialumia';
                } elseif ($detect_phone->isFly()) {
                    $targetDevice = 'fly';
                } elseif ($detect_phone->isAlcatel()) {
                    $targetDevice = 'alcatel';
                } elseif ($detect_phone->isAsusTablet()) {
                	$targetDevice = 'asustablet';
	            } elseif ($detect_phone->isAsus()) {
                    $targetDevice = 'asus';
                } elseif ($detect_phone->isNexus()) {
	                $targetDevice = 'nexus';
                } elseif ($detect_phone->isNexusTablet()) {
                	$targetDevice = 'nexustablet';
	            } elseif ($detect_phone->isHTC()) {
	                $targetDevice = 'htc';
                }else {
                    $targetDevice = '';
                }

                if (in_array($targetDevice, $phones) && $filters_array[$value]->p_includ == '1') {

                    $user_theme = $filters_array[$value]->theme;

                } elseif (!in_array($targetDevice, $phones) && $filters_array[$value]->p_includ == '0') {

                    $user_theme = $filters_array[$value]->theme;

                } else {
                    $user_theme = '';
                    continue;
                }

            }

            //Language
            if (($languages = $filters_array[$value]->language) != '') {

                $languages = explode(',', $languages);

                $detect = new Language();
                $targetDevice = $detect->getLanguage();

                if (in_array(strtoupper($targetDevice), $languages) && $filters_array[$value]->l_includ == '1') {

                    $user_theme = $filters_array[$value]->theme;

                } elseif (!in_array(strtoupper($targetDevice), $languages) && $filters_array[$value]->l_includ == '0') {

                    $user_theme = $filters_array[$value]->theme;

                } else {
                    $user_theme = '';
                    continue;
                }
            }

            //Country
            if (($countries = $filters_array[$value]->country) != '') {

                $countries = explode(',', $countries);

                $ip = wdstm_get_real_ip();

                $targetDevice = wdstm_get_country_by_ip($ip);

                if (in_array(strtoupper($targetDevice), $countries) && $filters_array[$value]->c_includ == '1') {

                    $user_theme = $filters_array[$value]->theme;

                } elseif (!in_array(strtoupper($targetDevice), $countries) && $filters_array[$value]->c_includ == '0') {

                    $user_theme = $filters_array[$value]->theme;

                } else {
                    $user_theme = '';
                    continue;
                }
            }

            //ip
            if (($ip = $filters_array[$value]->range_ip) != '') {
                $ip = unserialize($ip);
                $ip_from = ip2long($ip["from"]);
                $ip_to = ip2long($ip["to"]);

                $max = $ip_from > $ip_to ? $ip_from : $ip_to;
                $min = $ip_from < $ip_to ? $ip_from : $ip_to;

                $now_ip = ip2long(wdstm_get_real_ip());
                if ($min < $now_ip && $now_ip < $max) {
                    $user_theme = $filters_array[$value]->theme;
                } elseif ($min == $now_ip || $now_ip == $max) {
                    $user_theme = $filters_array[$value]->theme;
                } else {
                    $user_theme = '';
                    continue;
                }
            }

	        //type page
	        if (($typepage = $filters_array[$value]->typepage) != '') {

		        $typepage = explode(',', $typepage);

		        $targetDevice = wdstm_get_page_id();

		        if (in_array($targetDevice, $typepage)) {

			        $user_theme = $filters_array[$value]->theme;

		        } else {

			        $user_theme = '';
			        continue;
		        }
	        }


	        //Taxonomy
	        if (($taxonomy = $filters_array[$value]->taxonomy) != '') {

            	$taxonomy = explode(',', $taxonomy);

		        $targetDevice = wdstm_get_page_id();

			    if (in_array($targetDevice, $taxonomy)) {

			        $user_theme = $filters_array[$value]->theme;

			    } else {

			        $user_theme = '';
			        continue;
                }
            }

            if ($user_theme != '') {
                break;
            }           
        }
    }

    $selected_theme = ($user_theme != '') ? $user_theme : get_option('wdstm_default_theme');

    $wp_themes = wdstm_get_themes_list();
	$temp = '';
	$firstThemeinList = '';
    foreach($wp_themes as $theme) {
    	if($firstThemeinList == '') {
		    $firstThemeinList == $theme;
	    }

        if ($temp == '' && $theme['Name'] == get_option('wdstm_default_theme')) {
            $temp = $theme;
        }

        if ($theme['Name'] == $selected_theme) {
            return $theme;
        }
    }

    if($temp != '') {
    	return $temp;
    }

    if($firstThemeinList != '') {
    	return $firstThemeinList;
    }

    return false;
}

function wdstm_get_seasons($number) {
    $monthArr = array(
        '1'  => 'winter',
        '2'  => 'winter',
        '3'  => 'spring',
        '4'  => 'spring',
        '5'  => 'spring',
        '6'  => 'summer',
        '7'  => 'summer',
        '8'  => 'summer',
        '9'  => 'autumn',
        '10' => 'autumn',
        '11' =>'autumn',
        '12' => 'winter'
    );

    return $monthArr[$number];
}

/**
 * get stylesheet
 * @return mixed
 */
function wdstm_get_theme_stylesheet() {

    $theme_selected = wdstm_get_selected_theme();  

    return $theme_selected['Stylesheet'];

}

/**
 * get template
 * @return mixed
 */
function wdstm_get_theme_template() {

    $theme_selected = wdstm_get_selected_theme();

    return $theme_selected['Template'];
}

/**
 * get today day
 * @return false|string
 */
function wdstm_get_today_day() {
    $today = date("w", mktime(0,0,0,date("m"),date("d"),date("Y")));
    return $today;
}

/**
 * get day filter
 * @param string $key
 * @param string $string_array
 * @return string
 */
function wdstm_day_filter($key = '', $string_array='') {

    if(!$key || $key == 'edit') {

        $daysArray = array('', '', '', '', '', '', '', '');
        if($key == 'edit') {
            $data = unserialize($string_array);
            foreach ($data as $keyObj=>$value) {
                $daysArray[$value] = 'checked';
            }
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_days" data-class="days">
                <div class="wdstm-data-container">
                    <span><label class="wdstm-head-section">'. __('Day of The Week', 'wdstm') .':</label></span>
                    <span><label for="wdstm-sunday">'. __('Sun', 'wdstm') .'<input type="checkbox" id="wdstm-sunday" value="0" name="days" '. $daysArray['0'] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-monday">'. __('Mon', 'wdstm') .'<input type="checkbox" id="wdstm-monday" value="1" name="days" '. $daysArray['1'] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-tuesday">'. __('Tue', 'wdstm') .'<input type="checkbox" id="wdstm-tuesday" value="2" name="days" '. $daysArray['2'] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-wednesday">'. __('Wed', 'wdstm') .'<input type="checkbox" id="wdstm-wednesday" value="3" name="days" '. $daysArray['3'] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-thursday">'. __('Thu', 'wdstm') .'<input type="checkbox" id="wdstm-thursday" value="4" name="days" '. $daysArray['4'] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-friday">'. __('Fri', 'wdstm') .'<input type="checkbox" id="wdstm-friday" value="5" name="days" '. $daysArray['5'] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-saturday">'. __('Sat', 'wdstm') .'<input type="checkbox" id="wdstm-saturday" value="6" name="days" '. $daysArray['6'] .'><span class="wdstm-checkbox-view"></span></label></span>
                </div>
                <a class="wdstm-remove-filter" data-value="day" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {

        $data = unserialize($string_array);
        $daysArray = array('', '', '', '', '', '', '', '');
        foreach ($data as $keyObj=>$value) {
            $daysArray[$value] = 'checked';
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_days">
                <div class="wdstm-data-container">
                    <span><label class="wdstm-head-section">'. __('Day of The Week', 'wdstm') .':</label></span>
                    <span><label for="wdstm-sunday">'. __('Sun', 'wdstm') .'<input type="checkbox" disabled '. $daysArray['0'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled "></span></label></span>
                    <span><label for="wdstm-monday">'. __('Mon', 'wdstm') .'<input type="checkbox" disabled '. $daysArray['1'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-tuesday">'. __('Tue', 'wdstm') .'<input type="checkbox" disabled '. $daysArray['2'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-wednesday">'. __('Wed', 'wdstm') .'<input type="checkbox" disabled '. $daysArray['3'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-thursday">'. __('Thu', 'wdstm') .'<input type="checkbox" disabled '. $daysArray['4'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-friday">'. __('Fri', 'wdstm') .'<input type="checkbox" disabled '. $daysArray['5'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-saturday">'. __('Sat', 'wdstm') .'<input type="checkbox" disabled '. $daysArray['6'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                </div>
            </li>';
    }

    return $output;
}

/**
 * get period days filter
 * @param string $key
 * @param string $string_array
 * @return string
 */
function wdstm_day_periods_filter($key = '', $string_array='', $repeater = '') {

    if(!$key || $key == 'edit') {

        $data = array('from' => '', 'to' => '' );
        $repeat = '';

        if($key == 'edit') {
            $data = unserialize($string_array);
            $repeat = ($repeater == "1") ? 'checked' : '';
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_period-days" data-class="period">
                <label class="wdstm-repeater" for="wdstm-repeater">'. esc_html__('Repeat', 'wdstm') .'<input type="checkbox" id="wdstm-repeater" value="1" name="repeater" '. $repeat .'><span class="wdstm-checkbox-view"></span></label>
                <div class="wdstm-data-container">
                     <label class="wdstm-head-section">'. esc_html__('Period Days', 'wdstm') .':</label>
                    <input type="text" class="large-text regular-text" name="period-from" readonly placeholder="'. esc_attr__('Click to choose date', 'wdstm').'" value="'. $data["from"] .'">
                    <span> - </span>
                    <input type="text" class="large-text regular-text" name="period-to" readonly placeholder="'. esc_attr__('Click to choose date', 'wdstm').'" value="'. $data["to"] .'">
                </div>
                <a class="wdstm-remove-filter" data-value="days-period" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = unserialize($string_array);
	    $repeat = ($repeater == "1") ? 'checked' : '';
        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_period-days">
				<label class="wdstm-repeater" for="wdstm-repeater">'. esc_html__('Repeat', 'wdstm') .'<input type="checkbox" disabled '. $repeat .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label>
                <div class="wdstm-data-container">
                    <label class="wdstm-head-section">'. esc_html__('Period Days', 'wdstm') .':</label>
                    <input type="text" class="large-text regular-text" readonly value="'. $data["from"] .'">
                    <span> - </span>
                    <input type="text" class="large-text regular-text" readonly value="'. $data["to"] .'">
                </div>
            </li>';
    }

    return $output;
}

/**
 * get time filter
 * @param string $key
 * @param string $string_array
 * @return string
 */
function wdstm_time_filter($key = '', $string_array='') {

    if(!$key || $key == 'edit') {

        $data = array('from' => '', 'to' => '' );

        if($key == 'edit') {
            $data = unserialize($string_array);
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_period-time" data-class="time">
                <div class="wdstm-data-container">
                    <label class="wdstm-head-section">'. esc_html__('Period Time', 'wdstm') .':</label>
                    <input type="text" class="large-text regular-text" id="timeformat1" name="time-from" placeholder="'. esc_attr__('Click to choose time', 'wdstm').'" value="'. $data["from"] .'">
                    <span> - </span>
                    <input type="text" class="large-text regular-text" id="timeformat2" name="time-to" placeholder="'. esc_attr__('Click to choose time', 'wdstm').'" value="'. $data["to"] .'">
                </div>
                <a class="wdstm-remove-filter" data-value="time" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = unserialize($string_array);
        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_period-time">
                <div class="wdstm-data-container">
                    <label class="wdstm-head-section">'. esc_html__('Period Time', 'wdstm') .':</label>
                    <input type="text" class="large-text regular-text" readonly value="'. $data["from"] .'">
                    <span> - </span>
                    <input type="text" class="large-text regular-text" readonly value="'. $data["to"] .'">
                </div>
            </li>';
    }

    return $output;
}

/**
 * get seasons filter
 * @param string $key
 * @param string $string_array
 * @return string
 */
function wdstm_seasons_filter($key = '', $string_array='') {

    if(!$key || $key == 'edit') {
        $seasonArray = array('', '', '', '');

        if($key == 'edit') {
            $data = unserialize($string_array);
            foreach ($data as $key=>$value) {
                $seasonArray[$key] = 'checked';
            }
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_period-seasons" data-class="seasons">
                <div class="wdstm-data-container">
                    <span><label class="wdstm-head-section">'. esc_html__('Seasons', 'wdstm') .':</label></span>
                    <span><label for="wdstm-winter">'.esc_html__('Winter', 'wdstm').'<input type="checkbox" value="winter" id="wdstm-winter" name="seasons" '. $seasonArray[0] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-spring">'.esc_html__('Spring', 'wdstm').'<input type="checkbox" value="spring" id="wdstm-spring" name="seasons" '. $seasonArray[1] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-summer">'.esc_html__('Summer', 'wdstm').'<input type="checkbox" value="summer" id="wdstm-summer" name="seasons" '. $seasonArray[2] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-autumn">'.esc_html__('Autumn', 'wdstm').'<input type="checkbox" value="autumn" id="wdstm-autumn" name="seasons" '. $seasonArray[3] .'><span class="wdstm-checkbox-view"></span></label></span>
                </div>
                <a class="wdstm-remove-filter" data-value="seasons" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = unserialize($string_array);
        $seasonArray = array('', '', '', '');
        foreach ($data as $key=>$value) {
            $seasonArray[$key] = 'checked';
        }
        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_period-seasons"">
                <div class="wdstm-data-container">
                    <span><label class="wdstm-head-section">'. esc_html__('Seasons', 'wdstm') .':</label></span>
                    <span><label for="wdstm-winter">'.esc_html__('Winter', 'wdstm').'<input type="checkbox" disabled '. $seasonArray[0] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-spring">'.esc_html__('Spring', 'wdstm').'<input type="checkbox" disabled '. $seasonArray[1] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-summer">'.esc_html__('Summer', 'wdstm').'<input type="checkbox" disabled '. $seasonArray[2] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-autumn">'.esc_html__('Autumn', 'wdstm').'<input type="checkbox" disabled '. $seasonArray[3] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                </div>
            </li>';
    }

    return $output;
}

/**
 * get devices filter
 * @param string $key
 * @param string $string
 * @return string
 */
function wdstm_devices_filter($key = '', $string='') {

    if(!$key || $key == 'edit') {

        $deviceArr = array(
            'desktop'   => '',
            'tablet'    => '',
            'mobile'    => ''
        );

        if($key == 'edit') {
            $deviceArr[$string] = 'checked';
        }
        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_devices" data-class="device">
                <div class="wdstm-data-container">
                    <fieldset data-filter="devices">
                        <span><label class="wdstm-head-section">'.esc_html__('Device', 'wdstm').':</label></span>
                        <span><label for="wdstm-desktop">'.esc_html__('Desktop', 'wdstm').'<input type="radio" id="wdstm-desktop" class="wdstm-device" name="device" value="desktop" '. $deviceArr['desktop'] .'><span class="wdstm-checkbox-view"></span></label></span>
                        <span><label for="wdstm-tablet">'.esc_html__('Tablet', 'wdstm').'<input type="radio" id="wdstm-tablet" name="device" class="wdstm-device" value="tablet" '. $deviceArr['tablet'] .'><span class="wdstm-checkbox-view"></span></label></span>
                        <span><label for="wdstm-mobile">'.esc_html__('Mobile', 'wdstm').'<input type="radio" id="wdstm-mobile" name="device" class="wdstm-device" value="mobile" '. $deviceArr['mobile'] .'><span class="wdstm-checkbox-view"></span></label></span>
                    </fieldset>

                </div>
                <a class="wdstm-remove-filter" data-value="devices" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {

        $deviceArr = array(
            'desktop'   => '',
            'tablet'    => '',
            'mobile'    => ''
        );

        $deviceArr[$string] = 'checked';

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_devices">
                <div class="wdstm-data-container">
                    <fieldset>
                        <span><label class="wdstm-head-section">'.esc_html__('Device', 'wdstm').':</label></span>
                        <span><label for="wdstm-desktop">'.esc_html__('Desktop', 'wdstm').'<input type="radio" disabled '. $deviceArr['desktop'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                        <span><label for="wdstm-tablet">'.esc_html__('Tablet', 'wdstm').'<input type="radio" disabled '. $deviceArr['tablet'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                        <span><label for="wdstm-mobile">'.esc_html__('Mobile', 'wdstm').'<input type="radio" disabled '. $deviceArr['mobile'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    </fieldset>

                </div>
            </li>';
    }

    return $output;
}

/**
 * get type page filter
 * @param string $key
 * @param string $string
 * @return string
 */
function wdstm_typepage_filter($key = '', $string='') {

	$frontPageDisplays = wdstm_front_page_displays();

	$pagesArr = array(
		'frontpage' => '',
		'homepage' => ''
	);

	if(!$key || $key == 'edit') {

		if($key == 'edit') {
			$stringToArray = explode(',', $string);
			foreach ( $pagesArr as $page=>$value ) {
				if(in_array($frontPageDisplays[$page], $stringToArray)) {
					$pagesArr[$page] = 'checked';
				}
			}

		}

		$output = '<li class="wdstm-metabox__item wdstm-metabox__item_typepage" data-class="type_page">
                <div class="wdstm-data-container">
                    <fieldset>
                        <span><label class="wdstm-head-section">'.esc_html__('Type Page', 'wdstm').':</label></span>
                        <span><label for="wdstm-front">'.esc_html__('Front Page', 'wdstm').'<input type="checkbox" id="wdstm-front" name="typepage" value="'. $frontPageDisplays['frontpage'] .'" '. $pagesArr['frontpage'] .'><span class="wdstm-checkbox-view"></span></label></span>                        
                    </fieldset>
                </div>
                <a class="wdstm-remove-filter" data-value="typepage" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
	} else {

		$stringToArray = explode(',', $string);
		foreach ( $pagesArr as $page=>$value ) {
			if(in_array($frontPageDisplays[$page], $stringToArray)) {
				$pagesArr[$page] = 'checked';
			}
		}

		$output = '<li class="wdstm-metabox__item wdstm-metabox__item_typepage">
                <div class="wdstm-data-container">
                    <fieldset>
                        <span><label class="wdstm-head-section">'.esc_html__('Type Page', 'wdstm').':</label></span>
                        <span><label for="wdstm-front">'.esc_html__('Front Page', 'wdstm').'<input type="checkbox" disabled '. $pagesArr['frontpage'] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>                        
                    </fieldset>
                </div>
            </li>';
	}

	return $output;
}

/**


/**
 * get os filter
 * @param string $key
 * @param string $string_array
 * @return string
 */
function wdstm_os_filter($key = '', $string_array = '') {

    if(!$key || $key == 'edit') {

        $osArray = array('', '', '', '', '', '');

        if($key == 'edit') {
            $data = unserialize($string_array);
            foreach ($data as $key=>$value) {
                $osArray[$key] = 'checked';
            }
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_os" data-class="os">
                <div class="wdstm-data-container" data-filter="os">
                    <span><label class="wdstm-head-section">'. esc_html__('OS', 'wdstm') .':</label></span>
                    <span><label for="wdstm-windows">'.esc_html__('Windows', 'wdstm').'<input type="checkbox" value="Windows" id="wdstm-windows" class="wdstm-type-os" name="os" '. $osArray[0] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-macos">'.esc_html__('macOS', 'wdstm').'<input type="checkbox" value="OS X" id="wdstm-macos" class="wdstm-type-os" name="os" '. $osArray[1] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-linux">'.esc_html__('Linux', 'wdstm').'<input type="checkbox" value="Linux" id="wdstm-linux" class="wdstm-type-os" name="os" '. $osArray[2] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-ios">'.esc_html__('iOS', 'wdstm').'<input type="checkbox" value="iOS" id="wdstm-ios" class="wdstm-type-os" name="os" '. $osArray[3] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-android">'.esc_html__('Android', 'wdstm').'<input type="checkbox" value="Android" id="wdstm-android" class="wdstm-type-os" name="os" '. $osArray[4] .'><span class="wdstm-checkbox-view"></span></label></span>
                    <span><label for="wdstm-winphone">'.esc_html__('WinPhone', 'wdstm').'<input type="checkbox" value="Windows Phone" id="wdstm-winphone" class="wdstm-type-os" name="os" '. $osArray[5] .'><span class="wdstm-checkbox-view"></span></label></span>
                </div>
                <a class="wdstm-remove-filter" data-value="os" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = unserialize($string_array);
        $osArray = array('', '', '', '', '', '');
        foreach ($data as $key=>$value) {
            $osArray[$key] = 'checked';
        }
        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_os">
                <div class="wdstm-data-container">
                    <span><label class="wdstm-head-section">'. esc_html__('OS', 'wdstm') .':</label></span>
                    <span><label for="wdstm-windows">'.esc_html__('Windows', 'wdstm').'<input type="checkbox" disabled '. $osArray[0] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-macos">'.esc_html__('macOS', 'wdstm').'<input type="checkbox" disabled '. $osArray[1] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-linux">'.esc_html__('Linux', 'wdstm').'<input type="checkbox" disabled '. $osArray[2] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-ios">'.esc_html__('iOS', 'wdstm').'<input type="checkbox" disabled '. $osArray[3] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-android">'.esc_html__('Android', 'wdstm').'<input type="checkbox" disabled '. $osArray[4] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                    <span><label for="wdstm-winphone">'.esc_html__('WinPhone', 'wdstm').'<input type="checkbox" disabled '. $osArray[5] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                </div>
            </li>';
    }

    return $output;
}

/**
 * get browsers filter
 * @param string $key
 * @param string $string_array
 * @param string br_include
 * @return string
 */
function wdstm_browser_filter($key = '', $string_array = '', $includ = '1') {

    $browsers = wdstm_browsers_array();
    if(!$key || $key == 'edit') {

        $data = array();

        if($key == 'edit') {
            $data = explode(',', $string_array);
        }

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_browser" data-class="browser">
                <div class="wdstm-head-section">'.esc_html__('Browsers', 'wdstm').':</div>
                <div class="wdstm-data-container" data-filter="browser">                    
                    <div class="wdstm-data-container__left">                        
                        <fieldset>
                            <span><label for="wdstm-include-browser">'.esc_html__('Include', 'wdstm').'<input type="radio" id="wdstm-include-browser" name="br_include" value="1" '. $includ_key[0] .'><span class="wdstm-checkbox-view"></span></label></span>
                            <span><label for="wdstm-exclude-browser">'.esc_html__('Exclude', 'wdstm').'<input type="radio" id="wdstm-exclude-browser" name="br_include" value="0" '. $includ_key[1] .'><span class="wdstm-checkbox-view"></span></label></span>                            
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <select name="browser" multiple>';

                    foreach ($browsers as $keyObj => $browser) {
                        if(in_array($keyObj, $data)) {
                            $output .= '<option value="'. $keyObj .'" selected>'. $browser .'</option>';
                        } else {
                            $output .= '<option value="'. $keyObj .'">'. $browser .'</option>';
                        }
                    }

            $output .= '</select>
                    </div>                    
                </div>
                <a class="wdstm-remove-filter" data-value="browsers" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = explode(',', $string_array);

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_browser">
                <div class="wdstm-head-section">'.esc_html__('Browsers', 'wdstm').':</div>
                <div class="wdstm-data-container">
                    <div class="wdstm-data-container__left">                        
                        <fieldset>
                            <span><label>'.esc_html__('Include', 'wdstm').'<input type="radio" disabled '. $includ_key[0] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                            <span><label>'.esc_html__('Exclude', 'wdstm').'<input type="radio" disabled '. $includ_key[1] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>                            
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <ul class="wdstm-as-select">';
        foreach ($data as $browser) {
                $output .= '<li>'. $browsers[$browser] .'</li>';
            }

            $output .= '</ul>
                    </div>
                </div>
            </li>';
    }

    return $output;
}

/**
 * get phone filter
 * @param string $key
 * @param string $string_array
 * @param string br_include
 * @return string
 */
function wdstm_phone_filter($key = '', $string_array = '', $includ = '1') {

    $phones = wdstm_phones_array();
    if(!$key || $key == 'edit') {

        $data = array();

        if($key == 'edit') {
            $data = explode(',', $string_array);
        }

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_phone" data-class="phone">
                <div class="wdstm-head-section">'.esc_html__('Gadgets Models', 'wdstm').':</div>
                <div class="wdstm-data-container" data-filter="phone">                    
                    <div class="wdstm-data-container__left">                        
                        <fieldset>
                            <span><label for="wdstm-include-phone">'.esc_html__('Include', 'wdstm').'<input type="radio" id="wdstm-include-phone" name="p_include" value="1" '. $includ_key[0] .'><span class="wdstm-checkbox-view"></span></label></span>
                            <span><label for="wdstm-exclude-phone">'.esc_html__('Exclude', 'wdstm').'<input type="radio" id="wdstm-exclude-phone" name="p_include" value="0" '. $includ_key[1] .'><span class="wdstm-checkbox-view"></span></label></span>                            
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <select name="phone" multiple>';

                    foreach ($phones as $keyObj => $phone) {
                        if(in_array($keyObj, $data)) {
                            $output .= '<option value="'. $keyObj .'" selected>'. $phone .'</option>';
                        } else {
                            $output .= '<option value="'. $keyObj .'">'. $phone .'</option>';
                        }
                    }

            $output .= '</select>
                    </div>                    
                </div>
                <a class="wdstm-remove-filter" data-value="phone" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = explode(',', $string_array);

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_phone">
                <div class="wdstm-head-section">'.esc_html__('Gadgets Models', 'wdstm').':</div>
                <div class="wdstm-data-container">
                    <div class="wdstm-data-container__left">                        
                        <fieldset>
                            <span><label>'.esc_html__('Include', 'wdstm').'<input type="radio" disabled '. $includ_key[0] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                            <span><label>'.esc_html__('Exclude', 'wdstm').'<input type="radio" disabled '. $includ_key[1] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>                            
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <ul class="wdstm-as-select">';
            foreach ($data as $phone) {
                $output .= '<li>'. $phones[$phone] .'</li>';
            }

        $output .= '</ul>
                    </div>
                </div>
            </li>';
    }

    return $output;
}

/**
 * get country filter
 * @param string $key
 * @param string $string_array
 * @param string $includ
 * @return string
 */
function wdstm_country_filter($key = '', $string_array = '', $includ = '1') {

    $countries = wdstm_counrties_array();

    if(!$key || $key == 'edit') {

        $data = array();

        if($key == 'edit') {
            $data = explode(',', $string_array);
        }

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_country" data-class="country">
                <div class="wdstm-head-section">'.esc_html__('Countries', 'wdstm').':</div>
                <div class="wdstm-data-container">
                    <div class="wdstm-data-container__left">
                        <fieldset>
                            <span><label for="wdstm-include-country">'.esc_html__('Include', 'wdstm').'<input type="radio" id="wdstm-include-country" name="c_include" value="1" '. $includ_key[0] .'><span class="wdstm-checkbox-view"></span></label></span>
                            <span><label for="wdstm-exclude-country">'.esc_html__('Exclude', 'wdstm').'<input type="radio" id="wdstm-exclude-country" name="c_include" value="0" '. $includ_key[1] .'><span class="wdstm-checkbox-view"></span></label></span>                            
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <select name="country" multiple>';

                    foreach ($countries as $keyObj => $country) {
                        if(in_array($keyObj, $data)) {
                            $output .= '<option value="'. $keyObj .'" selected>'. $country .'</option>';
                        } else {
                            $output .= '<option value="'. $keyObj .'">'. $country .'</option>';
                        }
                    }

        $output .=      '</select>
                    </div>                    
                </div>
                <a class="wdstm-remove-filter" data-value="countries" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = explode(',', $string_array);

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_country"">
                <div class="wdstm-head-section">'.esc_html__('Countries', 'wdstm').':</div>
                <div class="wdstm-data-container">
                    <div class="wdstm-data-container__left">
                        <fieldset>
                            <span><label>'.esc_html__('Include', 'wdstm').'<input type="radio" disabled '. $includ_key[0] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                            <span><label>'.esc_html__('Exclude', 'wdstm').'<input type="radio" disabled '. $includ_key[1] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>                            
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <ul class="wdstm-as-select">';

                        foreach ($data as $country) {
                            $output .= '<li>'. $countries[$country] .'</li>';
                        }

            $output .= '</ul>
                    </div>
                </div>
            </li>';
    }

    return $output;
}

/**
 * get language filter
 * @param string $key
 * @param string $string_array
 * @param string $includ
 * @return string
 */
function wdstm_languages_filter($key = '', $string_array = '', $includ = '1') {

    $languages = wdstm_languages_array();

    if(!$key || $key == 'edit') {

        $data = array();

        if($key == 'edit') {
            $data = explode(',', $string_array);
        }

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_lang" data-class="language">
                <div class="wdstm-head-section">'.esc_html__('Languages', 'wdstm').':</div>
                <div class="wdstm-data-container" data-filter="language">
                    <div class="wdstm-data-container__left">
                        <fieldset>
                            <span><label for="wdstm-include-language">'.esc_html__('Include', 'wdstm').'<input type="radio" id="wdstm-include-language" name="l_include" value="1" '. $includ_key[0] .'><span class="wdstm-checkbox-view"></span></label></span>
                            <span><label for="wdstm-exclude-language">'.esc_html__('Exclude', 'wdstm').'<input type="radio" id="wdstm-exclude-language" name="l_include" value="0" '. $includ_key[1] .'><span class="wdstm-checkbox-view"></span></label></span>                            
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <select name="language" multiple>';

            foreach ($languages as $keyObj => $language) {
                if(in_array($keyObj, $data)) {
                    $output .= '<option value="'. $keyObj .'" selected>'. $language .'</option>';
                } else {
                    $output .= '<option value="'. $keyObj .'">'. $language .'</option>';
                }
            }

        $output .=      '</select>
                    </div>                    
                </div>
                <a class="wdstm-remove-filter" data-value="languages" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = explode(',', $string_array);

        if ($includ == '1') {
            $includ_key = ['checked', ''];
        } else {
            $includ_key = ['', 'checked'];
        }

        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_country">
                <div class="wdstm-head-section">'.__('Languages', 'wdstm').':</div>
                <div class="wdstm-data-container">
                    <div class="wdstm-data-container__left">
                        <fieldset>
                            <span><label>'.esc_html__('Include', 'wdstm').'<input type="radio" disabled '. $includ_key[0] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>
                            <span><label>'.esc_html__('Exclude', 'wdstm').'<input type="radio" disabled '. $includ_key[1] .'><span class="wdstm-checkbox-view wdstm-checkbox-view_disabled"></span></label></span>                           
                        </fieldset>        
                    </div>
                    <div class="wdstm-data-container__right">
                        <ul class="wdstm-as-select">';

            foreach ($data as $language) {
                $output .= '<li>'. $languages[$language] .'</li>';
            }

        $output .= '</ul>
                    </div>
                </div>
            </li>';
    }

    return $output;
}

/**
 * get range ip filter
 * @param string $key
 * @param string $string_array
 * @return string
 */
function wdstm_range_ip_filter($key = '', $string_array='') {

    if(!$key || $key == 'edit') {

        $data = array('from' => '', 'to' => '' );

        if($key == 'edit') {
            $data = unserialize($string_array);
        }
        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_range-ip" data-class="range_ip">
                <div class="wdstm-data-container">
                    <label class="wdstm-head-section">'. esc_html__('Range IP', 'wdstm') .':</label>
                    <input type="text" class="large-text regular-text wdstm-ip-adress" id="rangeIp1" name="range_ip-from" placeholder="000.000.000.000" value="'. $data["from"] .'">
                    <span> - </span>
                    <input type="text" class="large-text regular-text wdstm-ip-adress" id="rangeIp2" name="range_ip-to" placeholder="000.000.000.000" value="'. $data["to"] .'">
                </div>
                <a class="wdstm-remove-filter" data-value="range_ip" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
    } else {
        $data = unserialize($string_array);
        $output = '<li class="wdstm-metabox__item wdstm-metabox__item_range-ip">
                <div class="wdstm-data-container">
                    <label class="wdstm-head-section">'. esc_html__('Range IP', 'wdstm') .':</label>
                    <input type="text" class="large-text regular-text" readonly value="'. $data["from"] .'">
                    <span> - </span>
                    <input type="text" class="large-text regular-text" readonly value="'. $data["to"] .'">
                </div>
            </li>';
    }

    return $output;
}

/**
 * @param string $key
 * @param string $string_array
 *
 * @return string
 */
function wdstm_taxonomy_filter($key = '', $string_array = '') {

	if(!$key || $key == 'edit') {

		if ($key == 'edit') {
			$pages = wdstm_generate_page_list($string_array, 'edit');
			$posts = wdstm_generate_posts_list($string_array, 'edit');
		} else {
			$pages = wdstm_generate_page_list();
			$posts = wdstm_generate_posts_list();
		}

		$output = '<li class="wdstm-metabox__item wdstm-metabox__item_tax" data-class="taxonomy">
                <div class="wdstm-head-section">'.esc_html__('Posts and Pages', 'wdstm').':</div>
                <div class="wdstm-data-container">
                    <select name="taxonomy" multiple>'

		          . $pages . $posts .

		          '</select>
                </div>
                <a class="wdstm-remove-filter" data-value="taxonomy" href="#"><i class="fa icon-cancel" aria-hidden="true"></i></a>
            </li>';
	} else {

		$pages = wdstm_generate_page_list($string_array, 'show');
		$posts = wdstm_generate_posts_list($string_array, 'show');

		$output = '<li class="wdstm-metabox__item wdstm-metabox__item_tax">
                <div class="wdstm-head-section">'.esc_html__('Posts and Pages', 'wdstm').':</div>
                <div class="wdstm-data-container">
                    <ul class="wdstm-as-select">'

		          . $pages . $posts .

		          '</ul>
                </div>
            </li>';
	}
	return $output;
}



/**
 * get all filters from database
 * @return array|null|object
 */
function wdstm_get_filters() {
    global $wpdb;
    $table = wdstm_sign_get_table_name();

    $sql = 'SELECT * FROM ' . $table;
    $rows = $wpdb->get_results($sql, OBJECT_K);

    return $rows;
}

/**
 * get client ip
 * @return mixed
 */
function wdstm_get_real_ip () {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * @param $ipAddress
 * @return array
 */
function wdstm_get_country_by_ip($ip) {

    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
    if($ip_data && $ip_data->geoplugin_countryName != null){
        return $ip_data->geoplugin_countryCode;
    } else {
        return false;
    }
}

/**
 * post list
 * @return string
 */
function wdstm_generate_posts_list($ids = '', $type = '') {
    global $post;
    $option = '<optgroup label="' . esc_html__('Posts', 'wdstm') .'">';
    if($type == 'edit') {
	    $postslist = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'post' ,'order'=> 'ASC', 'orderby' => 'title' ) );
		$postArray = explode(',', $ids);
	    foreach ( $postslist as $post ){
	    	$selected = in_array($post->ID, $postArray) ? 'selected' : '';
		    $option .= '<option value="'. $post->ID .'" '. $selected .'>'. $post->post_title .'</option>';
	    }
	    $option .= '</optgroup>';

    } elseif($type == 'show') {
	    $option = '<li class="wdstm-as-optgroup">' . esc_html__('Posts', 'wdstm') .'</li>';
	    $postslist = get_posts( array( 'post_type' => 'post' ,'order'=> 'ASC', 'orderby' => 'title', 'include' => $ids ) );
	    foreach ( $postslist as $post ){
		    $option .= '<li>'. $post->post_title .'</li>';
	    }
    } else {
	    $postslist = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'post' ,'order'=> 'ASC', 'orderby' => 'title', 'include' => $ids ) );
	    foreach ( $postslist as $post ){
		    $option .= '<option value="'. $post->ID .'">'. $post->post_title .'</option>';
	    }
	    $option .= '</optgroup>';
    }
    wp_reset_postdata();

    return $option;
}

/**
 * page list
 * @return string
 */
function wdstm_generate_page_list($ids = '', $type = '') {
    global $post;

    $option = '<optgroup label="' . esc_html__('Pages', 'wdstm') .'">';

	if($type == 'edit') {
		$postslist = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'page' ,'order'=> 'ASC', 'orderby' => 'title' ) );
		$postArray = explode(',', $ids);
		foreach ( $postslist as $post ){
			$selected = in_array($post->ID, $postArray) ? 'selected' : '';
			$option .= '<option value="'. $post->ID .'" '. $selected .'>'. $post->post_title .'</option>';
		}
		$option .= '</optgroup>';

	} elseif($type == 'show') {
		$option = '<li class="wdstm-as-optgroup">' . esc_html__('Pages', 'wdstm') .'</li>';
		$postslist = get_posts( array( 'post_type' => 'page' ,'order'=> 'ASC', 'orderby' => 'title', 'include' => $ids ) );
		foreach ( $postslist as $post ){
			$option .= '<li>'. $post->post_title .'</li>';
		}
	} else {
		$postslist = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'page' ,'order'=> 'ASC', 'orderby' => 'title', 'include' => $ids ) );
		foreach ( $postslist as $post ){
			$option .= '<option value="'. $post->ID .'">'. $post->post_title .'</option>';
		}
		$option .= '</optgroup>';
	}
    wp_reset_postdata();


    return $option;
}


/**
 * create filters
 */
function wdstm_create_filters($value, $operation = '') {

    $output = '<li><form id="wdstm-edit-form" name="wdstm-edit-form"><ul id="wdstm-metabox-edit">';

    if($value->days != '') {
        $output .= wdstm_day_filter($operation, $value->days);
    }

    if ($value->period_days != '') {
        $output .= wdstm_day_periods_filter($operation, $value->period_days, $value->repeater);
    }

    if($value->time != "") {
        $output .= wdstm_time_filter($operation, $value->time);
    }

    if($value->seasons != '') {
        $output .= wdstm_seasons_filter($operation, $value->seasons);
    }

    if ($value->device_type != '') {
        $output .= wdstm_devices_filter($operation, $value->device_type);
    }

    if ($value->os != '') {
        $output .=  wdstm_os_filter($operation, $value->os);
    }

    if ($value->browser != '') {
        $output .=  wdstm_browser_filter($operation, $value->browser, $value->br_includ);
    }

    if ($value->phone != '') {
        $output .=  wdstm_phone_filter($operation, $value->phone, $value->p_includ);
    }

    if ($value->country != '') {
        $output .= wdstm_country_filter($operation, $value->country, $value->c_includ);
    }

    if ($value->language != '') {
        $output .= wdstm_languages_filter($operation, $value->language, $value->l_includ);
    }

    if ($value->range_ip != '') {
        $output .= wdstm_range_ip_filter($operation, $value->range_ip );
    }

	if ($value->typepage != '') {
		$output .= wdstm_typepage_filter($operation, $value->typepage );
	}

    if ($value->taxonomy != '') {
        $output .= wdstm_taxonomy_filter($operation, $value->taxonomy );
    }

    $wp_themes = wdstm_get_themes_list();

    $themes_names = array();

    foreach($wp_themes as $theme) {

        $themes_names[$theme['Name']] = $theme['Name'];
    }

    $theme = $value->theme;

    $output .= "</ul><div class=\"wdstm-theme-container wdstm-last-li\" data-class=\"select-theme\">
                    <span>" . __('Theme', 'wdstm') ."</span>
                    <select name=\"select-theme\">";
                        foreach ($themes_names as $key => $value) {

                        	$selected = ($theme == $value) ? 'selected' : '';

                            $output .= '<option value="' . esc_attr($key) .'"  '. $selected .'>' . esc_attr($value). '</option>';
                        }
           $output .=  "</select>
                </div>";

    $output .= '</form></li>';

    return $output;

}

/**
 * @return string
 */
function wdstm_get_page_id() {
	$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	$url .= ( $_SERVER["SERVER_PORT"] != 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	$url .= $_SERVER["REQUEST_URI"];
	$pageId = url_to_postid( $url );
	return $pageId;
}

function wdstm_front_page_displays() {
	$frontpage = get_option('page_on_front');
	$postpage = get_option('page_for_posts');

	return array('frontpage' => $frontpage, 'homepage' => $postpage);
}


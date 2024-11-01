<?php

function wdstm_options_page() {

	require_once WDSTM_PLUGIN_DIR . '/includes/mobile-detect/Mobile_Detect.php';

	$detect = new Mobile_Detect;

	$orderHandler = '';
	$mobile_class = '';
	if ($detect->isMobile() || $detect->isTablet()) {
		$orderHandler = 'opacity:1; margin-right: 15px;';
		$mobile_class = 'wdstm-mobile-view';
	}

    $wp_themes = wdstm_get_themes_list();

    $themes_names = array();

    foreach($wp_themes as $theme) {

        $themes_names[$theme['Name']] = $theme['Name'];
    }

    $default_theme = get_option('wdstm_default_theme');

    $activate_plugin = get_option('wdstm-activate-plugin');

    $filters_order = json_decode(get_option('wdstm_order_filter'));

    $filters_array = wdstm_get_filters();

    $filters_temp = array();

    if(is_array($filters_order) && count($filters_order) > 0 ) {
        foreach ($filters_order as $order_value) {
            $filters_temp[$order_value] = $filters_array[$order_value];
        }
        $filters_array = $filters_temp;
    }

	?>

	<h1 style="line-height: 1.1"><?php echo esc_html("WDS Themes Manager"); ?></h1>


    <div id="wdstm-ajax-loader" style="display: none"></div>

    <div class="wdstm-page <?php echo esc_attr($mobile_class)?>">

        <div id="wdstm-tabs" class ="wdstm-tabs wdstm-page__item wdstm-page__item_left">
            <ul>
                <li><a href="#wdstm-tabs-1"><?php esc_html_e("Manage Themes", "wdstm"); ?></a></li>
                <li><a href="#wdstm-tabs-2"><?php esc_html_e("Help", "wdstm"); ?></a></li>
            </ul>
            <div id="wdstm-tabs-1" class="wdstm-settings-wrap">
                <div class="wdstm-settings__item">
                    <div class="wdstm-settings">

                        <div class="flexbox-wrapper">
                            <div class="wdstm-onoffswitch">
                                <input type="checkbox" name="wdstm-checkbox1" id="wdstm-checkbox1" class="wdstm-ios-toggle" <?php echo $activate_plugin; ?>/>
                                <label for="wdstm-checkbox1" class="wdstm-checkbox-label" data-off="<?php echo esc_attr("Off"); ?>" data-on="<?php echo esc_attr("On"); ?>"></label>
                            </div>
                            <div class="default-theme-container">
                                <span><?php esc_html_e('Default theme', 'wdstm'); ?></span>
                                <select name="default-themes" id="default-themes">
                                    <option value=""><?php esc_html_e("Select Default Theme", "wdstm"); ?></option>
                                    <?php  foreach ($themes_names as $key => $value) {
                                        $select_option = ($key == $default_theme) ? 'selected' : "";
                                        ?>

                                        <option value="<?php echo $key; ?>" <?php echo $select_option; ?>><?php echo $value ?></option>

                                    <?php } ?>
                                </select>
                            </div>
                        </div>


                        <hr class="wdstm-hr">
                        <?php if(count($filters_array) > 0) : ?>
                        <ul class="wdstm-ul">
                            <li id="wdstm-draggable" class="wdstm-li"></li>
                        </ul>
                        <ul id="wdstm-sortable" class="wdstm-filter-container wdstm-ul">
                            <?php endif;?>

                            <?php foreach ($filters_array as $value) {
                                $title = ($value->title == '') ? esc_html__('New Filter', 'wdstm') : $value->title;

                                if(!array_key_exists($value->theme, $themes_names)) {
	                                $checkTheme = 'wdstm-no-theme';
                                } else {
	                                $checkTheme = '';
                                }

                                if( $value->on_off == 'on' ||  $value->on_off == '') {
                                    $status_filter = '';
                                    $status_icon = 'icon-toggle-on';
                                } else {
                                    $status_filter = 'wdstm-filter-disable';
	                                $status_icon = 'icon-toggle-off';
                                }

                                ?>

                                <li id="wdstm-filter-<?php echo esc_attr($value->id); ?>" data-id="<?php echo esc_attr($value->id); ?>" class="wdstm-li wdstm-li-single <?php echo esc_attr($status_filter) . ' ' . esc_attr($checkTheme); ?> ">
                                    <span class="wdstm-li-single__switcher"><i class="fa <?php echo esc_attr($status_icon); ?> wdstm-on-off-filter wdstm-on-off-filter-<?php echo esc_attr($value->id); ?>" data-id="<?php echo esc_attr($value->id); ?>" aria-hidden="true"></i></span>
                                    <div class="wdstm-form-header">
                                        <div class="like-h4">
                                            <input type="text" class="form-title form-title-<?php echo esc_attr($value->id); ?>" value="<?php echo esc_html($title); ?>" readonly>
                                            <span class="wdstm-block-icons">
                                                <i class="wdstm-drag-handlerectarrows" style="<?php echo esc_attr($orderHandler); ?>"></i>
                                                <i class="fa icon-feather wdstm-edit" data-id="<?php echo esc_attr($value->id); ?>" aria-hidden="true"></i>
                                                <i class="fa icon-up-open wdstm-slide-filter icon-down-open" aria-hidden="true"></i>
                                                <i class="fa icon-cancel wdstm-delete-filter" data-id="<?php echo esc_attr($value->id); ?>" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <ul class="wdstm-metabox-result wdstm-result-<?php echo esc_attr($value->id); ?>">

                                        <?php

                                        if($value->days != '') {
                                            echo wdstm_day_filter('days', $value->days);
                                        }

                                        if ($value->period_days != '') {
                                            echo wdstm_day_periods_filter('period_days', $value->period_days, $value->repeater);
                                        }

                                        if($value->time != "") {
                                            echo wdstm_time_filter('time', $value->time);
                                        }

                                        if($value->seasons != '') {
                                            echo wdstm_seasons_filter('seasons', $value->seasons);
                                        }

                                        if ($value->device_type != '') {
                                            echo wdstm_devices_filter('device_type', $value->device_type);
                                        }

                                        if ($value->os != '') {
                                            echo  wdstm_os_filter('os', $value->os);
                                        }

                                        if ($value->browser != '') {
                                            echo  wdstm_browser_filter('browser', $value->browser, $value->br_includ);
                                        }

                                        if ($value->phone != '') {
                                            echo  wdstm_phone_filter('phone', $value->phone, $value->p_includ);
                                        }

                                        if ($value->country != '') {
                                            echo wdstm_country_filter('country', $value->country, $value->c_includ);
                                        }

                                        if ($value->language != '') {
                                            echo wdstm_languages_filter('language', $value->language, $value->l_includ);
                                        }

                                        if ($value->range_ip != '') {
                                            echo wdstm_range_ip_filter('language', $value->range_ip);
                                        }

                                        if ($value->typepage != '') {
                                             echo wdstm_typepage_filter('typepage', $value->typepage);
                                        }

                                        if ($value->taxonomy != '') {
	                                        echo wdstm_taxonomy_filter('taxonomy', $value->taxonomy);
                                        }


                                        ?>

                                    </ul>
                                    <div class="wdstm-btn-wrapper wdstm-btn-wrapper_edit wdstm-edit-filter-<?php echo esc_attr($value->id); ?>" style="display: none;">
                                        <input type="button" class="wdstm-save-filter wdstm-btn" data-title="<?php echo esc_attr($title); ?>" data-operation="edit" data-id="<?php echo esc_attr($value->id); ?>" value="<?php _e('Save', 'wdstm'); ?>" />
                                        <input type="button" class="wdstm-btn wdstm-cancel" data-operation="cancel-edit" data-id="<?php echo esc_attr($value->id); ?>" value="<?php _e('Cancel', 'wdstm'); ?>" />
                                    </div>
                                    <div class="wdstm-form-footer">
                                        <div class="wdstm-theme-container">
                                            <span><?php esc_html_e('Theme', 'wdstm'); ?>:</span>
                                            <span><?php echo esc_html($value->theme); ?></span>
                                        </div>
                                    </div>

                                </li>

                            <?php } ?>


                            <?php if(count($filters_array) > 0) { ?>
                        </ul>
                    <?php } ?>

                        <form id="wdstm-add-filter" name="filtersForm" style="display: none;">
                            <div class="wdstm-form-header">
                                <input type="text" name="title" class="wdstm-form-header_title" placeholder="<?php esc_attr_e("Filter title", "wdstm"); ?>">
                            </div>
                            <ul id="wdstm-metabox" class="wdstm-metabox">


                            </ul>
                            <div id="wdstm-filter-preloader" class="wdstm-filter-preloader text-center" style="display:none">
                                <img src="<?php echo WDSTM_MEDIA_DIR ?>/img/ellipsis.svg"  alt="Load..." width="50" height="50">
                            </div>
                            <div class="wdstm-form wdstm-form_chooser-wrapper">
                                <div class="wdstm-form__chooser-filter">
                                    <a class="add-filter-line" href="#"><i class="fa icon-plus" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="wdstm-list-filters" style="display: none;">
                                <select name="wdstm-filters" id="wdstm-filters" class="filters-listener">
                                    <option value=""><?php _e('Choose filter', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-day" value="day"><?php esc_html_e('Day of The Week', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-days-period" value="days-period"><?php esc_html_e('Period Days', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-time" value="time"><?php esc_html_e('Time', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-seasons" value="seasons"><?php esc_html_e('Seasons', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-devices" value="devices"><?php esc_html_e('Devices', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-os" value="os"><?php esc_html_e('Os', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-browsers" value="browsers"><?php esc_html_e('Browsers', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-phone" value="phone"><?php esc_html_e('Gadgets Models', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-countries" value="countries"><?php esc_html_e('Countries', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-languages" value="languages"><?php esc_html_e('Languages', 'wdstm') ?></option>
                                    <option class="wdstm-filters__item wdstm-range_ip" value="range_ip"><?php esc_html_e('Range IP', 'wdstm') ?></option>
                                    <?php if((get_option('page_on_front') != 0)  || (get_option('page_for_posts') != 0)) :?>
                                    <option class="wdstm-filters__item wdstm-typepage" value="typepage"><?php esc_html_e('Type Page', 'wdstm') ?></option>
                                    <?php endif; ?>
                                    <option class="wdstm-filters__item wdstm-taxonomy" value="taxonomy"><?php esc_html_e('Posts and Pages', 'wdstm') ?></option>
                                </select>
                            </div>
                            <div class="wdstm-form-footer">
                                <div class="wdstm-theme-container">
                                    <span><?php esc_html_e('Theme', 'wdstm'); ?>:</span>
                                    <select name="select-theme" id="select-theme">
                                        <?php  foreach ($themes_names as $key => $value) { ?>
                                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_attr($value) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="wdstm-submit wdstm-btn-wrapper" style="display: none;">
                                <input id="wdstm-save-filter" data-operation="save" type="button" class="wdstm-save-filter wdstm-btn" value="<?php esc_attr_e('Save', 'wdstm'); ?>" />
                                <input type="button" class="wdstm-btn wdstm-cancel" data-operation="cancel-save" value="<?php esc_attr_e('Cancel', 'wdstm'); ?>" />
                            </div>


                        </form>

                        <div class="wdstm-new-filter">
                            <a href="#" id="wdstm-new-filter__btn" class="wdstm-new-filter__btn"><i class="fa icon-plus-3" aria-hidden="true"></i></a>
                        </div>

                    </div>
                </div>

            </div>
            <div id="wdstm-tabs-2" class="wdstm-help">
                <div class="wdstm-help__text">
                    <p><?php echo esc_html("WDS Themes Manager is a highly useful, versatile plugin developed by"); ?> <strong><?php echo esc_html("Web Design Sun"); ?></strong> <?php echo esc_html("agency for easy and all-encompassing management
                        of themes on WordPress website. The plugin enables creating numerous filters with different assigned options.
                        One can set up different themes to be viewed according to the filter category, which can be:"); ?></p>
                    <ul>
                        <li><?php echo esc_html("days of the week;"); ?></li>
                        <li><?php echo esc_html("time duration;"); ?></li>
                        <li><?php echo esc_html("seasons;"); ?></li>
                        <li><?php echo esc_html("device type;"); ?></li>
                        <li><?php echo esc_html("gadget models;"); ?></li>
                        <li><?php echo esc_html("OS;"); ?></li>
                        <li><?php echo esc_html("browser;"); ?></li>
                        <li><?php echo esc_html("range IP;"); ?></li>
                        <li><?php echo esc_html("country;"); ?></li>
                        <li><?php echo esc_html("language;"); ?></li>
                        <li><?php echo esc_html("posts and pages."); ?></li>
                    </ul>
                    <h4><?php echo esc_html("What can you do with WDS Themes Manager?") ?></h4>
                    <p><?php echo esc_html("Users demand individual approach and they get bored without constant changes or improvements of the product.
                        Using WDS Themes Manager you can assign different WordPress themes that will vary in all imaginable situations.
                        Thus, a website will always look “active and live”, in progress and search for better solutions.") ?></p>
                    <p><?php echo esc_html("So, how to use WDS Themes Manager effectively for a WordPress web application?") ?></p>
                    <ul class="wdstm-helper-gallery">
                        <li>
                            <p><?php echo esc_html("After activating the plugin, it is ready for exploitation, and you will see this view on the screen."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-0.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("After activating the plugin"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-0.png" alt="<?php echo esc_attr("After activating the plugin"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Enable plugin."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-1.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Enable plugin"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-1.png" alt="<?php echo esc_attr("Enable plugin"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Choose a Theme, that will be displayed as Default."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-2.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Choose a Theme"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-2.png" alt="<?php echo esc_attr("Choose a Theme"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("After pressing on the green cross you can create your first filter."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-3.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Create First Filter"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-3.png" alt="<?php echo esc_attr("Create First Filter"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Pick a theme you want be viewed with the filter you are going to apply."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-4.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Pick a theme"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-4.png" alt="<?php echo esc_attr("Pick a theme"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Name the filter to not get lost or confused."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-5.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Name the filter"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-5.png" alt="<?php echo esc_attr("Name the filter"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Choose what you want to filter from the scroll down menu."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-6.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Choose what you want to filter"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-6.png" alt="<?php echo esc_attr("Choose what you want to filter"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Make sure your filters don’t withstand with each other."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-7.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Don’t withstand with each other"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-7.png" alt="<?php echo esc_attr("Don’t withstand with each other"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Press Ctrl to choose several items."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-8.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("To choose several items"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-8.png" alt="<?php echo esc_attr("To choose several items"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("And, surely, don't forget to choose pages to which you apply those filters."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-9.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("To choose pages"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-9.png" alt="<?php echo esc_attr("To choose pages"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("Press ‘Save’ to finish filter creating."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-10.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Press ‘Save’"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-10.png" alt="<?php echo esc_attr("Press Save"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("You can edit or delete any filter any time."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-11.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Edit or delete"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-11.png" alt="<?php echo esc_attr("Edit or delete"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("You may change order of filters created."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-12.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("Change order"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-12.png" alt="<?php echo esc_attr("Change order"); ?>">
                            </a>
                        </li>
                        <li>
                            <p><?php echo esc_html("After pressing ‘Save’ a filter will change the features."); ?></p>
                            <a href="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-13.png" class="wdstm-lightbox-link" data-fancybox="<?php echo esc_attr('group'); ?>" data-caption="<?php echo esc_attr("After pressing Save"); ?>">
                                <img src="<?php echo esc_url(WDSTM_SCREENSHOT_DIR) ?>Screenshot-13.png" alt="<?php echo esc_attr("After pressing Save"); ?>">
                            </a>
                        </li>
                    </ul>
                    <p><?php echo esc_html("WDS Themes Manager is currently available in a pilot version that’s why there still some things that
                        need more time to be fixed. Our team considered necessary to inform about two things you should keep in your mind:"); ?></p>
                    <ol>
                        <li>
                            <p><?php echo esc_html("different themes support different features. So when switching to a different theme from the current one you have,
                                your website outlook may lose some features it used to have with the previous theme;"); ?></p>
                        </li>
                        <li>
                            <p><?php echo esc_html("your filters shouldn’t withstand with each other. For example, if in your filter you excluded mobile phones,
                                you can’t further, for example, include some models of mobile phones applicable to the same theme."); ?></p>
                        </li>
                    </ol>
                    <p><strong><?php echo esc_html("Web Design Sun"); ?></strong> <?php echo esc_html("team wishes you to enjoy your WordPress theme management with the help of WDS Themes Manager plugin."); ?></p>
                </div>
            </div>
        </div>

        <div class="wdstm-page__item wdstm-page__item_right">
            <div class="wdstm-logo">
                <div class="wdstm-logo__img-wrap text-center">
                    <img src="<?php echo esc_url(WDSTM_MEDIA_DIR) ?>/img/logo.png" width="206" height="80" class="wdstm-logo__img" alt="Web Design Sun">
                </div>
                <div class="wdstm-logo__description text-center">
                    <?php echo esc_html('Web Design Sun creates helpful WordPress plugins for better development experience of all engineers working with WordPress. We provide custom WordPress web application development services worldwide. Web Design Sun has extensive experience in WordPress development using any theme, plugin or tool to create a high end software product'); ?>
                </div>
            </div>
            <div class="wdstm-bug">
                <div class="wdstm-bug_description text-center">
                    <p><strong><?php echo esc_html('Found a bug?'); ?></strong><br><?php echo esc_html('Let us know!'); ?><br><a title="<?php echo esc_attr('Send Email to Web Design Sun'); ?>" href="mailto:contact@webdesignsun.com"><?php echo esc_html('contact@webdesignsun.com'); ?></a></p>
                </div>
            </div>
            <div class="wdstm-donate">
                <h3 class="wdstm-donate_head text-center"><?php echo esc_html('Beer / Coffee'); ?></h3>
                <div class="wdstm-donate_description text-center">
                    <?php echo esc_html('Why don’t you stand some beer or coffee to our hardworking developers for their excellent job?'); ?>
                </div>
                <div class="wdstm-donate__btn text-center">
                    <form id="wdstm-donate-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="W9698MF5HCXLC">
                        <a href="#" class="wdstm-donate__btn_link" title="<?php esc_attr('Donate'); ?>">
                            <span class="wdstm-joke beer"><i class="fa icon-emo-beer" aria-hidden="true"></i></span>
                            <span class="wdstm-joke coffee"><i class="fa icon-emo-coffee" aria-hidden="true"></i></span>
                        </a>
                    </form>
                </div>
            </div>
        </div>

    </div>

<?php
}
?>
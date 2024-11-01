jQuery('document').ready(function ($) {
    'use strict';

    $("[data-fancybox]").fancybox({
        loop : true,
        arrows : true,
        infobar : true,
        toolbar : true,
        buttons : [
            'slideShow',
            'fullScreen',
            'thumbs',
            'close'
        ]
    });

    $('#timeformat1').timepicker({ 'timeFormat': 'H:i:s' });
    $('#timeformat2').timepicker({ 'timeFormat': 'h:i:s' });
    $( "#wdstm-tabs" ).tabs();

    var filters         = $('#wdstm-add-filter'),
        body            = $('body'),
        optionFilter    = $('.filters-listener'),
        day             = $('.wdstm-metabox__item_days'),
        defaultThemes   = $('#default-themes'),
        seasons         = $('.wdstm-metabox__item_period-seasons'),
        filterPreloader = $('#wdstm-filter-preloader'),
        ajaxLoader      = $('#wdstm-ajax-loader'),
        filter,
        placeForFilter  = '',
        filterObj       = {};

    /**
     * open settings form
     */
    $(body).on('click', '#wdstm-new-filter__btn', function(e) {
        e.preventDefault();
        if(findEditFilter()) {

            filters.slideDown('fast');
            $('.wdstm-submit').show('fast');
            $('.wdstm-new-filter').hide('fast');
            // $('.wdstm-save-order').hide('fast');

        }

    });

    /**
     * open filter select
     */
    $(body).on('click', '.add-filter-line', function (e) {
        e.preventDefault();
        $(this).toggleClass('wdstm-open-filter').children().toggleClass('icon-minus');
        $('.wdstm-list-filters').slideToggle('fast');

    });

    /**
     * chose type filter
     */
    $(body).on('change', '.filters-listener',function (e) {
        var operation = $(this).data('operation');
        filter = '';
        filter = $(this).val();
        $('.wdstm-list-filters').slideToggle('fast');
        $('.add-filter-line').removeClass('wdstm-open-filter').children().removeClass('icon-minus');
        if(filter !== '') {
            if(operation !== 'edit') {
                $('.wdstm-' + filter).prop('disabled', true);
                $(filterPreloader).show();
                loadFilter(filter);
                checkOptionConflicts(filter, true);
            } else {
                placeForFilter = 'editZone';
                $('#wdstm-filters-edit .wdstm-' + filter).prop('disabled', true);
                $('#wdstm-edit-preloader').show();
                loadFilter(filter);
                checkOptionConflicts(filter, true);

            }


        }
    });

    /**
     * save default theme
     */
    $(defaultThemes).on('change',function (e) {
        var defThemes = $(defaultThemes).val();
        var entry = {
            action: 'wdstm_save_default_theme',
            theme: defThemes
        };

        $.ajax({
            url: wdstmajax.ajaxurl,
            type: "post",
            dataType : "Json",
            data: entry,
            success: function(respond, textStatus, jqXHR){
                var alertText = 'success';
                if(respond.result == 'Error') {
                    var alertText = 'error';
                }
                swal({
                    title: respond.result,
                    timer: 2000,
                    type: alertText,
                    showConfirmButton: false
                });
            },
            error: function(jqXHR, textStatus, errorThrown){
                swal("", textStatus, "error");
            }
        });
    });

    /**
     * remove filter from settings
     */
    $(body).on('click', '.wdstm-remove-filter', function(e) {
        e.preventDefault();
        var classValue = $(this).data('value');
        $('.wdstm-' + classValue).prop('disabled', false);
        checkOptionConflicts(classValue, false);
        delete filterObj[classValue];
        checkFilterConflict();
        $(this).closest('.wdstm-metabox__item').remove();
        $(optionFilter).val('');
    });

    /**
     * save filter
     */
    $(body).on('click', '.wdstm-save-filter', function(e){
        e.preventDefault();
        var operation = $(this).data('operation'),
            i, visibleLi, formName,
            onOffStatus = 'on',
            alertPopup = 0;

        var filterId ='',
            editTitle = '',
            form;
        if(operation === 'edit') {
            formName = 'wdstm-edit-form';
            filterId = $(this).data('id');
            editTitle = $('.form-title-' + filterId).val();
            onOffStatus = $('.wdstm-on-off-filter-' + filterId).data('onoff');
            visibleLi = $('#wdstm-edit-form').find('li');
        } else {
            visibleLi = $('#wdstm-metabox').find('li');
            formName = 'filtersForm';
        }

        var entry = formNewData(formName);

        // check filter yes or now
        if(visibleLi.length > 0) {
            var formArrKeys = forInObj(entry, 'key');

            for( i = 0; i < visibleLi.length; i++) {
                var classFlag = $(visibleLi[i]).data('class');
                var keyFilter = jQuery.inArray( classFlag, formArrKeys);
                if( keyFilter === -1) {
                    $(visibleLi[i]).addClass('wdstm-focus');
                    alertPopup = 1;
                } else {
                    var formArrValues = forInObj(entry, 'values');

                    switch (classFlag) {
                        case 'period':
                        case 'time':
                        case 'range_ip':
                            if(formArrValues[keyFilter] === '') {
                                $(visibleLi[i]).addClass('wdstm-focus');
                                alertPopup = 1;
                            }
                        break;
                    }
                }

            }

            if(alertPopup) {
                swal({
                    title: "Choose Condition",
                    timer: 2000,
                    type: 'error',
                    showConfirmButton: false
                });
                return false;
            }

        } else {
            swal({
                title: "Add Filter",
                timer: 2000,
                type: 'error',
                showConfirmButton: false
            });
            return false;
        }



        if(hasElementRange('from', entry) || hasElementRange('to', entry)) {
            var inputsIp = document.getElementsByClassName('wdstm-ip-adress');
            for(i = 0; i < inputsIp.length; i++) {

                var result = validateIPaddress(inputsIp[i].value, inputsIp[i]);
                if(!result) {
                    return false;
                }
            }
        }

        entry['action'] = 'wdstm_save_filter';
        entry['operation'] = operation;
        entry['filter_id'] = filterId;
        entry['edit_title'] = editTitle;
        entry['on_off'] = onOffStatus;

        $(ajaxLoader).show();

        $.ajax({
            url: wdstmajax.ajaxurl,
            type: "post",
            dataType : "Json",
            data: entry,
            success: function(respond, textStatus, jqXHR){
                $(ajaxLoader).hide();
                swal({
                    title: respond.question,
                    timer: 3000,
                    type: 'success',
                    showConfirmButton: false
                });
                document.location.reload();
            },
            error: function(jqXHR, textStatus, errorThrown){
                $(ajaxLoader).hide();
                swal({
                    title: textStatus,
                    timer: 3000,
                    type: 'error',
                    showConfirmButton: false
                });
                console.log('error', textStatus);
            }
        });
    });

    /**
     * slide up/down saved filter
     */
    $(body).on('click', '.wdstm-slide-filter', function () {
        var that = this;

        var id = $(that).prev().data('id');

        if($('.wdstm-edit-filter-' + id).is(':visible')) {
            swal({
                title: "You can't closed filter",
                timer: 3000,
                type: 'warning',
                showConfirmButton: false
            });
            return false;
        }

        $(that).toggleClass('icon-down-open');
        var nextBlock1 = $(that).closest('.wdstm-form-header').next();
        var footerBlock = $(nextBlock1).next().next();
        $(nextBlock1).slideToggle();
        $(footerBlock).slideToggle();

    });


    /**
     * on/off filter
     */
    $(body).on('click', '.wdstm-on-off-filter', function() {
       var that = this;

        var id = $(that).data('id');

        $(that).toggleClass('icon-toggle-on').toggleClass('icon-toggle-off').closest('.wdstm-li-single').toggleClass('wdstm-filter-disable');

        if( $(that).hasClass('icon-toggle-off') ) {
            $(that).data('onoff', 'off');
            onOffFilter(id, 'off');
        } else {
            $(that).data('onoff', 'on');
            onOffFilter(id, 'on');
        }


    });


    /**
     * delete saved filter
     */
    $(body).on('click', '.wdstm-delete-filter', function() {
        if(findEditFilter()) {
            var id = $(this).data('id');
            swal({
                    title: "Are you sure?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes",
                    closeOnConfirm: false
                },
                function(){
                    deleteFilter(id);
                });
        }


    });

    var tempEditHtml;

    /**
     * edit filter
     */
    $(body).on('click', '.wdstm-edit', function() {

        $(this).next('.icon-down-open').click();

        if(findEditFilter()) {

            $(ajaxLoader).show();
            var id = $(this).data('id');

            var entry = {
                action: 'wdstm_edit_filter',
                id: id
            };

            $.ajax({
                url: wdstmajax.ajaxurl,
                type: "post",
                dataType : "Json",
                data: entry,
                success: function(respond, textStatus, jqXHR){
                    tempEditHtml = $('.wdstm-result-' + respond.id).html();
                    $('.wdstm-result-' + respond.id).html(respond.filter);
                    $('input[name*="period"], .datepicker').datepicker({ dateFormat: 'dd-mm-yy' });
                    $('#timeformat1').timepicker({ 'timeFormat': 'H:i:s' });
                    $('#timeformat2').timepicker({ 'timeFormat': 'H:i:s' });
                    $('.wdstm-edit-filter-' + respond.id).show().next().hide();
                    $('.wdstm-new-filter').hide();

                    //clone button for filters select
                    var choserBtn = $('.wdstm-form_chooser-wrapper').clone();
                    //clone preloader
                    var clonePreloader = $('#wdstm-filter-preloader').clone().attr('id', 'wdstm-edit-preloader');
                    //clone for filters select
                    var contentList = $('.wdstm-list-filters').clone().addClass('wdstm-filters-edit');
                    $(contentList).children('select').attr('id','wdstm-filters-edit').attr('data-operation','edit');

                    $('.wdstm-result-' + respond.id).after(contentList);
                    $('.wdstm-result-' + respond.id).after(choserBtn);
                    $('.wdstm-last-li').before(clonePreloader);
                    $('.form-title-' + respond.id).prop('readonly', false);
                    $(ajaxLoader).hide();
                    //disable filters
                    findVisibleFilters(respond.id);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    console.log(textStatus);
                    swal("", textStatus, "error, reload page");
                }
            });

        }
    });

    /**
     * cancel save/edit
     */
    $(body).on('click', '.wdstm-cancel', function() {

        var cancel = $(this).data('operation');
        $('.wdstm-list-filters').hide();
        $('.wdstm-filters__item').prop('disabled', false);
        $('.filters-listener').val('');
        $('.wdstm-new-filter').show();
        $('.add-filter-line').removeClass('wdstm-open-filter').children().removeClass('icon-minus');
        filterObj = {};
        filter = '';
        placeForFilter = '';
        switch(cancel) {
            case "cancel-save":
                $('.wdstm-form-header_title').val('');
                $('#wdstm-metabox .wdstm-metabox__item').remove();
                $(filters).slideUp();
                $('.wdstm-submit').hide('fast');
                break;
            case "cancel-edit":
                var id = $(this).data("id");
                $('.wdstm-filters-edit').remove();
                $('#wdstm-edit-form').remove();
                $('.wdstm-result-' + id).next().remove();
                $('.wdstm-result-' + id).html(tempEditHtml);
                $('.form-title-' + id).prop('readonly', true);
                $(this).parent().hide().next().show();
                tempEditHtml = '';
       }
    });

    var listenerClickable = false;

    /**
     * activate plugin
     */
    $(body).on('click', '#wdstm-checkbox1', function(e) {

        if( listenerClickable ) {
            return false;
        }
        listenerClickable = true;
        var entry = {
            action: 'wdstm_activate_filters'
        };

        $.ajax({
            url: wdstmajax.ajaxurl,
            type: "post",
            dataType : "Json",
            data: entry,
            success: function(respond, textStatus, jqXHR){
                swal({
                    title: respond.result,
                    timer: 2000,
                    type: 'success',
                    showConfirmButton: false
                });
                listenerClickable = false;
            },
            error: function(jqXHR, textStatus, errorThrown){
                swal("", textStatus, "error");
            }
        });
    });

    /**
     * sortable
     */
    $( "#wdstm-sortable" ).sortable({
        handle: '.wdstm-drag-handlerectarrows',
        revert: true,
        axis: "y",
        scroll: true,
        placeholder: "ui-state-highlight",
        update: function( event, ui ) {
            saveOrder();
        }
    });
    $( "#wdstm-draggable" ).draggable({
        connectToSortable: "#wdstm-sortable",
        helper: "clone",
        revert: "invalid"
    });
    $( ".wdstm-ul, .wdstm-li" ).disableSelection();

    /**
     * action on checkbox or roundbutton
     * save result to [filterObj]
     */
    $(body).on('change', ':radio, :checkbox', function() {
       var typeFilter = $(this).parent().parent().data('filter');
       var valueFilter = $(this).val();
       if(!!typeFilter) {
           filterObj[typeFilter] = valueFilter;
           checkFilterConflict();
       }
    });

    $(body).on('click', '.wdstm-data-container', function() {
       $(this).closest('li').removeClass('wdstm-focus');
    });

    /**
     *  get filter
     * @param filter
     */
    function loadFilter(filter) {
        var entry = {
            action: 'wdstm_create_filter',
            filter: filter
        };

        $.ajax({
            url: wdstmajax.ajaxurl,
            type: "post",
            dataType : "Json",
            data: entry,
            success: function(respond, textStatus, jqXHR){
                addFragment(respond.filter);
                if(placeForFilter !== 'editZone') {
                    $(filterPreloader).hide();
                    $(optionFilter).val('');
                } else {
                    $('#wdstm-edit-preloader').hide();
                    $('.filters-listener').val('');

                }

            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log('ERROR: ' + textStatus );
            }
        });
    }

    /**
     * add filter
     * @param content
     */
    function addFragment(content) {
        var parent;
        if(placeForFilter !== 'editZone') {
            parent = $('#wdstm-metabox');
        } else {
            parent = $('#wdstm-metabox-edit');
        }

        $(parent).append(content);
        initDatepicker(filter);
        checkOsConflict();
        checkFilterConflict();

    }

    /**
     * init datepicker or timepicker
     */
    function initDatepicker(value) {
        switch (value) {
            case 'days-period':
                $('input[name*="period"], .datepicker').datepicker({ dateFormat: 'dd-mm-yy' });
                break;
            case 'time':
                $('#timeformat1').timepicker({ 'timeFormat': 'H:i:s' });
                $('#timeformat2').timepicker({ 'timeFormat': 'H:i:s' });
                break;
        }
    }

    /**
     * check conflicts between filter
     * @param filter
     */
    function checkOptionConflicts(filterValue, flag) {

        switch (filterValue) {
            case 'typepage':
                if(flag) {
                    $('.wdstm-taxonomy').prop('disabled', true);
                } else {
                    $('.wdstm-taxonomy').prop('disabled', false);
                }
                break;
            case 'taxonomy':
                if(flag) {
                    $('.wdstm-typepage').prop('disabled', true);
                } else {
                    $('.wdstm-typepage').prop('disabled', false);
                }
                break;
            case 'seasons':
                if(flag) {
                    $('.wdstm-days-period').prop('disabled', true);
                } else {
                    $('.wdstm-days-period').prop('disabled', false);
                }
                break;
            case 'days-period':
                if(flag) {
                    $('.wdstm-seasons').prop('disabled', true);
                } else {
                    $('.wdstm-seasons').prop('disabled', false);
                }
                break;
            default:
                break;
        }
    }

    function checkFilterConflict() {

        if(!!filterObj.devices) {
            $('.wdstm-type-os').prop('disabled', false);
            switch (filterObj.devices) {
                case 'desktop':
                    $('#wdstm-winphone').prop('disabled', true).prop('checked', false);
                    $('#wdstm-ios').prop('disabled', true).prop('checked', false);
                    $('#wdstm-android').prop('disabled', true).prop('checked', false);
                    break;
                case 'tablet':
                    $('#wdstm-windows').prop('disabled', true).prop('checked', false);
                    $('#wdstm-macos').prop('disabled', true).prop('checked', false);
                    $('#wdstm-linux').prop('disabled', true).prop('checked', false);
                    $('#wdstm-winphone').prop('disabled', true).prop('checked', false);
                    break;
                case 'mobile':
                    $('#wdstm-windows').prop('disabled', true).prop('checked', false);
                    $('#wdstm-macos').prop('disabled', true).prop('checked', false);
                    $('#wdstm-linux').prop('disabled', true).prop('checked', false);
                    break;
                case 'bot':
                    $('.wdstm-type-os').prop('disabled', true).prop('checked', false);
                    break;
                default:
                    break;
            }
        } else {
            $('.wdstm-type-os').prop('disabled', false);
        }

        if(!!filterObj.os) {
            $('.wdstm-device').prop('disabled', false);
            switch (filterObj.os) {
                case 'windows':
                case 'macos':
                case 'linux':
                    $('#wdstm-mobile').prop('disabled', true).prop('checked', false);
                    $('#wdstm-tablet').prop('disabled', true).prop('checked', false);
                    break;
                case 'ios':
                case 'adroid':
                    $('#wdstm-desktop').prop('disabled', true).prop('checked', false);
                    break;
                case 'winphone':
                    $('#wdstm-desktop').prop('disabled', true).prop('checked', false);
                    $('#wdstm-tablet').prop('disabled', true).prop('checked', false);
                    break;

            }
        } else {
            $('.wdstm-device').prop('disabled', false);
        }
    }

    function checkOsConflict() {
        var device = $('.wdstm-device[name=device]:checked').val();
        if(!!device) {
            filterObj['devices'] = device;
        }

    }

    /**
     * enable some filters if edit
     */
    function findVisibleFilters(id) {
        var parentContainer = $('.wdstm-result-' + id).find('#wdstm-metabox-edit');
        var listLi = parentContainer.children();
        var targetFilters = [];
        for(var i = 0; i< listLi.length; i++) {
            targetFilters.push($(listLi[i]).find('.wdstm-remove-filter').data('value'));
        }
        targetFilters.forEach(function(item){
            $('.wdstm-' + item).prop('disabled', true);
            checkOptionConflicts(item, true);
        });

    }

    /**
     * delete filter from database
     * @param id
     */
    function deleteFilter(id) {
        var entry = {
            action: 'wdstm_deleter_filter',
            id: id
        };

        $(ajaxLoader).show();

        $.ajax({
            url: wdstmajax.ajaxurl,
            type: "post",
            dataType : "Json",
            data: entry,
            success: function(respond, textStatus, jqXHR){
                $(ajaxLoader).hide();
                swal({
                    title: respond.response,
                    timer: 2000,
                    type: 'success',
                    showConfirmButton: false
                });

                $('#wdstm-filter-' + respond.id).remove();
            },
            error: function(jqXHR, textStatus, errorThrown){
                swal("", textStatus, "error");
                $(ajaxLoader).hide();
            }
        });
    }

    function onOffFilter(id, onOff) {
        var entry = {
            action: 'wdstm_on_off_filter',
            id: id,
            on_off: onOff
        };

        $(ajaxLoader).show();

        $.ajax({
            url: wdstmajax.ajaxurl,
            type: "post",
            dataType : "Json",
            data: entry,
            success: function(respond, textStatus, jqXHR){
                $(ajaxLoader).hide();
                swal({
                    title: respond.question,
                    timer: 2500,
                    type: 'success',
                    showConfirmButton: false
                });
            },
            error: function(jqXHR, textStatus, errorThrown){
                swal({
                    title: textStatus,
                    timer: 2500,
                    type: 'error',
                    showConfirmButton: false
                });
                $(ajaxLoader).hide();
            }
        });
    }

    /**
     * alert if user deleted theme
     */
    setTimeout(function () {
        var list = document.getElementsByClassName('wdstm-no-theme');
        if(list.length > 0) {
            var textStatus = "You deleted one or more WordPress themes, check your settings";
            swal({
                title: textStatus,
                timer: 5000,
                type: 'error',
                showConfirmButton: false
            });
        }
    }, 400);

    /**
     * save order
     */
    function saveOrder() {
        var list = document.getElementsByClassName('wdstm-li-single');
        var hierarchyArr = [],
            id;
        for(var i = 0; i < list.length; i++) {
            id = $(list[i]).data('id');
            hierarchyArr.push(id);
        }

        $(ajaxLoader).show();

        var entry = {
            action: 'wdstm_save_order',
            order: JSON.stringify(hierarchyArr)
        };

        $.ajax({
            url: wdstmajax.ajaxurl,
            type: "post",
            dataType : "Json",
            data: entry,
            success: function(respond, textStatus, jqXHR){
                $(ajaxLoader).hide();
                swal({
                    title: respond.result,
                    timer: 2000,
                    type: 'success',
                    showConfirmButton: false
                });
                $('.wdstm-save-order').hide();
            },
            error: function(jqXHR, textStatus, errorThrown){
                swal("", textStatus, "error");
                $(ajaxLoader).hide();
            }
        });
    }

    function validateIPaddress(inputText, target) {
        var ipformat = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        if(inputText.match(ipformat))  {
            target.focus();
            return true;
        } else {
            swal("You have entered an invalid IP address!");
            target.focus();
            return false;
        }
    }

    function findEditFilter() {
        var editForm = $('#wdstm-edit-form').is(":visible");
        var newFilterBtn = $('.wdstm-new-filter').is(":visible");
        if( editForm || !newFilterBtn) {
            swal({
                title: "Save another form",
                timer: 2000,
                type: 'warning',
                showConfirmButton: false
            });
            if(editForm) {
                $('#wdstm-edit-form').closest('li').addClass('wdstm-focus');
            } else {
                $('#wdstm-add-filter').addClass('wdstm-focus');
            }

            $('html, body').animate({
                scrollTop: $(".wdstm-focus").offset().top
            }, 1000);
            return false;
        } else {
            return true;
        }
    }

    function forInObj(entry, type) {
        var result = [];
        var key;
        if(type === 'key') {
            for (key in entry) {
                result.push(key);
            }
        } else {
            for (key in entry) {
                result.push(entry[key]);
            }
        }

        return result;
    }

    /**
     * has value element in object
     * @param element
     * @param obj
     * @returns {number}
     */
    function hasElementRange (element, obj) {
        var result = 0;
        if (!!obj['range_ip']) {
            var range = obj['range_ip'];
            var result = (range[element] != '') ? 1 : 0;
        }
        return result;
    };

    /**
     * create new data object
     * @param formName
     * @returns {Object}
     */
    function formNewData (formName) {
        var result = new Object(null);

        var daysArr = [];

        var typePage = [];

        var periodObj = {
            'from' : '',
            'to' : ''
        };

        var rangeIpObj = {
            'from' : '',
            'to' : ''
        }

        var timeObj = {
            'from' : '',
            'to' : ''
        };

        var seasonsObj = {};
        var seasonsFlag = 0;

        var osObj = {};
        var osFlag = 0;

        var form = document.forms[formName];
        var formElements = form.elements;
        var formLength = formElements.length;

        for(var i = 0; i < formLength; i++) {
            var name = formElements[i].name;
            var elem = formElements[i];
            switch (name) {
                case 'days':
                    if(elem.checked) {
                        daysArr.push(elem.value);
                    }
                    break;
                case 'typepage':
                    if(elem.checked) {
                        typePage.push(elem.value);
                    }
                    break;
                case 'period-from':
                    periodObj.from = elem.value;
                    break;
                case 'period-to':
                    periodObj.to = elem.value;
                    break;
                case 'range_ip-from':
                    rangeIpObj.from = elem.value;
                    break;
                case 'range_ip-to':
                    rangeIpObj.to = elem.value;
                    break;
                case 'repeater':
                    result.repeater = (elem.checked) ? elem.value : '';
                    break;
                case 'time-from':
                    timeObj.from = elem.value;
                    break;
                case 'time-to':
                    timeObj.to = elem.value;
                    break;
                case 'seasons':
                    var seasonsHelper = {
                        'winter': '0',
                        'spring': '1',
                        'summer': '2',
                        'autumn': '3'
                    }
                    if(elem.checked) {
                        seasonsObj[seasonsHelper[elem.value]] = elem.value;
                        seasonsFlag = 1;
                    }
                    break;
                case 'device':
                case 'br_include':
                case 'p_include':
                case 'c_include':
                case 'l_include':
                    if(elem.checked) {
                        result[name] = elem.value;
                    }
                    break;
                case 'os':
                    var osHelper = {
                        'Windows': '0',
                        'OS X': '1',
                        'Linux': '2',
                        'iOS': '3',
                        'Android': '4',
                        'Windows Phone': '5'
                    };
                    if(elem.checked) {
                        osObj[osHelper[elem.value]] = elem.value;
                        osFlag = 1;
                    }
                    break;
                case 'browser':
                case 'phone':
                case 'country':
                case 'language':
                case 'taxonomy':
                    var select = form.elements[name];
                    var length = select.options.length;
                    var valuesArr = [];

                    for (var j = 0; j < length; j++) {
                        var option = select.options[j];
                        if(option.selected) {
                            valuesArr.push(option.value);
                        }
                    }
                    if(valuesArr.length > 0) {
                        result[name] = valuesArr.join(',');
                    }

                    break;
                default:
                    result[name] = elem.value;
                    break;
            }


            if(daysArr.length > 0) {
                result.days = daysArr;
            }

            if(typePage.length > 0) {
                result.type_page = typePage.join(',');
            }

            if((periodObj.from != '') || (periodObj.to != '')) {
                result.period = periodObj;
            }

            if((timeObj.from != '') || (timeObj.to != '')) {
                result.time = timeObj;
            }

            if(seasonsFlag) {
                result.seasons = seasonsObj;
            }

            if(osFlag) {
                result.os = osObj;
            }

            if((rangeIpObj.from != '') || (rangeIpObj.to != '')) {
                result.range_ip = rangeIpObj;
            }
        }

        return result;

    }

    $('.wdstm-donate__btn_link').on('click', function(e) {
        e.preventDefault();
        $('#wdstm-donate-form').submit();
    })

});




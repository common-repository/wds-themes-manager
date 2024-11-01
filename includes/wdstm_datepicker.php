<?php

wdstm_datepicker_js();

function wdstm_datepicker_js(){

	// init datepicker
	if( is_admin() )
		add_action('admin_footer', 'wdstm_init_datepicker', 99 );

	function wdstm_init_datepicker(){
		?>
		<script type="text/javascript">
            jQuery(document).ready(function($){
                'use strict';
                $.datepicker.setDefaults({
                    closeText: 'Close',
                    prevText: '<Prev',
                    nextText: 'Next>',
                    currentText: 'Today',
                    monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
                    monthNamesShort: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                    dayNames: ['Sunday','Monday','Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    dayNamesShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                    dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
                    weekHeader: 'Sun',
                    dateFormat: 'dd-mm-yy',
                    firstDay: 1,
                    showAnim: 'slideDown',
                    isRTL: false,
                    showMonthAfterYear: false,
                    yearSuffix: ''
                } );

                // Инициализация
                $('input[name*="period"], .datepicker').datepicker({ dateFormat: 'dd-mm-yy' });

            });
		</script>
		<?php
	}
}


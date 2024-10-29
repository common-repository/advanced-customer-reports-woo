jQuery(document).ready(function($) {

    var current_user_id = null;
    var current_username = null;

    function loadReport(start_date, end_date, status, page = 1) {
        // Show loading indicator
        $('#loadingreport').show();

        // Format the dates
        var start_date_format = start_date ? moment(start_date).format('MMMM D, YYYY') : '';
        var end_date_format = end_date ? moment(end_date).format('MMMM D, YYYY') : '';

        if(current_username) {
            if(start_date) {
                $('#loadingreporttext').text('Loading report for ' + current_username + ' between ' + start_date_format + ' and ' + end_date_format + '...');
            } else {
                $('#loadingreporttext').text('Loading report for ' + current_username + '...');
            }
        } else {
            if(start_date) {
                $('#loadingreporttext').text('Loading report for all customers between ' + start_date_format + ' and ' + end_date_format + '...');
            } else {
                $('#loadingreporttext').text('Loading report for all customers...');
            }
        }

        $.ajax({
            url: acreportsScripts.ajaxurl,
            type: 'POST',
            data: {
                action: 'acreports_generate_customer_report',
                user_id: current_user_id,
                username: current_username,
                start_date: start_date,
                end_date: end_date,
                status: status,
                page: page,
                nonce: acreportsScripts.nonce
            },
            success: function(response) {
                $('#report_results').html(response);
                $('#loadingreport').hide();
                loadOrders(1);
                $('#generate_report').prop('disabled', false);
                $('#generate_report').css('opacity', '1');
                $('#generate_report').css('pointer-events', 'auto');

            }
        });
    }

    $('#generate_report').on('click', function() {

        // Make sure field not empty
        if (!$('#username_picker').val()) {
            alert('Please enter a username to generate a report for.');
            return;
        }

        $('#loadingreport').show();
        $('#report_results').html('');

        current_user_id = null; // Reset user_id
        current_username = $('#username_picker').val(); // Use username from input
        $('#clear_username').css('display', 'inline-block');

        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var status = $('#status').val();

        $('#generate_report').prop('disabled', true);
        $('#generate_report').css('opacity', '0.5');
        $('#generate_report').css('pointer-events', 'none');

        loadReport(start_date, end_date, status, 1);
    });

    /*
    * Form submission
    */
    jQuery(document).ready(function($) {
        $('#acreports-settings-form').submit(function(e) {
            e.preventDefault();

            var data = {
                'action': 'acreports_save_settings',
                'show_date_filters': $('#show_date_filters').is(':checked') ? 1 : 0,
                'acreports_settings_nonce_field': $('#acreports_settings_nonce_field').val()
            };

            $.post(ajaxurl, data, function(response) {
                alert('Settings saved');
            });
        });
    });

    /*
    * Listen for changes on the username picker
    */
    $('#username_picker').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'acreports_search_usernames',
                    term: request.term
                },
                success: function(data) {
                    response(JSON.parse(data));
                }
            });
        },
        select: function(event, ui) {
            current_user_id = null; // Reset user_id
            current_username = ui.item.label;
            loadReport($('#start_date').val(), $('#end_date').val(), $('#status').val(), 1);
        }
    });

    /*
    * For pagination
    */
    function loadOrders(page) {
        var page = $(this).data('page');
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var status = $('#status').val();
        var all_orders = $('#all_orders').text();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'acreports_load_orders_ajax',
                username: current_username,
                start_date: start_date,
                end_date: end_date,
                status: status,
                all_orders: all_orders,
                page: page
            },
            success: function(response) {
                $('#orders-content').html(response);
            },
            error: function() {
                console.log('Error loading orders - customer not found.');
            }
        });
    }
    $(document).on('click', '.pagination-link', loadOrders);

    /*
    * Clear username picker input
    */
    current_username = "<?php echo esc_js($prefill_username); ?>";
    $('#username_picker').on('input', function() {
        if ($(this).val()) {
            $('#clear_username').css('display', 'inline-block');
        } else {
            $('#clear_username').hide();
        }
    });
    $('#clear_username').on('click', function() {
        $('#username_picker').val('');
        $(this).hide();
    });
    $('#clear_username').hide();

    /*
    * Tooltip
    */
    var $tooltip = $('#pro_tooltip');
    $('.pro-feature').hover(
        function() { // Mouse enter
            var offset = $(this).offset();
            $tooltip.css({
                top: offset.top - 30,
                left: offset.left + $(this).width() / 2 - $tooltip.width() / 2
            }).css('visibility', 'visible').css('opacity', 1);
        },
        function() { // Mouse leave
            $tooltip.css('visibility', 'hidden').css('opacity', 0);
        }
    );

    /*
    * On click of the view report button, set the username and click the generate report button
    */
    $(document).on('click', '.view-customer-report', function(e){
        var username = $(this).data('username');
        $('#username_picker').val(username);
        $('#generate_report').click();
    });

    /*
    * Report Tabs
    */
    $(document).on('click', '.acreports-tab', function() {

        var tabId = $(this).data('tab');

        // Hide all tab contents
        $('.tab-content').hide();

        // Show the clicked tab's content
        $('#' + tabId).show();

        // Remove active class from all tab buttons
        $('.acreports-tab').removeClass('active');

        // Add active class to clicked tab button
        $(this).addClass('active');

    });

    /*
    * Tab Switching
    */
    $(document).on('click', '.nav-tab', function(e) {
        e.preventDefault();

        // Hide all tab content
        $('#tabs-content > div').hide();

        // Show clicked tab content
        $('#' + $(this).attr('id') + '-content').show();

        // Set active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
    });

    /*
    * Product tabs
    */
    $(document).on('click', '.product-tab-link', function() {
        var tab_id = $(this).attr('data-tab');
        $('.product-tab-link').removeClass('current');
        $('.product-tab-content').removeClass('current');
        $(this).addClass('current');
        $("#" + tab_id).addClass('current');
    });

    /*
    * Date Range Picker
    *
    * Set the date range picker values based on the selected option
    * 
    */
    var acrFormatDate = function(date) {
        var dd = date.getDate();
        var mm = date.getMonth() + 1; //January is 0!
        var yyyy = date.getFullYear();
        if (dd < 10) {
            dd = '0' + dd;
        } 
        if (mm < 10) {
            mm = '0' + mm;
        } 
        return yyyy + '-' + mm + '-' + dd;
    };
    jQuery('#date_range').on('change', function() {
        var date_range = jQuery(this).val();
        if (date_range == 'all_time') {
            jQuery('#start_date').val('');
            jQuery('#end_date').val(acrFormatDate(new Date()));
        } else if (date_range == 'this_month') {
            var today = new Date();
            var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            var lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            jQuery('#start_date').val(acrFormatDate(firstDay));
            jQuery('#end_date').val(acrFormatDate(lastDay));
        } else if (date_range == 'last_month') {
            var today = new Date();
            var firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            var lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
            jQuery('#start_date').val(acrFormatDate(firstDay));
            jQuery('#end_date').val(acrFormatDate(lastDay));
        } else if (date_range == 'this_year') {
            var today = new Date();
            var firstDay = new Date(today.getFullYear(), 0, 1);
            var lastDay = new Date(today.getFullYear(), 11, 31);
            jQuery('#start_date').val(acrFormatDate(firstDay));
            jQuery('#end_date').val(acrFormatDate(lastDay));
        } else if (date_range == 'last_year') {
            var today = new Date();
            var firstDay = new Date(today.getFullYear() - 1, 0, 1);
            var lastDay = new Date(today.getFullYear() - 1, 11, 31);
            jQuery('#start_date').val(acrFormatDate(firstDay));
            jQuery('#end_date').val(acrFormatDate(lastDay));
        } else if (date_range == 'custom') {
            jQuery('#start_date').val('');
            jQuery('#end_date').val('');
        }
    });

});
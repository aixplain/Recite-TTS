(function ($) {
    //'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    var processAjaxObject = null;

    $(document).ready(function () {
        $('.aixplain-bss .shortcode').click(function () {
            const text = $(this).text();
            const clipboard = navigator.clipboard;
            if (clipboard !== undefined && clipboard !== "undefined") {
                navigator.clipboard.writeText(text);
                $('#aixplain-bss-response').html('<div class="updated saved-message">Copied!</div>');
                setTimeout('jQuery(".updated.saved-message").fadeOut()', 1000);
            } else {
                if (document.execCommand) {
                    const el = document.createElement("input");
                    el.value = text;
                    document.body.append(el);

                    el.select();
                    el.setSelectionRange(0, value.length);

                    if (document.execCommand("copy")) {
                        $('#aixplain-bss-response').html('<div class="updated saved-message">Copied!</div>');
                        setTimeout('jQuery(".updated.saved-message").fadeOut()', 1000);
                    }

                    el.remove();
                } else {
                }
            }
        });
        $('#aixplain-bss-process-action').bind('click', function () {
            if (processAjaxObject) {
                processAjaxObject.abort();
                processAjaxObject = null;
                $(this).html('Process');
            } else {
                $(this).html('Processing... | click to stop processing');
                process();
            }
        });
        // $('.aixplain-bss form').submit(function(e){
        //     e.preventDefault();
        //    $.post(form,function()"{")
        // });

        $('.synthesize-action').click(function () {
            const $action = $(this);
            if($action.html()==='processing')return;
            $action.html('processing').css('color','#000');
            $.post(ajaxurl, {action: 'aixplain_bss_synthesize_single_post', post_id: $action.data('id')}).then(function(data){
                $action.parent().html('<a target="_blank" href="'+data.url+'">Yes</a>');

            }).catch(function(e){
                $action.html('Synthesize').css('color','red');
                $('#wp-header-end').append('<div class="synthesize-message-'+$action.data('id')+' error error-message">' + e.responseJSON.data + '</div>');
                setTimeout('jQuery(".synthesize-message-'+$action.data('id')+'.error.error-message").fadeOut()', 4000);
            });
        });

        $('.un-synthesize-action').click(function () {
            const $action = $(this);
            if($action.html()==='processing')return;
            $action.html('processing').css('color','#000');
            $.post(ajaxurl, {action: 'aixplain_bss_unsynthesize_single_post', post_id: $action.data('id')}).then(function(data){
                $action.parent().html('<a target="_blank" href="'+data.url+'">Yes</a>');

            }).catch(function(e){
                $action.html('Synthesize').css('color','red');
                $('#wp-header-end').append('<div class="synthesize-message-'+$action.data('id')+' error error-message">' + e.responseJSON.data + '</div>');
                setTimeout('jQuery(".synthesize-message-'+$action.data('id')+'.error.error-message").fadeOut()', 4000);
            });
        });
    })


    const process = function () {

        processAjaxObject = $.post(ajaxurl, {action: 'aixplain_bss_synthesize_all_posts'});
        processAjaxObject.then(function (res) {
            //console.log(res);
            $('#aixplain-bss-response').html('Processed ' + res.processed + ' of ' + res.total);
            $('#aixplain-bss-total-blogs').html(res.total);
            $('#aixplain-bss-synthesised-blogs').html(res.processed);
            $('#aixplain-bss-un-synthesised-blogs').html(res.total - res.processed);
            if (parseInt(res.processed) < parseInt(res.total)) {
                setTimeout( function () {
                    process();
                },100);
            } else {
                $('#aixplain-bss-process-action').prop('disabled', true).html('Done Processing');
                processAjaxObject = null;
            }
        }).catch(function (e) {
            //console.log(e);
            $('#aixplain-bss-process-action').html('Process');
            $('#aixplain-bss-response').html('<div class="error error-message">' + e.responseJSON.data + '</div>');
            setTimeout('jQuery(".error.error-message").fadeOut()', 4000);
            processAjaxObject = null;
        });
    }


})(jQuery);



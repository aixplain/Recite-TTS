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
    var processTimeoutRef = null;

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
        var $processAction = $('#aixplain-bss-process-action');
        $processAction.bind('click', function () {
            if (processTimeoutRef || processAjaxObject) {
                stopProcess($(this));
            } else {

                process($(this));
            }
        });
        if ($processAction.data("trigger-onload")) {
            if ($processAction.data("is-stop"))
                stopProcess($processAction);
            else
                process($processAction);
        }
        // $('.aixplain-bss form').submit(function(e){
        //     e.preventDefault();
        //    $.post(form,function()"{")
        // });

        $('.synthesize-action').click(function () {
            var $action = $(this);
            console.log($action.html())
            if ($action.html() === 'processing') return;
            $action.html('processing').css('color', '#000');
            $.post(ajaxurl, {
                action: 'aixplain_bss_synthesize_single_post',
                post_id: $action.data('id')
            }).then(function (data) {
                if (data.url)
                    $action.parent().html('<a target="_blank" href="' + data.url + '">Yes</a>');
                else
                    $action.parent().html('Failed, ' + data.error);

            }).catch(function (e) {
                $action.html('Synthesize').css('color', 'red');
                $('#wp-header-end').append('<div class="synthesize-message-' + $action.data('id') + ' error error-message">' + e.responseJSON.data + '</div>');
                setTimeout('jQuery(".synthesize-message-' + $action.data('id') + '.error.error-message").fadeOut()', 4000);
            });
        });

        $('#aixplain-bss-reset-posts-has-api-errors-action').click(function () {
            const $action = $(this);
            if ($action.html() === 'resetting') return;
            $action.html('resetting');
            $.post(ajaxurl, {
                action: 'aixplain_bss_reset_api_errors_posts'
            }).then(function (res) {
                $('#aixplain-bss-total-blogs').html(res.total);
                $('#aixplain-bss-synthesised-blogs').html(res.processed);
                $('#aixplain-bss-un-synthesised-blogs').html(res.to_be_processed);
                $('#aixplain-bss-not-possible-blogs').html(res.not_possible_to_process);
                $('#aixplain-bss-blogs-has-api-error').html(res.posts_has_api_error);
                $action.parent().remove();
                window.location.reload();
            }).catch(function (e) {
                $action.html('Reset');
                setTimeout('jQuery(".synthesize-message-' + $action.data('id') + '.error.error-message").fadeOut()', 4000);
            });
        });

        $('.un-synthesize-action').click(function () {
            const $action = $(this);
            if ($action.html() === 'processing') return;
            $action.html('processing').css('color', '#000');
            $.post(ajaxurl, {
                action: 'aixplain_bss_unsynthesize_single_post',
                post_id: $action.data('id')
            }).then(function (data) {
                $action.parent().html('<span >No</span>');

            }).catch(function (e) {
                $action.html('Synthesize').css('color', 'red');
                $('#wp-header-end').append('<div class="synthesize-message-' + $action.data('id') + ' error error-message">' + e.responseJSON.data + '</div>');
                setTimeout('jQuery(".synthesize-message-' + $action.data('id') + '.error.error-message").fadeOut()', 4000);
            });
        });
    })


    const process = function (obj) {
        if (obj)
            $(obj).html('Processing... | click to stop processing');
        processAjaxObject = $.post(ajaxurl, {action: 'aixplain_bss_synthesize_all_posts'});
        processAjaxObject.then(function (res) {
            //console.log(res);
            $('#aixplain-bss-response').html('Processed ' + res.processed + ' of ' + (res.total - res.posts_has_api_error - res.not_possible_to_process));
            $('#aixplain-bss-total-blogs').html(res.total);
            $('#aixplain-bss-synthesised-blogs').html(res.processed);
            $('#aixplain-bss-un-synthesised-blogs').html(res.to_be_processed);
            $('#aixplain-bss-not-possible-blogs').html(res.not_possible_to_process);
            $('#aixplain-bss-blogs-has-api-error').html(res.posts_has_api_error);
            if (res.isRunning) {
                processTimeoutRef = setTimeout(function () {
                    process();
                }, 5000);
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

    const stopProcess = function (obj) {
        if (obj)
            $(obj).html('Stopping...');
        if (processAjaxObject) {
            processAjaxObject.abort();
            processAjaxObject = null;
        }
        if (processTimeoutRef) {
            processTimeoutRef = clearTimeout(processTimeoutRef);
        }

        stopProcessAjaxObject = $.post(ajaxurl, {action: 'aixplain_bss_stop_synthesize_all_posts'});
        stopProcessAjaxObject.then(function (res) {
            //console.log(res);
            $('#aixplain-bss-response').html('');
            $('#aixplain-bss-total-blogs').html(res.total);
            $('#aixplain-bss-synthesised-blogs').html(res.processed);
            $('#aixplain-bss-un-synthesised-blogs').html(res.to_be_processed);
            $('#aixplain-bss-not-possible-blogs').html(res.not_possible_to_process);
            $('#aixplain-bss-blogs-has-api-error').html(res.posts_has_api_error);
            $('#aixplain-bss-process-action').html('Process');
            stopProcessAjaxObject = null;
        }).catch(function (e) {
            //console.log(e);
            $('#aixplain-bss-process-action').html('Process');
            $('#aixplain-bss-response').html('<div class="error error-message">' + e.responseJSON.data + '</div>');
            setTimeout('jQuery(".error.error-message").fadeOut()', 4000);
            stopProcessAjaxObject = null;
        });
    }


})(jQuery);



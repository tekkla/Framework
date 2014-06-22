// ---------------------------------------------------------------------------- 
// Function with commands to use on "ready" and in/after ajax requests
// ----------------------------------------------------------------------------
function webReadyAndAjax() {

    // Bind datepicker
    $('.web-form-datepicker').webDatepicker();

    // Bind error popover
    $('.form-control[data-error]').webErrorPop();

    // beautifiying xdebug oputput including ajax return values add styling
    // hooks to any XDEBUG output
    $('font>table').addClass('xdebug-error');
    $('font>table *').removeAttr('style').removeAttr('bgcolor');
    $('font>table tr:first-child').addClass('xdebug-error_description');
    $('font>table tr:nth-child(2)').addClass('xdebug-error_callStack');

    // Fade out elements
    $('.web-fadeout').delay(web_fadeout_time).slideUp(800, function() {

        $(this).remove();
    });
}

// ----------------------------------------------------------------------------
// Eventhandler "ready"
// ----------------------------------------------------------------------------
$(document).ready(function() {

    // scroll to top button
    $(window).scroll(function() {

        if ($(this).scrollTop() > 100) {
            $('#web-scrolltotop').fadeIn();
        } else {
            $('#web-scrolltotop').fadeOut();
        }
    });

    // Run function with commands to be used on "ready" and "ajaxComplete"
    webReadyAndAjax()
});

// ----------------------------------------------------------------------------
// Eventhandler on "ajaxStart"
// ----------------------------------------------------------------------------
$(document).ajaxStart(function() {

    // Show loading circle on ajax loads
    $('body').addClass("loading");
});

// ----------------------------------------------------------------------------
// Do this on "ready" and on "ajaxComplete" events
// ----------------------------------------------------------------------------
$(document).ajaxStop(function(event) {

    // Hide loading circle
    $('body').removeClass("loading");
});

// ----------------------------------------------------------------------------
// Input|textarea maxlength counter
// ----------------------------------------------------------------------------
$(document).on('keyup input paste', 'textarea[maxlength]', function() {

    if ($(this).data('counter') !== undefined) {
        var limit = parseInt($(this).attr('maxlength'));
        var text = $(this).val();
        var chars = text.length;

        if (chars > limit) {
            var new_text = text.substr(0, limit);
            $(this).val(new_text);
        }
        var counterid = $(this).data('counter');

        if ($(counterid).length > 0)
            $(counterid).text(limit - chars);
    }
});

// ----------------------------------------------------------------------------
// Scroll to top click handler
// ----------------------------------------------------------------------------
$(document).on('click', '#web-scrolltotop', function(event) {

    if (navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {           
        window.scrollTo(0,0) // first value for left offset, second value for top offset
    }else{
        $('html,body').animate({
            scrollTop: 0,
            scrollLeft: 0
        }, 800, function(){
            $('html,body').clearQueue();
        });
    }    

    return false;
});

// ----------------------------------------------------------------------------
// ClickHandler for back button
// ----------------------------------------------------------------------------
$(document).on('click', '.web-btn-back', function(event) {

    document.history.go(-1);
});

$(document).on('click', '*[data-web-confirm]', function(event) {

    if ($(this).data('web-ajax') !== undefined)
        return;

    // confirmation wanted?
    if ($(this).data('web-confirm') !== undefined) {
        var result = confirm($(this).data('web-confirm'));
        if (!result)
            return false;
    }
});

// ----------------------------------------------------------------------------
// Ajax based click-handler to links with the data attribute 'data-web-ajax'
// ----------------------------------------------------------------------------
$(document).on('click', '*[data-web-ajax]', function(event) {

    // confirmation wanted?
    if ($(this).data('web-confirm') !== undefined) {
        var result = confirm($(this).data('web-confirm'));
        if (!result)
            return false;
    }

    // Prepare options object
    var ajaxOptions = {

        // On success the response parser is called
        success : parseWebJson,

        // Returntype is JSON
        dataType : 'json'
    };

    // Which url to reqest? The data attribute "web-form"
    // indicates that we are going to send a
    // form. Without this, it is a normal link, that we are
    // going to load.
    if ($(this).data('web-form') === undefined) {

        // WebExt links will be handled by GET
        ajaxOptions.type = 'GET';

        // Get id of the clicked link
        var id = this.id;

        // Try to get url either from links href attribute or
        if ($(this).attr('href') !== undefined) {
            var url = $(this).attr('href');
        } else if ($(this).data('href') !== undefined) {
            var url = $(this).data('href');
        } else {
            alert('WebExt Ajax: No URI to query found. Neither as "href" nor as "data-href". Aborting request.');
            return false;
        }
    } else {

        // WebExt forms will be handled py POST
        ajaxOptions.type = 'POST';

        // Get the form ID from the clicked link
        var id = $(this).data('web-form');

        // Get action url
        var url = $('#' + id).attr('action');

        // experimental usage of ckeditor 4 inline editor. id is
        // the div where the content is present
        // control the hidden form where we put the content
        // before serialization gathers the form data
        // for ajax post.
        if ($(this).data('inline-id') !== undefined && $(this).data('inline-control') !== undefined) {
            var control = $(this).data('inline-control');
            var content = $('#' + $(this).data('inline-id')).html();
            $('#' + control).val(content);
        }

        // Since this is a form post, get the data to send to
        // server
        ajaxOptions.data = $('#' + id).serialize();
    }

    // Set the url to use
    ajaxOptions.url = url + '/ajax';

    // Add error handler
    ajaxOptions.error = function(XMLHttpRequest, textStatus, errorThrown) {

        parseWebJson({
            cmd : {
                type : 'error',
                target : '#web-message',
                mode : 'replace',
                content : XMLHttpRequest.responseText
            }
        });
    };

    // Fire ajax request!
    $.ajax(ajaxOptions);

    event.preventDefault();
});

// ----------------------------------------------------------------------------
// Json parser for WebExt ajax response
// ----------------------------------------------------------------------------
function parseWebJson(json) {

    // console.debug(json);

    $.each(json, function(i, v) {

        switch (v.type) {
            case 'refresh':
                window.location.replace(v.content);
                return true;
                break;
            case "html":
                switch (v.mode) {
                    case "replace":
                        $(v.target).html(v.content);
                        webReadyAndAjax();
                        break;
                    case "before":
                        $(v.target).before(v.content);
                        webReadyAndAjax();
                        break;
                    case "after":
                        $(v.target).after(v.content);
                        webReadyAndAjax();
                        break;
                    case "prepend":
                        $(v.target).prepend(v.content);
                        webReadyAndAjax();
                        break;
                    case "append":
                        $(v.target).append(v.content);
                        webReadyAndAjax();
                        break;
                    case "remove":
                        $(v.target).remove();
                        break;
                }
                break;

            case "alert":
                Apprise(v.content);
                webReadyAndAjax();
                break;
            case "error":
                $(v.target).addClass('fade in').html('<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>' + v.content).alert();
                webReadyAndAjax();
                $(v.target).bind('closed.bs.alert', function() {

                    $(this).removeClass().html('').unbind('closed.bs.alert');
                });

                break;
            case "log":
            case "console":
                console.log(v.content);
                break;
            case "modal":

                // fill dialog with content
                $('#web-modal').html(v.content).modal({
                    keyboard : false
                });
                webReadyAndAjax();
                break;

            case 'load_script':
                $.getScript(v.content);
                break;
        }

    });
}

/**
 * Created by felix on 02.07.17.
 */

Blinkfair.Alert = function() {
    "use strict";

    function showAlert(message, alertType, timeout)
    {
        $("#alert-show").remove();
        $('#alert-hook').append('<div id="alert-show" class="alert alert-' +  alertType + '"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + message +'</div>');

        window.setTimeout(function() {
            $("#alert-show").remove();
        }, timeout);
    }

    // Public API
    return {
        showAlert: function(message, alertType, timeout)
        {
            showAlert(message, alertType, timeout);
        }
    };
}
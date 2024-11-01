/**
 * Handles our ActiveCampaign site tracking
 */

jQuery(document).ready(function($){
    
    if (typeof(acap_email) == 'undefined' || acap_email == null)
        acap_email = getCookie('acap_email');

    console.log(trackcmp_actid);
    console.log(acap_email);

    // set cookie using js - this is duplicate for setcookie in php
    setCookie("acap_email", acap_email, (20 * 365));

    if (dont_show_tracking_code)
        return;
    var trackcmp = document.createElement("script");
    trackcmp.async = true;
    trackcmp.type = 'text/javascript';
    trackcmp.src = '//trackcmp.net/visit?actid='+trackcmp_actid+'&e='+acap_email+'&r='+encodeURIComponent(document.referrer)+'&u='+encodeURIComponent(window.location.href);
    var trackcmp_s = document.getElementsByTagName("script");
    if (trackcmp_s.length) {
            trackcmp_s[0].parentNode.appendChild(trackcmp);
    } else {
            var trackcmp_h = document.getElementsByTagName("head");
            trackcmp_h.length && trackcmp_h[0].appendChild(trackcmp);
    }
    
});


/**
 * Handles our ActiveCampaign events
 */

/**
 * Fires an AJAX post. This will do an action for our ACAP events
 * 
 * @param {string} Any string value
 * @returns {undefined}
 */
function acap_events($event, $value) {
    jQuery.post(
        ajaxurl, 
        {
            'action': 'acap_events',
            'event':  $event,
            'data':   $value
        }, 
        function(data){}
    );
    
    return;
}


function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

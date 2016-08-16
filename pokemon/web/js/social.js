$(document).ready(function() {
    $.ajaxSetup({ cache: true });
    $.getScript('//connect.facebook.net/en_US/sdk.js', function(){
        window.fbAsyncInit = function() {
            FB.init({
                appId      : '1756332607957995',
                xfbml      : true,
                version    : 'v2.6'
            });
        };

        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    });

});

function myshare(type,url,text){
    var popUpH = 436;
    var popUpW = 626;

    var t = $(window).height()/2 - popUpH/2;
    var l = $(window).width()/2 - popUpW/2;

    if(type == 'fb'){
        FB.ui({
            method: 'share',
            href: url,
        }, function(response){});
    } else if(type =='vk'){
        url = 'http://vk.com/share.php?url='+encodeURIComponent(url);
        window.open(url,'','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + popUpW + 'px, height=' + popUpH +'px, top=' + t + 'px, left=' + l + 'px');
    } else if(type == 'gp'){
        url = 'https://plus.google.com/share?url='+encodeURIComponent(url);
        window.open(url,'','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + popUpW + 'px, height=' + popUpH +'px, top=' + t + 'px, left=' + l + 'px');
    }
}
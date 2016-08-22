$(window).on( "scroll", function(){

    if (  $('.ban-topper-placeholder').length != 0 ){    

        if ( $(window).scrollTop() < ( $('.ban-topper-placeholder').height() - 200 + $('header').height() ) ) {

            var topS = $('header').height() - $(window).scrollTop() ;

            $('.ban-topper').css('top', topS );

        } else {

            var topS = 200 - $('.ban-topper-placeholder').height()  ;
            $('.ban-topper').css('top', topS  );

        }
    }
});


function googleMapContact(mapWrap){
    function initialize() {
        var myLatlng = new google.maps.LatLng(cordXcontact ,cordYcontact);
        var myOptions = {
            zoom: 5,
            center: myLatlng,
             styles: [{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}],
            disableDefaultUI: false, //без управляющих елементов
            mapTypeId: google.maps.MapTypeId.ROADMAP, // SATELLITE - снимки со спутника,
            zoomControlOptions: {
               position: google.maps.ControlPosition.LEFT_BOTTOM // позиция слева внизу для упр елементов
            }
        }
        var map = new google.maps.Map(document.getElementById(mapWrap), myOptions);

        var contentString = '<div class="marker-test">'+googleTextcontact+'</div>';
        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });


        /*маркер на svg*/
       // var SQUARE_PIN = 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z'
        //больше - http://map-icons.com/
        /*/маркер на svg*/

        var image = 'images/pock.png';   // иконка картинкой

        
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            animation: google.maps.Animation.DROP, // анимация при загрузке карты
            icon: image //  иконка картинкой
           /* icon: {                               //маркер на svg
                path: SQUARE_PIN,
                fillColor: '#fff',
                fillOpacity: 0.7,
                strokeColor: '#FF3232',
                strokeWeight: 5
            },*/
        });

        /*анимация при клике на маркер*/
        marker.addListener('click', toggleBounce);
        function toggleBounce() {
          if (marker.getAnimation() !== null) {
            marker.setAnimation(null);
          } else {
            marker.setAnimation(google.maps.Animation.BOUNCE);
          }
        }
        /*/анимация при клике на маркер*/

        /*По клику открываеться инфоблок*/
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.open(map,marker);
        });

    }
    initialize();
}

if ( $('body').find('#map-contact').length == 1 ){
    google.maps.event.addDomListener(window, "load", googleMapContact('map-contact'));
}




$(document).ready(function(){

    $('.cabinet-wrap select').styler();

    $('.my-added-pockemon').on('click', '.left-list>ul>li>.title-row-table', function(){

        if ( $(this).closest('li').hasClass('active') ){

             $(this).closest('li').removeClass('active');
            $(this).closest('li').find('.hovered-content').slideUp(300);

        } else {
            $(this).closest('ul').find('li').each(function(){

                $(this).removeClass('active');
                $(this).find('.hovered-content').slideUp(300);

            }) ;
            $(this).closest('li').addClass('active');
            $(this).closest('li').find('.hovered-content').slideDown(300);
        }
    });

    $('.user-info').on('click', '.reduct-key>b',  function(){

        /* submit change */

            if( $(this).closest('.contact-form-item').hasClass('i-can-write') ){

                $(this).closest('form').submit();

            }

        /* submit change */

        /* open change field */

            if( $(this).closest('.contact-form-item').find('.hidden-part').length == 0 ){

                $(this).closest('.contact-form-item').addClass('i-can-write');
                $(this).closest('form').find('input').removeAttr('disabled'); //.attr("disabled","disabled");

            } else {

                $(this).closest('.contact-form-item').addClass('i-can-write');
                $(this).closest('.contact-form-item').find('.hidden-part').slideDown(300);

                $(this).closest('form').find('input').removeAttr('disabled'); //.attr("disabled","disabled");

                console.log( $(this).closest('form').find('input') );

                if( $(this).closest('form').hasClass('rewrite_pass') ){
                    $(this).closest('form').find('input[name="user_pass"]').val('');

                     $(this).closest('form').find('.pass-input').removeClass('dirty');
                }

            }
        
        /* open change field */        

    });


    /* dropdown select  */

        $(document).on('click', function(event){


            if(!$('.pockemon-drop-down').is(event.target) && $('.pockemon-drop-down').has(event.target).length === 0 ){
                $('.pockemon-drop-down').find('.drop-list').slideUp(300);
            } 

        })

        $('.lister-tubber').on('click', '.pockemon-drop-down .placeholder-drop', function(){

            $(this).closest('.pockemon-drop-down').find('input').focus();

            $(this).closest('.pockemon-drop-down').find('.drop-list').slideDown(300);

        });

        $('.lister-tubber').on('click', '.drop-list ul>li', function(){

            $(this).closest('.pockemon-drop-down').find('.drop-list').slideUp(300);
            var currentchecker = $(this).html() ;
            $('.pockemon-drop-down').find('.placeholder-drop').html( currentchecker ); 

        });

        $('.lister-tubber').on('keypress', 'input', function(e){
            console.log( String.fromCharCode(e.keyCode) );

            var api = $('.drop-list').data('jsp');     

            var iScrollToThis ;    

           for (var i = 0; i < $('.drop-list').find('ul>li').length; i++) {

               var slovo = $('.drop-list').find('ul>li').eq(i).find('p').text() ;

               var iScrollToThis ;
               
               if ( slovo[0] == String.fromCharCode(e.keyCode) || slovo[0] == str.toLowerCase( String.fromCharCode(e.keyCode) ) ) {

                   iScrollToThis = i;

                   console.log( iScrollToThis );

                   api.scrollTo( 0, iScrollToThis * 76  );

                   return false;

               }
               
           }

            $('.pockemon-drop-down .drop-list').find('.jspPane').css('top', -160 );

        })
    
    /* dropdown select  */


    setTimeout(function(){
        $('.drop-list').jScrollPane({
            showArrows: true,
            animateScroll: true
        });
        $('.drop-list').css('display', 'none');

    }, 300);


    



    

});

$(window).load(function(){

});

$(window).resize(function(){

});
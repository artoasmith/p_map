
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
        var cordXcontact  = 0 ; 
        var cordYcontact  = 0 ;
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

        var image = 'images/map-location-pin.png';   // иконка картинкой

        
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            animation: google.maps.Animation.DROP, // анимация при загрузке карты
            icon: image //  иконка картинкой

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

if ( $('body').find('#add-pockemon').length == 1 ){
    google.maps.event.addDomListener(window, "load", googleMapContact('add-pockemon'));
}




/* logick tubber  */

    $(document).on('click', '.lister-tubber>ul>li', function(){


    });

/* logick tubber  */




$(document).ready(function(){
    
    if ( $('.cabinet-detail').length == 1 ) {

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
                console.log( (String.fromCharCode(e.keyCode)).toLowerCase() ) ;

                var api = $('.drop-list').data('jsp');     

                var iScrollToThis ;    

                for (var i = 0; i < $('.drop-list').find('ul>li').length; i++) {

                    var slovo = $('.drop-list').find('ul>li').eq(i).find('p').text() ;

                    var iScrollToThis ;

                    var stranChar = String.fromCharCode(e.keyCode) ;
                    
                    
                    if ( slovo[0] == stranChar || slovo[0] == ( stranChar.toUpperCase() ) ) {

                        iScrollToThis = i;

                        //  console.log( iScrollToThis );

                        api.scrollTo( 0, iScrollToThis * 76  );

                        return false;

                    }
                    
                }

            // $('.pockemon-drop-down .drop-list').find('.jspPane').css('top', -160 );

            })
    
        /* dropdown select  */

        /* tabs */

            $('.cabinet-detail').on('click', '.left>ul>li', function(){

                $('.cabinet-detail .left>ul>li').removeClass('active');
                $(this).addClass('active');

                var currentTab  = $(this).index();

                if ( $('.center>ul>li').eq(currentTab).hasClass('active') ){

                    console.log('some some some');

                } else {

                    $('.center>ul>li').fadeOut(1, function() {

                        $('.center>ul>li').removeClass('active');

                        $('.center>ul>li').eq(currentTab).fadeIn(1, function(){

                                $(this).addClass('active');

                            });

                    });                   

                }

                if( $('.center>ul>li').eq(currentTab).find('.pockemon-drop-down').length ==1 ){ 

                    $('.drop-list').css({'display': 'block', "opacity": '0'});

                    $('.drop-list').jScrollPane({
                            showArrows: true,
                            animateScroll: true
                        });

                        setTimeout(function(){
                            
                            $('.drop-list').css({'display': 'none', "opacity": '1'});

                        }, 300);

                }

                if( $('.center>ul>li').eq(currentTab).find('.add-new-pockemon').length ==1 ){

                    $('.topper-tab').find('ul>li').removeClass('otrbotano').removeClass('current');
                    $('.topper-tab').find('ul>li').eq(0).addClass('current');
                }

            });

        /* tabs */

        /* form */

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


        /* form */

        /* burger */

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

        /* burger */
    }
    
});

$(window).load(function(){

    if ( $('.cabinet-detail').length == 1 ) {

        $('.cabinet-detail').find('.center>ul>li').each(function(){
            if (  !$(this).hasClass('active') ){
                $(this).css('display', 'none');
            }
        })

    }

});

$(window).resize(function(){

});


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
                $('.pockemon-drop-down').find('.placeholder-drop').attr('data-pockemon', $(this).attr('data-pockemon') );
                $('.pockemon-drop-down').find('.placeholder-drop').html( currentchecker ); 

                $('.add-new-pockemon').removeClass('stage1').addClass('stage2');

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


                if( $('.center>ul>li').eq(currentTab).find('.add-new-pockemon').length ==1 ){

                    $('.topper-tab').find('ul>li').removeClass('otrbotano').removeClass('current');
                    $('.topper-tab').find('ul>li').eq(0).addClass('current');
                }

                if( $('.center>ul>li').eq(currentTab).find('#my-pockemon').length ==1 ){ 

                    google.maps.event.trigger(map3, 'resize');

                    map3.setCenter( new google.maps.LatLng(user_points[0].locationY , user_points[0].locationX) );

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


                /* emulate click on marker */

                var currentID = $(this).closest('li').attr('data-id') ;

                for (var i = 0; i < markers.length; i++) {

                    if ( currentID == markers[i].dataSum ) {

                       map3.setZoom(16);

                        map3.panTo( markers[i].getPosition() );

                    }
                                        
                }
                



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
        });

        $('.cabinet-detail').find('.left>ul>li:first').click();

        

        /* add content to chooser */


            $('.drop-list').find('ul').html('li li li');

            var allIAdd = '';

            for (var i = 0; i < pokemon.length; i++) {
                allIAdd += "<li data-pockemon='"+ pokemon[i].id + "'>"+
                    "<p>"+ pokemon[i].name +"</p>" +
                    "<img src='" + pokemon[i].image + "' alt=''>"+
                "</li>";
                
            }

            $('.drop-list').find('ul').html(allIAdd);

                $('.drop-list').jScrollPane({
                        showArrows: false,
                        animateScroll: true
                });
/*
                setTimeout(function(){
                    
                    $('.drop-list').css({'display': 'none', "opacity": '1'});

                }, 300);
*/

        /* add content to chooser */

    }




});

$(window).resize(function(){

});
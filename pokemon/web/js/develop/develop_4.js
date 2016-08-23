
 /* load form */

        function formCharge() {
            if ( !$('.contact-form-item').length == "" ){
                $('.contact-form-item').find('input').each(function(){

                    if( !$(this).val().length == 0 ) {
                        $(this).closest('.form_input').addClass('dirty');
                    }

                });
            }
        }
        
    /* load form */

$(document).ready(function(){

    /* form beutify */

        $('input').on('focusin', function(){
            $(this).closest('.form_input').addClass('focused');
        });

        $('input').on('focusout', function(){
            $(this).closest('.form_input').removeClass('focused');
        });

        $('input').on('keyup', function(){
            
            if( $(this).val().length == 0 ){
                $(this).closest('.form_input').removeClass('dirty');
            } else {
                $(this).closest('.form_input').addClass('dirty').removeClass('error');
            }

        });

    /* form beutify */

    /* pockeball beutify */


        $('.pocke a').hover(function(){
            console.log('on');

            if( $(this).hasClass('top') ){
                $(this).closest('.pocke').addClass('top-hover');
            }
            if( $(this).hasClass('bot') ){
                $(this).closest('.pocke').addClass('bot-hover');
            }

        }, function(){
            console.log('off');
            $(this).closest('.pocke').removeClass('bot-hover').removeClass('top-hover');

        });

    /* pockeball beutify */

    /* pockeball open */

        $('.top-pock').on('click', function(){ /* login */

            if( $('.pockeball').hasClass('open-reg') ){

                $('.form-reg').slideUp(300, function(){

                    $('.pockeball').removeClass('open-reg').addClass('open-login'); 

                    setTimeout(function(){

                        $('.form-log').slideDown(300, function(){                        

                        });

                    }, 500); 

                });

            } else {

                $('.form-log').slideDown(300, function(){                    
                    
                });
                $('.pockeball').removeClass('open-reg').addClass('open-login'); 

            }

           // $('.pockeball').removeClass('open-reg').addClass('open-login'); 

        });

        $('.bot-pock').on('click', function(){ /* reg */

            if( $('.pockeball').hasClass('open-login') ){

                $('.form-log').slideUp(300, function(){

                    $('.pockeball').removeClass('open-login').addClass('open-reg'); 

                    setTimeout( function(){

                        $('.form-reg').slideDown(300, function(){                        

                        });

                    }, 500) 

                });

            } else {

                $('.pockeball').removeClass('open-login').addClass('open-reg'); 

                setTimeout( function(){

                    $('.form-reg').slideDown(300, function(){                        

                    });

                }, 500)
                

            }

          //  $('.pockeball').removeClass('open-login').addClass('open-reg'); 
            
        });



        $('.circle').on('click', function(){ /* circle */

            if( $('.pockeball').hasClass('open-reg') ){
                $('.form-reg').slideUp(300, function(){
                    $('.pockeball').removeClass('open-reg').removeClass('open-login');
                });
            }

            if( $('.pockeball').hasClass('open-login') ){
                $('.form-log').slideUp(300, function(){
                    $('.pockeball').removeClass('open-reg').removeClass('open-login');
                });
            }

          //  $('.pockeball').removeClass('open-reg').removeClass('open-login');

        });

    /* pockeball open */

    /* slider */

        $('.slider-row>.content').slick({
            infinite: false,
            dots: true,
            slidesToShow: 5,
            slidesToScroll: 1, 
            swipeToSlide: true
        });

    /* slider */
});

$(window).load(function(){

    /* load form */

        formCharge();
        
    /* load form */

});

$(window).resize(function(){

});

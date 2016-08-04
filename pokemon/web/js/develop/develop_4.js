


$(document).ready(function(){

    /* form beutify */

        $('form input').on('focusin', function(){
            $(this).closest('.form_input').addClass('focused');
            console.log('in');
        });

        $('form input').on('focusout', function(){
            $(this).closest('.form_input').removeClass('focused');
            console.log('out');
        });

        $('form input').on('keyup', function(){
            
            if( $(this).val().length == 0 ){
                $(this).closest('.form_input').removeClass('dirty');
            } else {
                $(this).closest('.form_input').addClass('dirty').removeClass('error');
            }
        });

    /* form beutify */

});

$(window).load(function(){
    /* load form */
        if ( !$('form').length == 0 ){
            $('form').find('input').each(function(){

                if( !$(this).val().length == 0 ) {
                    $(this).closest('.form_input').addClass('dirty');
                }

            })
        }
    /* load form */
});

$(window).resize(function(){

});

/*валидация формы*/
function validate(form, options){
    var setings = {
        errorFunction:null,
        submitFunction:null,
        highlightFunction:null,
        unhighlightFunction:null
    }
    $.extend(setings, options);

    var $form = $(form);

    if ($form.length && $form.attr('novalidate') === undefined) {
        $form.on('submit', function(e) {
            e.preventDefault();
        });

        $form.validate({
            errorClass : 'errorText',
            focusCleanup : true,
            focusInvalid : false,
            invalidHandler: function(event, validator) {
                if(typeof(setings.errorFunction) === 'function'){
                    setings.errorFunction(form);
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo( element.closest('.form_input'));
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('error');
                $(element).closest('.form_row').addClass('error').removeClass('valid');
                if( typeof(setings.highlightFunction) === 'function' ) {
                    setings.highlightFunction(form);
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('error');
                if($(element).closest('.form_row').is('.error')){
                    $(element).closest('.form_row').removeClass('error').addClass('valid');
                }
                if( typeof(setings.unhighlightFunction) === 'function' ) {
                    setings.unhighlightFunction(form);
                }
            },
            submitHandler: function(form) {
                if( typeof(setings.submitFunction) === 'function' ) {
                    setings.submitFunction(form);
                } else {
                    $form[0].submit();
                }
            }
        });

        $('[required]',$form).each(function(){
            $(this).rules( "add", {
                required: true,
                messages: {
                    required: "Вы пропустили"
                }
            });
        });

        if($('[type="email"]',$form).length) {
            $('[type="email"]',$form).rules( "add",
            {
                messages: {
                    email: "Невалидный email"
                 }
            });
        }

        if($('.tel-mask[required]',$form).length){
            $('.tel-mask[required]',$form).rules("add",
            {
                messages:{
                    required:"Введите номер мобильного телефона."
                }
            });
        }

        $('[type="password"]',$form).each(function(){
            if($(this).is("#re_password") == true){
                $(this).rules("add", {
                    minlength:3,
                    equalTo:"#password",
                    messages:{
                        equalTo:"Неверный пароль.",
                        minlength:"Недостаточно символов."
                    }
                });
            }
        })
    }
}


function popNext(popupId, popupWrap){

    $.fancybox.open(popupId,{
        padding:0,
        fitToView:false,
        wrapCSS:popupWrap,
        autoSize:true,
        afterClose: function(){
            $('form').trigger("reset");
            clearTimeout(timer);
        }
    });

    var timer = null;

    timer = setTimeout(function(){
        $('form').trigger("reset");
        $.fancybox.close(popupId);
    },2000);

}


/*маска на инпуте*/
function Maskedinput(){
    if($('.tel-mask')){
        $('.tel-mask').mask('+9 (999) 999-99-99 ');
    }
}

function sendToSerwerConfirm( dataId ){

    $.ajax({
        url : '/app_dev.php/pointsConfirm/' + dataId,
        method:'POST',
        success : function(data){

            console.log( data );
            
            if( data.error ){

                switch(data.code ) {
                    case '1': 
                        console.log( data.message );
                        location.replace( "/login" );
                        break;

                    case '2':  
                        console.log( data.message );

                        for (var i = 0; i > stack.length; i++){
                            if ( stack[i].id == dataId ){
                               stack.splice( i , 1 )
                            }
                        }
                        break;
                    case '3': 
                        alert( data.message );
                        break;

                }

            } else {
                if ( data.success ){
                    for (var i = 0; i > stack.length; i++){
                        if ( stack[i].id == data.point.id ){
                            stack[i].confirmed = data.point.confirmed ;
                        }
                    }

                } else {
                    console.log(' some error difinition ');
                }
            }
            

        }
    });
}


function sendToSerwerReject( dataId ){

    $.ajax({
        url : '/app_dev.php/pointsReject/' + dataId,
        method:'POST',
        success : function(data){

            console.log( data );
            
            if( data.error ){

                switch(data.code ) {
                    case '1': 
                        console.log( data.message );
                        location.replace( "/login" );
                        break;

                    case '2':  
                        console.log( data.message );

                        for (var i = 0; i > stack.length; i++){
                            if ( stack[i].id == dataId ){
                               stack.splice( i , 1 )
                            }
                        }
                        break;
                    case '3': 
                        alert( data.message );
                        break;

                }

                

            } else {
                if ( data.success ){
                    for (var i = 0; i > stack.length; i++){
                        if ( stack[i].id == dataId ){
                            stack[i].confirmed = data.point.confirmed ;
                        }
                    }

                } else {
                    console.log(' some error difinition ');
                }
            }
            

        }
    });
}

function validationReg(form){

    var thisForm = $(form);
    var formSur = thisForm.serialize();

    $.ajax({
        url : thisForm.attr('action'),
        data: formSur,
        method:'POST',
        success : function(data){

            console.log(data);
            if ( data.success ) {
                console.log('reload');
                //location.reload();
            }
            else {
               thisForm.closest('div').find('.error-row').css('display','block').find('p').html(data.message);
            }

        }
    });

}

function changeSomeCabinet(form){

    $(form).closest('.contact-form-row').addClass('loading-change');

  var thisForm = $(form);
  var formSur = thisForm.serialize();

    $.ajax({
        url : thisForm.attr('action'),
        data: formSur,
        method:'POST',
        success : function(data){

            $(form).closest('.contact-form-row').removeClass('loading-change');

            $(form).closest('.contact-form-item').removeClass('i-can-write');

            console.log(data);

            if ( data.success ) {
                console.log('reload');
                //location.reload();
            }
            else {
              $('.error-field-for-all').css('display','block').find('p').html(data.message);
            }

        }
    });
}


$(document).ready(function(){

    /* send data to server from map backstage  */ 

        $('.hide-content .button-block>.butt').click( function(){

            if ( $('.hide-content').hasClass('activate') ) {

                if( $(this).hasClass('confirm') ){
                    sendToSerwerConfirm ( $('.hide-content').attr('data-pokemon-id')  );
                }
                if( $(this).hasClass('not-confirm') ){
                    sendToSerwerReject( $('.hide-content').attr('data-pokemon-id') );                    
                }     
            }
            
        });
    /* send data to server from map backstage  */ 

    /* login */

        validate('.form-log form', {submitFunction:validationReg} );

        validate('.form-reg form', {submitFunction:validationReg} );
        

    /* login */

        validate('.rewrite_pass', {submitFunction:changeSomeCabinet} );
        validate('.rewrite_email', {submitFunction:changeSomeCabinet} );
        validate('.rewrite_name', {submitFunction:changeSomeCabinet} );

});
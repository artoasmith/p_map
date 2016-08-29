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

/*Отправка формы с вызовом попапа*/
function validationCall(form){

  var thisForm = $(form);
  var formSur = thisForm.serialize();

    $.ajax({
        url : thisForm.attr('action'),
        data: formSur,
        method:'POST',
        success : function(data){
            if ( data.trim() == 'true') {
                thisForm.trigger("reset");
                popNext("#call_success", "call-popup");
            }
            else {
               thisForm.trigger('reset');
            }

        }
    });
}


/*маска на инпуте*/
function Maskedinput(){
    if($('.tel-mask')){
        $('.tel-mask').mask('+9 (999) 999-99-99 ');
    }
}

/*fansybox на форме*/
function fancyboxForm(){
  $('.fancybox-form').fancybox({
    openEffect  : 'fade',
    closeEffect : 'fade',
    autoResize:true,
    wrapCSS:'fancybox-form',
    'closeBtn' : true,
    fitToView:true,
    padding:'0'
  })
}

//ajax func for programmer

function someAjax(item, someUrl, successFunc, someData){

    $(document).on('click', item, function(e){

        e.preventDefault();

        var itemObject = $(this);
        var ajaxData = null;

        if(typeof someData == 'function'){
            ajaxData = someData(itemObject);
        }else{
            ajaxData = someData;
        }

        console.log(ajaxData);

        $.ajax({
            url:someUrl,
            data:ajaxData,
            method:'POST',
            success : function(data){
                successFunc(data, itemObject);
            }
        });

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
                location.reload();
            }
            else {
               thisForm.closest('div').find('.error-row').css('display','block').find('p').html('Неверный логин или пароль');
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


function somePockemonConfirm() {

   var thisIDiSend = $('.map').find('.hide-content').attr('data-pokemon-id');

    console.log( thisIDiSend );

    $.ajax({        
        url : '/pointsConfirm/'+ thisIDiSend ,
        method:'POST',
        success : function(data){

            if( data == true ){

                $('.map').find('.hide-content').addClass('confirm-pokemon');

            } else {
                alert('someShit');
            }        

        }
    });

}

function somePockemonNotConfirm() {

   var thisIDiSend = $('.map').find('.hide-content').attr('data-pokemon-id');

    console.log( thisIDiSend );

    $.ajax({        
        url : '/pointsConfirm/'+ thisIDiSend ,
        method:'POST',
        success : function(data){

            if( data == true ){

                $('.map').find('.hide-content').removeClass('confirm-pokemon');

            } else {
                alert('someShit');
            }        

        }
    });

}

function addNewPockemonConfirm() {

    sendAlone = {
        "locationX" : markerAdd[0].getPosition().lat(),
        "locationY" : markerAdd[0].getPosition().lng(),
        "pokemon"   : $('.placeholder-drop').attr('data-pockemon'),
        "address"   : $('.add-new-pockemon').find('.row-place .place').html()
    };

    console.log( sendAlone );

    $.ajax({    
        url : '/points',
        data: {
            point: sendAlone
        },
        method:'POST',
        success : function(data){

            if( data == true ){

                $('.add-new-pockemon').removeClass('stage2').addClass('stage3');

            } else {
                alert('someShit');
            }        

        }
    });
    
}


$(document).ready(function(){

   /* login */

        validate('.form-log form', {submitFunction:validationReg} );

    /* login */
/*
    validate('.rewrite_pass', {submitFunction:changeSomeCabinet} );
    validate('.rewrite_email', {submitFunction:changeSomeCabinet} );
    validate('.rewrite_name', {submitFunction:changeSomeCabinet} );
*/
    Maskedinput();
    fancyboxForm();

    validate('.rewrite_pass', {submitFunction:changeSomeCabinet} );
    validate('.rewrite_email', {submitFunction:changeSomeCabinet} );
    validate('.rewrite_name', {submitFunction:changeSomeCabinet} );

    $('.add-new-pockemon').on('click', '.chooser .button-block .confirm', function( e ){
        e.preventDefault();
        addNewPockemonConfirm();
    });

    $('.map').on('click', '.hide-content .button-block .confirm', function( e ){
        e.preventDefault();
        somePockemonConfirm();
    });

    $('.map').on('click', '.hide-content .button-block .confirm', function( e ){
        e.preventDefault();
        somePockemonNotConfirm();
    });

});
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
            debugger;
            if ( data.success ) {
                console.log('reload');
                location.reload();
            } else if( data.message ){
                openFancySucc(data.message); //fancy_message
            }else {
               thisForm.closest('div').find('.error-row').css('display','block').find('p').html((typeof data.error[0] != 'undefined'?data.error[0]:'Ошибка, перезагрузите страницу и попробуйте еще раз'));
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
        url : '/pointsReject/'+ thisIDiSend ,
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

function openFancy(massage) {
    
    $('#error-pop').find('.text').html( massage );

    $.fancybox.open('#error-pop',{
        padding:0,
        fitToView:false,
        wrapCSS: 'fancybox-form',
        autoSize:true,
        afterClose: function(){
            clearTimeout(timer);
        }
    });

    var timer = null;

    timer = setTimeout(function(){
        $.fancybox.close('#error-pop');
    },10000);
}

function openFancySucc(massage, locationPlace) {
    
    $('#succ-pop').find('.text').html( massage );

    $.fancybox.open('#succ-pop',{
        padding:0,
        fitToView:false,
        wrapCSS: 'fancybox-form',
        autoSize:true,
        afterClose: function(){
            clearTimeout(timer);
        }
    });

    var timer = null;

    timer = setTimeout(function(){
        $.fancybox.close('#error-pop');
        location.replace( locationPlace );
    },3000);
}

$(document).ready(function(){

   /* login */

        validate('.find-pockemon .form-log form', {submitFunction:validationReg} );
        validate('.find-pockemon .form-reg form', {submitFunction:validationReg} );
        validate('#call-popup .form-log form', {submitFunction:validationReg} );
        validate('#call-popup2 .form-reg form', {submitFunction:validationReg} );
        validate('#call-me-baby form', {submitFunction:validationReg} );

    /* login */

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

    $('.map').on('click', '.hide-content .button-block .not-confirm', function( e ){
        e.preventDefault();
        somePockemonNotConfirm();
    });

    $('.fancybox-form-open-log').click(function(){

       $('#call-popup').fadeIn(500, function(){
           $('#call-popup').addClass('open');

       });
       $('#call-popup .pockeball ').addClass('open-login');

       $('#call-popup .pockeball .mid .form-reg').css('display', 'none');
       $('#call-popup .pockeball .mid .form-log').css('display', 'block');

       if ($(window).height() < 940 ||  $(window).width() < 1080 ){
            $('#call-popup .converter').jScrollPane({
                showArrows: false,
                animateScroll: true
            });
       }

   });

   $('.fancybox-form-open-reg').click(function(){

       $('#call-popup2').fadeIn(500, function(){
           $('#call-popup2').addClass('open');

       });
       $('#call-popup2 .pockeball ').addClass('open-reg');

       $('#call-popup2 .pockeball .mid .form-reg').css('display', 'block');
       $('#call-popup2 .pockeball .mid .form-log').css('display', 'none');

       if ($(window).height() < 940 ||  $(window).width() < 1080 ){
            $('#call-popup2 .converter').jScrollPane({
                showArrows: false,
                animateScroll: true
            });
       }

   });

    $('#call-popup .circle').click(function(){

        $('#call-popup .pockeball ').removeClass('open-login');

        $('#call-popup .pockeball .mid .form-log').slideUp(300);

        $('#call-popup').fadeOut(500, function(){

            $('#call-popup').removeClass('open');

        });

    });

    $('#call-popup2 .circle').click(function(){

        $('#call-popup2 .pockeball ').removeClass('open-reg');

        $('#call-popup2 .pockeball .mid .form-reg').slideUp(300);

        $('#call-popup2').fadeOut(500, function(){

            $('#call-popup2').removeClass('open');

        });
        
    });

});
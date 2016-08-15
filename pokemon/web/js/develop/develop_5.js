function loginFormSubmit(form) {
    var data = form.serialize();
    $.ajax({
        method:'POST',
        url:'/app_dev.php/login_check',
        data:data,
        success:function (data) {
            if(data.success){
                location.reload();
            } else {
                console.log('error');
            }
        }
    });

    return false;
}

function regFormSubmit(form) {
    var data = form.serialize();
    $.ajax({
        method:'POST',
        url:'/app_dev.php/registration',
        data:data,
        success:function (data) {
            console.log(data);
        }
    });

    console.log( 'go' );

    return false;
}

$(document).ready(function(){

});

$(window).load(function(){

});

$(window).resize(function(){

});
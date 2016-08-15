function loginFormSubmit(form) {
    var data = form.serialize();
    $.ajax({
        type:'post',
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


$(document).ready(function(){

});

$(window).load(function(){

});

$(window).resize(function(){

});
var uniqid = $('input[id$=_uniqid]').attr('id').split('_');
var currentType = $('select#'+uniqid[0]+'_type').val();

var fields = {
    image: '<div class="form-group" id="sonata-ba-field-container-'+uniqid[0]+'_value"><label class="control-label required" for="'+uniqid[0]+'_value">Картинка</label><div class=" sonata-ba-field sonata-ba-field-standard-natural  "><input id="'+uniqid[0]+'_file" name="'+uniqid[0]+'[value]" required="required" class=" form-control" type="file"></div></div>',
    string: '<div class="form-group" id="sonata-ba-field-container-'+uniqid[0]+'_value"><label class="control-label required" for="'+uniqid[0]+'_value">Значение</label><div class=" sonata-ba-field sonata-ba-field-standard-natural  "><input id="'+uniqid[0]+'_value" name="'+uniqid[0]+'[value]" required="required" maxlength="255" class=" form-control" type="text"></div></div>',
    text: '<div class="form-group" id="sonata-ba-field-container-'+uniqid[0]+'_value"><label class="control-label required" for="'+uniqid[0]+'_value">Значение</label><div class=" sonata-ba-field sonata-ba-field-standard-natural  "><textarea id="'+uniqid[0]+'_value" name="'+uniqid[0]+'[value]" required="required" class=" form-control"></textarea></div></div>',
    checkbox: '<div class="form-group" id="sonata-ba-field-container-'+uniqid[0]+'_value"><label class="control-label required" for="'+uniqid[0]+'_value">Значение</label><div class=" sonata-ba-field sonata-ba-field-standard-natural  "><input id="'+uniqid[0]+'_value" name="'+uniqid[0]+'[value]" class=" form-control" type="hidden" value="0"/><a class="btn " href="javascript:void(0)"></a></div></div>'
};
if( $('select#'+uniqid[0]+'_type').val() == 'image' ) {
    $('#sonata-ba-field-container-' + uniqid[0] + '_value').replaceWith(fields['image']).show();
    $('.sonata-ba-form form').attr('enctype', 'multipart/form-data');
} else if( $('select#'+uniqid[0]+'_type').val() == 'checkbox' ){
    var val = parseInt($('input#'+uniqid[0]+'_value').val());
     $('#sonata-ba-field-container-' + uniqid[0] + '_value').replaceWith(fields['checkbox']).show();

     if(val>0){
         $('input#'+uniqid[0]+'_value').val('1');
         $('#sonata-ba-field-container-'+uniqid[0]+'_value a').addClass('btn-success');
         $('#sonata-ba-field-container-'+uniqid[0]+'_value a').html('Да');
     } else {
         $('input#'+uniqid[0]+'_value').val('0');
         $('#sonata-ba-field-container-'+uniqid[0]+'_value a').addClass('btn-danger');
         $('#sonata-ba-field-container-'+uniqid[0]+'_value a').html('Нет');
     }
}
console.log(parseInt($('input#'+uniqid[0]+'_value').val()));
$('input[id$=_uniqid]').attr('id', uniqid[1]);
$('input#uniqid').val(uniqid[0]);


$('select#'+$('input#uniqid').val()+'_type').on('change', function() {
    $('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value').replaceWith(fields[$(this).val()]).show();
    currentType = $(this).val();
    if( currentType == 'image')
    {
        $('.sonata-ba-form form').attr('enctype', 'multipart/form-data');
    } else if( $('select#'+uniqid[0]+'_type').val() == 'checkbox' ){
        $('#sonata-ba-field-container-' + uniqid[0] + '_value').replaceWith(fields['checkbox']).show();

        if(parseInt($('input#'+uniqid[0]+'_value').val())>0){
            $('input#'+uniqid[0]+'_value').val('1');
            $('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value a').addClass('btn-success');
            $('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value a').html('Да');
        } else {
            $('input#'+uniqid[0]+'_value').val('0');
            $('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value a').addClass('btn-danger');
            $('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value a').html('Нет');
        }
    }
});
$(document).on('click','#sonata-ba-field-container-'+$('input#uniqid').val()+'_value a',function(){
   if(parseInt($('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value input').val())>0){
       $('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value input').val(0);
       $(this).removeClass('btn-success');
       $(this).addClass('btn-danger');
       $(this).html('Нет');
   } else {
       $('#sonata-ba-field-container-'+$('input#uniqid').val()+'_value input').val(1);
       $(this).addClass('btn-success');
       $(this).removeClass('btn-danger');
       $(this).html('Да');
   }
});


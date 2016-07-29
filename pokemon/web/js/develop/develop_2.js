// <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

    var googleText = 'pockemon here';
    var cordX = 10;
    var cordY = 10;
    var map ;

    var ser = {
        "points" : [
                        {
                            "point" : [10, 10],
                            "text" : "123"
                        },
                        {
                            "point" : [20, 20],
                            "text" : "123"
                        },
                        {
                            "point" : [20, 10],
                            "text" : "123"
                        },
                        {
                            "point" : [10, 20],
                            "text" : "123"
                        },
                        {
                            "point" : [12, 15],
                            "text" : "123"
                        }

                    ]
        }

var currentPosition;
var stack;
var stashNames = [];

function onLoadStartData(){

    currentPosition = [ 55.807398 , 37.432006 ] ;
    /* визов геолокації */

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
        flagShtock= true ; 
    } else {
        console.log("Geolocation is not supported by this browser.");
    }

    /* обробка помилок */

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                alert("Please open in mozilla");
                superAjax();
                break;
            case error.POSITION_UNAVAILABLE:
                 console.log("Location information is unavailable.");
                break;
            case error.TIMEOUT:
                 console.log( "The request to get user location timed out.");
                break;
            case error.UNKNOWN_ERROR:
                 console.log("An unknown error occurred.")
                break;
        }
    }

    /* коли все добре */
    function showPosition(position) {
        currentPosition = [ position.coords.latitude , position.coords.longitude ] ;
        superAjax();
    };

}

function superAjax(){

   // console.log(currentPosition);

    $.ajax({
        url : '/location/'+currentPosition[0]+'/'+ currentPosition[1],
        // data: formSur,
        method:'POST',
        success : function(data){
            
         //   console.log(data);

            stack = data ;

            /* map init */
                if ( $('body').find('#map').length == 1 ){
                    google.maps.event.addDomListener(window, "load", googleMap('map'));
                }
            /* map init */

            /* content init */

                addContentToHell();

            /* content init */

        }
    });
}

function addContentToHell(){

    var htmlStack='';

    for ( var i = 0;  i < stack.length; i++ ){
        htmlStack += "<div class='item' data-names='" + stack[i].name + "'>" +
                "<div class='num'> #" + stack[i].pokemon +"</div>" +
                "<div class='look'>" +
                "<img src='"+ stack[i].image +"' "+
                "alt='" + stack[i].name + "' title='" + stack[i].name +"'>" +
            "</div>" +
            "<div class='name'>" + stack[i].name + "</div>" +
            "<div class='disance'> " + stack[i].distance + " </div>" +
            "<div class='button'>"+
                "<a href='" + stack[i].id + "'> <span>ПОКАЗАТЬ</span> </a>" +
            "</div>" +
        "</div>";
    }
        
    $('.sorting-wrap .items-wrap').html(htmlStack);

        /* tyt sort add */
       // forSortAdd();

    /* end compose */ 
}
/*
    function forSortAdd(){

        for (var i = 0; i < stack.length; i++) {
            stashNames[i] = stack[i].name;
        };

    }
    
*/  

function googleMap(mapWrap) {
    function initialize() {

        var myLatlng = new google.maps.LatLng(currentPosition[0] , currentPosition[1]);
        var myOptions = {
            zoom: 17,
            center: myLatlng,
            disableDefaultUI: false, //без управляющих елементов
            mapTypeId: google.maps.MapTypeId.ROADMAP, // SATELLITE - снимки со спутника,
            scaleControl: false,
            scrollwheel: false,
            navigationControl: false,
            mapTypeControl: false,
            styles: [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#9ea7b2"}]}],
            zoomControlOptions: {
                position: google.maps.ControlPosition.LEFT_BOTTOM // позиция слева внизу для упр елементов
            }
        }
        map = new google.maps.Map(document.getElementById(mapWrap), myOptions);
        /*
        var contentString = '<div class="marker-test">' + googleText + '</div>';
        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });
        */


        /*маркер на svg*/
        //  var SQUARE_PIN = 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z';
        //больше - http://map-icons.com/
        /*/маркер на svg*/

           // иконка картинкой

        for ( var i = 0;  i < stack.length; i++ ){

            var myLatlng2 = new google.maps.LatLng( stack[i].locationX, stack[i].locationY );
            var image = stack[i].image ;

            var marker = new google.maps.Marker({
                position: myLatlng2,
                map: map,
                animation: google.maps.Animation.DROP, // анимация при загрузке карты
                icon: image //  иконка картинкой
                /* icon: {                               //маркер на svg
                    path: SQUARE_PIN,
                    fillColor: '#fff',
                    fillOpacity: 0.7,
                    strokeColor: '#FF3232',
                    strokeWeight: 5
                },
                */
            });
        }
        

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
        /*
                google.maps.event.addListener(marker, 'click', function() {
                    infowindow.open(map,marker);
                });
        */
    }
    initialize();
}



$(document).ready(function(){

    /* tabs */
        $('.displaying>li').on('click', function(){
            $(this).index();
            $('.displaying>li').removeClass('active');
            $(this).addClass('active');

            switch ( $(this).index() ) {
                case 0:
                    $('.sorting-wrap .bot-row').removeClass('card').addClass('list');
                    break;
            
                case 1:
                    $('.sorting-wrap .bot-row').removeClass('list').addClass('card');
                    break;
            }
        });
    /* tabs */

    $('.sorting>li').on('click', function(){

        var steak;

        var i = 0;

        $('.sorting-wrap>.bot-row>.items-wrap').find('.item').each(function(){

            stashNames[i] = $(this).find('.name').html();
            i++;

        });
        

        if ( $(this).index() == 0 ){

            steak = stashNames.sort();

        } else {

            steak = (stashNames.sort()).reverse();

        }

        setTimeout(function(){

            $('.sorting-wrap>.bot-row>.items-wrap').find('.item').each(function(){

                $(this).css("order", steak.indexOf( $(this).attr('data-names') ) ); 
            
            });

        }, 500);

        
        
    });
});

$(window).load(function(){

    onLoadStartData();

});
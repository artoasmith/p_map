// <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

    var googleText = 'pockemon here';

var currentPosition;
var stack = [];
var stashNames = [];
var markers = [];

function onLoadStartData(){

    currentPosition = [ 55.807398 , 37.432006 ] ;


    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
        flagShtock= true ; 
    } else {
        console.log("Geolocation is not supported by this browser.");

        alert( " select your position " );

    }

    /* обробка помилок */

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                alert( " select your position " );
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

    console.log( currentPosition );

    $.ajax({
        url : '/app_dev.php/location/'+currentPosition[0]+'/'+ currentPosition[1],
        // data: formSur,
        method:'POST',
        success : function(data){
            
           console.log(data);

            stack = data ;

            /* map init */
                if ( ($('body').find('#map').length == 1) && !$('#map').hasClass('map-is-init') ){
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
            "<div class='disance'> " + stack[i].distance + " км </div>" +
            "<div class='button'>"+
                "<a href='" + stack[i].id + "' data-locationx='" + stack[i].locationX + 
                "' data-locationy='" + stack[i].locationY + "' > <span>ПОКАЗАТЬ</span> </a>" +
            "</div>" +
        "</div>";
    }
        
    $('.sorting-wrap .items-wrap').html(htmlStack);

        /* tyt sort add */
       // forSortAdd();

    /* end compose */ 
}
  

function googleMap(mapWrap) {
    function initialize() {

        $('#map').addClass('map-is-init');
        var myLatlng = new google.maps.LatLng(currentPosition[0] , currentPosition[1]);
        var myOptions = {
            zoom: 3,
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


        for ( var i = 0;  i < stack.length; i++ ){

            var infowindow = new google.maps.InfoWindow({
                content: '<h1>' + stack[i].name + '</h1>'
            });

            var myLatlng2 = new google.maps.LatLng( stack[i].locationX, stack[i].locationY );
            var image = stack[i].image ;

            var marker = new google.maps.Marker({
                position: myLatlng2,
                map: map,
                title: stack[i].name,
                animation: google.maps.Animation.DROP, // анимация при загрузке карты
                icon: image //  иконка картинкой
            });

            markers.push(marker);
            makeInfoWindowEvent(map, infowindow, marker);
        }

        function makeInfoWindowEvent(map, infowindow, marker) {

            google.maps.event.addListener(marker, 'click', function() {
                
                infowindow.open(map, marker);
                map.panTo( marker.getPosition() );

                setTimeout(function () { infowindow.close(); }, 5000);
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
        

        function addMarker(location) {
            var markerA = new google.maps.Marker({
                position: location,
                map: map
            });
            map.panTo( markerA.getPosition() );

           // map.setZoom( Math.round( (map.zoom  + 1/map.zoom )* 1.1)  );
            markers.push(markerA);
        }

        function setMapOnAll(map) {
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(map);
            }
        }

          map.addListener('click', function(e) {

            addMarker(e.latLng);

           console.log( e.ca.x , e.ca.y  );

           currentPosition = [ e.ca.x , e.ca.y ] ;

           superAjax();
           console.log( currentPosition );

        });
        
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

    /* sorting */
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
    /* sorting */

    /*  */
    $('.sorting-wrap').on('click', '.item>.button>a', function(e){
        e.preventDefault();

        var target = $('.map').offset().top;

        $(scroller).stop().animate({scrollTop:target},800);

        $(this).attr('data-locationy')

        map.panTo( new google.maps.LatLng( $(this).attr('data-locationx') , $(this).attr('data-locationy') ) );

        return false;

    })
});

$(window).load(function(){

    onLoadStartData();

});
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
    
    

function googleMap(mapWrap) {
    function initialize() {

        var myLatlng = new google.maps.LatLng(cordX, cordY);
        var myOptions = {
            zoom: 5,
            center: myLatlng,
            disableDefaultUI: false, //без управляющих елементов
            mapTypeId: google.maps.MapTypeId.ROADMAP, // SATELLITE - снимки со спутника,
            scaleControl: false,
            scrollwheel: false,
            styles: [{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}],
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

        var image = 'images/pock.png';   // иконка картинкой

        for ( var i = 0;  i < ser.points.length; i++ ){

            var myLatlng2 = new google.maps.LatLng(ser.points[i].point[0], ser.points[i].point[1]);

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

if ( $('body').find('#map').length == 1 ){
    google.maps.event.addDomListener(window, "load", googleMap('map'));
}

$(document).ready(function(){
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


    })
});

$(window).load(function(){

});
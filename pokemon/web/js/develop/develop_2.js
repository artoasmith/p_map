// <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

/* я бачу це всюди */
    var googleText = 'pockemon here';
    var currentPosition;
    var stack = [];
    var stashNames = [];
    var markers = [];
    var markersNew = [] ;
    var map ;
    var directionsDisplay;
    var directionsService = new google.maps.DirectionsService();

/* я око саурона */

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
                 superAjax( 1 );
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
        superAjax( 1 );
    };

}


function makeRoadToPockemon(){
    $('.road-is-so-far').click(function () {
    
    function calcRoute( startPath , endPath ) {
        // var start = document.getElementById("start").value;
        // var end = document.getElementById("end").value;
        var request = {
            origin: startPath ,
            destination: endPath,
            travelMode: google.maps.TravelMode.WALKING
        };
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
            }
        });

        markers = [];
            console.log(stack);
            for (var i = 0; i < stack.length; i++) {


                var infowindow = new google.maps.InfoWindow({
                    content: '<h1>' + stack[i].name + '</h1>'
                });

                var myLatlng2 = new google.maps.LatLng(stack[i].locationY, stack[i].locationX);
                var image = stack[i].image;

                var marker = new google.maps.Marker({
                    position: myLatlng2,
                    map: map,
                    title: stack[i].name,
                    dataSum: stack[i].id,
                    // animation: google.maps.Animation.DROP, // анимация при загрузке карты
                    icon: image //  иконка картинкой
                });

                markers.push(marker);
                makeInfoWindowEvent(map, infowindow, marker);
            }
        }

        if ( $('.hide-content').hasClass('activate') ) {

            var wereIGO =  $('.hide-content').attr('data-pokemon-id');
            var points = [];

            for (var i = 0; i < stack.length; i++ ){

                if ( stack[i].id == wereIGO ){

                    point = [ stack[i].locationX , stack[i].locationY ]
                    
                }

            }

            var startPath = new google.maps.LatLng( currentPosition[0], currentPosition[1] ) ;
            var endPath = new google.maps.LatLng( point[1] , point[0] );

            calcRoute( startPath , endPath );
            
        }
    })
}

function superAjax( page ) {

    console.log('start ');

    $.ajax({
        url: '/app_dev.php/location/' + currentPosition[1] + '/' + currentPosition[0] + '?page='+ page,
        // data: formSur,
        method: 'POST',
        success: function (data) {
            console.log(' end ');

            var st = data.points.length;
            var dataSt = data.points;

            if (stack.length == 0) {
               // stack = dataSt;

                for (var i = 0; i < dataSt.length; i++) {

                    stack[stack.length] = dataSt[i];

                    for (var j = 0; j < pokemon.length; j++) {

                        if (pokemon[j].id == dataSt[i].pokemon) {
                            stack[stack.length - 1].image = pokemon[j].image;
                            stack[stack.length - 1].name = pokemon[j].name;
                        }

                    }

                }

                stack = _.uniqBy(stack, "id" );

            }

            /* map init */
            if (($('body').find('#map').length == 1) && !$('#map').hasClass('map-is-init')) {

                google.maps.event.addDomListener(window, "load", googleMap('map'));

            } else {

                for (var i = 0; i < dataSt.length; i++) {

                    stack[stack.length] = dataSt[i];

                    for (var j = 0; j < pokemon.length; j++) {


                        if (pokemon[j].id == dataSt[i].pokemon) {
                            stack[stack.length - 1].image = pokemon[j].image;
                            stack[stack.length - 1].name = pokemon[j].name;
                        }

                    }

                    var infowindow = new google.maps.InfoWindow({
                        content: '<h1>' + dataSt[i].name + '</h1>'
                    });

                    var myLatlng2 = new google.maps.LatLng(dataSt[i].locationY, dataSt[i].locationX);
                    var image = dataSt[i].image;

                    var marker = new google.maps.Marker({
                        position: myLatlng2,
                        map: map,
                        title: dataSt[i].name,
                        dataSum: dataSt[i].id,
                      //  animation: google.maps.Animation.DROP, // анимация при загрузке карты
                        icon: image //  иконка картинкой
                    });

                    markers.push(marker);
                    makeInfoWindowEvent(map, infowindow, marker);
                }

                stack = _.uniqBy(stack, "id" );

            }

            /* map init */

            /* content init */

          //  addContentToHell();

            /* content init */

            if( (data.meta.totalCount - data.meta.page) >0 ){

                superAjax( data.meta.page + 1 );

            } else {
                console.log(' end upload ');
            }

            console.log('done');

        }
    });
}

function addContentToHell(){

    // pokemon
    var htmlStack='';
    var gg = 0;
    for ( var i = 0;  i < pokemon.length; i++ ){
        gg++;

        if (gg == 1){
            htmlStack +=  '<div class="sliding-items">';
        }
        


        htmlStack += '<div class="item">'+
                        '<div class="top">'+  '2km' + '</div>'+
                        '<div class="circle">' + 
                            '<div class="con">' +
                                '<img src="' + pokemon[i].image + '" alt="">'+
                            '</div>' +
                        '</div>' +
                        '<div class="named">' + pokemon[i].name + '</div>'+
                    '</div>' ;

        if (gg == 3){
            htmlStack += '</div>';
            gg = 0;
        }

    }

    
        console.log(htmlStack);
        
    $('.slider-row .content').html( htmlStack );

    /* slider */

        $('.slider-row>.content').slick({
            infinite: false,
            dots: true,
            slidesToShow: 5,
            slidesToScroll: 3, 
            swipeToSlide: true
        });

    /* slider */

}

function showMeThisPockemon( whatPockemonIWillShow ){

    var curent =  _.find(stack, { 'id': whatPockemonIWillShow }); ;

    console.log( curent );

    if( curent.confirmed ){
        $('.map').find('.hide-content').addClass('confirm-pokemon');
    }

    $('.hide-content').addClass('activate').attr('data-pokemon-id', curent.id );
    
    $('.map').find('.topper>.con>img').attr( 'src', curent.image );
    $('.map').find('.after-all>.top-name').html( curent.name );
    $('.map').find('.after-all>.distance>span').html( curent.distance );

}
  

function makeInfoWindowEvent(map, infowindow, marker) {

    google.maps.event.addListener(marker, 'click', function() {
        
        infowindow.open(map, marker);

        $('.hide-content').removeClass('not-login');

        map.panTo( marker.getPosition() );

        showMeThisPockemon( marker.dataSum ) ;

        setTimeout(function () { infowindow.close(); }, 3000);
    });
}

function googleMap(mapWrap) {
    function initialize() {

        $('#map').addClass('map-is-init');
        var myLatlng = new google.maps.LatLng(currentPosition[0] , currentPosition[1]);
        directionsDisplay = new google.maps.DirectionsRenderer();
        var myOptions = {
            zoom: 15,
            center: myLatlng,
            disableDefaultUI: false, //без управляющих елементов
            mapTypeId: google.maps.MapTypeId.ROADMAP, // SATELLITE - снимки со спутника,
            scaleControl: false,
            scrollwheel: true,
            navigationControl: false,
            mapTypeControl: false,
            styles: [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#9ea7b2"}]}],
            zoomControlOptions: {
                position: google.maps.ControlPosition.LEFT_BOTTOM // позиция слева внизу для упр елементов
            }
        }
        map = new google.maps.Map(document.getElementById(mapWrap), myOptions);


        /* тут точка покемейстра */ 

            var myLatlng2 = new google.maps.LatLng(currentPosition[0] , currentPosition[1]);
            var image = 'images/pock.png' ;

            var marker = new google.maps.Marker({
                position: myLatlng2,
                map: map,
               // animation: google.maps.Animation.BOUNCE, // анимация при загрузке карты
                icon: image //  иконка картинкой
            });

            markers.push(marker);
            makeInfoWindowEvent(map, infowindow, marker);

        /* точка покемейстра */

        for ( var i = 0;  i < stack.length; i++ ){

            var infowindow = new google.maps.InfoWindow({
                content: '<h1>' + stack[i].name + '</h1>'
            });

            var myLatlng2 = new google.maps.LatLng( stack[i].locationY, stack[i].locationX );
            var image = stack[i].image ;

            var marker = new google.maps.Marker({
                position: myLatlng2,
                map: map,
                title: stack[i].name,
                dataSum: stack[i].id,
               // animation: google.maps.Animation.DROP, // анимация при загрузке карты
                icon: image //  иконка картинкой
            });

            markers.push(marker);
            makeInfoWindowEvent(map, infowindow, marker);
        }

        makeInfoWindowEvent(map, infowindow, marker) ;
            
        marker.addListener('click', toggleBounce);
            
        function toggleBounce() {

            if (marker.getAnimation() !== null) {
                marker.setAnimation(null);
            } else {
                marker.setAnimation(google.maps.Animation.BOUNCE);
            }
            
        }
        

        function addMarker(location) {

            var image = 'images/pock.png' ;
            var markerA = new google.maps.Marker({
                position: location,
                map: map,
                icon: image
            });
            map.panTo( markerA.getPosition() );

            markers.push(markerA);
        }



        map.addListener('click', function(e) {

            addMarker(e.latLng);
            currentPosition = [  e.latLng.lat() , e.latLng.lng() ] ;
            superAjax();
        });

     directionsDisplay.setMap(map);

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

    /* some */
        $('.sorting-wrap').on('click', '.item>.button>a', function(e){
            e.preventDefault();

            var target = $('.map').offset().top;

            $(scroller).stop().animate({scrollTop:target},800);

            $(this).attr('data-locationy');

            map.panTo( new google.maps.LatLng( $(this).attr('data-locationx') , $(this).attr('data-locationy') ) );

            return false;

        })

    /* some */

    /* road */

        makeRoadToPockemon();

    /* road */

});

$(window).load(function(){

    addContentToHell();

    if( $('body').find('.map-page').length == 1 ){
        onLoadStartData();
    }
    

});
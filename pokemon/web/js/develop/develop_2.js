// <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>

/* я бачу це всюди */
    var googleText = 'pockemon here';
    var currentPosition;
    var stack = [];
    var stashNames = [];
    var markers = [];
    var markerAdd = [];
    var markersNew = [] ;
    var map,  map2 , map3;
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

function searchOnMAin(){
    var le = 0;
    var slideTo = 0 ;

    $('.contein-search form input').on('keyup', function(){

        var whatISearch = ($(this).val()).toLowerCase() ;
        
        if ( (whatISearch.length > 0 ) && ( le != whatISearch.length ) ){
            
            $('.slider-row').find('.item').removeClass('it-can-be');
            $('.slider-row').find('.item').each(function(){

                if ( whatISearch == ($(this).find('.named').html().substring( whatISearch.length, 0)).toLowerCase() ){

                    $(this).addClass('it-can-be');
                    slideTo = $(this).closest('.sliding-items').index() ;
                    return ;
                } 

            });
        }

        le = whatISearch.length;

        //$('.contein-search').find('.slider-row>.content').slickGoTo(1);
        $('#iwanttoslide').slick('slickGoTo', slideTo) ;
        //console.log(slideTo);

    });

    $('.contein-search form').on('submit', function(){
        return false ;
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
        


        htmlStack += '<div class="item" data-pockemon="'+ pokemon[i].id +'">'+
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
        
    $('.slider-row .content').html( htmlStack );

    /* slider */

        setTimeout( function(){

            $('#iwanttoslide').slick({
                infinite: false,
                dots: true,
                slidesToShow: 5,
                slidesToScroll: 1, 
                swipeToSlide: true,
                responsive: [
                    {
                    breakpoint: 1100,
                    settings: {
                        slidesToShow: 4,
                        dots: false,
                        slidesToScroll: 1
                        }
                    },
                    {
                    breakpoint: 820,
                    settings: {
                        dots: false,
                        slidesToShow: 3,
                        slidesToScroll: 1
                        }
                    }
                ]
            });

        }, 1000);        

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

function makeInfoWindowEventForThree(map, infowindow, marker) {

    google.maps.event.addListener(marker, 'click', function() {
        
        infowindow.open(map, marker);

        map.panTo( marker.getPosition() );

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
            scaleControl: true,
            scrollwheel: true,
            navigationControl: true,
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
           // var image = stack[i].image ;

            var image = {
                url: stack[i].image,
              //  scaledSize: new google.maps.Size(120, 120)
            };

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
            superAjax(1);
        });

     directionsDisplay.setMap(map);

     google.maps.event.addListener(map, 'zoom_changed', function(event) {

        console.log('changed'+ map.getZoom() );

     });

    }
    initialize();
}

function googleMap2(mapWrap) {
    function initialize() {
        var myLatlng = new google.maps.LatLng( 36 , 36 );
        var myOptions = {
            zoom: 8,
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
        map2 = new google.maps.Map(document.getElementById(mapWrap), myOptions);  
        var geocoder = new google.maps.Geocoder; 
        var infowindow = new google.maps.InfoWindow;
            /*
                    function addMarkerNew(location) {

                        var image = 'images/pock.png' ;
                        var markerA = new google.maps.Marker({
                            position: location,
                            map: map2,
                            icon: image
                        });
                        map2.panTo( markerA.getPosition() );

                        markers.push(markerA);
                    }
            */
        function clearOverlays() {
            if (markerAdd) {
                for (i in markerAdd) {
                markerAdd[i].setMap(null);
                }
            }
        }
        
        function geocodeLatLng(geocoder, map,  positionMarker , infowindow) {
           
            geocoder.geocode({'location': positionMarker }, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                if (results[1]) {

                     var image = 'images/pock.png' ;
                        markerA = new google.maps.Marker({
                            position: positionMarker,
                            map: map2,
                            adressMarker: results[1].formatted_address,
                            icon: image
                        });
                        map2.panTo( markerA.getPosition() );

                        markerAdd.push(markerA);


                        infowindow.setContent(results[1].formatted_address);
                        infowindow.open(map2, markerA);

                } else {
                    window.alert('No results found');
                }
                } else {

                    var image = 'images/pock.png' ;
                        markerA = new google.maps.Marker({
                            position: positionMarker,
                            map: map2,
                            adressMarker: '' + positionMarker  ,
                            icon: image
                        });
                        map2.panTo( markerA.getPosition() );

                        markerAdd.push(markerA);

                        infowindow.setContent( '' + positionMarker  );
                        infowindow.open(map2, markerA);
                    //window.alert('Geocoder failed due to: ' + status);

                }
            });
        }



        map2.addListener('click', function(e) {
           // addMarkerNew(e.latLng);

          markerAdd = [];

            clearOverlays();

            geocodeLatLng(geocoder, map2, e.latLng, infowindow);

            $('.add-new-pockemon').removeClass('start').removeClass('stage2').removeClass('stage3').addClass('stage1');

             $('.add-new-pockemon').find('.placeholder-drop').html("<p> Выбрать покемона </p><img src='images/search.png' alt=''>");

            setTimeout(function(){

                $('.add-new-pockemon').find('.row-place .conteiner .place').html( markerAdd[0].adressMarker );

            }, 500);

            

            

           // currentPosition = [  e.latLng.lat() , e.latLng.lng() ] ;
        });

     // directionsDisplay.setMap(map);

    }
    initialize();
}

function googleMap3(mapWrap) {
    function initialize() {
        var myLatlng = new google.maps.LatLng(user_points[0].locationY , user_points[0].locationX);
        directionsDisplay = new google.maps.DirectionsRenderer();
        var myOptions = {
            zoom: 10,
            center: myLatlng,
            disableDefaultUI: false, //без управляющих елементов
            mapTypeId: google.maps.MapTypeId.ROADMAP, // SATELLITE - снимки со спутника,
            scaleControl: true,
            scrollwheel: true,
            navigationControl: true,
            mapTypeControl: true,
            styles: [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#9ea7b2"}]}],
            zoomControlOptions: {
                position: google.maps.ControlPosition.LEFT_BOTTOM // позиция слева внизу для упр елементов
            }
        }
        map3 = new google.maps.Map(document.getElementById(mapWrap), myOptions);

        var geocoder = new google.maps.Geocoder;
        var infowindow = new google.maps.InfoWindow;

        for ( var i = 0;  i < user_points.length; i++ ){

            var curent =  _.find(pokemon, { 'id': user_points[i].pokemon }); ;         

            var infowindow = new google.maps.InfoWindow({
                content: '<h1>' + curent.name + '</h1>'
            });

            var myLatlng2 = new google.maps.LatLng( user_points[i].locationY, user_points[i].locationX );
            var image = curent.image ;

            var marker = new google.maps.Marker({
                position: myLatlng2,
                map: map3,
                title: curent.name,
                dataSum: user_points[i].id,
               // animation: google.maps.Animation.DROP, // анимация при загрузке карты
               icon: image //  иконка картинкой
            });

            markers.push(marker);
            makeInfoWindowEventForThree(map3, infowindow, marker);
        }

        //makeInfoWindowEvent(map3, infowindow, marker) ;
            
        marker.addListener('click', toggleBounce3);
            
        function toggleBounce3() {

            if (marker.getAnimation() !== null) {
                marker.setAnimation(null);
            } else {
                marker.setAnimation(google.maps.Animation.BOUNCE);
            }
            
        }

     directionsDisplay.setMap(map3);

    }
    initialize();
}


function myAddedPockemon() {
    
    console.log(user_points);

    google.maps.event.addDomListener(window, "load", googleMap3('my-pockemon'));

    var listOfMyPock = '<ul>';

    for (var i = 0; i < user_points.length; i++) {

        var curent =  _.find(pokemon, { 'id': user_points[i].pokemon }); 

       // user_points[i].adress = '' + resAdress;


        listOfMyPock += "<li data-id="+ user_points[i].id +">"+
                            "<div class='title-row-table'>"+
                                "<div class='name'>" + curent.name + "</div>"+
                                "<div class='con'>"+
                                    "<img src='" + curent.image + "' alt=''>"+
                                "</div>"+
                            "</div>"+
                            "<div class='hovered-content'>"+
                                "<div class='row-adress'>" +
                                    "<span>"+ "в разроботке" +  "</span>" +
                                "</div>"+
                                "<div class='results'>"+
                                    "<div class='good'>"+
                                        "<div class='tili'>Одобрение</div>"+
                                        "<div class='vali'>"+ user_points[i].confirm +"</div>"+
                                    "</div>"+
                                    "<div class='bad'>"+
                                        "<div class='tili'>Отрицание</div>"+
                                        "<div class='vali'> " + user_points[i].enabled + "</div>" +
                                    "</div>"+
                                "</div>"+
                                "<div class='row-pay-me'>"+
                                    "<div class='hred'>Вознаграждение</div>"+
                                    "<div class='price'>" + "в разроботке" +  " $</div>"+
                                "</div>"+
                            "</div>"+
                        "</li>";
        
    }

    listOfMyPock += '</ul>';

    $('.my-added-pockemon').find('.left-list').html( listOfMyPock );

}

function addNewPockemonToMap() {

    google.maps.event.addDomListener(window, "load", googleMap2('add-pockemon'));
    
    $('.add-new-pockemon').addClass('start');

    /* 

    $('.add-new-pockemon').removeClass('start').addClass('stage1');

    $('.add-new-pockemon').removeClass('stage1').addClass('stage2');

    $('.add-new-pockemon').removeClass('stage2').addClass('stage3');

    */
    
}

function scrollToChoosenPock() {
    
    $('.contein-search').on('click', '.slider-row .item', function(){

        var whatPockemonIChoose  = parseInt( $(this).attr('data-pockemon') );

        console.log( whatPockemonIChoose );

        var curent =  _.find(stack, { 'pokemon': whatPockemonIChoose }); 

        if (curent != undefined ){

            var target = $('#map').offset().top-100;
            $(scroller).animate({scrollTop:target},500);

            if( curent.confirmed ){
                $('.map').find('.hide-content').addClass('confirm-pokemon');
            }

            $('.hide-content').addClass('activate').attr('data-pokemon-id', curent.id );
            
            $('.map').find('.topper>.con>img').attr( 'src', curent.image );
            $('.map').find('.after-all>.top-name').html( curent.name );
            $('.map').find('.after-all>.distance>span').html( curent.distance );

            map.setZoom(18);
            map.panTo( new google.maps.LatLng( curent.locationY , curent.locationX ) );
           

        } else {

            alert('no such pockemon near than you');

        }

        console.log( curent );

    });

}

$(document).ready(function(){
    

    if (  $('body').find('.contein-search').length == 1 ){

        searchOnMAin();     
        scrollToChoosenPock()

    }

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
    /*
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
   

    if( $('body').find('#map').length == 1){
        onLoadStartData() ;
    };



    if( $('.slider-row').length != 0 ){
        addContentToHell();
    };
    
/*
    if( $('body').find('.map-page').length == 1 ){
        onLoadStartData();
    };
*/
    
    if( $('body').find('.my-added-pockemon').length == 1 ){

        myAddedPockemon();
    
    };

    if( $('body').find('.add-new-pockemon').length == 1 ){
        addNewPockemonToMap();
    };
    
    

});
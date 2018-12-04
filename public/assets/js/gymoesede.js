/*
    
    Gymnasium Oesede | 50 Jahre
    unter Nutzung von jQuery (JS-Framework)

*/
$(document).ready(function() {
   
    $('.hamburger-button').click(function() {
        $('.sidemenu').slideDown();
    });
    
    $('.closebutton').click(function() {
        $('.sidemenu').slideUp();
    })

    $('*[data-href]').click(function() {
        window.location.href = $(this).data('href');
    });
});
$(document).ready(function() {

    $('#resetPassword').click(function() {
        console.log("Das Passwort wird nun zurückgesetzt.");
        $.ajax($(this).data('id'), {
           success: function(data) {
               console.log("output: " + data);
           }
        })
    });

    $('.ui.toggle.checkbox').checkbox();

});
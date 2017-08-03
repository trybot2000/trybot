$(document).ready(function() {
$('.div-input input').focus();
    $("#frmName").on('submit', function(event) {
        event.preventDefault();
        var payload = {
            name: $('#frmName input').val()
        };
        if (payload) {
            $.post('/whom', payload, function(data, textStatus, xhr) {
                if (data == true) {
                  window.location.href = '/';
                }
            });
        }
    });
});
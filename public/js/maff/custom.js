$(document).ajaxStart(function(){
    $('.modal').show();
 }).ajaxStop(function(){
    $('.modal').hide();
 });
 
function get_projeto() {
    var unorc = $('#unorc').val();
    if (unorc) {
        var url = 'inc/get_campos.php?unorc=' + unorc;
        $.get(url, function(dataReturn) {
            $('#load_projetos').html(dataReturn);
        });
    }
}

function get_eldespesa() {
    var unorc = $('#projeto').val();
    if (unorc) {
        var url = 'inc/get_campos.php?projeto=' + unorc;
        $.get(url, function(dataReturn) {
            $('#load_eldespesa').html(dataReturn);
        });
    }
}

function get_fonte() {
    var unorc = $('#eldespesa').val();
    if (unorc) {
        var url = 'inc/get_campos.php?eldespesa=' + unorc;
        $.get(url, function(dataReturn) {
            $('#load_fontes').html(dataReturn);
        });
    }
}

$('#myTab a').click(function(e) {
    e.preventDefault();
    $(this).tab('show');
});
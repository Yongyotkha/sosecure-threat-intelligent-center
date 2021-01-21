$(document).ready(function () {

});


function get_word_leak_compromise(val, type)
{
    var html = '';
    if(type == 'data_leak') {
        if(val == 'social') {
            html = 'PUBLIC';
        } else if(val == 'darkweb_public') {
            html = 'DARK WEB';
        }
        
    } else if(type == 'compromise') {
        if(val == 'compromise') {
            html = 'PUBLIC';
        } else if(val == 'darkweb') {
            html = 'DARK WEB';
        } else if(val == 'webserver') {
            html = 'WEB SERVER';
        }
        
    } else {
        html = '';
    }
    
    return html;
}
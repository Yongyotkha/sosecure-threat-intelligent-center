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
        }  else if(val == 'credential' || val == 'Credential') {
            html = 'CREDENTIAL';
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

function set_cookie_site(value){
    let d = new Date();
    d.setTime(d.getTime() + (1 * 2 * 60 * 60 * 1000));
    let expires = "expires="+d.toUTCString();
    document.cookie = 'insight_select_site_search' + "=" + value + ";" + expires + ";path=/";
}

function get_cookie_site(){
    if (document.cookie.split(';').some(function(item) {
        return item.trim().indexOf('insight_select_site_search=') == 0
    })) {
        return document.cookie.split('; ').find(row => row.startsWith('insight_select_site_search')).split('=')[1];
    }else{
        return false;
    }
}

function cookie_change_site(rountURL,dummySite){
    if(get_cookie_site()){
            let currentVal = $(`#${dummySite} option:nth-child(2)`).val();
            let firstCurrentVal = $(`#${dummySite} option:nth-child(1)`).val();
            let cookieVal = get_cookie_site();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: rountURL,
                type: "get",
                data: ({
                    'currentVal':currentVal,
                    'firstCurrentVal':firstCurrentVal,
                    'cookieVal':cookieVal,
                }),
                datatype: "html",
                beforeSend: function(){
                    loading('load');
                },
            }).done(function(data){
                $(`#${dummySite}`).val(data.siteValue).trigger("change");
                loading('stop_load');
            }).fail(function(jqXHR, ajaxOptions, thrownError){
                loading('stop_load');
                console.log("No response from server");
            });
        }
}
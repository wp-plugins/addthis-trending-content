(function($) {
	
//    $('.atmorelink').live('click', function(e){
//        e.preventDefault();
//        var atmore_id   =   $(this).attr('id');
//        $('.atfollowservice').filter('.atmore').toggleClass('hidden');
//        $('.atmore').toggleClass('hidden');
//        $('#'+atmore_id+' .atmore_span').toggleClass('hidden');
//        $('#'+atmore_id+' .atless_span').toggleClass('hidden');
//    });
    
//    $('#toggleBtn').live('click', function(e){
//        e.preventDefault();
//        $('#toggleBtn').text() == 'More options...' ? $('#toggleBtn').text('Less options...') : $('#toggleBtn').text('More options...');
//        $('.atmore').toggleClass('hidden');  
//    });    

    $('.trending_height').live('blur', function(e){
        if( ('auto' != $('.trending_height').val()) && isNaN($('.trending_height').val()) || (500 < $('.trending_height').val())){
            $('.trending_height').css('border', '1px solid red');
            $('.button-secondary').hide();
        }else{
            $('.trending_height').css('border', '1px solid #DFDFDF');
            $('.button-secondary').show();
        }
    });
    
    $('.trending_width').live('blur', function(e){
        if( ('auto' != $('.trending_width').val()) && isNaN($('.trending_width').val()) || (500 < $('.trending_width').val())){
            $('.trending_width').css('border', '1px solid red');
            $('.button-secondary').hide();
        }else{
            $('.trending_width').css('border', '1px solid #DFDFDF');
            $('.button-secondary').show();
        }
    });   

    $('#bg_check').live('click', function(e){  
        if ($('#bg_check').is(':checked')) {
            $('#bg_color').removeAttr('disabled','');
        }else{
            $('#bg_color').attr('disabled','disabled');
            $('#bg_color').val('');
        }
    });
    
    $('#border_check').live('click', function(e){
        if ($('#border_check').is(':checked')) {
            $('#border').removeAttr('disabled','');
        }else{
            $('#border').attr('disabled','disabled');
            $('#border').val('');
        }
    });  
    
    function show_more(widget_id){
        $('.atfollowservice').filter('.atmore').toggleClass('hidden');
        $('.'+widget_id+'_atmore').toggleClass('hidden');
        $('#'+widget_id+'_atmore_span').toggleClass('hidden');
        $('#'+widget_id+'_atless_span').toggleClass('hidden');     
        return false;
    }   
    
   window.show_more =   show_more;
})(jQuery);

jQuery(document).ready(function() {
		
    jQuery('#height').blur(function(){  
        jQuery('#addthis_trendingcontent_demo').css('height', jQuery('#height').val()+'px');
    });
    
    jQuery('#width').blur(function(){  
        jQuery('#addthis_trendingcontent_demo').css('width', jQuery('#width').val()+'px');
    });   
    
    jQuery('#addthis-trending-content-title').change(function(){
    	jQuery('#addthis-trending-preview-title').html(jQuery('#addthis-trending-content-title').val());
    });

    jQuery('#links').change(function(){  
        var link_count  =   jQuery('#links').val();
        var sample_link_html    =   '<li class="addthis-content-row"><a class="addthis-content-link" href="">Sample link to your most popular content will appear here</a></li>';
        jQuery('.addthis-content-list').html('');
        for(var i = 0; i < link_count; i++){
            jQuery('.addthis-content-list').append(sample_link_html);
        }
    });    
    
    jQuery('#bg_color').blur(function(){  
        jQuery('#addthis_trendingcontent_demo').css('background-color', jQuery('#bg_color').val());
    });  
    
    jQuery('.miniColors-trigger').blur(function(){  
        jQuery('#addthis_trendingcontent_demo').css('background-color', jQuery('#bg_color').val());
    });    
    
    jQuery('#border').blur(function(){  
        jQuery('#addthis_trendingcontent_demo').css('border', '1px solid '+jQuery('#border').val());
    });  
    
    jQuery('.miniColors-trigger').blur(function(){  
        jQuery('#addthis_trendingcontent_demo').css('border', '1px solid '+jQuery('#border').val());
    });  
    
    if(!jQuery('#bg_color').val())
        jQuery("#bg_check").attr("checked", false);
    
    if(!jQuery('#border').val())
        jQuery("#border_check").attr("checked", false);
    
    // show 1st time load values
	jQuery('#addthis-trending-preview-title').html(jQuery('#addthis-trending-content-title').val());
    var link_count  =   jQuery('#links').val();
    var sample_link_html    =   '<li class="addthis-content-row"><a class="addthis-content-link" href="">Sample link to your most popular content will appear here</a></li>';
    jQuery('.addthis-content-list').html('');
    for(var i = 0; i < link_count; i++){
        jQuery('.addthis-content-list').append(sample_link_html);
    }
    
    jQuery('#addthis_trendingcontent_demo').css('height', jQuery('#height').val()+'px');
    
    jQuery('#addthis_trendingcontent_demo').css('width', jQuery('#width').val()+'px');
    
    jQuery('#addthis_trendingcontent_demo').css('background-color', jQuery('#bg_color').val());
    
    jQuery('#addthis_trendingcontent_demo').css('border', '1px solid '+jQuery('#border').val());

});

function valDimension(inst, save_widget_id){
    if( ('auto' != inst.value) && isNaN(inst.value)){
        document.getElementById(inst.id).style.border = '1px solid red';
        document.getElementById(save_widget_id).style.display = 'none';
    }else{  
        document.getElementById(inst.id).style.border = '1px solid #DFDFDF';
        document.getElementById(save_widget_id).style.display = 'block';
    }
}
    
function enablePicker(inst, picker_id, enabled){  
    if(inst.checked || 1 == enabled){
        document.getElementById(picker_id).disabled = false;
        jQuery("#"+picker_id).miniColors();
    }else{
        document.getElementById(picker_id).disabled = true;
        document.getElementById(picker_id).value = '';
    }
}

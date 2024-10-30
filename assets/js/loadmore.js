jQuery(document).ready(function($){

	jQuery('#spf-loadmore .spf-load-button').click(function(e){	

		var next_url = jQuery(this).data('next_url');	
		var posts_container = jQuery('#spf-posts');					

		var ajax_data = {
			'action': 'spf_get_next_post_set',
			'_spfnonce': spf.nonce,		
			'next_url': next_url,
		};


		jQuery.post(spf.ajaxurl, ajax_data, function(response) {

			var data = response.data;
			var next_page = response.next_page;
			var media_url = false; 

			if(data){

				jQuery.each(data,function(i, item){

					if(item.media_type==='IMAGE'){

						media_url = item.media_url;					

					}else if(item.media_type==='CAROUSEL_ALBUM'){

						media_url = false;	

					}else if(item.media_type==='VIDEO'){
						media_url = false;
					}

					if( media_url != false ){

						post_html = '<div class="spf-single-img"><img src="'+media_url+'" class="spf-responsive"/></div>';

						posts_container.append(post_html);
					}
					
				});
			}			
			

			if(next_page){
				jQuery('#spf-loadmore .spf-load-button').data('next_url', next_page);
			}else{
				jQuery('#spf-loadmore .spf-load-button').hide();
			}			
			
		});

	});

});
function mv_sc_preview_changes(){
	if( mv_sc_skip_prompt || confirm( mv_sc_preview_changes_name + ' ?') )
		jQuery('#post-preview').click();	
}

function mv_sc_publish(){
	if( mv_sc_skip_prompt || confirm( mv_sc_publish_name + ' ?') )
		jQuery('#publish').click();	
}

function mv_sc_save_post(){
	// If already published, saving = re-publish
	if(jQuery('#save-post').length){
		if( mv_sc_skip_prompt || confirm( mv_sc_save_post_name + ' ?') )
			jQuery('#save-post').click();
	}else{
		mv_sc_publish();
	}	
}
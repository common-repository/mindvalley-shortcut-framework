<?php
/*
Plugin Name: MindValley Shortcut Framework
Plugin URI: http://mindvalley.com
Description: Collection of Additional Keyboard Shortcuts for Post Editor page
Author: MindValley
Version: 0.1.2
*/

class MVShortcutFramework{
	// Binding functions defined in shortcut_fn.js
	var $bind_functions = array(
		'Preview Changes' 	=> 'mv_sc_preview_changes',
		'Publish' 			=> 'mv_sc_publish',
		'Save Post' 		=> 'mv_sc_save_post'
	);
	
	var $default_settings = array(
		'skip_prompt' => 'true',
		'keys_bindings' => array( 
					'mv_sc_preview_changes' => 'l',
					'mv_sc_publish' => 'p',
					'mv_sc_save_post' => 's'
		)
	);

	function MVShortcutFramework(){
		global $pagenow;
		
		add_action('admin_menu', array( &$this, 'add_options_page'));
		
		// Attach to Add New or Edit pages only
		if( is_admin() 
			&& ( 'post-new.php' == $pagenow 
				||	('post.php' == $pagenow && 'edit' == $_REQUEST['action']) )){
			
			add_action('admin_footer', array( &$this, 'admin_footer'));
			add_filter('tiny_mce_before_init', array( &$this, 'add_filters' ) );
			$this->enqueue_scripts(); 
		}	
	}
	
	function add_options_page(){
	  	add_options_page('MV Shortcut Framework', 'MV Shortcut', 'manage_options', 'mv-sc-framework', array( &$this, 'the_option_page') );
	}
	
	function the_option_page(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		?>
		<div class="wrap">
        <?php screen_icon(); ?>
		<h2>MV Shortcut Framework Settings</h2>
		<?php
			if( isset($_POST['mv_sc_submit']) || isset($_POST['mv_sc_submit_reset']) ){
				// Verify nonce
				if(wp_verify_nonce($_POST['mv_sc_nonce'],'mv_sc_settings_update') ){
					if( isset($_POST['mv_sc_submit_reset']) )
						update_option( 'mv_sc_settings', $this->default_settings );
					else
						update_option( 'mv_sc_settings', $_POST['mv_sc_settings'] );	
				}else{
					?>
					<div class="updated below-h2" id="message"><p>Invalid form submit action detected. Settings not saved.</p></div>
					<?php
				}
			}
			
			$settings = array_merge($this->default_settings, get_option('mv_sc_settings' , array()));
			
		?>
		<br />
        
        <form action="" method="post">
        	<table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('When I press the shortcut') ?> </th>
                    <td>
                    <fieldset>
                        <input id="prompt-action" type="radio" name="mv_sc_settings[skip_prompt]" value="false" <?php checked('false', $settings['skip_prompt']); ?> />
                        <label for="prompt-action"><?php _e('Prompt me to reconfirm action.');?></label>
                        <br/>
                        <input id="skip-prompt" type="radio" name="mv_sc_settings[skip_prompt]" value="true" <?php checked('true', $settings['skip_prompt']); ?> />
                        <label for="skip-prompt"><?php _e('Just do it.'); ?></label>
                    </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Key Assignment') ?> </th>
                    <td>
                    	<table width="100%" cellpadding="0" cellspacing="0">
                    	<?php
							foreach($this->bind_functions as $name => $fn){
						?>
                        	<tr>
                                <td style="padding:0;margin:0" width="120"><strong><?php echo $name?></strong></td>
                                <td style="padding:0;margin:0">=> Ctrl+Alt+<select name="mv_sc_settings[keys_bindings][<?php echo $fn?>]">
                                    <?php
                                        // a to z
                                        for($i=97;$i<=122;$i++){
                                            echo '<option value="'.chr($i).'"';
											selected( chr($i), $settings['keys_bindings'][$fn] );
											echo '>'.chr($i).'</option>';	
                                        }
                                    ?>
                                </select>
                                </td>
                           	</tr>
                        <?php }?>
                        </table>
                        <br />
                        * For Mac OS users : "Ctrl" refers to the Control (<img src="<?php echo plugins_url('/images/control.png', __FILE__)?>" />) key, NOT the Command (<img src="<?php echo plugins_url('/images/command.png', __FILE__)?>" />) key. "Alt" refers to the Option (<img src="<?php echo plugins_url('/images/option.png', __FILE__)?>" />) key.
                	</td>
				</tr>
            </table>
            <br/ >
        	<?php wp_nonce_field('mv_sc_settings_update','mv_sc_nonce'); ?>
            <input type="submit" name="mv_sc_submit" value="Save Changes" /> <input type="submit" name="mv_sc_submit_reset" value="Reset to Default" onclick="return confirm('Reset everything to default?')" />
        </form>
        
		</div>
        <?php

	}
	
	function admin_footer(){
		$settings = array_merge($this->default_settings, get_option('mv_sc_settings' , array()));
		?>
        <script language="javascript">
			var mv_sc_skip_prompt = <?php echo $settings['skip_prompt']?>;
			
			// Document wide hotkeys
			jQuery(document).ready(function(){
				<?php 	
					foreach($this->bind_functions as $name => $fn) {
						echo "if( 'function' == typeof({$fn}) )	{ jQuery(document).add('input:text, textarea').bind('keydown', 'alt+ctrl+".$settings['keys_bindings'][$fn]."', {$fn}); }";
					}
				?>
			});
			
			// Bind tinyMCE editor Hotkeys
			// Called after tinyMCE oninit
			function bindTinyMCEKeys(){
				
				if(tinyMCE && tinyMCE.activeEditor) { 
					
					if( jQuery.client.os == 'Mac') {
						<?php 
							foreach($this->bind_functions as $name => $fn) {
								echo "if( 'function' == typeof({$fn}) )	tinyMCE.activeEditor.addShortcut('alt+meta+".$settings['keys_bindings'][$fn]."', '{$name}', {$fn}); ";
							}
						?>
					}else{
						<?php 
							foreach($this->bind_functions as $name => $fn) {
								echo "if( 'function' == typeof({$fn}) )	tinyMCE.activeEditor.addShortcut('alt+ctrl+".$settings['keys_bindings'][$fn]."', '{$name}', {$fn}); ";
							}
						?>
					}
					
				}
					
			}
        </script>
        <?php	
	}
	
	function add_filters($initArray){
		$initArray['oninit'] = 'bindTinyMCEKeys';
		return $initArray;
	}
	
	function enqueue_scripts(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('mv-jq-client', plugins_url('/jquery.client.js', __FILE__), array('jquery'));
		wp_enqueue_script('mv-jq-hotkeys', plugins_url('/jquery.hotkeys.js', __FILE__), array('jquery'));
		wp_enqueue_script('mv-shortcut-fn', plugins_url('/shortcut_fn.js', __FILE__), array('jquery','mv-jq-hotkeys'));
	}

}

new MVShortcutFramework();
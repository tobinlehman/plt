<?php 
$iea_admin_buttons = new iea_admin_buttons;
add_action("iea_admin_menu", array($iea_admin_buttons  , 'menu'));
add_action("admin_init", array($iea_admin_buttons  , 'save'));
class iea_admin_buttons{


	
	
function menu(){
	
add_submenu_page ( 'articulate_content', "Custom Buttons",  "Custom Buttons", "manage_options", 'articulate-settings-button',array($this, 'view') );	
	
}
function save(){
if(isset($_POST['iea_save'])){
if($_POST['iea_save'] == 'buttons')
{
	$opt=get_quiz_embeder_options();
	$cbox_themes=quiz_embeder_get_colorbox_themes();
	//echo "<pre>"; print_r($_POST); echo "</pre>";
	/*$opt['lightbox_script']=$_POST['lightbox_script'];
	$opt['colorbox_transition']=$_POST['colorbox_transition'];
	$opt['colorbox_theme']=$_POST['colorbox_theme'];
	$opt['nivo_lightbox_effect']=$_POST['nivo_lightbox_effect'];
	$opt['nivo_lightbox_theme']=$_POST['nivo_lightbox_theme'];	
	$opt['size_opt']=$_POST['size_opt'];

	$opt['height']=intval($_POST['height']);
	$opt['width']=intval($_POST['width']);

	$opt['height_type']=$_POST['height_type'];
	$opt['width_type']=$_POST['width_type'];*/
	$buttons=array();
	if(isset($_POST['buttons']) && is_array($_POST['buttons']))
	{
	foreach($_POST['buttons'] as $btn){$btn=trim($btn); if($btn!=""){$buttons[]=$btn;}}
	}
	$opt['buttons']=$buttons;
	
	update_option('quiz_embeder_option', $opt);

}	
}
}
function view(){
	$opt=get_quiz_embeder_options();
	$cbox_themes=quiz_embeder_get_colorbox_themes();
	
	?>
 	
    <h2 class="header">Button Settings</h2>
    <div style="background-color:#FFF;padding:10px;margin:10pxmax-width:700px">
     <form action="" method="post" >
	<input type="hidden" name="iea_save" value="buttons">
		
	<h4 class="header">Custom Buttons</h4> 
		<div id="button_area">
		<?php if(is_array($opt['buttons'])){
		
			foreach($opt['buttons'] as $btn){
		 ?>
			<div class="button_box">
			<div class="close_button" title="Delete"></div>
			<div class="imgbox"><img src="<?php echo $btn?>"  /></div>
			<input type="text" size="20" name="buttons[]" class="image_source" value="<?php echo $btn ?>">&nbsp;
			
			<a class="img_upload_button addimgero btn-floating btn-large waves-effect waves-light blue"><i class="material-icons">perm_media</i></a>
       
			</div>	
			<?php }}?>
		</div>
		<div><input type="button" class="waves-effect waves-light btn" value="Add Button" onclick="add_new_button()" /> &nbsp;&nbsp;
		<input type="submit" value="Save" name="save" class="waves-effect waves-light btn" /></div>
</form>
    
  

    
    </form>
    
    
    <?php
	
}
	
	
	
	
	
}
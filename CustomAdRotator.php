<?php
/* Plugin Name: Custom Ad Rotator
Description: Add this shortcode [CustomAdRotator] after the post and this one in the sidebar [CustomAdRotator id=sidebar]
Author: Zoltan benyei
Version: 1.0
*/

function remove_magic_quotes() {

		$_GET    = stripslashes_deep($_GET);
        $_POST   = stripslashes_deep($_POST);
        $_COOKIE = stripslashes_deep($_COOKIE);
        $_REQUEST = stripslashes_deep($_REQUEST);
		
    }

if ( is_admin() ) {

	add_action ( 'admin_menu', 'rotator_options_page');
		
}

function mysql_table_creator() {

	global $wpdb;
	$table_name = $wpdb->prefix . "adrotator"; 
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name )	{
   
	  
	$sql = "CREATE TABLE $table_name (
	id INTEGER(10) AUTO_INCREMENT,
	adcode TEXT NOT NULL,
	location TEXT NOT NULL,
	image_url TEXT NOT NULL,
	js_code TEXT NOT NULL,
	dest_url TEXT NOT NULL,
	total_clicks INTEGER(10) DEFAULT 0,
	impressions INTEGER(10) DEFAULT 0,
	default_ad TEXT NOT NULL,
	PRIMARY KEY (id) )";
	
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	add_option('sample_table_version','1.0');
	
	}
	
}

register_activation_hook( __FILE__, 'mysql_table_creator' );

function rotator_options_page() {

	add_options_page ( 'Custom Ad Rotator', 'Custom Ad Rotator', 2, 'CustomAdRotator', 'rotator_admin_page' );

}

function rotator_admin_page() {

$msg = '';

if ( $_GET['action'] == 'delete') {
	
	global $wpdb;
	$wpdb->delete( $wpdb->prefix . "adrotator", array( 'id' => $_GET['id'] ) );

}

if ( $_GET['action'] == 'reset') {
	
	global $wpdb;
	$wpdb->update(
	
		$wpdb->prefix . "adrotator", 
		
		array('total_clicks' => 0, 
			  'impressions' => 0
			),
		
		array( 'id' => $_GET['id'] )	
				
	);

}


if ( $_POST['update'] ) {

remove_magic_quotes();

	global $wpdb;
			
	$wpdb->update(
	
		$wpdb->prefix . "adrotator", 
		
		array('adcode' => $_POST['adcode'], 
			  'location' => $_POST['location'],
			  'image_url' => $_POST['imageurl'],
			  'js_code' => $_POST['leadpagesscript'],
			  'dest_url' => $_POST['desturl'],
			  'default_ad' => $_POST['defaultad']
			),
		
		array( 'id' => $_POST['update_id'] )	
				
	);
	
	$msg = 'Ad is updated!';
	
	
}

if ( $_GET['action'] == 'edit') {
global $wpdb;
$table = $wpdb->get_row( "SELECT * from " . $wpdb->prefix . "adrotator WHERE id = " . $_GET['id'] . "" );
?>
<div style = "border:solid red 2px;padding:10px;display:inline-block;">
<h3>Update Ad</h3>
<form action = "" method = "post">
<label for = "adcode">Adcode: </label><input type = "text" name = "adcode" id = "adcode" value = "<?php echo $table->adcode; ?>" /><br /><br />
<input type = "radio" name = "location" value = "End of Post" <?php if ( $table->location == 'End of Post' ) echo 'checked'; ?> />&nbsp;<label for = "location">End of Post </label><br />
<input type = "radio" name = "location" value = "Sidebar" <?php if ( $table->location == 'Sidebar' ) echo 'checked'; ?> />&nbsp;<label for = "location">Sidebar </label><br /><br />
<label for = "imageUrl">Image URL: </label><input type = "text" name = "imageurl" id = "imageurl" value = "<?php echo $table->image_url; ?>" style = "width:400px;" /><br /><br />
<label for = "Leadpagesscript">Leadpages script: </label><br /><textarea name = "leadpagesscript" id = "leadpagesscript" cols = "90" rows = "3"><?php echo $table->js_code; ?></textarea><br /><br />
<label for = "destUrl">Destination URL: </label><input type = "text" name = "desturl" id = "desturl" value = "<?php echo $table->dest_url; ?>" style = "width:400px;" /><br /><br />
<input type = "checkbox" name = "defaultad" value = "yes" <?php if ( $table->default_ad == 'yes' ) echo 'checked'; ?> />&nbsp;<label for = "defaultad">Make this the default Ad</label><br /><br />
<input class = 'button-primary' type = "submit" name = "update" value = "Update Ad" id = "update" />
<a class = 'button-primary' href = "options-general.php?page=CustomAdRotator">Go back to main page</a>
<input type = "hidden" name = "update_id" value = "<?php echo $table->id ?>" />
</form>
<div style = "font-weight:bold;color:#FF0000;font-size:14px;padding:5px;"><?php echo $msg; ?></div>
</div>
<?php
exit();
}

if ( isset ( $_POST['create_ad'] ) ) {

	remove_magic_quotes();

	global $wpdb;
	$wpdb->insert( $wpdb->prefix. 'adrotator', array('adcode' => $_POST['adcode'], 
													 'location' => $_POST['location'],
													 'image_url' => $_POST['imageurl'],
													 'js_code' => $_POST['leadpagesscript'],
													 'dest_url' => $_POST['desturl'],
													 'default_ad' => $_POST['defaultad']) );											


}

global $wpdb;
$rows = $wpdb->get_results( "SELECT * from " . $wpdb->prefix . "adrotator ORDER BY id" );

?>
<style type="text/css">
#adtable {
border-collapse: collapse;
}
#adtable tr th, #adtable tr td {
border:solid #000000 1px;
padding:5px 10px;
}
</style>

<h2>Rotator Admin Page</h2>


<table id = "adtable">
<tr>
<th>AdCode</th><th>Location</th><th>Ad Image URL</th><th>Leadpages script</th><th>Destination URL</th><th>Default Ad</th><th>Total Clicks / Impressions</th><th>Edit</th><th>Delete</th>
</tr>

<?php
$delBtn = plugins_url( 'images/delete.png', __FILE__ );

foreach ( $rows as $val ) {

$adcode = $val->adcode;
$location = $val->location;
$image_url = htmlspecialchars($val->image_url);
$js_code = htmlspecialchars($val->js_code);
$dest_url = htmlspecialchars($val->dest_url);

if ( $val->impressions == 0 ) {
	
	$total = $val->total_clicks . ' / ' . $val->impressions . ' (0%)';

} else {

	$total = $val->total_clicks . ' / ' . $val->impressions . ' (' . round(($val->total_clicks/$val->impressions)*100) . '%)';

}

echo "<tr><td>".$adcode."</td><td>".$location ."</td><td>".$image_url."</td><td>".$js_code."</td><td>".$dest_url."</td><td>".$val->default_ad."</td><td>".$total." <a href = 'options-general.php?page=CustomAdRotator&action=reset&id=".$val->id."'>Reset</a></td><td><a href='options-general.php?page=CustomAdRotator&action=edit&id=".$val->id."'>Edit</a></td><td style = 'text-align:center;'><a onclick=\"return confirm('Are you sure you want to delete this ad?');\" href='options-general.php?page=CustomAdRotator&action=delete&id=".$val->id."'><img src = '".$delBtn."' alt = 'delete' title = 'Delete' /></a></td></tr>";

}
?>
</table>

<br />

<div style = "border:solid red 2px;padding:10px;display:inline-block;">

<h3>Add New Ad</h3>

<form action = "" method = "post">

<label for = "adcode">Adcode: </label><input type = "text" name = "adcode" id = "adcode" /><br /><br />
<input type = "radio" name = "location" value = "End of Post" checked />&nbsp;<label for = "location">End of Post </label><br />
<input type = "radio" name = "location" value = "Sidebar" />&nbsp;<label for = "location">Sidebar </label><br /><br />
<label for = "imageUrl">Image URL: </label><input type = "text" name = "imageurl" id = "imageurl" style = "width:400px;" /><br /><br />
<label for = "Leadpagesscript">Leadpages script: </label><br /><textarea name = "leadpagesscript" id = "leadpagesscript" cols = "90" rows = "3"></textarea><br /><br />
<label for = "destUrl">Destination URL: </label><input type = "text" name = "desturl" id = "desturl" style = "width:400px;" /><br /><br />
<input type = "checkbox" name = "defaultad" value = "yes" />&nbsp;<label for = "defaultad">Make this the default Ad</label><br /><br />
<input type = "submit" name = "create_ad" value = "Create Ad" />
</form>

</div>

<?php
}

function get_ad( $adcode, $loc, $def = '' ) {
		
		global $wpdb;
		$table = $wpdb->get_row( "SELECT * from " . $wpdb->prefix . "adrotator WHERE adcode = '" . $adcode . "' AND location = '" . $loc . "' AND default_ad = '". $def ."'" );

		if ( $loc == 'Sidebar' && $table->image_url != '' && $table->js_code == ''  ) { 
		
			$content .= '
			<div class = "customAd" id = "'.$table->id.'" style = "text-align:center;">
				<a href = "'.$table->dest_url.'"><img src = "'.$table->image_url.'" alt = "click me" /></a>
			</div>';
			
		} elseif ( $loc == 'Sidebar' && $table->image_url == '' && $table->js_code != '' )   {
				
			$content .= '<div class = "customAd" id = "'.$table->id.'" style = "text-align:center;">'.$table->js_code.'</div>';
		
		
		} elseif ( $loc == 'End of Post' && $table->image_url != '' && $table->js_code == '' )  {
		
			$content .= '
			<div class = "customAd" id = "'.$table->id.'" style = "text-align:center;">
				<a href = "'.$table->dest_url.'"><img src = "'.$table->image_url.'" alt = "click me" /></a>
			</div>';
		
		
		} elseif ( $loc == 'End of Post' && $table->image_url == '' && $table->js_code != '' )  {
		
			$content .= '<div class = "customAd" id = "'.$table->id.'" style = "text-align:center;">'.$table->js_code.'</div>';
		
		} 
				
		$wpdb->query( "UPDATE ". $wpdb->prefix ."adrotator SET impressions = impressions + 1  WHERE id = '".$table->id."'" );
		
		return $content;

}

function CustomAdRotator($atts) {

	extract( shortcode_atts( array('id' => '',), $atts ) );

	$cbc = $_GET['cbc'];


	//sidebar
	if ( $id == 'sidebar' ) {
		
		//URL is SET
		if ( isset ($cbc) ) {
				
			echo "<script type='text/javascript'>
			jQuery(function(){
				jQuery.cookie('sidebar', '".$cbc."', { expires: 30, path: '/' });
			});
			</script>";

			return get_ad( $cbc, 'Sidebar' );
		
		//URL is NOT SET
		} else {
		
			//sidebar cokkie is SET
			if ( isset( $_COOKIE["sidebar"] ) ) {
			
				return get_ad( $_COOKIE["sidebar"], 'Sidebar' );
			
			//sidebar cokkie is NOT SET
			} else {
			
				return get_ad( $_COOKIE["sidebar"], 'Sidebar', 'yes' );
			
			}
			
		}
	//post
	} else {

		//URL is SET
		if ( isset ($cbc) ) {
			
			echo "<script type='text/javascript'>
			jQuery(function(){
				jQuery.cookie('post', '".$cbc."', { expires: 30, path: '/' });
			});
			</script>";
			
			return get_ad( $cbc, 'End of Post' );
		
		//URL is NOT SET
		} else {
		
			//post cokkie is SET
			if ( isset( $_COOKIE["post"] ) ) {
			
				return get_ad( $_COOKIE["post"], 'End of Post' );
			
			//post cokkie is NOT SET
			} else {
			
				return get_ad( $_COOKIE["post"], 'End of Post', 'yes' );
			
			}
		
		}

	}
	
	

}

function add_rotator_scripts() {
			
	wp_enqueue_script( 'jquery' ); 	
	wp_register_script( 'cookie', plugins_url( 'js/jquery.cookie.js', __FILE__ ) );
	wp_enqueue_script( 'cookie' );
	
}



function updateTotalClicks() {

    echo '<script type="text/javascript">
	jQuery(function() {

		jQuery(".customAd a").click(function() {
								
			var id = jQuery(this).parent().attr("id");
									
			jQuery.ajax({
				type: "POST",
				async: false,
				url: "'.plugins_url().'/CustomAdRotator2/incClicks.php",
				data: "id=" + id,
				success: function () {
								
					return true
					
				}
			});
						
		});
			
	});
	</script>';

}

add_action( 'wp_footer', 'updateTotalClicks' );
add_action( 'wp_enqueue_scripts', 'add_rotator_scripts' );
add_shortcode( 'CustomAdRotator', 'CustomAdRotator' );
?>
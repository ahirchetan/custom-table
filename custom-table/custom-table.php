<?php 
/*
 * @wordpress-plugin
 * Plugin Name:       Custom Table
 * Plugin URI:        https://wordpress.org/plugins/custom-table/
 * Description:       custom plugin is used to post list table collumn extendable
 * Author:            Chetan
 * Author URI:        https://wordpress.org/
 * Version:           1
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Text Domain:       custom-table
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */


/**
 * Add default post collumn in the database
 */
function setup_collumn_options() {
	if (empty(get_option('show_columns'))) {
	$default_options=array(
	     'ID'=>'ID',
	     'post_title'=>'Post Title',
	     'post_thumbnail'=>'Post Thumbnail',
	     'post_excerpt'=>'Post Excerpt'
	 );
	$show_options = array(
	     'user_email'=>'email',
	     'display_name'=>'author'
	 );
	$hide_options= array();
	 add_option('default_columns',serialize($default_options));
	 add_option('show_columns',serialize($show_options));
	 add_option('hide_columns',serialize($hide_options));
	 }
} 
add_action( 'init', 'setup_collumn_options' );

/**
 * Activate the plugin.
 */
function custom_table_activate() { 
	// Trigger our function that default collumn options add in database.
	setup_collumn_options(); 
	// Clear the permalinks after active the plugin.
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'custom_table_activate' );


function custom_table_deactivate() {
	// remove all options into database, so the rules are no longer in memory.
 	delete_option('default_columns');
 	delete_option('show_columns');
 	delete_option('hide_columns');

	// Clear the permalinks after deactive the plugin.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'custom_table_deactivate' );


function setup_all_script(){
	wp_enqueue_script( 'ajax-script', plugins_url( '/custom_js.js', __FILE__ ), array('jquery') );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
?>
<link rel="stylesheet" type="text/css"href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css"/>
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
$(function() {
$("#post_table").dataTable();
});
</script>
<?php
}
add_action("wp_footer","setup_all_script");

function posts_table_shortcodes_init(){
	add_shortcode( 'gest_posts_table', 'post_table' );
	// do_action("custom_table_script_include");	
}


add_action( 'wp_ajax_show_columns', "show_columns");
add_action( 'wp_ajax_nopriv_show_columns', "show_columns"); 
function show_columns(){ 
	if(isset($_POST['action']) && !empty($_POST['action'])) {
    	$action = $_POST['action'];       
    	$option_key = $_POST['option_key'];       
    	$option_value = $_POST['option_value']; 
    	$button_name = $_POST['button_name']; 

        echo $option_value."-".$option_key;
        $extendcolunm = array($option_key => $option_value );
    	$defaultoptions=unserialize(get_option('default_columns'));
    	$show_columns=unserialize(get_option('show_columns'));
    	$hide_columns=unserialize(get_option('hide_columns'));
    	if($button_name =="show"){
		    if (!isset($defaultoptions[$option_key])) {
		        $new_arr = serialize(array_merge($defaultoptions,$extendcolunm));
		        $hide_columns = serialize(array_merge($hide_columns,$extendcolunm));
		        
		        update_option('default_columns',$new_arr);
		        update_option('hide_columns',$hide_columns);

		        unset($show_columns[$option_key]);		        
		        update_option('show_columns',serialize($show_columns));
		        print_r($new_arr);
		   	}
    	}
    	if($button_name =="hide"){
		    if (isset($defaultoptions[$option_key])) {
		        $show_columns = serialize(array_merge($show_columns,$extendcolunm));
		        
		        update_option('show_columns',$show_columns);

		        unset($defaultoptions[$option_key]);		        
		        unset($hide_columns[$option_key]);		        
		        update_option('default_columns',serialize($defaultoptions));
		        update_option('hide_columns',serialize($hide_columns));
		   	}
    	}
	  } 	
    wp_die();
}

// add_action('init', 'show_columns');
add_action('init', 'posts_table_shortcodes_init');

 function  post_table($atts){
	$post_default_data = unserialize(get_option('default_columns'));
	$post_show_data = unserialize(get_option('show_columns'));
	$post_hide_data = unserialize(get_option('hide_columns'));
    extract( shortcode_atts( array (
        'post_type' => 'post',
        'order' => 'date',
        'orderby' => 'title',
        'numberposts' => -1,
        'color' => '',
        'fabric' => '',
        'category' => ''), 
    	$atts,
		'get_posts_table'
		) );

		$args = array(
		'numberposts'	=> $numberposts,
		'post_type'		=> $post_type
		);

		
	$custom_posts = get_posts( $args );

	if( ! empty( $custom_posts ) ){
$output = "";
?>

<!-- 
 for column add and remove from post table 
 -->
<div class="dropdown_list" style="display: flex;">
	<div class="column_1">
		<label for="expand_column" style="display: block;">Show Column</label>
<select id="expand_column">
    <option value="">Choose Column</option>
	<?php foreach ($post_show_data as $key => $value): ?>
    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>		
	<?php endforeach ?>
</select>
		<button id="show" style="padding: 12px;">show</button>
	</div>
	<div class="column_2">
		<label for="hide_column" style="display: block;">Hide Column</label>
		<select id="hide_column">
		    <option value="">Choose Column</option>
			<?php foreach ($post_hide_data as $key => $value): ?>
	    		<option value="<?php echo $key; ?>"><?php echo $value; ?></option>		
			<?php endforeach ?>
		</select>
		<button id="hide" style="padding: 12px;">hide</button>
	</div>
</div>
<?php
$output .= '<table id="post_table">';
	$output .= '<thead>';
		$output .= '<tr>';
		foreach($post_default_data as $post_show){
				$output .= '<th>'.$post_show.'</th>';
		}
				$output .= '<th>action</th>';
		$output .= ' </tr> ';
	$output .= ' </thead> ';

	$output .= ' <tbody> ';
			foreach ( $custom_posts as $p ){

		$output .= ' <tr> ';
				$output .= '<td>'.$p->ID.'</td>';
				$output .= '<td>'. $p->post_title . '</td>';
				$output .= '<td> <img src="'. get_the_post_thumbnail_url($p->ID).'" width="100px" height="100px"></td>';
				$output .= '<td>'.$p->post_excerpt.'</td>';
				// print_r($post_show_data);
				if(isset($post_default_data['display_name'])){
					$output .= '<td>'.get_the_author_meta('display_name', $p->post_author).'</td>';
				}
				if(isset($post_default_data['user_email'])){
					$output .= '<td>'.get_the_author_meta('user_email', $p->post_author).'</td>';
				}
				$output .= '<td><a target="_blank" href="'. get_permalink( $p->ID ) . '"> view </a> </td>';
		$output .= ' </tr> ';
			}
	$output .= ' </tbody> ';
$output .= ' </table> ';

	}

return $output;
}

?>
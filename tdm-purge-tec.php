<?php
	/**
		* Plugin Name: TEC: Purge DB
		* Plugin URI: http://www.theeventscalendar.com
		* Description: Add-on for The Events Calendar. Purges the deleted events from the database, 50 at a time.
		* Version: 1.0
		* Author: Andras Guseo | The Events Calendar
		* Author URI: http://www.theeventscalendar.com
		* License: GPLv2 or later
		* License URI: http://www.gnu.org/licenses/gpl-2.0.html
	*/
	
	/*
		* Adds a link to the admin bar. Clicking on that link purges the database of 
		* "deleted" events that are also removed from the trash, but are still in the database. 
		* This allows you to re-import events. The script deletes 50 instances per page load
		* Code by Nico & Andras
	*/
	
	
function purge_deleted_events () {
	
	/* Get all events from database which are 'deleted_event' */
	$posts = get_posts( array( 'post_type'=>'deleted_event', "posts_per_page" => 50, 'post_status' => 'trash', ) );
	
	/* If there are any then purge them. If 50 has been purged, then there is still more
	* so popup message to run again. */
	?>
	
	<div class="overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color:rgba(255,255,255,0.5);z-index:10000">
		<div id="tdm-popup" style="background-color:yellow;position:fixed;top:30%;padding:5%;left:30%;right:30%;font-size:2em;line-height:1.5em;text-align:center;">
			<?php
			$i = 0;
			
			/* If there are posts, start purging */
			if ( !empty( $posts ) ) :
				foreach ( $posts as $post ) :
					wp_delete_post( $post->ID, true);
					$i++;
				endforeach;
		
				/* If there were 50 posts offer more purging */
				if ( $i == 50 ) : ?>
					<?echo $i ?> deleted events purged.<br/>
					<a href="edit.php?post_status=trash&post_type=tribe_events&purge_tec=true">Click here again</a>
					
				<?php 
				/* If there are less than 50 posts, then offer closing */
				else : ?>
					<?echo $i ?> deleted events purged.<br/>Done purging!<br/>
					<a href="edit.php?post_status=trash&post_type=tribe_events">Close</a>
				<?php endif;
				
			/* If there are none to be purged, offer closing */
			else : ?>
				There are no events to be purged.<br/><a href="edit.php?post_status=trash&post_type=tribe_events">Close</a>
			<?php endif; ?>
		</div>	 
	</div><!-- .overlay -->
	<?php
	}
	
/**
 * Action - Call the purging
 */
function start_purging_deleted_events () {
    /* If we clicked on the link in the admin bar, then start purging */
    if ( ! empty ( $_REQUEST['purge_tec'] ) ) {
        if ( 'true' === $_REQUEST['purge_tec'] ) {
            purge_deleted_events();
        }
    }
}
add_action( 'admin_init', 'start_purging_deleted_events' );

/**
 * Add link to the WP Toolbar
 * Always visible
 */
function tdm_purge_tec_toolbar_link( $wp_admin_bar ) {
	$numb = intval( wp_count_posts( 'deleted_event' )->trash );
	if ( current_user_can( 'manage_options' ) ) {
		$args = array(
			'id' => 'tdm_purge_tec_plugin',
			'title' => 'Purge Events (' . $numb . ')', 
			'href' => 'edit.php?post_type=tribe_events&purge_tec=true', 
			'meta' => array(
				'class' => 'tdm_purge_tec_plugin', 
				'title' => 'Purge ' . $numb .' events from database',
			)
		);
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'admin_bar_menu', 'tdm_purge_tec_toolbar_link', 999 );

/**
 * Add button to the Events -> Trash page
 * Only visible when there are events to be purged
 */
function add_purge_button() {
	global $typenow, $pagenow;
	
	$numb = intval(wp_count_posts( 'deleted_event' )->trash);

	if ( 'edit.php' == $pagenow 
		&& isset( $_REQUEST['post_type'] ) 
		&& $_REQUEST['post_type'] == 'tribe_events' 
		&& isset( $_REQUEST['post_status'] ) 
		&& $_REQUEST['post_status'] == 'trash' 
		&& 0 < $numb ) :
		?>
		<div class="purge" style="display: inline-flex;margin-right: 10px;margin-top: 1px;">
			<a href="edit.php?post_status=trash&post_type=tribe_events&purge_tec=true" class="button">Purge (<?php echo $numb ?>)</a>
		</div>
	<?php endif;
}
add_action( 'restrict_manage_posts', 'add_purge_button', 90 );
?>

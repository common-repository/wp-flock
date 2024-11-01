<?php
//** SET UP THE ADMINISTRATION LINKS *********************************//
function fl_links( $links ) {
 $settings_link = '<a href="./options-general.php?page=wp-flock/flconfig.php">User Groups</a>';
 array_unshift( $links, $settings_link ); 
 return $links; 

}
function fl_add_pages() {
  // the options page
	add_options_page( "Manage User Groups", "User Groups", 6, __FILE__, 'fl_display_options' );
	// the post meta box
	add_meta_box( 'postfl', 'Post Visibility', 'fl_post_advanced', 'post', 'side', 'low' );
	add_meta_box( 'pagefl', 'Page Visibility', 'fl_post_advanced', 'page', 'side', 'low' );
}

//** PRINT THE ADMINISTRATION PAGE ***********************************//
function fl_display_options() {
  global $wpdb;
  // create the option variables
  add_option( 'fl_groups' );
  
  // get our current groups, if any
  //$g = unserialize( get_option( 'fl_groups' ) );

  //-- make some changes! --------------------------------------------//
  // are we deleting a group?
  if( !empty( $_POST['deleteit'] ) && !empty( $_POST['delgroup'] ) ){
    foreach( $_POST['delgroup'] as $gID )
      $wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->flgroups` WHERE `gID` = %d;", $gID ) );
      
    echo '<div id="message" class="updated fade"><p>';
    if( $r === FALSE ) {
      $wpdb->show_errors();
      _e( '  Error deleting group!<br/>', JXP_DOMAIN );
      $wpdb->print_error();
      $wpdb->hide_errors();
    } else
		  _e( '  Group(s) deleted successfully.', JXP_DOMAIN );
		echo '</p></div>';
  }

	// are we adding a new group
	if( !empty( $_POST['add_group'] ) ) {
    // stick it in our database!
    $r = $wpdb->query( $wpdb->prepare(
      "INSERT INTO `$wpdb->flgroups` ( `gName`, `gMask` )  VALUES( %s, %s );"
      ,$_POST['fl_gname']
      ,$_POST['fl_ljmask']
     ) );

    echo '<div id="message" class="updated fade"><p>';
    if( $r === FALSE ) {
      $wpdb->show_errors();
      _e( '  Error adding new group!<br/>', JXP_DOMAIN );
      $wpdb->print_error();
      $wpdb->hide_errors();
    } else
		  _e( '  New group added successfully.', JXP_DOMAIN );
		echo '</p></div>';
	}
	
  // are we editing an old group
	if( !empty( $_POST['edit_group'] ) ) {
    // update the database!
    $r = $wpdb->query( $wpdb->prepare(
      "UPDATE `$wpdb->flgroups` SET gName = %s, gMask = %s WHERE gID = %d;"
      ,$_POST['fl_gname']
      ,$_POST['fl_ljmask']
      ,$_GET['gID']
    ) );

    echo '<div id="message" class="updated fade"><p>';
    if( $r === FALSE ) {
      $wpdb->show_errors();
      _e( '  Error editing group!<br/>', JXP_DOMAIN );
      $wpdb->print_error();
      $wpdb->hide_errors();
    } else
		  _e( '  Group edited successfully.', JXP_DOMAIN );
		echo '</p></div>';
	}

	//-- print some stuff ----------------------------------------------//
?>
<div class="wrap">
<h2><?php _e( 'Flock Configuration', FL_DOMAIN ); ?></h2>

<?php if( empty( $_GET['gID'] ) ) { ?>
<h3><?php _e( 'Curent Groups', FL_DOMAIN ); ?></h3>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php wp_nonce_field('update-options'); ?>
<div class="tablenav">
  <div class="alignleft">
    <input type="submit" value="Delete" name="deleteit" class="button-secondary delete" />
  </div>

  <br class="clear" />
  </div>
  <br class="clear" />

  <table class="widefat">
  <thead>
    <tr>
	    <th scope="col" class="check-column">&nbsp;</th>
      <th>Name</th><th style="width: 10%; text-align: center;">LJ Bitmask</th><th style="text-align: center">Edit</th>
    </tr>
	</thead>

	<tbody>
    <tr id="link-2" valign="middle">
<?php
  // get the existing table data
  $gs = $wpdb->get_results( 'SELECT `gID`, `gName`, `gMask` FROM `'. $wpdb->flgroups .'` ORDER BY gName;' );
  foreach( $gs as $g ) {
    switch( $g->gMask ){
      case -1:
        $gval = '--';
        break;
      case 0:
        $gval = '<em>friends only</em>';
        break;
      default:
        $gval = $g->gMask;
    }
    print "<tr><th scope=\"row\" class=\"check-column\"><input type=\"checkbox\" name=\"delgroup[]\" value=\"$g->gID\" /></th>".
          "<td><strong>$g->gName</strong></td>".
          "<td style='text-align: center;'>$gval</td>".
          "<td style='text-align: center;'><a href='./options-general.php?page=wp-flock/flconfig.php&gID=$g->gID'>edit</a></td>\n</tr>\n";
  }
  
  ?>
    </tr>
  </tbody>
</table>
</form>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php wp_nonce_field('update-options'); ?>
<h3><?php _e( 'Add New Group', FL_DOMAIN ); ?></h3>
<table class="form-table">
  <tr valign="top">
		<th scope="row"><?php _e( 'Name', FL_DOMAIN ); ?></th>
		<td><input name="fl_gname" type="text" id="fl_gname" size="40" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'LiveJournal Bitmask', FL_DOMAIN ); ?></th>
		<td><input name="fl_ljmask" type="text" id="fl_ljmask" size="4" value="0" />
		<p class="setting-description"><?php _e( '<strong>JournalPress Only:</strong> Use <code>0</code> to crosspost posts with this group as friends-only, and <code>-1</code> to not crosspost at all. Other values will correspond to journal usemasks, so please do <strong>not</strong> set them unless you know what you\'re doing.', FL_DOMAIN ); ?></p>
		</td>
	</tr>

</table>

<p class="submit">
	<input type="submit" name="add_group" value="<?php _e( 'Add New Group', FL_DOMAIN ); ?>" style="font-weight: bold;" />
</p>
</form>
<?php } else { $k = $_GET['gKey']; /* EDIT A GROUP */
  // get the existing table data
  $g = $wpdb->get_row( 'SELECT `gID`, `gName`, `gMask` FROM `'. $wpdb->flgroups .'` WHERE gID = \''. $_GET['gID'] .'\';' );
?>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php wp_nonce_field('update-options'); ?>
<h3><?php _e( 'Edit Group', FL_DOMAIN ); _e( ' (<a href="./options-general.php?page=wp-flock/flconfig.php">back</a>)' ); ?></h3>
<table class="form-table">
  <tr valign="top">
		<th scope="row"><?php _e( 'Name', FL_DOMAIN ); ?></th>
		<td><input name="fl_gname" type="text" id="fl_gname" size="40" value="<?php echo $g->gName; ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'LiveJournal Bitmask', FL_DOMAIN ); ?></th>
		<td><input name="fl_ljmask" type="text" id="fl_ljmask" size="4" value="<?php echo $g->gMask; ?>" />
		<p class="setting-description"><?php _e( '<strong>JournalPress Only:</strong> Use <code>0</code> to crosspost posts with this group as friends-only, and <code>-1</code> to not crosspost at all. Other values will correspond to journal usemasks, so please do <strong>not</strong> set them unless you know what you\'re doing.', FL_DOMAIN ); ?></p>
		</td>
	</tr>
</table>

<p class="submit">
	<input type="submit" name="edit_group" value="<?php _e( 'Edit Group', FL_DOMAIN ); ?>" style="font-weight: bold;" />
</p>
</form>

<?php } ?>
</div>
<?php
}


//** PRINT THE POST/PAGE OPTIONS *************************************//
function fl_post_advanced() {
  global $wpdb;
  // get our current groups, if any
  $gs = $wpdb->get_results( 'SELECT `gID`, `gName` FROM `'. $wpdb->flgroups .'` ORDER BY gName;' );
  
  // does our post have any existing group settings?
  //$pg = get_post_meta( $_GET['post'], '_fl_groups', true );

  $gs = $wpdb->get_results(  $wpdb->prepare( 
          'SELECT g.gID, g.gName, c.oID FROM `'. $wpdb->flgroups .'` AS g LEFT JOIN `'. $wpdb->flcaps .'` AS c ON g.gID = c.gID AND c.oType = %s AND c.oID = %s;'
          ,'post'
          ,$_GET['post']
        ) );
  //$gs = $wpdb->get_results(  'SELECT gID, gName FROM `'. $wpdb->flgroups .'` ORDER BY gName;' );
  if( $gs ){
    foreach( $gs as $g ){
      $guse = !empty( $g->oID ) ? ' checked="checked"' : '';
      echo '<p><label class="selectit"><input type="checkbox" name="fl_groups[]" value="', $g->gID ,'"', $guse ,' /> ', $g->gName ,'</label></p>';
    }
  } else {
    _e( '<p>It seems you don\'t have any groups. Would you like to <a href="./options-general.php?page=wp-flock/flconfig.php">define some</a>?</p>', FL_DOMAIN );
  }
}

//** PRINT THE USER PAGE OPTIONS *************************************//
function fl_user_groups() {
  global $wpdb, $user_ID;
  // don't show these unless we're of an adminly persuasion
	if( !current_user_can( 'manage_options' ) || empty( $_GET['user_id'] ) ) 
		return;

  //$ug = get_usermeta( $_GET['user_id'], 'fl_user_groups' );
  // $ug = is_array( $ug ) ? unserialize( $ug ) : false;
		
  _e( '<h3>Member Of...</h3>', FL_DOMAIN );
  $gs = $wpdb->get_results(  $wpdb->prepare( 
          'SELECT g.gID, g.gName, c.oID FROM `'. $wpdb->flgroups .'` AS g LEFT JOIN `'. $wpdb->flcaps .'` AS c ON g.gID = c.gID AND c.oType = %s AND c.oID = %s;'
          ,'user'
          ,$_GET['user_id']
        ) );
  if( $gs ){
    echo '<p>';
    foreach( $gs as $g ){
      $guse = $g->oID == $_GET['user_id'] ? ' checked="checked"' : '';
      echo '<label class="selectit" style="margin-left: 1.5em;"><input type="checkbox" name="fl_groups[]" value="', $g->gID ,'"', $guse ,' /> ', $g->gName ,'</label>';
    }
    echo '</p>';
  } else {
    _e( '<p>It seems you don\'t have any groups. Would you like to <a href="./options-general.php?page=wp-flock/flconfig.php">define some</a>?</p>', FL_DOMAIN );
  }
}
<?php
/** UPDATE USER META **************************************************/
function fl_update_groups( $user_id ){
  global $wpdb;
  // don't do this unless we're of an adminly persuasion
	if( !current_user_can( 'manage_options' ) ) 
		return $user_id;

  $r = $wpdb->query( $wpdb->prepare(
        "DELETE FROM `$wpdb->flcaps` WHERE oType = %s AND oID = %s;"
        ,'user'
        ,$user_id
      ) );
  if( !empty( $_POST['fl_groups'] ) ) {
    foreach( $_POST['fl_groups'] as $gID ) {
      // stick it in our database!
      $r = $wpdb->query( $wpdb->prepare(
        "INSERT INTO `$wpdb->flcaps` ( `oType`, `oID`, `gID` )  VALUES( %s, %s, %s );"
        ,'user'
        ,$user_id
        ,$gID
      ) );
    }
  }
  return $user_id;
}

/** UPDATE POST META **************************************************/
function fl_save( $post_id ){
  global $wpdb;
  if( empty( $_POST['fl_groups'] ) )
    return $post_id;

  $r = $wpdb->query( $wpdb->prepare(
        "DELETE FROM `$wpdb->flcaps` WHERE oType = %s AND oID = %s;"
        ,'post'
        ,$post_id
      ) );
  foreach( $_POST['fl_groups'] as $gID ) {
    // stick it in our database!
    $r = $wpdb->query( $wpdb->prepare(
      "INSERT INTO `$wpdb->flcaps` ( `oType`, `oID`, `gID` )  VALUES( %s, %s, %s );"
      ,'post'
      ,$post_id
      ,$gID
    ) );
  }
  
  return $post_id;
}
// mark a post as private if we've assigned it groups
function fl_status_save( $post_status ){
  if( empty( $_POST['fl_groups'] ) || $_POST['post_status'] != 'publish' )
    return $post_status;
  else
    return 'private';
}

/** CHECK POST/PAGE VISIBILITY ****************************************/
// Enable users with the right level to read a private post
// This is required because single post viewing has special logic
// $allcaps = Capabilities the user currently has
// $caps = Primitive capabilities being tested / requested
// $args = array with:
// $args[0] = original meta capability requested
// $args[1] = user being tested
// $args[2] = post id to view
// See code for assumptions
function fl_has_cap( $allcaps, $caps, $args ){
  global $wpdb;
  // This handler is only set up to deal with certain
  // capabilities. Ignore all other calls into here.
  if( !in_array( 'read_private_posts', $caps ) && !in_array( 'read_private_pages', $caps ) )
    return $allcaps;  // these aren't the droids you're looking for
 
  $hasCap = $wpdb->get_var(  $wpdb->prepare( 
          'SELECT COUNT(*) AS hasCap FROM `'. $wpdb->flcaps .'` AS p JOIN `'. $wpdb->flcaps .'` AS u ON u.gID = p.gID WHERE p.oType = %s AND u.oType = %s AND p.oID = %d AND u.oID = %d;'
          ,'post'
          ,'user'
          ,$args[2]
          ,$args[1]
        ) );
  $allcaps['read_private_posts'] = $allcaps['read_private_pages'] = $hasCap;
  return $allcaps;
}
// fuck damn there is SO much that could go wrong here...
function fl_query( $sql ){
  global $wpdb, $user_ID;

  if( fl_query_match( $sql ) ){
    $sql = fl_query_cleanup( $sql );

    // where clause modification, case #1, eg.
    // AND wp_posts.post_type = 'post' AND ( wp_posts.post_status = 'publish'
    $sql = preg_replace(
      "/({$wpdb->posts}\.)?post_type[\s]*=[\s]*[\'|\"](post|page)[\'|\"][\s]*AND[\s]*\([\s]*({$wpdb->posts}\.)?post_status[\s]*=[\s]*[\'|\"]publish[\'|\"]/"
      ," {$wpdb->posts}.post_type = '$2' AND ( {$wpdb->posts}.post_status = 'publish' OR ( {$wpdb->posts}.post_status = 'private' AND EXISTS ( SELECT p.gID FROM {$wpdb->flcaps} AS p JOIN {$wpdb->flcaps} AS u ON u.gID = p.gID WHERE p.oType = 'post' AND {$wpdb->posts}.ID = p.oID AND u.oType = 'user' AND u.oID = '$user_ID' ) )"
      ,$sql
    );
    
    // where clause modification, case #2, eg.
    // post_type = 'page' AND post_status = 'publish'
    $sql = preg_replace(
      "/(\()?({$wpdb->posts}\.)?post_type[\s]*=[\s]*[\'|\"](post|page)[\'|\"][\s]*AND[\s]*[\s]*({$wpdb->posts}\.)?post_status[\s]*=[\s]*[\'|\"]publish[\'|\"][\s]*(\))?/"
      ," ( {$wpdb->posts}.post_type = '$3' AND ( {$wpdb->posts}.post_status = 'publish' OR ( {$wpdb->posts}.post_status = 'private' AND EXISTS ( SELECT p.gID FROM {$wpdb->flcaps} AS p JOIN {$wpdb->flcaps} AS u ON u.gID = p.gID WHERE p.oType = 'post' AND {$wpdb->posts}.ID = p.oID AND u.oType = 'user' AND u.oID = '$user_ID' ) ) OR ( {$wpdb->posts}.post_status = 'private' AND {$wpdb->posts}.post_author = '$user_ID' ) ) )"
      ,$sql
    );
    
    // where clause modification, case #3, eg.
    // p.post_type = 'post' AND p.post_status = 'publish'
    $sql = preg_replace(
      "/(p\.)?post_type[\s]*=[\s]*[\'|\"](post|page)[\'|\"][\s]*AND[\s]*[\s]*(p\.)?post_status[\s]*=[\s]*[\'|\"]publish[\'|\"]/"
      ," p.post_type = '$2' AND ( p.post_status = 'publish' OR ( p.post_status = 'private' AND EXISTS ( SELECT pg.gID FROM {$wpdb->flcaps} AS pg JOIN {$wpdb->flcaps} AS u ON u.gID = pg.gID WHERE pg.oType = 'post' AND p.ID = pg.oID AND u.oType = 'user' AND u.oID = '$user_ID' ) ) OR ( p.post_status = 'private' AND p.post_author = '$user_ID' ) )"
      ,$sql
    );

    //echo $sql;
  }

  return $sql;
}
// edit the query?
function fl_query_match( $sql ){
  global $wpdb;
  return ( ( preg_match( "/post_status[\s]*=[\s]*[\'|\"]publish[\'|\"]/", $sql ) ) && ( preg_match("/[\s|,]{$wpdb->posts}[\s|,]/", $sql ) ) );
}
// clean-up hacks for playing nice with other plugins
function fl_query_cleanup( $sql ){
  global $wpdb;
  return $sql;
}


/** FILTER THE RSS FEED ***********************************************/
function fl_content_rss( $content ){
  global $post;

  if( $post->post_status != 'private' )
    return $content;

  return "<em>Please log in to view this protected post.</em>";
}

/** INTERNAL FUNCTIONS ************************************************/

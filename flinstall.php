<?php
//** INSTALL & UNINSTALL *********************************************//
function fl_install() {
  global $wpdb;
  // check for capability
	if( !current_user_can( 'activate_plugins' ) ) 
		return;

  // create the database
  $flgroups = $wpdb->prefix . 'fl_groups';
  $flcaps = $wpdb->prefix . 'fl_caps';
  
  if( $wpdb->get_var( "show tables like '$flgroups'" ) != $flgroups ) {
		$sql = "CREATE TABLE IF NOT EXISTS " . $flgroups . " (
             `gID` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
             `gName` VARCHAR( 40 ) NOT NULL ,
             `gDesc` VARCHAR( 255 ) NOT NULL ,
             `gMask` TINYINT( 3 ) NOT NULL ,
             `gPostDefault` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
             `gUserDefault` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
             UNIQUE ( `gName` )
		       ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  } if( $wpdb->get_var( "show tables like '$flcaps'" ) != $flcaps ) {
		$sq2 = "CREATE TABLE IF NOT EXISTS `" . $flcaps . "` (
              `oType` enum('post','user') NOT NULL,
              `oID` bigint(20) NOT NULL,
              `gID` tinyint(3) unsigned NOT NULL,
              PRIMARY KEY  (`oType`,`oID`,`gID`)
            ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sq2 );
  }

  update_option( 'fl_installed', '0.1.1' );
}

function fl_uninstall(){
  // do stuff in here in the future hey
  delete_option( 'fl_installed' );
}
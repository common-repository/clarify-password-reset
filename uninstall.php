<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

// Clean up the options we might have created
delete_option( 'flizcpr_nosavefix' );
delete_option( 'flizcpr_nowarn' );

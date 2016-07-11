<?php
global $wpdb;

// Drop PM table and plugin option when uninstall
$wpdb->query( "DROP table {$wpdb->prefix}pm" );
delete_option( 'cl_pmwpm_option' );
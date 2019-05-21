<?php
/**
 * Plugin Name:     ACF Relationship Sync
 * Description:     Allow ACF relationship fields to be bidirectional, syncing configured fields on update.
 * Author:          Jesse Day
 * Author URI:      jesseday.us
 * Text Domain:     acf-relationship-sync
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Acf_Relationship_Sync
 */

require(plugin_dir_path( __FILE__ ) . 'src/Listeners/OneToMany.php');
require(plugin_dir_path( __FILE__ ) . 'src/Relationships/OneToMany.php');

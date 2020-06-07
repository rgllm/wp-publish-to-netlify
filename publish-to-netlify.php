<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rgllm.com
 * @since             1.0.0
 * @package           publish-to-netlify
 *
 * @wordpress-plugin
 * Plugin Name:       Publish to Netlify
 * Plugin URI:        https://wordpress.org/plugins/publish-to-netlify/
 * Description:       Connect your WordPress website to Netlify by triggering build hooks on save and or update.
 * Version:           1.0.2
 * Author:            rgllm
 * Author URI:        https://rgllm.com/
 * License:           GPL
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       publish-to-netlify
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require plugin_dir_path( __FILE__ ) . 'inc/class-emitter.php';

function run_plugin() {
	$emitter = new Emitter();
	$emitter->run();
}

run_plugin();

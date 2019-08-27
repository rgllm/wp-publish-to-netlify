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
 * @package           Publish_To_Netlify
 *
 * @wordpress-plugin
 * Plugin Name:       Publish to Netlify
 * Plugin URI:        https://github.com/rgllm/publish-to-netlify
 * Description:       Connect your WordPress website to Netlify by triggering build hooks on save and or update.
 * Version:           1.0.2
 * Author:            Rogério Moreira
 * Author URI:        https://rgllm.com
 * License:           GPL
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       publish-to-netlify
 */

defined('ABSPATH') or die('You do not have access to this file.');

class publishToNetlifyHook
{

    public function __construct() {
        add_action('admin_menu', array($this, 'create_plugin_settings_page'));
        add_action('admin_init', array($this, 'setup_sections'));
        add_action('admin_init', array($this, 'setup_fields'));
        add_action( 'save_post', array($this, 'wordpress_netlify_enqueue'), 10, 2 );
        add_action( 'save_page', array($this, 'wordpress_netlify_enqueue'), 10, 2 );
        add_action('acf_save_post', array($this, 'wordpress_netlify_enqueue'), 20);
    }

    public function add_settings_page() { ?>
        <div class="wrap">
            <h1>Publish to Netlify</h1>
            <hr>
            <form method="POST" action="options.php">
                <?php
                settings_fields('publish_to_netlify_fields');
                do_settings_sections('publish_to_netlify_fields');
                submit_button();
                ?>
            </form>
            <footer>
                <h3>Documentation</h3>
                <p><a href="https://pt.wordpress.org/plugins/publish-to-netlify/">WordPress Support Forum</a></p>
                <p><a href="https://github.com/rgllm/wp-publish-to-netlify/">Github repository</a></p>
            </footer>

        </div><?php
    }

    public function wordpress_netlify_enqueue($post_id, $post) {
        $status = $post->post_status;
        $webhook_url = get_option('webhook_address');

        if ( ($status === 'publish') && (!empty( $webhook_url ) )) {
           wp_remote_post( $webhook_url );
        }
    }

    public function create_plugin_settings_page() {
        $page_title = 'Publish to Netlify';
        $menu_title = 'Publish to Netlify';
        $capability = 'manage_options';
        $slug = 'publish_to_netlify_fields';
        $callback = array($this, 'add_settings_page');
        add_options_page($page_title, $menu_title, $capability, $slug, $callback);
    }

    public function setup_sections() {
        add_settings_section('netlify_settings_section', 'Settings', array($this, 'section_callback'), 'publish_to_netlify_fields');
    }

    public function section_callback($arguments){
        switch ($arguments['id']) {
            case 'netlify_settings_section':
                break;
        }
    }

    public function setup_fields()
            {
                $fields = array(
                    array(
                        'uid' => 'webhook_address',
                        'label' => 'Netlify Build Hook URL',
                        'section' => 'netlify_settings_section',
                        'type' => 'text',
                        'placeholder' => 'build-url.netlify.com',
                        'default' => '',
                    ),
                );
                foreach ($fields as $field) {
                    add_settings_field($field['uid'], $field['label'], array($this, 'field_callback'), 'publish_to_netlify_fields', $field['section'], $field);
                    register_setting('publish_to_netlify_fields', $field['uid']);
                }
            }

            public function field_callback($arguments)
            {

                $value = get_option($arguments['uid']);

                if (!$value) {
                    $value = $arguments['default'];
                }

                switch ($arguments['type']) {
                    case 'text':
                    case 'password':
                    case 'number':
                        printf('<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value);
                        break;
                    case 'textarea':
                        printf('<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value);
                        break;
                    case 'select':
                    case 'multiselect':
                        if (!empty($arguments['options']) && is_array($arguments['options'])) {
                            $attributes = '';
                            $options_markup = '';
                            foreach ($arguments['options'] as $key => $label) {
                                $options_markup .= sprintf('<option value="%s" %s>%s</option>', $key, selected($value[array_search($key, $value, true)], $key, false), $label);
                            }
                            if ($arguments['type'] === 'multiselect') {
                                $attributes = ' multiple="multiple" ';
                            }
                            printf('<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup);
                        }
                        break;
                    case 'radio':
                    case 'checkbox':
                        if (!empty($arguments['options']) && is_array($arguments['options'])) {
                            $options_markup = '';
                            $iterator = 0;
                            foreach ($arguments['options'] as $key => $label) {
                                $iterator++;
                                $options_markup .= sprintf('<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked($value[array_search($key, $value, true)], $key, false), $label, $iterator);
                            }
                            printf('<fieldset>%s</fieldset>', $options_markup);
                        }
                        break;
                }
            }
        }

        new publishToNetlifyHook;
        ?>

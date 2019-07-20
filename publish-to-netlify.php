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
 * Plugin URI:        https://rgllm.com/publish-to-netlify
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Rogério Moreira
 * Author URI:        https://rgllm.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       publish-to-netlify
 * Domain Path:       /languages
 */

defined('ABSPATH') or die('You do not have access to this file, sorry mate');

class publishToNetlifyHook
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create_plugin_settings_page'));
        add_action('admin_init', array($this, 'setup_sections'));
        add_action('admin_init', array($this, 'setup_fields'));
        add_action('admin_enqueue_scripts', array($this, 'wordpress_netlify_enqueue'));
    }

    public function add_settings_page()
    { ?>
    <div class="wrap">
        <h1>Publish to Netlify settings</h1>
        <hr>
        <?php
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            $this->admin_notice();
        } ?>
        <form method="POST" action="options.php">
            <?php
            settings_fields('publish_to_netlify_fields');
            do_settings_sections('publish_to_netlify_fields');
            submit_button();
            ?>
        </form>
        <footer>
            <h3>Plugin documentation</h3>
            <p><a href="https://github.com/rgllm">Documentation</a></p>
            <p><a href="https://github.com/rgllm">Github</a></p>
        </footer>

    </div><?php
        }

        public function wordpress_netlify_enqueue($hook)
        {
            if ('post.php' != $hook && 'post-new.php' != $hook) {
                return;
            }
            ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var _this = this;
            $("td > input").css("width", "100%");

            var webhook_url = '<?php echo (get_option('webhook_address')) ?>';
            var netlify_user_agent = '<?php echo (get_option('netlify_user_agent')) ?>';
            var netlify_api_key = '<?php echo (get_option('netlify_api_key')) ?>'
            var netlify_site_id = '<?php echo (get_option('netlify_site_id')) ?>';

            var netlifySites = "https://api.netlify.com/api/v1/sites/";
            var req_url = netlifySites + netlify_site_id + '/deploys?access_token=' + netlify_api_key;

            if (adminpage == 'post-php') {
                jQuery('#publish, #original_publish').on('click', function(e) {
                    jQuery.ajax({
                        type: "POST",
                        url: webhook_url,
                        dataType: "json",
                        header: {
                            "User-Agent": netlify_user_agent
                        }
                        success: function(d) {}
                    })
                })
            }
        });
    </script> <?php
            }

            public function create_plugin_settings_page()
            {
                // Add the menu item and page
                $page_title = 'Publish to Netlify';
                $menu_title = 'Publish to Netlify';
                $capability = 'manage_options';
                $slug = 'publish_to_netlify_fields';
                $callback = array($this, 'add_settings_page');
                $icon = 'dashicons-cloud';
                $position = 100;
                add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
            }

            public function admin_notice()
            { ?>
    <div class="notice notice-success is-dismissible">
        <p>Your settings have been updated!</p>
    </div><?php
        }

        public function setup_sections()
        {
            add_settings_section('netlify_settings_section', 'Netlify settings', array($this, 'section_callback'), 'publish_to_netlify_fields');
        }

        public function section_callback($arguments)
        {
            switch ($arguments['id']) {
                case 'netlify_settings_section':
                    echo 'Netlify webhooks settings';
                    break;
            }
        }

        public function setup_fields()
        {
            $fields = array(
                array(
                    'uid' => 'webhook_address',
                    'label' => 'Webhook Build URL',
                    'section' => 'netlify_settings_section',
                    'type' => 'text',
                    'placeholder' => 'build-url.netlify.com',
                    'default' => '',
                ),
                array(
                    'uid' => 'netlify_site_id',
                    'label' => 'Netlify site_id',
                    'section' => 'netlify_settings_section',
                    'type' => 'text',
                    'placeholder' => 'e.g. 5b8e927e-82e1-4786-4770-a9a8321yes43',
                    'default' => '',
                ),
                array(
                    'uid' => 'netlify_api_key',
                    'label' => 'Netlify API Key',
                    'section' => 'netlify_settings_section',
                    'type' => 'text',
                    'placeholder' => 'o-auth token',
                    'default' => '',
                ),
                array(
                    'uid' => 'netlify_user_agent',
                    'label' => 'User-Agent',
                    'section' => 'netlify_settings_section',
                    'type' => 'text',
                    'placeholder' => 'e.g. and-website-url.netlify.com',
                    'default' => '',
                )
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
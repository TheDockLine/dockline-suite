<?php

/**
 * Handles GitHub integration for version control and updates.
 *
 * @link       https://thedockline.com
 * @since      1.0.0
 *
 * @package    Dockline_Suite
 * @subpackage Dockline_Suite/includes
 */
class Dockline_Suite_GitHub_Updater
{
    public $plugin_name;
    public $version;
    public $cache_key;
    public $cache_allowed;

    public function __construct($plugin_name, $version)
    {
        $this->version = $version;
        $this->plugin_name = $plugin_name;
        $this->cache_key = 'dockline_suite';
        $this->cache_allowed = true;

        // Hook into the upgrader process complete action
        add_action('upgrader_process_complete', array($this, 'on_plugin_update'), 10, 2);
    }

    public function request()
    {

        $remote = get_transient($this->cache_key);

        if (false === $remote || !$this->cache_allowed) {

            $remote = wp_remote_get(
                'https://api.github.com/repos/TheDockLine/dockline-suite/releases/latest',
                array(
                    'timeout' => 10,
                    'headers' => array(
                        'Accept' => 'application/json',
                    )
                )
            );

            if (
                is_wp_error($remote)
                || 200 !== wp_remote_retrieve_response_code($remote)
                || empty(wp_remote_retrieve_body($remote))
            ) {
                return false;
            }

            set_transient($this->cache_key, $remote, DAY_IN_SECONDS);
        }

        $remote = json_decode(wp_remote_retrieve_body($remote));

        return $remote;
    }


    public function info($res, $action, $args)
    {

        // do nothing if you're not getting plugin information right now
        if ('plugin_information' !== $action) {
            return false;
        }

        // do nothing if it is not our plugin
        if ($this->plugin_name !== $args->slug) {
            return false;
        }

        // get updates
        $remote = $this->request();

        if (!$remote) {
            return false;
        }

        $res = new stdClass();
        $git_version = ltrim($remote->tag_name, 'v');

        $res->name = 'Dockline Suite';
        $res->slug = 'dockline-suite';
        $res->version = $git_version;
        $res->tested = '6.7.2';
        $res->requires = '6.0';
        $res->author = 'Levi Mardis';
        $res->author_profile = 'https://thedockline.com';
        $res->download_link = $remote->assets[0]->browser_download_url;
        $res->trunk = $remote->assets[0]->browser_download_url;
        $res->requires_php = '6.0';
        $res->last_updated = $remote->published_at;

        $res->sections = array(
            'description' => 'This plugin is a suite of tools for Dockline websites.',
            'changelog' => $remote->body
        );


        $res->banners = array(
            'low' => 'https://www.thedockline.com/wp-content/uploads/2025/02/update-image.jpg',
            'high' => 'https://www.thedockline.com/wp-content/uploads/2025/02/update-image.jpg'
        );

        return $res;
    }

    public function update($transient)
    {

        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->request();
        $git_version = ltrim($remote->tag_name, 'v');

        if (
            $remote
            && version_compare($this->version, $git_version, '<')
            && version_compare($remote->requires, get_bloginfo('version'), '<')
            && version_compare($remote->requires_php, PHP_VERSION, '<')
        ) {
            $res = new stdClass();
            $res->slug = $this->plugin_name;
            $res->plugin = dirname(plugin_basename(__DIR__)) . '/' . $this->plugin_name . '.php';
            $res->new_version = $git_version;
            $res->tested = '5.8';
            $res->package = $remote->assets[0]->browser_download_url;

            $transient->response[$res->plugin] = $res;
        }

        return $transient;
    }

    /**
     * Callback function for when a plugin is updated.
     *
     * @since 1.0.2
     * 
     * @param object $upgrader_object The upgrader object.
     * @param array $options The options passed to the upgrader.
     */
    public function on_plugin_update($upgrader_object, $options)
    {
        // Check if the updated item is a plugin
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            // Get the list of updated plugins
            $updated_plugins = $options['plugins'];

            // Check if the Dockline Suite plugin is in the updated plugins list
            if (in_array('dockline-suite/dockline-suite.php', $updated_plugins)) {
                // Perform actions specific to the Dockline Suite plugin update
                error_log('Dockline Suite plugin has been updated. Clearing cache or performing other actions.');
                delete_transient($this->cache_key); // Example action: clear cache
            }
        }
    }
}

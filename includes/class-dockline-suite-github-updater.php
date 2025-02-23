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
        $res->tested = '5.8';
        $res->requires = '3.0';
        $res->author = $remote->author->login;
        $res->author_profile = $remote->author->html_url;
        $res->download_link = $remote->assets[0]->browser_download_url;
        $res->trunk = $remote->assets[0]->browser_download_url;
        $res->requires_php = '5.3';
        $res->last_updated = $remote->published_at;

        $res->sections = array(
            'description' => $remote->body,
            'changelog' => $remote->body
        );

        if (!empty($remote->banners)) {
            $res->banners = array(
                'low' => $remote->banners->low,
                'high' => $remote->banners->high
            );
        }

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
            $res->plugin = dirname(plugin_basename(__DIR__)) . '/' . $this->plugin_name . '.php'; // misha-update-plugin/misha-update-plugin.php
            $res->new_version = $git_version;
            $res->tested = '5.8';
            $res->package = $remote->assets[0]->browser_download_url;

            $transient->response[$res->plugin] = $res;
        }

        return $transient;
    }

    public function purge()
    {

        if (
            $this->cache_allowed
            && 'update' === $options['action']
            && 'plugin' === $options['type']
        ) {
            // just clean the cache when new plugin version is installed
            delete_transient($this->cache_key);
        }
    }
}

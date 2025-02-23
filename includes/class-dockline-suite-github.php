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

class Dockline_Suite_GitHub
{

    /**
     * The GitHub repository owner.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $owner    The GitHub repository owner.
     */
    private $owner;

    /**
     * The GitHub repository name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $repo    The GitHub repository name.
     */
    private $repo;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $owner    The GitHub repository owner.
     * @param    string    $repo     The GitHub repository name.
     */
    public function __construct($owner, $repo)
    {
        $this->owner = $owner;
        $this->repo = $repo;
    }

    /**
     * Check for updates on GitHub.
     *
     * @since    1.0.0
     * @return   array    An array containing the latest version and download URL.
     */
    public function check_for_updates()
    {
        $api_url = "https://api.github.com/repos/{$this->owner}/{$this->repo}/releases/latest";
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['tag_name']) || !isset($body['assets'][0]['browser_download_url'])) {
            return false;
        }

        return [
            'version' => $body['tag_name'],
            'download_url' => $body['assets'][0]['browser_download_url']
        ];
    }

    /**
     * Download and update the plugin from GitHub.
     *
     * @since    1.0.0
     * @param    string    $download_url    The URL to download the latest version from.
     * @return   bool    True if the update was successful, false otherwise.
     */
    public function update_plugin($download_url)
    {
        $temp_file = download_url($download_url);

        if (is_wp_error($temp_file)) {
            return false;
        }

        $plugin_dir = WP_PLUGIN_DIR . '/dockline-suite';
        $unzip_result = unzip_file($temp_file, $plugin_dir);

        if (is_wp_error($unzip_result)) {
            return false;
        }

        unlink($temp_file);

        return true;
    }
}

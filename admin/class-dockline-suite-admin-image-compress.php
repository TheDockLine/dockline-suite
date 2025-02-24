<?php

/**
 * Class to handle image compression functionality
 *
 * @since      1.0.2
 * 
 * @package    Dockline_Suite
 * @subpackage Dockline_Suite/admin
 */
class Dockline_Suite_Admin_Image_Compress
{

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.2
     */
    public function __construct()
    {
        add_filter('wp_handle_upload', array($this, 'compress_uploaded_image'), 999, 2);
    }

    /**
     * Compress uploaded image using Voix.ly API
     * 
     * @since    1.0.2
     * 
     * @param array $file Array containing uploaded file info
     * @param string $context The type of upload action
     * @return array Modified file array
     */
    public function compress_uploaded_image($file, $context)
    {
        // Only process images
        if (!preg_match('!^image/!', $file['type'])) {
            return $file;
        }

        // Add additional checks if needed
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($file['type'], $allowed_types)) {
            return $file;
        }

        // Check if compression is enabled
        if (get_option('dockline_image_compress_enable') !== 'enabled') {
            return $file;
        }

        // Prepare API request
        $api_url = 'https://api.voix.ly/convert';
        $filename = basename($file['file']);

        // Read the image file as binary data
        $image_data = file_get_contents($file['file']);
        if ($image_data === false) {
            error_log('Dockline Suite: Failed to read image file: ' . $file['file']);
            return $file;
        }

        if (get_option('dockline_image_compress_output_size') === '') {
            $output_size = '';
        } else {
            $output_size = ' -size ' . intval(get_option('dockline_image_compress_output_size')) * 1000;
        }

        // Make API request using raw binary data
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Content-Type' => 'multipart/form-data',
                'X-Options' => '-q ' . get_option('dockline_image_compress_level') . $output_size
            ),
            'body' => $image_data,
            'timeout' => 120
        ));

        // Check for errors
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            if (is_wp_error($response)) {
                error_log('Dockline Suite: Error in image compression API request: ' . $response->get_error_message());
            } else {
                error_log('Dockline Suite: Unexpected response code: ' . wp_remote_retrieve_response_code($response));
            }
            return $file;
        }

        // Get response body
        $response_body = wp_remote_retrieve_body($response);

        if (empty($response_body)) {
            error_log('Dockline Suite: No response received from API for file: ' . $filename);
            return $file;
        }

        // Assuming the API returns binary data for the compressed image
        $webp_filename = pathinfo($file['file'], PATHINFO_DIRNAME) . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        file_put_contents($webp_filename, $response_body);

        // Update file information
        $file['file'] = $webp_filename;
        $file['url'] = str_replace(pathinfo($file['url'], PATHINFO_EXTENSION), 'webp', $file['url']);
        $file['type'] = 'image/webp';
        $file['size'] = filesize($file['file']);

        return $file;
    }
}

<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://thedockline.com
 * @since      1.0.2
 *
 * @package    Dockline_Suite
 * @subpackage Dockline_Suite/admin/partials
 */
?>

<div class="wrap">
    <h1>General Settings</h1>
    <p>This is the General page for Dockline Suite.</p>
    <form method="post" action="options.php">
        <?php
        settings_fields('dockline_betheme_overrides_settings');
        do_settings_sections('dockline-suite-betheme-overrides');
        submit_button();
        ?>
    </form>
</div>
<?php

/**
 * Betheme Overrides Admin Class
 *
 * This class handles the Betheme overrides settings for the Dockline Suite plugin.
 *
 * @package    Dockline_Suite
 * @subpackage Dockline_Suite/admin
 */
class Dockline_Suite_Admin_Betheme_Overrides
{

    /**
     * Constructor for the class.
     *
     * @since    1.0.2
     */
    public function __construct()
    {
        add_action('admin_footer', array($this, 'betheme_licnese_overlay'));
    }

    public function betheme_licnese_overlay()
    {
        if (get_option('dockline_betheme_override_license') !== 'enabled') {
            return;
        }

?>
        <style>
            .mfn-ui .mfn-register-now {
                display: none !important;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.mfn-subheader .subheader-buttons').append($('<input>', {
                    type: 'submit',
                    value: 'Save changes',
                    class: 'mfn-btn mfn-btn-green btn-save-changes'
                }));
            });
        </script>
<?php
    }
}

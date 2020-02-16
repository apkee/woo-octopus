<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://apkee.hk/kedi
 * @since      1.0.0
 *
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="octopus_reference_number_submission" style="padding-top:16px;padding-bottom:16px;">
<form name="form_octopus_reference_number" method="POST" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
<?php wp_nonce_field('update_octopus_reference_number'); ?>
<div><?php _e('Submit your Otocpus reference number:', 'woo-octopus') ?><input name="octopus_reference_number" type="text" value="" maxlength="30" style="width:250px;margin:8px 4px;"></div>
<input type="submit" value="<?php _e('Submit','woo-octopus') ?>">
</form>
</div>
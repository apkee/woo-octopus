<?php
/**
 * Octopus transaction reference number display
 *
 * @since      1.0.0
 * @package    Woo_Octopus
 * @subpackage Woo_Octopus/public/partials
 */

global $pagenow;
$params['reference_no'] = isset($params['reference_no']) ? $params['reference_no']: '';

if ($pagenow == 'edit.php') { ?>
  <span class="woo-octopus-reference-no">(Octopus reference no.: <?php echo $params['reference_no']; ?>)</span>
<?php } elseif ($pagenow == 'post.php') { ?>
  <p class="form-field form-field-wide woo-octopus-reference-no">Octopus reference no.: <?php echo $params['reference_no']; ?></p>
<?php }

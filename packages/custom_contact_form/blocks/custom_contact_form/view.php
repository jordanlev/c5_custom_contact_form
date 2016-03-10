<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 3.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

$dom_id = "contact-form-{$bID}";
$errors = empty($errors) ? array() : $errors;
?>

<script type="text/javascript">
var contact_form_is_processing = false;
$(document).ready(function() {
	$('#<?php echo $dom_id; ?> form').ajaxForm({
		'url': '<?php echo Loader::helper('concrete/urls')->getToolsURL('ajax_submit', 'custom_contact_form'); ?>',
		'dataType': 'json',
		'data': {
			'bID': <?php echo $bID; ?>,
			'cID': <?php echo Page::getCurrentPage()->getCollectionID(); ?>,
			'ccm_token': '<?php echo Loader::helper('validation/token')->generate(); ?>'
		 },
		'beforeSubmit': function() {
			if (contact_form_is_processing) {
				return false; //prevent re-submission while waiting for response
			}
			contact_form_is_processing = true;
			$('#<?php echo $dom_id; ?> .errors').hide();
			$('#<?php echo $dom_id; ?> .errors .error-items').html('');
			$('#<?php echo $dom_id; ?> .submit').hide();
			$('#<?php echo $dom_id; ?> .processing').show();
		},
		'success': function(response) {
			contact_form_is_processing = false;
			if (response.success) {
				$('#<?php echo $dom_id; ?> .processing').hide();
				$('#<?php echo $dom_id; ?> form').fadeOut('', function() {
					$('#<?php echo $dom_id; ?> .success').fadeIn();
					$('#<?php echo $dom_id; ?> form').clearForm();
				});
			} else { //validation error
				var errorItems = '';
				for (var i = 0, len = response.errors.length; i < len; i++) {
					errorItems += '<li>' + response.errors[i] + '</li>';
				}
				$('#<?php echo $dom_id; ?> .errors .error-items').html(errorItems);
				$('#<?php echo $dom_id; ?> .errors').slideDown();
				$('#<?php echo $dom_id; ?> .processing').hide();
				$('#<?php echo $dom_id; ?> .submit').show();
				$('#<?php echo $dom_id; ?> form input, #<?php echo $dom_id; ?> form textarea').placeholder();
			}
			
			//scroll up to the success/error message
			$('body,html').animate({scrollTop : ($('#<?php echo $dom_id; ?>').offset().top - 50)}, 200);
		}
	});
	
	$('#<?php echo $dom_id; ?> form input, #<?php echo $dom_id; ?> form textarea').placeholder();
});
</script>

<div id="<?php echo $dom_id; ?>" class="custom-contact-form">

	<div class="success" style="display:<?php echo $show_thanks ? 'block' : 'none'; ?>;">
		<?php echo nl2br($thanks_msg); ?>
	</div>

	<div class="errors" style="display:<?php echo !empty($errors) ? 'block' : 'none'; ?>;">
		<span class="error-header"><?php echo t('Please correct the following errors:'); ?></span>
		<ul class="error-items">
			<?php foreach ($errors as $error): ?>
				<li><?php echo $error; ?></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<form novalidate method="post" action="<?php echo $this->action('submit'); ?>" <?php echo $has_files ? 'enctype="multipart/form-data"' : ''; ?>>
		
		<?php $this->inc($fields_template); ?>
		
		<?php /* Spam honeypot fields
			DEV NOTES about spam honeypot fields:
			The first field must remain blank, and the second field must retain its value.
			The combination of these 2 seems to catch about 90% of spam.

			CAUTION: Don't make a field that is "visuallyhidden" AND has a real-sounding name
			(e.g. "website" or "username"), because some browser toolbars will auto-fill data
			for legitemate users -- see http://news.ycombinator.com/item?id=3300135 and http://news.ycombinator.com/item?id=3301110
			[This doesn't apply to our current situation because we're not using a real-sounding name
			nor are we using the "visuallyhidden" technique... but just sayin' for future reference.]
			*/ ?>
			<div style="display: none;">
				<label>
					<?php echo $honeypot_blank_field_label; ?>
					<input type="text" name="<?php echo $honeypot_blank_field_name; ?>" value="" />
				</label>
			</div>
			<input type="hidden" name="<?php echo $honeypot_retained_field_name; ?>" value="<?php echo $honeypot_retained_field_value; ?>" />
		<?php /* END Spam honeypot fields */ ?>
		
		<div class="processing" style="display: none;">
			<img src="<?php echo ASSETS_URL_IMAGES; ?>/throbber_white_16.gif" width="16" height="16" alt="<?php echo t('form processing indicator'); ?>" />
			<span><?php echo t('Processing...'); ?></span>
		</div>
		
	</form>

</div>

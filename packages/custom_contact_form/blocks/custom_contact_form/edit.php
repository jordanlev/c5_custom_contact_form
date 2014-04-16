<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 2.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

?>
		
<div class="ccm-ui">
	<div class="form-horizontal">
		
		<div class="control-group">
			<label class="control-label" for="thanks_msg"><?php echo t('Thank You Message'); ?></label>
			<div class="controls">
				<?php echo $form->textarea('thanks_msg', $thanks_msg, array('class' => 'input-xxlarge', 'style' => 'height: 80px;')); ?>
			</div>
		</div>	
		
		<div class="control-group">
			<label class="control-label" for="notification_email_from"><?php echo t('Notification Emails "From" Address'); ?></label>
			<div class="controls">
				<?php echo $form->text('notification_email_from', $notification_email_from, array('class' => 'input-xxlarge')); ?>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="notification_email_to"><?php echo t('Send Notification Emails To'); ?></label>
			<div class="controls">
				<?php echo $form->text('notification_email_to', $notification_email_to, array('class' => 'input-xxlarge')); ?>
				<span class="help-block" style="font-style: italic;"><?php echo t('Separate multiple email addresses with commas'); ?></span>
			</div>
		</div>
		
	</div>
</div>

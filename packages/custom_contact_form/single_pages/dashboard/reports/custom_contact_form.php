<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 3.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

$dh = Loader::helper('concrete/dashboard');
$form = Loader::helper('form');
?>


<?php echo $dh->getDashboardPaneHeaderWrapper($c->getCollectionName()); ?>
	
	<?php if (count($forms) > 1): ?>
		<form action="<?php echo $this->action('view'); ?>" method="get" class="js-segment-filter form-inline">
			<label for="form_key"><?php echo t('Form:'); ?></label>
			<?php echo $form->select('form_key', $forms, $form_key); ?>
			<noscript><input type="submit" class="btn ccm-input-submit" value="<?php echo t('Go'); ?>"></noscript>
			<span class="loading-indicator" style="display:none;"><img src="<?php echo ASSETS_URL_IMAGES; ?>/throbber_white_16.gif" width="16" height="16" alt="<?php echo t('loading indicator'); ?>" /></span>
		</form>
		<script>
		$(document).ready(function() {
			$('.js-segment-filter select').on('change', function() {
				var $form = $(this).closest('form');
				$form.find('.loading-indicator').show();
				$form.trigger('submit');
			});
		});
		</script>
	
		<hr>
	<?php endif; ?>
	
	<?php if (!empty($error_no_forms)): ?>
		
		<p style="font-weight: bold; color: red;">
			<?php echo t('Development Error: No forms have been defined in "%s"', '/packages/custom_contact_form/models/custom_contact_form.php'); ?>
		</p>
	
	<?php elseif (!empty($error_invalid_form_key)): ?>
		
		<p style="font-weight: bold; color: red;">
			<?php echo t('Error: Unknown form (could not locate form with key "%s" in "%s")', $form_key, '/packages/custom_contact_form/models/custom_contact_form.php'); ?>
		</p>
		
	<?php elseif (empty($submissions)): ?>
		
		<p><?php echo t('This form has no submissions.'); ?></p>
		
	<?php else: ?>
		
		<form method="post" action="<?php echo $this->action('download', $form_key); ?>">
			<?php echo $token; ?>
			<?php echo $form->submit('download', t('Download All (.csv)'), array('class' => 'primary')); ?>
		</form>
	
		<form id="custom_contact_form_submissions" method="post" action="<?php echo $this->action('delete', $form_key); ?>">
			<?php echo $token; ?>
			<table class="table table-condensed table-striped table-bordered">
				<tr>
					<th><?php echo $form->checkbox('select_all', '1'); ?></th>
					<th><?php echo t('Submitted At'); ?></th>
					<th><?php echo t('From Page'); ?></th>
					<th><?php echo t('IP Address'); ?></th>
					<?php foreach ($fields as $name => $label): ?>
						<th><?php echo $label; ?></th>
					<?php endforeach; ?>
				</tr>

				<?php foreach ($submissions as $submission): ?>
				<tr>
					<td><?php echo $form->checkbox('delete_ids[]', $submission['id']); ?></td>
					<td><?php echo $submission['submitted_at']; ?></td>
					<td><?php
						//don't bother loading collection object for every row -- just cheat and use index.php?cID=xxx urls
						$url = DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=' . $submission['page_cID'];
						echo '<a href="' . $url . '" target="_blank">';
						echo $submission['page_title']; //this has already been html-escaped
						echo '</a>';
					?></td>
					<td><?php echo $submission['ip_address']; ?></td>
					<?php foreach ($fields as $name => $label): ?>
						<td><?php echo $submission['fields'][$name]; /* value has already been html-escaped */ ?></td>
					<?php endforeach; ?>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php echo $form->submit('submit_delete', t('Deleted Checked Items'), array('class' => 'error', 'disabled' => 'disabled')); ?>
		</form>
		
	<?php endif; ?>	
	
<?php echo $dh->getDashboardPaneFooterWrapper(); ?>

<script type="text/javascript">
$(document).ready(function() {
	$('#select_all').on('change', function() {
		$('input[name="delete_ids[]"]').prop('checked', $(this).prop('checked'));
		toggleDeleteButton();
	});
	
	$('input[name="delete_ids[]"]').on('change', function() {
		toggleDeleteButton();
	});
	
	function toggleDeleteButton() {
		var cntRows = $('input[name="delete_ids[]"]').length;
		var cntChecked = $('input[name="delete_ids[]"]:checked').length;
		
		$('#select_all').prop('checked', (cntRows == cntChecked));

		if (cntChecked == 0) {
			$('#submit_delete').attr('disabled', 'disabled');
		} else {
			$('#submit_delete').removeAttr('disabled');
		}
	}
	toggleDeleteButton();
	
	$('#custom_contact_form_submissions').on('submit', function() {
		var cntChecked = $('input[name="delete_ids[]"]:checked').length;
		var msg = '<?php echo t("You are about to permanently delete"); ?> ' + cntChecked + ' <?php echo t("records"); ?>. <?php echo t("This action cannot be undone. Are you sure you wish to continue?"); ?>';
		return confirm(msg);
	});
});
</script>
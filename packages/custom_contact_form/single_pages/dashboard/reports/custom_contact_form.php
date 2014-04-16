<?php defined('C5_EXECUTE') or die("Access Denied.");

$dh = Loader::helper('concrete/dashboard');
$th = Loader::helper('text');
$form = Loader::helper('form');
?>


<?php echo $dh->getDashboardPaneHeaderWrapper($c->getCollectionName()); ?>
	
	<?php if (empty($submissions)): ?>
		
		<p><?php echo t('There are no form submissions at this time.'); ?></p>
		
	<?php else: ?>
		
		<form method="post" action="<?php echo $this->action('download'); ?>">
			<?php echo $token; ?>
			<?php echo $form->submit('download', t('Download All (.csv)'), array('class' => 'primary')); ?>
		</form>
	
		<div style="width: 100%; overflow-x: scroll; margin-bottom: 10px;">
			<form id="custom_contact_form_submissions" method="post" action="<?php echo $this->action('delete'); ?>">
				<?php echo $token; ?>
				<table class="table table-condensed table-striped table-bordered">
					<tr>
						<th><?php echo $form->checkbox('select_all', '1'); ?></th>
						<th><?php echo t('Submitted At'); ?></th>
						<th><?php echo t('IP Address'); ?></th>
						<?php foreach ($fields as $name => $label): ?>
							<th><?php echo $label; ?></th>
						<?php endforeach; ?>
					</tr>

					<?php foreach ($submissions as $submission): ?>
					<tr>
						<td><?php echo $form->checkbox('delete_ids[]', $submission['id']); ?></td>
						<td><?php echo $submission['submitted_at']; ?></td>
						<td><?php echo $submission['ip_address']; ?></td>
						<?php foreach ($fields as $name => $label): ?>
							<td><?php echo nl2br($th->entities($submission['fields'][$name])); ?></td>
						<?php endforeach; ?>
					</tr>
					<?php endforeach; ?>
				</table>
				<?php echo $form->submit('submit_delete', t('Deleted Checked Items'), array('class' => 'error', 'disabled' => 'disabled')); ?>
			</form>
		</div>
		
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
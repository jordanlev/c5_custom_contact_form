<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 2.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

Loader::model('custom_contact_form', 'custom_contact_form');

class DashboardReportsCustomContactFormController extends Controller {
	
	public function on_start() {
		$this->initCSRFToken();
	}
	
	private function initCSRFToken() {
		$token = Loader::helper('validation/token');
		if (!empty($_POST) && !$token->validate()) {
			die($token->getErrorMessage());
		}
		$this->set('token', $token->output('', true));
	}
	
	public function view() {
		$this->set('fields', CustomContactForm::getFieldNamesAndLabelsForDashboardReport());
		$this->set('submissions', CustomContactForm::getSubmissionsForDashboardReport());
	}
	
	public function download() { //In single_pages, do not prepend "action_" (unlike blocks)
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="contact_form_submissions.csv"');

		$fields = CustomContactForm::getFieldNamesAndLabelsForDashboardReport();
		$submissions = CustomContactForm::getSubmissionsForDashboardReport();
		
		//heading row
		echo t('Submitted At') . ',' . t('IP Address') . ',' . implode(',', $fields) . "\n";
		
		//data rows
		foreach ($submissions as $submission) {
			$quoted_values = array(
				'"' . $submission['submitted_at'] . '"',
				'"' . $submission['ip_address'] . '"',
			);
			foreach ($fields as $name => $label) {
				$quoted_values[] = str_replace('"', '""', $submission['fields'][$name]);
			}
			echo implode(',', $quoted_values) . "\n";
		}
		
		exit;
	}
	
	public function delete() { //In single_pages, do not prepend "action_" (unlike blocks)
		$delete_ids = $this->post('delete_ids');
		if (!empty($delete_ids) && is_array($delete_ids)) {
			CustomContactForm::deleteSubmissions($delete_ids);
		}
		$this->redirect('/dashboard/reports/custom_contact_form');
	}
	
}

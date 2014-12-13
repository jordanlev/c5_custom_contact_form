<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 3.0, by Jordan Lev
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
	
	public function view($form_key = null) {
		//If dropdown filter was chosen from dashboard,
		// redirect back to ourselves with the form_key in the url...
		if (!empty($_GET['form_key'])) {
			$this->redirect('/dashboard/reports/custom_contact_form', $_GET['form_key']);
		}
		
		$forms = CustomContactForm::getFormKeysAndTitles();
		$this->set('forms', $forms);
		if (empty($forms)) {
			$this->set('error_no_forms', true);
			return;
		}
		
		$form_key = empty($form_key) ? key($forms) : $form_key; //use first form's key if none provided
		$this->set('form_key', $form_key);
		if (!array_key_exists($form_key, $forms)) {
			$this->set('error_invalid_form_key', true);
			return;
		}
		
		$this->set('fields', CustomContactForm::getFieldNamesAndLabelsForDashboardReport($form_key));
		$this->set('submissions', CustomContactForm::getSubmissionsForDashboardView($form_key));

		$this->addHeaderItem(Loader::helper('html')->css('dashboard.css', 'custom_contact_form'));
	}
	
	public function download($form_key) { //In single_pages, do not prepend "action_" (unlike blocks)
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="' . $form_key . '_form_submissions.csv"');

		$fields = CustomContactForm::getFieldNamesAndLabelsForDashboardReport($form_key);
		$submissions = CustomContactForm::getSubmissionsForDashboardExport($form_key);
		
		//heading row
		echo '"' . t('Submitted At') . '","' . t('From Page') . '","' . t('IP Address') . '","' . implode('","', $fields) . '"' . "\r\n";
		
		//data rows
		foreach ($submissions as $submission) {
			$quoted_values = array(
				'"' . $submission['submitted_at'] . '"',
				'"' . str_replace('"', '""', $submission['page_title']) . '"',
				'"' . $submission['ip_address'] . '"',
			);
			
			foreach ($fields as $name => $label) {
				$value = $submission['fields'][$name];
				
				//convert all CR and LF to CRLF for excel
				$value = str_replace("\r\n", "\n", $value);
				$value = str_replace("\r", "\n", $value);
				$value = str_replace("\n", "\r\n", $value);
				
				//escape quotes
				$value = str_replace('"', '""', $value);
				
				//surround value in quotes
				$value = '"' . $value . '"';
				
				$quoted_values[] = $value;
			}
			echo implode(',', $quoted_values) . "\r\n"; //output this row
		}
		
		exit;
	}
	
	public function delete($form_key = null) { //In single_pages, do not prepend "action_" (unlike blocks)
		//Note that we don't actually need the form key to delete submissions,
		// but we do want it so we know where to redirect back to when we're done.
		
		$delete_ids = $this->post('delete_ids');
		if (!empty($delete_ids) && is_array($delete_ids)) {
			CustomContactForm::deleteSubmissions($delete_ids);
		}
		
		$this->redirect('/dashboard/reports/custom_contact_form', $form_key);
	}
	
}

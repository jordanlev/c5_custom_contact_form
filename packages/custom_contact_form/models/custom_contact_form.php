<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 3.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

class CustomContactForm {
	
	/**
	 * FORM & FIELD DEFINITIONS
	 *
	 * The array key for each form should correspond to a template file in the 
	 * /packages/custom_contact_form/blocks/custom_contact_form/view_form_fields/ directory
	 * (for example, a key of "my_first_form" would require a file named "my_first_form.php").
	 *
	 * The list of 'fields' for each form determines what data will be saved to the database.
	 * Each field in the list must have a key that matches the html input "name"
	 * (as per the form markup), followed by an array of settings.
	 *
	 * Available settings for each field:
	 *
	 *  'label': Human-readable field name that gets displayed in error messages,
	 *           notification emails, and dashboard report.
	 *
	 *  'exclude_from_dashboard': Set this to true if you do not want this field displayed
	 *                            in the dashboard report.
	 *
	 *  'exclude_from_notification': Set this to true if you do not want this field displayed
	 *                               in the notification email.
	 *
	 *  'reply_to': Set this to true on an email field to use the user-supplied address
	 *              as the "reply-to" in notification emails.
	 *
	 *  'required': Set this to true if you want to validate that a value was provided on a form field.
	 *
	 *  'maxlength': By default, all fields are capped at 250 characters
	 *               (to avoid overly-large database records if bots submit extremely large values).
	 *               But you can set this to any number if you want to override the 250 character
	 *               limit (useful for textarea fields).
	 *               If you want no limit, you can set this to 0 or false,
	 *               but I don't recommend that (better to set it to an arbitrarily
	 *               high value, like 5000 or 10000).
	 *
	 *  'email': Set this to true if you want to validate that a submitted value
	 *           is an email address (does a *very* loose validation -- only checks
	 *           for an "@" symbol and a "." period).
	 *
	 * NOTE: This info pertains only to PROCESSING form submissions, NOT the form display!
	 *       It is entirely up to you to output the html markup for each field
	 *       by creating/editing files in the block's /view_form_fields/ folder.
	 */
	public static $forms_and_fields = array(
		'my_first_form' => array(
			'title' => 'My First Form',
			'fields' => array(
				'name' => array('label' => 'Name', 'required' => true),
				'email' => array('label' => 'Email', 'required' => true, 'email' => true, 'reply_to' => true),
				'topic' => array('label' => 'Topic', 'required' => true),
				'message' => array('label' => 'Message', 'maxlength' => 5000),
				'subscribe' => array('label' => 'Subscribe'),
			),
		),
		
		//Here is an example of a 2nd form. You can delete it if you only need 1 form...
		'a_different_form' => array(
			'title' => 'A Different Form',
			'fields' => array(
				'first_name' => array('label' => 'First Name', 'required' => true),
				'last_name' => array('label' => 'Last Name', 'required' => true),
				'email' => array('label' => 'Email', 'required' => true, 'email' => true, 'reply_to' => true),
				'phone' => array('label' => 'Phone #'),
				'address' => array('label' => 'Street Address'),
				'city' => array('label' => 'City'),
				'state' => array('label' => 'State'),
				'zip' => array('label' => 'ZIP Code'),
			),
		),
		
		//3rd form would go here, etc...
		// 'yet_another_form' => array(
		// 	'title' => 'Yet Another Form',
		// 	'fields' => array(
		// 		'whatever' => array('label' => 'Whatever'),
		// 		'etc' => array('label' => 'Etcetera'),
		// 	),
		// ),
	);
	
	
	//Spam honeypot settings (you probably don't need to change these, but you can if you want).
	// Note that while it is tempting to use real-sounding names for the honeypot fields,
	// this can cause problems with peoples' browsers auto-filling saved values
	// (see http://news.ycombinator.com/item?id=3300135 and http://news.ycombinator.com/item?id=3301110).
	public static $honeypot_blank_field_name = 'honeypot1'; //name of hidden field that is empty and should stay empty upon form submission
	public static $honeypot_blank_field_label = 'Leave Blank'; //lets people using screenreaders know that this field should not be filled in
	public static $honeypot_retained_field_name = 'honeypot2'; //name of hidden field that contains a specific value and should retain that value upon form submission
	public static $honeypot_retained_field_value = '7'; //arbitrary value
	
	
///////////////////////////////////////////////////////////////////////////////////////////////////
// YOU PROBABLY WON'T EVER NEED TO CHANGE ANYTHING BELOW HERE
///////////////////////////////////////////////////////////////////////////////////////////////////
	
	public static function getFormKeysAndTitles($sanitize_titles = true) {
		$th = Loader::helper('text');
		$keys_and_titles = array();
		foreach (self::$forms_and_fields as $key => $info) {
			$keys_and_titles[$key] = $sanitize_titles ? $th->entities($info['title']) : $info['title'];
		}
		return $keys_and_titles;
	}
	
	public static function getReplyToFieldName($form_key) {
		$field_name = '';
		foreach (self::$forms_and_fields[$form_key]['fields'] as $name => $field) {
			if (!empty($field['reply_to'])) {
				$field_name = $name;
				break;
			}
		}
		return $field_name;
	}
	
	public static function getFieldNamesAndLabelsForDashboardReport($form_key) {
		$fields = array();
		foreach (self::$forms_and_fields[$form_key]['fields'] as $name => $field) {
			if (empty($field['exclude_from_dashboard'])) {
				$fields[$name] = $field['label'];
			}
		}
		return $fields;
	}
	
	public static function getSubmissionsForDashboardReport($form_key) {
		$sql = 'SELECT *'
		     . ' FROM custom_contact_form_submissions s'
		     . ' INNER JOIN custom_contact_form_submission_fields f'
		     . '   ON s.id = f.submission_id'
		     . ' WHERE s.form_key = ?'
		     . ' ORDER BY s.submitted_at DESC';
		$vals = array($form_key);
		$records = Loader::db()->GetArray($sql, $vals);
		
		$include_field_names = array_keys(self::getFieldNamesAndLabelsForDashboardReport($form_key));

		$submissions = array();
		foreach ($records as $record) {
			$id = $record['id'];
			if (!array_key_exists($id, $submissions)) {
				$submissions[$id] = array(
					'id' => $id,
					'submitted_at' => $record['submitted_at'],
					'ip_address' => $record['ip_address'],
					'page_cID' => $record['page_cID'],
					'page_title' => $record['page_title'],
					'fields' => array_fill_keys($include_field_names, ''), //populate all defined fields with default empty value (in case a saved record is missing a particular field)
				);
			}
			
			$field_name = $record['field_name'];
			if (in_array($field_name, $include_field_names)) {
				$submissions[$id]['fields'][$field_name] = $record['field_value'];
			}
		}
		
		return $submissions;
	}
	
	public static function deleteSubmissions($ids_array) {
		$safe_ids_array = array();
		foreach ($ids_array as $unsafe_id) {
			$safe_id = (int)$unsafe_id;
			if (!empty($safe_id)) {
				$safe_ids_array[] = $safe_id;
			}
		}
		
		if (empty($safe_ids_array)) {
			return;
		}
		
		$safe_ids_string = implode(',', $safe_ids_array);
		
		$db = Loader::db();
		
		$sql = "DELETE FROM custom_contact_form_submission_fields WHERE submission_id IN ({$safe_ids_string})";
		$db->Execute($sql);
		
		$sql = "DELETE FROM custom_contact_form_submissions WHERE id IN ({$safe_ids_string})";
		$db->Execute($sql);
	}

}


class CustomContactFormSubmission {
	private $form_key;
	
	private $submitted_at;
	private $ip_address;
	private $page_cID;
	private $page_title;
	
	private $field_defs = array();
	private $field_values = array();
	
	private $honeypot_blank_field_value = null;
	private $honeypot_retained_field_value = null;
	
	public function __construct($form_key, $post, $page_cID) {
		$this->form_key = $form_key;
		
		$this->submitted_at = date('Y-m-d H:i:s');
		$this->ip_address = Loader::helper('validation/ip')->getRequestIP();
		$this->page_cID = (int)$page_cID;
		$page = Page::getByID($this->page_cID);
		$this->page_title = $page->getCollectionID() ? $page->getCollectionName() : t('[unknown page]');

		$this->field_defs = $this->isFormKeyValid() ? CustomContactForm::$forms_and_fields[$this->form_key]['fields'] : array();
		
		foreach ($post as $name => $value) {
			//"whitelist" the fields we grab from POST (because it is unsafe to save arbitrary data submitted by the user)
			if (array_key_exists($name, $this->field_defs)) {
				$this->field_values[$name] = $value;
			}
		}
		
		if (array_key_exists(CustomContactForm::$honeypot_blank_field_name, $post)) {
			$this->honeypot_blank_field_value = $post[CustomContactForm::$honeypot_blank_field_name];
		}
		
		if (array_key_exists(CustomContactForm::$honeypot_retained_field_name, $post)) {
			$this->honeypot_retained_field_value = $post[CustomContactForm::$honeypot_retained_field_name];
		}
	}
	
	public function validate() {
		$e = Loader::helper('validation/error');
		
		if (!$this->isFormKeyValid()) {
			$e->add(t('Cannot identify form. Please reload the page and try again.'));
		}
		
		foreach ($this->field_defs as $name => $field_def) {
			$field_is_set = array_key_exists($name, $this->field_values);
			$field_is_empty = empty($this->field_values[$name]);
			$field_value = $field_is_set ? $this->field_values[$name] : null;
			$field_label = empty($field_def['label']) ? $name : $field_def['label'];
			
			if (!empty($field_def['required']) && $field_is_empty) {
				$e->add(t('%s is required', $field_label));
			}
			
			if ($field_is_set && !$field_is_empty) {
				$maxlength = array_key_exists('maxlength', $field_def) ? (int)$field_def['maxlength'] : 250;
				if ($maxlength && (strlen($field_value) > $maxlength)) {
					$e->add(t('%s cannot exceed %d characters in length', $field_label, $maxlength));
				}
				
				if (!empty($field_def['email']) && !preg_match("/^\S+@\S+\.\S+$/", $field_value)) { //see: http://stackoverflow.com/questions/201323/what-is-the-best-regular-expression-for-validating-email-addresses#201447
					$e->add(t('%s is not a valid email address', $field_label));
				}
			}
		}
		
		$iph = Loader::helper('validation/ip');
		if (!$iph->check()) {
			$e->add($iph->getErrorMessage());
		}
		
		if (!empty($this->honeypot_blank_field_value)) {
			$e->add(t('ERROR: You must leave the "%s" field blank (this helps us prevent spam)', CustomContactForm::$honeypot_blank_field_label));
		}
		
		if ($this->honeypot_retained_field_value !== CustomContactForm::$honeypot_retained_field_value) {
			$e->add('Internal Server Error'); //don't give a descriptive error message for this -- it's most likely a spambot
		}
		
		//Note that we don't have to validate CSRF tokens ourselves
		// because C5 handles it for us via the $this->action() function.

		//Note that we don't validate page_cID because it's not essential information.
		
		return $e;
	}
	
	public function save() {
		$db = Loader::db();
		
		$submission_data = array(
			'form_key' => $this->form_key,
			'submitted_at' => $this->submitted_at,
			'ip_address' => $this->ip_address,
			'page_cID' => $this->page_cID,
			'page_title' => $this->page_title,
		);
		
		$db->AutoExecute('custom_contact_form_submissions', $submission_data, 'INSERT');
		$id = $db->Insert_ID();
		
		$sql = 'DELETE FROM custom_contact_form_submission_fields WHERE submission_id = ?';
		$vals = array((int)$id);
		$db->Execute($sql, $vals);
		foreach ($this->field_values as $name => $value) {
			//no need to check against the field definitions
			// because fields were already "whitelisted" in the constructor
			$field_data = array(
				'submission_id' => (int)$id,
				'field_name' => $name,
				'field_value' => $value,
			);
			$db->AutoExecute('custom_contact_form_submission_fields', $field_data, 'INSERT');
		}
	}
	
	public function getFormKey() { return $this->form_key; }
	public function getSubmittedAt() { return $this->submitted_at; }
	public function getIPAddress() { return $this->ip_address; }
	public function getPageCID() { return $this->page_cID; }
	public function getPageTitle() { return $this->page_title; }
	
	//Returns array of fields, each of which is an array containing a 'label' and a 'value'.
	//Fields marked as 'exclude_from_notification' are excluded.
	public function getNotificationEmailFields() {
		$fields = array();
		foreach ($this->field_defs as $name => $field_def) {
			if (empty($field_def['exclude_from_notification'])) {
				$fields[$name] = array(
					'label' => $field_def['label'],
					'value' => $this->field_values[$name],
				);
			}
		}
		return $fields;
	}
	
	//Returns the user-submitted "reply-to" address
	// (or empty string if no fields are designated with the 'reply-to' setting).
	public function getNotificationEmailReplyTo() {
		$field_name = CustomContactForm::getReplyToFieldName($this->form_key);
		return (empty($field_name) ? '' : $this->field_values[$field_name]);
	}
	
	public function getFormTitle() {
		return $this->isFormKeyValid() ? CustomContactForm::$forms_and_fields[$this->form_key]['title'] : t('[unknown form]');
	}
	
	private function isFormKeyValid() {
		return !empty($this->form_key) && array_key_exists($this->form_key, CustomContactForm::$forms_and_fields);
	}
	
}

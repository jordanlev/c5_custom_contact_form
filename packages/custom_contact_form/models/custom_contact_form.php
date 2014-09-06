<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 2.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

class CustomContactForm {
	
	/**
	 * FIELD DEFINITIONS
	 *
	 * This is where you set which fields are saved to the database,
	 * how they're validated, and how they're displayed in error messages,
	 * notification emails, and the dashboard report (but not how they're displayed
	 * in the form itself -- see NOTE below).
	 *
	 * Each field in the list must have a key that matches the input "name"
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
	 * NOTE: These definitions only pertain to PROCESSING form submissions -- NOT the form display!
	 *       It is entirely up to you to output the html markup for each field
	 *       in the block's view_form_fields.php file.
	 */
	public static $fields = array(
		'name' => array('label' => 'Name', 'required' => true),
		'email' => array('label' => 'Email', 'required' => true, 'email' => true, 'reply_to' => true),
		'topic' => array('label' => 'Topic', 'required' => true),
		'message' => array('label' => 'Message', 'maxlength' => 5000),
		'subscribe' => array('label' => 'Subscribe'),
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
	
	public static function getReplyToFieldName() {
		$field_name = '';
		foreach (self::$fields as $name => $field) {
			if (!empty($field['reply_to'])) {
				$field_name = $name;
				break;
			}
		}
		return $field_name;
	}
	
	public static function getFieldNamesAndLabelsForDashboardReport() {
		$fields = array();
		foreach (self::$fields as $name => $field) {
			if (empty($field['exclude_from_dashboard'])) {
				$fields[$name] = $field['label'];
			}
		}
		return $fields;
	}
	
	public static function getSubmissionsForDashboardReport() {
		$sql = 'SELECT *'
		     . ' FROM custom_contact_form_submissions s'
		     . ' INNER JOIN custom_contact_form_submission_fields f'
		     . '   ON s.id = f.submission_id'
		     . ' ORDER BY s.submitted_at DESC';
		$records = Loader::db()->GetArray($sql);
		
		$include_field_names = array_keys(self::getFieldNamesAndLabelsForDashboardReport());

		$submissions = array();
		foreach ($records as $record) {
			$id = $record['id'];
			if (!array_key_exists($id, $submissions)) {
				$submissions[$id] = array(
					'id' => $id,
					'submitted_at' => $record['submitted_at'],
					'ip_address' => $record['ip_address'],
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
	private $fields = array();
	private $honeypot_blank_field_value = null;
	private $honeypot_retained_field_value = null;
	
	public function __construct($post) {
		foreach ($post as $name => $value) {
			//"whitelist" the fields we grab from POST (because it is unsafe to save arbitrary data submitted by the user)
			if (array_key_exists($name, CustomContactForm::$fields)) {
				if (is_array($value)) {
					$value = implode(", ", $value);
				}
				
				$this->fields[$name] = $value;
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
		
		foreach (CustomContactForm::$fields as $name => $field) {
			$field_is_set = array_key_exists($name, $this->fields);
			$field_is_empty = empty($this->fields[$name]);
			$field_value = $field_is_set ? $this->fields[$name] : null;
			$field_label = empty($field['label']) ? $name : $field['label'];
			
			if (!empty($field['required']) && $field_is_empty) {
				$e->add(t('%s is required', $field_label));
			}
			
			if ($field_is_set && !$field_is_empty) {
				$maxlength = array_key_exists('maxlength', $field) ? (int)$field['maxlength'] : 250;
				if ($maxlength && (strlen($field_value) > $maxlength)) {
					$e->add(t('%s cannot exceed %d characters in length', $field_label, $maxlength));
				}
				
				if (!empty($field['email']) && !preg_match("/^\S+@\S+\.\S+$/", $field_value)) { //see: http://stackoverflow.com/questions/201323/what-is-the-best-regular-expression-for-validating-email-addresses#201447
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
		
		return $e;
	}
	
	public function save() {
		$db = Loader::db();
		
		$submission_data = array(
			'submitted_at' => date('Y-m-d H:i:s'),
			'ip_address' => Loader::helper('validation/ip')->getRequestIP(),
		);
		
		$db->AutoExecute('custom_contact_form_submissions', $submission_data, 'INSERT');
		$id = $db->Insert_ID();
		
		$sql = 'DELETE FROM custom_contact_form_submission_fields WHERE submission_id = ?';
		$vals = array((int)$id);
		$db->Execute($sql, $vals);
		foreach ($this->fields as $name => $value) {
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
	
	//Returns array of fields, each of which is an array containing a 'label' and a 'value'.
	//Fields marked as 'exclude_from_notification' are excluded.
	public function getNotificationEmailFields() {
		$fields = array();
		foreach (CustomContactForm::$fields as $name => $field) {
			if (empty($field['exclude_from_notification'])) {
				$fields[$name] = array(
					'label' => $field['label'],
					'value' => $this->fields[$name],
				);
			}
		}
		return $fields;
	}
	
	//Returns the user-submitted "reply-to" address
	// (or empty string if no fields are designated with the 'reply-to' setting).
	public function getNotificationEmailReplyTo() {
		$field_name = CustomContactForm::getReplyToFieldName();
		return (empty($field_name) ? '' : $this->fields[$field_name]);
	}
}

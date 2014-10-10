<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 3.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

Loader::model('custom_contact_form', 'custom_contact_form');

class CustomContactFormBlockController extends BlockController {
	
	protected $btName = 'Contact Form';
	protected $btTable = 'btCustomContactForm';
	protected $btInterfaceWidth = "750";
	protected $btInterfaceHeight = "250";
	
	protected $btCacheBlockRecord = true;
	//Do not cache the output -- it will prevent thanks/error messages from being displayed!
	protected $btCacheBlockOutput = false;
	protected $btCacheBlockOutputOnPost = false;
	protected $btCacheBlockOutputForRegisteredUsers = false;
	protected $btCacheBlockOutputLifetime = CACHE_LIFETIME;
	
	
	public function on_page_view() {
		$this->addFooterItem(Loader::helper('html')->javascript('jquery.form.js'));
	}
	
	public function view() {
		$fields_template_relative_path = "/view_form_fields/{$this->form_key}.php";
		$fields_template_absolute_path = dirname(__FILE__) . $fields_template_relative_path;
		if (!file_exists($fields_template_absolute_path)) {
			throw new Exception(t('Custom Contact Form Error: Missing form fields template file %s', $fields_template_absolute_path));
		}
		$this->set('fields_template', $fields_template_relative_path);
		
		$has_files = CustomContactForm::hasFileFields($this->form_key);
		$this->set('has_files', $has_files);
		
		$this->set('showThanks', (!empty($_GET['thanks']) && ($_GET['thanks'] == $this->bID)));
		
		$this->set('honeypot_blank_field_name', CustomContactForm::$honeypot_blank_field_name);
		$this->set('honeypot_blank_field_label', CustomContactForm::$honeypot_blank_field_label);
		$this->set('honeypot_retained_field_name', CustomContactForm::$honeypot_retained_field_name);
		$this->set('honeypot_retained_field_value', CustomContactForm::$honeypot_retained_field_value);
	}
		
	//Validate the block add/edit dialog (this is *NOT* for the front-end form)
	public function validate($args) {
	    $e = Loader::helper('validation/error');
     	
		if (empty($args['form_key'])) {
			$e->add(t('You must choose a form.'));
		}

		if (empty($args['thanks_msg'])) {
			$e->add(t('You must enter a Thank-You Message.'));
		}
		
		if (empty($args['notification_email_from'])) {
			$e->add(t('You must enter a "Notification From" Email Address.'));
		}
		
		if (empty($args['notification_email_to'])) {
			$e->add(t('You must enter at least one "Notification To" Email Address.'));
		}
     	
	    return $e;
	}
	
	public function add() {
		$this->setAvailableForms();
	}
	public function edit() {
		$this->setAvailableForms();
	}
	private function setAvailableForms() {
		$this->set('available_forms', CustomContactForm::getFormKeysAndTitles());
	}
	
	
/*** FRONT-END PROCESSING ***/
	public function action_submit() {
		$page_cID = Page::getCurrentPage()->getCollectionID(); //do this instead of asking the block object for its page, because the block could be on a page defaults or in a stack!
		$error = $this->processForm($page_cID);
		if ($error->has()) {
			$this->set('errors', $error->getList());
		} else {
			$this->successRedirect();
		}
	}

	//Note that this function could be called either by this controller's action_submit() method
	// OR by the ajax tool. We must have page_cID passed in because the ajax tool cannot call
	// Page::getCurrentPage() (so it must get the cID itself via form hidden field).
	public function processForm($page_cID = 0) {
		$submission = new CustomContactFormSubmission($this->form_key, $page_cID);
		$error = $submission->validate();
		if (!$error->has()) {
			$submission->save();
			$this->sendNotificationEmail($submission);
		}
		return $error;
	}

	private function sendNotificationEmail($submission) {
		$mh = Loader::helper('mail');

		$mh->to($this->notification_email_to);
		$mh->from($this->notification_email_from);
		$reply_to = $submission->getNotificationEmailReplyTo();
		if ($reply_to) {
			$mh->replyto($reply_to);
		}

		$mh->addParameter('form_title', $submission->getFormTitle());
		$mh->addParameter('timestamp', strtotime($submission->getSubmittedAt()));
		$mh->addParameter('page_title', $submission->getPageTitle());
		$mh->addParameter('dashboard_url', BASE_URL . View::url('/dashboard/reports/custom_contact_form', $submission->getFormKey()));
		$mh->addParameter('fields', $submission->getNotificationEmailFieldLabelsAndValues());
		$mh->load('admin_notify', 'custom_contact_form');

		@$mh->sendMail(); 
	}
	
	private function successRedirect() {
		//Redirect back to this same page to avoid resubmit-on-reload problem.
		$redirect = Loader::helper('navigation')->getCollectionURL(Page::getCurrentPage()); //Get absolute url (location headers should be absolute URL's -- see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.30)
		$redirect .= (strstr($redirect, '?') ? '&' : '?') . 'thanks=' . $this->bID;
		header("Location: " . $redirect);
		die;
	}
}

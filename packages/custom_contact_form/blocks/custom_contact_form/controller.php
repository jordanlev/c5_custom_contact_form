<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 2.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

Loader::model('custom_contact_form', 'custom_contact_form');

class CustomContactFormBlockController extends BlockController {
	
	protected $btDescription = "Custom Contact Form";
	protected $btName = "Custom Contact Form";
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
		$this->set('showThanks', !empty($_GET['thanks']));
		
		$this->set('honeypot_blank_field_name', CustomContactForm::$honeypot_blank_field_name);
		$this->set('honeypot_blank_field_label', CustomContactForm::$honeypot_blank_field_label);
		$this->set('honeypot_retained_field_name', CustomContactForm::$honeypot_retained_field_name);
		$this->set('honeypot_retained_field_value', CustomContactForm::$honeypot_retained_field_value);
	}
		
	//Validate the block add/edit dialog (this is *NOT* for the front-end form)
	public function validate($args) {
	    $e = Loader::helper('validation/error');
     	
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
	
	
/*** FRONT-END PROCESSING ***/
	public function action_submit() {
		$this->processForm(false);
	}
	
	public function processForm($is_ajax = false) {
		$submission = new CustomContactFormSubmission($_POST);
		$error = $submission->validate();
		if ($error->has()) {
			if ($is_ajax) {
				return $error->getList();
			} else {
				$this->set('errors', $error->getList());
			}
		} else {
			$submission->save();
			$this->sendNotificationEmail($submission);
			if ($is_ajax) {
				return null;
			} else {
				$this->successRedirect();
			}
		}
	}
	
	private function sendNotificationEmail($submission) {
		$mh = Loader::helper('mail');
		$mh->to($this->notification_email_to);
		$mh->from($this->notification_email_from);
		$reply_to = $submission->getNotificationEmailReplyTo();
		if ($reply_to) {
			$mh->replyto($reply_to);
		}
		$mh->addParameter('fields', $submission->getNotificationEmailFields());
		$mh->load('admin_notify', 'custom_contact_form');
		@$mh->sendMail(); 
	}

	private function successRedirect() {
		//Redirect back to this same page to avoid resubmit-on-reload problem.
		$redirect = Loader::helper('navigation')->getCollectionURL(Page::getCurrentPage()); //Get absolute url (location headers should be absolute URL's -- see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.30)
		$redirect .= (strstr($redirect, '?') ? '&' : '?') . 'thanks=1';
		header("Location: " . $redirect);
		die;
	}
}

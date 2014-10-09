<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 3.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

class CustomContactFormPackage extends Package {

	protected $pkgHandle = 'custom_contact_form';
	public function getPackageName() { return t('Custom Contact Form'); }
	public function getPackageDescription() { return t('Highly customizable contact forms.'); }
	protected $appVersionRequired = '5.6';
	protected $pkgVersion = '3.0';
	
	public function install() {
		$pkg = parent::install();
		
		//Install block
		BlockType::installBlockTypeFromPackage('custom_contact_form', $pkg);
		
		//Install dashboard page
		Loader::model('single_page');
		$p = SinglePage::add('/dashboard/reports/custom_contact_form', $pkg);
		$p->update(array('cName' => t('Contact Form Submissions')));
		$p->setAttribute('icon_dashboard', 'icon-list-alt');
	}
	
	public function uninstall() {
		parent::uninstall();
		$db = Loader::db();
		$db->Execute('DROP TABLE btCustomContactForm'); //Do NOT drop package-level tables -- we don't want to accidentally lose submission data!
	}

}

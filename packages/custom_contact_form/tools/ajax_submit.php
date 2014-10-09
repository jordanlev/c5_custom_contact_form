<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 2.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

$vth = Loader::helper('validation/token');
if (!$vth->validate()) {
	$errors = array(t('Invalid form submission -- please reload the page and try again.'));
} else if (empty($_POST['bID']) || !(int)$_POST['bID']) {
	$errors = array(t('Invalid form submission. Please reload the page and try again.')); //slightly different message to help with debugging in case of error
} else {
	$b = Block::GetById($_POST['bID']);
	if ($b) {
		$bc = new CustomContactFormBlockController($b);
		$cID = empty($_POST['cID']) ? 0 : (int)$_POST['cID'];
		$errors = $bc->processForm($cID)->getList(); //processForm() returns a c5 error object, on which we can call the getList() function
	} else {
		$errors = array(t('Invalid form submission. Please reload the page and try again'));
	}
}

//Send response
$response = array(
	'success' => empty($errors),
	'errors' => $errors,
);
echo Loader::helper('json')->encode($response);

exit;

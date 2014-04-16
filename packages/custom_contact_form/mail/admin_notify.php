<?php defined('C5_EXECUTE') or die("Access Denied.");

$subject = '['.SITE.'] New Contact Form Submission';

$body = "A new submission has been made to the contact form:\n\n";
foreach ($fields as $name => $field) {
	$body .= "{$field['label']}: {$field['value']}\n";
}

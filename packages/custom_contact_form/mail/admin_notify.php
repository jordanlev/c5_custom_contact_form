<?php defined('C5_EXECUTE') or die("Access Denied.");

$subject = 'New Website Form Submission: ' . $form_title;

$body = 'Submitted on ' . date('n/j/y \a\t g:ia', $timestamp) . ' from page "' . $page_title . '":' . "\n\n";
foreach ($fields as $name => $field) {
	$body .= "{$field['label']}: {$field['value']}\n";
}
$body .= "\n";
<?php defined('C5_EXECUTE') or die("Access Denied.");
$th = Loader::helper('text');

$subject = 'New Website Form Submission: ' . $form_title;

$body = 'Submitted on ' . date('n/j/y \a\t g:ia', $timestamp) . ' from page "' . $page_title . '":' . "\n\n";
$bodyHTML = '<p>' . $th->entities($body) . '</p><table><tbody>' . "\n";

foreach ($fields as $name => $field) {
	$body .= "{$field['label']}: {$field['value']}\n";
	$bodyHTML .= '<tr><td align="right">' . $th->entities($field['label']) . ':</td><td>' . $th->entities($field['value']) . '</td></tr>'. "\n";
}
$body .= "\n";
$bodyHTML .= "</tbody></table>\n";

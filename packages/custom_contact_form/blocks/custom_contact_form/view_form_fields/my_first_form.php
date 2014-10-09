<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 3.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

$form = Loader::helper('form');

/* DEV NOTES:
 *  ~ You don't need to populate field values upon validation failure re-display,
 *    because C5 form helpers do that automatically for us.
 *  ~ If you're using placeholders in lieu of visible labels,
 *    you should still have a <label> that is "visuallyhidden" for screenreaders,
 *    AND another <label> inside a <noscript> tag for non-JS people on browsers
 *    that lack native support for the placeholder attribute!
 *  ~ Note that we're not using the C5 form helper for the submit button,
 *    because we don't want a "name" attribute on it. If you want to add a name attribute,
 *    be careful not to use the name "submit", as this might cause problems with javascript
 *    (because it trounces the built-in form.submit() method)!
 */
?>

<?php echo $form->label('name', 'Name:'); ?>
<?php echo $form->text('name', null, array('placeholder' => 'Name')); ?>

<br />

<?php echo $form->label('email', 'Email:'); ?>
<?php echo $form->text('email', null, array('placeholder' => 'Email')); ?>

<br />

<?php echo $form->label('topic', 'Choose:'); ?>
<?php
$topic_options = array(
	'' => '-- Choose One --',
	'First Topic' => 'First Topic',
	'Second Topic' => 'Second Topic',
	'Third Topic' => 'Third Topic',
);
echo $form->select('topic', $topic_options, null, array('placeholder' => 'Choose'));
?>

<br />

<?php echo $form->label('message', 'Message:'); ?>
<?php echo $form->textarea('message', null, array('placeholder' => 'Message')); ?>

<br />

<input type="hidden" name="subscribe" value="no">
<?php echo $form->checkbox('subscribe', 'yes', null, array('placeholder' => 'Sign Up For Our Newsletter')); ?>
<?php echo $form->label('subscribe', 'Sign Up For Our Newsletter'); ?>

<br />

<input type="submit" value="Submit" />

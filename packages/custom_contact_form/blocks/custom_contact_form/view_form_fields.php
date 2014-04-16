<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * Custom Contact Form version 2.0, by Jordan Lev
 *
 * See https://github.com/jordanlev/c5_custom_contact_form for instructions
 */

$form = Loader::helper('form');

/* DEV NOTES:
 *  ~ If you add a file upload to this form, remember to add enctype="multipart/form-data"
 *    to the form tag (in view.php)! You should also disable ajax functionality
 *    because it probably won't work with file uploads.
 *  ~ You don't need to populate field values upon validation failure re-display,
 *    because C5 form helpers do that automatically for us.
 *  ~ If you're using placeholders in lieu of visible labels,
 *    you should still have a <label> that is "visuallyhidden" for screenreaders,
 *    AND another <label> inside a <noscript> tag for non-JS people on browsers
 *    that lack native support for the placeholder attribute!
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

<?php echo $form->checkbox('subscribe', 'yes', null, array('placeholder' => 'Sign Up For Our Newsletter')); ?>
<?php echo $form->label('subscribe', 'Sign Up For Our Newsletter'); ?>

<br />

<?php echo $form->submit('submit', 'Submit'); ?>
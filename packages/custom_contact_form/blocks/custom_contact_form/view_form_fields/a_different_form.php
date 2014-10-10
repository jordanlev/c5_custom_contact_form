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


<label>
	*Name:
	<?php echo $form->text('first_name', null, array('placeholder' => 'First')); ?>
	<?php echo $form->text('last_name', null, array('placeholder' => 'Last')); ?>
</label>

<br />

<label>
	*Email:
	<?php echo $form->text('email'); ?>
</label>

<br />

<label>
	Phone:
	<?php echo $form->text('phone'); ?>
</label>

<br />

<label>
	Street Address:
	<?php echo $form->text('address'); ?>
</label>

<br />

<label>
	City, State, ZIP:
	<?php echo $form->text('city', null, array('placeholder' => 'City')); ?>
	<?php echo $form->text('state', null, array('style' => 'width: 45px;', 'maxlength' => '2', 'placeholder' => 'State')); ?>
	<?php echo $form->text('zip', null, array('style' => 'width: 100px;', 'maxlength' => '10', 'placeholder' => 'ZIP')); ?>
</label>

<br />

<label>
	Project Proposal:
	<?php echo $form->file('proposal'); ?>
</label>

<br>

<input type="submit" value="Submit" />

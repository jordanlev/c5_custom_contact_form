# Custom Contact Form

A Concrete5 package for designers who build custom sites. It is **not** a "form builder" (like the built-in "form" block or like the form addons in the marketplace) because it does not allow the people editing the site to configure the form fields. Instead, it gives you (the designer) complete control over the configuration and display of the form fields. All form processing and reporting is handled by the package... all you have to do is tell it which fields you want (via code) and what the markup is for the form (via html).

## Why would I use this?
Use this when you want to have complete control over the styling of your site's contact form, but don't need to give users the ability to configure form fields. This is definitely not perfect for every situation -- you will need to weigh the pros and cons depending on the requirements of your project.

## Features
It is up to you (the designer) to write the form field markup, but everything else is handled automatically by the addon:

* Block can be placed on any page in the site
* Basic form field validation (required, max length, email format) with error message display
* User-customizable "thank-you" message upon success
* Ajax form submission (but also completely functional without javascript enabled)
* Basic spam honeypot using hidden empty fields (no ugly CAPTCHA!)
* Email notifications (with a customizable email template)
* One form field can be designated as the "reply-to" address for notification emails
* Dashboard reporting of all form submissions (with the ability to delete old submissions when the list grows too large)
* CSV download of all submissions from dashboard

## Limitations
The biggest limitation (other than the fact that users cannot configure form fields themselves) is that this package only gives you 1 contact form. You can place the contact form block on as many pages of the site as you want, but each of those blocks will always display the same exact form (and all form submissions get logged to the same list). If you need to display different custom forms on your site, you'll need to copy the package and change the various names and handles -- see instructions below.

## Installation / Customization

### Required Steps
1. Move the `custom_contact_form` directory from this repo's `packages` directory
   to your site's top-level `packages` directory (note: this is an entire package,
   not just a block -- so you must install the entire package and not just the block).

2. Define form fields and validation rules by modifying the package's `/models/custom_contact_form.php` file.

3. Customize form markup by modifying the package's `/blocks/custom_contact_form/view_form_fields.php` file.


### Optional Steps
4. If you want to change the template for the notification email, modify the package's `/mail/admin_notify.php`.

5. If you're not using "placeholders" in your form fields html,
  delete the package's `/blocks/custom_contact_form/js/jquery.placeholder.min.js` file,
  and delete the two lines of code containing the word "placeholder"
  in the package's `/blocks/custom_contact_form/view.php` file.

6. Add a `view.css` file to the package's `/blocks/custom_contact_form/` directory
   (although I recommend just putting the form styles in your theme css instead to reduce http requests).

7. If you want to change the name of the package and/or block
   (which I don't recommend unless you need two different custom form blocktypes on one site):
   * Change these file/directory names:
      * `packages/custom_contact_form/`
      * `packages/custom_contact_form/blocks/custom_contact_form/`
      * `packages/custom_contact_form/controllers/dashboard/reports/custom_contact_form.php`
      * `packages/custom_contact_form/single_pages/dashboard/reports/custom_contact_form.php`
   * Then find/replace the following strings in the package code with appropriate replacements:
      * "custom_contact_form"
      * "Custom Contact Form"
      * "CustomContactForm"
      * "custom-contact-form"

# Custom Contact Form

A Concrete5 package for designers who build custom sites. It is **not** a "form builder" (like the built-in "form" block or like the form addons in the marketplace) because it does not allow the people editing the site to configure the form fields. Instead, it gives you (the designer) complete control over the configuration and display of the form fields. All form processing and reporting is handled by the package... all you have to do is tell it which fields you want (via code) and what the markup is for the form (via html).

## Why would I use this?
Use this when you want to have complete control over the styling of your site's contact form, but don't need to give users the ability to configure form fields. This is definitely not perfect for every situation -- you will need to weigh the pros and cons depending on the requirements of your project.

## Features
It is up to you (the designer) to write the form field markup, but everything else is handled automatically by the addon:

* Block can be placed on any page in the site
* Multiple custom forms per site
* Basic form field validation (required, max length, email format) with error message display
* File uploads (saved to a file set)
* User-customizable "thank-you" message upon success
* Ajax form submission (but also completely functional without javascript enabled)
* Basic spam honeypot using hidden empty fields (no ugly CAPTCHA!)
* Akismet (spam filtering service) integration
* Email notifications (with a customizable email template)
* One form field can be designated as the "reply-to" address for notification emails
* Dashboard reporting of all form submissions (with the ability to delete old submissions when the list grows too large)
* CSV download of all submissions from dashboard

## Installation / Customization

### Required Steps
1. Move the `custom_contact_form` directory from this repo's `packages` directory
   to your site's top-level `packages` directory.

2. Install the addon via Dashboard > Extend Concrete5 > Install.

3. Define forms, fields and validation rules by modifying the package's `/models/custom_contact_form.php` file.

4. Customize form markup by adding/editing template files in the package's `/blocks/custom_contact_form/view_form_fields/` directory. (Note that template file names must match the "form key", e.g. the `my_example` form must have template file named `my_example.php`).

### Optional Steps
4. If you have any file upload fields, create the file set that they should be saved to
  and then set permissions on that file set so only admin users can view its files
  ("advanced permissions" must be enabled in order for you to set permissions on the file set
  as a whole -- I know it's not ideal to have to enable this just for one little thing,
  but if you don't do this then all uploaded files will be publicly viewable!)

5. If you want to change the template for the notification email, modify the package's `/mail/admin_notify.php`.

6. If you're not using "placeholders" in your form fields html,
  delete the package's `/blocks/custom_contact_form/js/jquery.placeholder.min.js` file,
  and delete the two lines of code containing the word "placeholder"
  in the package's `/blocks/custom_contact_form/view.php` file.

7. Add a `view.css` file to the package's `/blocks/custom_contact_form/` directory
  (although I recommend just putting the form styles in your theme css instead to reduce http requests).

8. To enable [Akismet](https://akismet.com/) for spam filtering, you must get an API key from them, then add this to your site's `config/site.php` file: `define('CUSTOM_CONTACT_FORM_AKISMET_API_KEY', 'your-api-key-goes-here');`.


## Notes
* If you are already using an older version of this package on your site,
  note that it is **NOT** easily upgradeable to this latest version 3.0.
  You should only use this package on sites that don't already have
  an older version of `custom_contact_form` installed.
* The block has caching disabled (because otherwise errors and success messages
  do not get displayed). This might prevent Concrete5's "full page chacheing"
  from working (depending on how you have caching set in the dashboard),
  so depending on your caching strategy you might not want to put this form
 on every page of your site.

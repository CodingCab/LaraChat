<?php

namespace Tests\Browser\Routes\Settings;

use App\Models\MailTemplate;
use Tests\DuskTestCase;
use Throwable;

class MailTemplatesPageTest extends DuskTestCase
{
    private string $uri = '/settings/mail-templates';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->testUser->assignRole('admin');

        $this->browser()
            ->loginAs($this->testUser)
            ->visit('dashboard');

        $this->startRecording('How to change mail notification template?');

        $this->say('Hi Guys! I will demonstrate how to change mail notification template.');

        $this->say('First, go to the "Settings" page by clicking the menu icon in the top right corner and selecting "Settings".');

        $this->clickButton('#dropdownMenu');
        $this->clickButton('#menu_settings_link');

        $this->say('Next, click "Email Templates" to access the email template settings.');

        $this->clickButton('#setting-mail-templates');

        $this->say('Here, you can see all the available templates.');
        $this->say('You can edit a template by clicking the edit icon.');

        $mailTemplate = MailTemplate::first();
        $this->clickButton('#edit-' . $mailTemplate->id);

        $this->say('Fill in the fields');

        $this->type('#edit-to', '');
        $this->say('"To": Enter the recipient’s email address. This field is usually left blank if the email system automatically assigns recipients.');

        $this->type('#edit-reply_to', '');
        $this->say('Reply To": Enter an email address where recipients can send replies.');

        $this->type('#edit-subject', '');
        $this->say('"Subject": Modify the email subject. You can use variables (e.g., #{{variables.product.name}}) to insert dynamic content.');

        $this->type('#edit-html_template', '');
        $this->say('"HTML Template": Edit the email’s HTML content. This is where you design the email layout, styles, and content.');

        $this->type('#edit-text_template', '');
        $this->say('"Text Template": Provide a plain text version of the email for recipients who may not support HTML emails.');

        $this->say('Save or Cancel Changes');
        $this->say('Click Save to apply the changes.');
        $this->say('Click Cancel if you do not want to save the edits.');

        $this->clickButton('#edit-save');

        $this->say('To preview an email template, simply click on one of the templates.');
        $this->clickButton('#preview-link-' . $mailTemplate->id);

        $this->visit(route('settings.mail_template_preview', $mailTemplate), $this->testUser);
        $this->say('Than you can see the preview template in new tab');

        $this->stopRecording();
    }
}

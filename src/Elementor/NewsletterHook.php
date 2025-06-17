<?php

namespace BeeComm\Elementor;

final class NewsletterHook
{
    public function register(): void
    {
        add_action('elementor_pro/forms/new_record', [$this, 'handleFormSubmission'], 10, 2);
    }

    public function handleFormSubmission($record, $handler): void
    {
        // Make sure it's the right form
        $formName = $record->get_form_settings('form_name');
        if ($formName !== 'newsletter_signup') {
            return;
        }

        $rawFields = $record->get('fields');
        $email = $rawFields['email']['value'] ?? null;

        if ($email && is_email($email)) {
            // Example: add email to a mailing list or log
            // newsletter_add_to_list($email);
            error_log("Newsletter signup: {$email}");
        }
    }
}

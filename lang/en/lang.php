<?php return [
    'plugin' => [
        'name' => 'Messaging',
        'description' => ''
    ],
    'permissions' => [
        'view_messages' => 'View messages',
    ],
    'calendar' => [
        'select_events' => 'Select the events that concern this message',
    ],
    'models' => [
        'settings' => [
            'play_sound' => 'Play a sound when a message arrives (like Facebook)',
            'settings' => 'Messaging Settings',
            'settings_description' => 'Manage messaging based settings',
            'your_email_notifications' => 'Your Email Notifications',
        ],
        'conversation' => [
            'label' => 'Conversation',
            'label_plural' => 'Conversations',
            'groups_functionality_not_complete' => 'Groups functionality not complete',
            'busy' => 'Our team is busy',
            'no_friends' => 'No friends? Maybe try talking to people...',
            'start_conversation' => 'Click on +Conversation above to start a conversation',
            'empty' => 'No Conversations',
            'inbox' => 'Inbox',

            'sorting' => [
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'email' => 'Email',
                'date' => 'Date',
                'count' => 'Count',
                'type' => 'Type',
            ],
        ],
        'message' => [
            'reply' => 'Reply',
            'send' => 'Send',
            'save_draft' => 'Save draft',
        ],
        'calendar' => [
            'select_events' => 'Select events',
        ],
        'user' => [
            'acornassociated_imap_section' => 'IMAP mailbox connection settings',
            'acornassociated_imap_comment' => 'This Messaging plugin is an IMAP email client for reading and sending emails. Below are the <a target="_blank" href="https://en.wikipedia.org/wiki/Internet_Message_Access_Protocol">IMAP</a> settings. Check your email provider, like <a target="_blank" href="https://support.google.com/mail/answer/7126229?hl=en#zippy=%2Cstep-change-smtp-other-settings-in-your-email-client">gmail.com</a>, for the correct setup. If you are already using an email client, like <a target="_blank" href="https://en.wikipedia.org/wiki/Mozilla_Thunderbird">Thunderbird</a>, then you can check the setup in its Accounts section.',
            'acornassociated_smtp_section' => 'SMTP settings',
            'acornassociated_smtp_comment' => 'Outgoing email using the <a target="_blank" href="https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol">SMTP</a> protocol.',
            'acornassociated_general_section' => 'General settings',
            'acornassociated_imap_username' => 'IMAP username',
            'acornassociated_imap_password' => 'IMAP password',
            'acornassociated_imap_server' => 'IMAP server',
            'acornassociated_imap_port' => 'IMAP port',
            'acornassociated_imap_protocol' => 'IMAP protocol',
            'acornassociated_imap_encryption' => 'IMAP encryption',
            'acornassociated_imap_authentication' => 'IMAP authentication',
            'acornassociated_imap_validate_cert' => 'IMAP validate SSL certificate',
            'acornassociated_smtp_server' => 'SMTP server',
            'acornassociated_smtp_port' => 'SMTP port',
            'acornassociated_smtp_encryption' => 'SMTP encryption',
            'acornassociated_smtp_authentication' => 'SMTP authentication',
            'acornassociated_smtp_username' => 'SMTP username',
            'acornassociated_smtp_password' => 'SMTP password',
            'acornassociated_messaging_sounds' => 'Messaging sounds',
            'acornassociated_messaging_email_notifications' => 'Messaging email notifications',
            'acornassociated_messaging_autocreated' => 'Messaging autocreated',
            'acornassociated_imap_last_fetch' => 'IMAP last fetch',
        ],
    ]
];

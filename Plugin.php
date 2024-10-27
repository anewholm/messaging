<?php namespace Acorn\Messaging;

use System\Classes\PluginBase;
use Acorn\User\Controllers\Users;
use BackendAuth;
use Acorn\Messaging\Models\Settings;

use Event;
use \Winter\Storm\Mail\Mailer;
use \Winter\Storm\Mail\MailManager;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Config;

class Plugin extends PluginBase
{
    public $require = ['Acorn.User'];

    public function boot()
    {
        Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
            // This javascript on every page, shows data change notifications
            $controller->addJs('plugins/acorn/messaging/assets/js/acorn.messaging.monitor.js');
        });

        Event::listen('mailer.register', function (MailManager $mailmanager, Mailer $mailer) {
            // If the authentiacated user has their own SMTP setup, then use it
            $user = BackendAuth::user();
            $userHasCustomSetup = ($user->acorn_SMTP_server && $user->acorn_SMTP_username && $user->acorn_SMTP_password);
            if ($userHasCustomSetup) {
                $mailer->alwaysFrom($user->acorn_SMTP_username, $user->first_name);
                $transport = $mailer->getSymfonyTransport();
                $stream    = $transport->getStream();
                $transport->setUsername($user->acorn_SMTP_username);
                $transport->setPassword($user->acorn_SMTP_password);
                $stream->setHost($user->acorn_SMTP_server);
                $stream->setPort($user->acorn_SMTP_port);
            }
        });

        Users::extendFormFields(function ($form, $model, $context) {
            // Defaults
            if (is_null($model->acorn_IMAP_server))
                $model->acorn_IMAP_server = Settings::get('default_IMAP_server') ?: 'imap.stackmail.com';
            if (is_null($model->acorn_IMAP_username))
                $model->acorn_IMAP_username = $model->email;
            if (is_null($model->acorn_IMAP_port))
                $model->acorn_IMAP_port = Settings::get('default_IMAP_port') ?: 993;
            if (is_null($model->acorn_IMAP_validate_cert))
                $model->acorn_IMAP_validate_cert = TRUE;
            if (is_null($model->acorn_messaging_email_notifications))
                $model->acorn_messaging_email_notifications = Settings::get('default_email_notifications');
            if (is_null($model->acorn_messaging_sounds))
                $model->acorn_messaging_sounds = Settings::get('default_sounds');

            if (is_null($model->acorn_SMTP_server))
                $model->acorn_SMTP_server = Settings::get('default_SMTP_server') ?: 'smtp.stackmail.com';
            if (is_null($model->acorn_SMTP_port))
                $model->acorn_SMTP_port = Settings::get('default_SMTP_port') ?: 465;
            if (is_null($model->acorn_SMTP_username))
                $model->acorn_SMTP_username = $model->email;

            $docroot   = app()->basePath();
            $pluginDir = str_replace($docroot, '~', dirname(__FILE__));
            $form->addTabFields([
                // --------------------------------------------- IMAP
                'description_IMAP' => [
                    'label'   => '',
                    'tab'     => 'Messaging',
                    'type'    => 'partial',
                    'path'    => "$pluginDir/models/_description", // This is a dummy, just to hold the comment
                    'comment' => '<h2>IMAP mailbox connection settings</h2><p class="help-block">This Messaging plugin is an IMAP email client for reading and sending emails. Below are the <a target="_blank" href="https://en.wikipedia.org/wiki/Internet_Message_Access_Protocol">IMAP</a> settings. Check your email provider, like <a target="_blank" href="https://support.google.com/mail/answer/7126229?hl=en#zippy=%2Cstep-change-smtp-other-settings-in-your-email-client">gmail.com</a>, for the correct setup. If you are already using an email client, like <a target="_blank" href="https://en.wikipedia.org/wiki/Mozilla_Thunderbird">Thunderbird</a>, then you can check the setup in its Accounts section.</p>',
                    'commentHtml' => TRUE,
                ],
                'acorn_IMAP_username' => [
                    'label'   => 'Username',
                    'tab'     => 'Messaging',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is usually just your email address',
                    'required' => FALSE,
                ],
                'acorn_IMAP_password' => [
                    'label'   => 'Password',
                    'tab'     => 'Messaging',
                    'span'    => 'right',
                    'type'    => 'sensitive',
                    'required' => FALSE,
                ],

                'acorn_IMAP_server' => [
                    'label'   => 'Server',
                    'tab'     => 'Messaging',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is often just imap + your provider domain name. For example: imap.gmail.com',
                    'required' => TRUE,
                ],
                'acorn_IMAP_port' => [
                    'label'   => 'Port',
                    'tab'     => 'Messaging',
                    'span'    => 'right',
                    'type'    => 'number',
                    'comment' => 'It is rare for this not to be the default, 993',
                    'required' => TRUE,
                ],

                'acorn_IMAP_protocol' => [
                    'label'   => 'Protocol',
                    'tab'     => 'Messaging',
                    'span'    => 'left',
                    'type'    => 'dropdown',
                    'options' => [
                        'imap' => 'IMAP',
                        'pop3' => 'POP3',
                        'nntp' => 'NNTP',
                    ],
                    'required' => TRUE,
                ],
                'acorn_IMAP_encryption' => [
                    'label'   => 'Encryption',
                    'tab'     => 'Messaging',
                    'span'    => 'right',
                    'type'    => 'dropdown',
                    'options' => [
                        'ssl' => 'SSL',
                        'tls' => 'TLS',
                        'notls'    => 'NOTLS',
                        'starttls' => 'StartTLS',
                    ],
                    'required' => TRUE,
                ],

                'acorn_IMAP_authentication' => [
                    'label'   => 'Authentication',
                    'tab'     => 'Messaging',
                    'span'    => 'left',
                    'type'    => 'text',
                ],
                'acorn_IMAP_validate_cert' => [
                    'label'   => 'Validate Certificate',
                    'tab'     => 'Messaging',
                    'span'    => 'right',
                    'type'    => 'checkbox',
                ],

                // --------------------------------------------- SMTP
                'description_SMTP' => [
                    'label'   => '',
                    'tab'     => 'Messaging',
                    'type'    => 'partial',
                    'path'    => "$pluginDir/models/_description", // This is a dummy, just to hold the comment
                    'comment' => '<hr/><h2>SMTP settings</h2><p class="help-block">Outgoing email using the <a target="_blank" href="https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol">SMTP</a> protocol.</p>',
                    'commentHtml' => TRUE,
                ],
                'acorn_SMTP_server' => [
                    'label'   => 'Server',
                    'tab'     => 'Messaging',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is often just smtp + your provider domain name. For example: smtp.gmail.com',
                    'required' => FALSE,
                ],
                'acorn_SMTP_port' => [
                    'label'   => 'Port',
                    'tab'     => 'Messaging',
                    'span'    => 'right',
                    'type'    => 'number',
                    'required' => TRUE,
                ],
                'acorn_SMTP_encryption' => [
                    'label'   => 'Encryption',
                    'tab'     => 'Messaging',
                    'span'    => 'left',
                    'type'    => 'dropdown',
                    'options' => [
                        'ssl' => 'SSL',
                        'tls' => 'TLS',
                        'starttls' => 'StartTLS',
                    ],
                    'required' => TRUE,
                ],
                'acorn_SMTP_authentication' => [
                    'label'   => 'Authentication Method',
                    'tab'     => 'Messaging',
                    'span'    => 'right',
                    'type'    => 'dropdown',
                    //'readOnly' => TRUE,
                    'options' => [
                        'normal' => 'Normal Password',
                    ],
                    'required' => TRUE,
                ],
                'acorn_SMTP_username' => [
                    'label'   => 'Username',
                    'tab'     => 'Messaging',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is usually just your email address',
                    'required' => FALSE,
                ],
                'acorn_SMTP_password' => [
                    'label'   => 'Password',
                    'tab'     => 'Messaging',
                    'span'    => 'right',
                    'type'    => 'sensitive',
                    'required' => FALSE,
                ],

                // --------------------------------------------- General
                'description_general' => [
                    'label'   => '',
                    'tab'     => 'Messaging',
                    'type'    => 'partial',
                    'path'    => "$pluginDir/models/_description", // This is a dummy, just to hold the comment
                    'comment' => '<hr/><h2>General settings</h2>',
                    'commentHtml' => TRUE,
                ],
                'acorn_messaging_email_notifications' => [
                    'label' => 'Your Email Notifications',
                    'tab'   => 'Messaging',
                    'type' => 'dropdown',
                    'span' => 'left',
                    'comment' => 'Send emails to your email address when a message is received.',
                    'options' => [
                        'N' => 'No email notifications',
                        'A' => 'All messages',
                        'D' => 'Daily digest',
                        'W' => 'Weekly digest',
                    ],
                ],
                'acorn_messaging_sounds' => [
                    'label' => 'Sound alerts',
                    'tab'   => 'Messaging',
                    'type' => 'checkbox',
                    'span' => 'right',
                    'comment' => 'Play a sound when a message arrives (like Facebook)',
                ],
            ]);
        });
    }

    public function register()
    {
        $this->registerConsoleCommand('messaging.run', 'Acorn\Messaging\Console\RunCommand');
    }


    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Messaging Settings',
                'description' => 'Manage messaging based settings.',
                'category'    => 'Acorn',
                'icon'        => 'icon-wechat',
                'class'       => 'Acorn\Messaging\Models\Settings',
                'order'       => 500,
                'keywords'    => 'messaging email communication',
                'permissions' => ['acorn_messaging_settings']
            ]
        ];
    }
}

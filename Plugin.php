<?php namespace AcornAssociated\Messaging;

use System\Classes\PluginBase;
use AcornAssociated\User\Controllers\Users;
use BackendAuth;
use AcornAssociated\Messaging\Models\Settings;
use AcornAssociated\Messaging\Console\RunCommand;

use Event;
use \Winter\Storm\Mail\Mailer;
use \Winter\Storm\Mail\MailManager;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Config;

class Plugin extends PluginBase
{
    public $require = ['AcornAssociated.User'];

    public function boot()
    {
        Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
            // This javascript on every page, shows data change notifications
            $controller->addJs('plugins/acornassociated/messaging/assets/js/acornassociated.messaging.monitor.js');
        });

        Event::listen('mailer.register', function (MailManager $mailmanager, Mailer $mailer) {
            // If the authentiacated user has their own SMTP setup, then use it
            $user = BackendAuth::user();
            $userHasCustomSetup = ($user->acornassociated_smtp_server && $user->acornassociated_smtp_username && $user->acornassociated_smtp_password);
            if ($userHasCustomSetup) {
                $mailer->alwaysFrom($user->acornassociated_smtp_username, $user->first_name);
                $transport = $mailer->getSymfonyTransport();
                $stream    = $transport->getStream();
                $transport->setUsername($user->acornassociated_smtp_username);
                $transport->setPassword($user->acornassociated_smtp_password);
                $stream->setHost($user->acornassociated_smtp_server);
                $stream->setPort($user->acornassociated_smtp_port);
            }
        });

        Users::extendFormFields(function ($form, $model, $context) {
            // Defaults
            if (is_null($model->acornassociated_imap_server))
                $model->acornassociated_imap_server = Settings::get('default_IMAP_server') ?: 'imap.stackmail.com';
            if (is_null($model->acornassociated_imap_username))
                $model->acornassociated_imap_username = $model->email;
            if (is_null($model->acornassociated_imap_port))
                $model->acornassociated_imap_port = Settings::get('default_IMAP_port') ?: 993;
            if (is_null($model->acornassociated_imap_validate_cert))
                $model->acornassociated_imap_validate_cert = TRUE;
            if (is_null($model->acornassociated_messaging_email_notifications))
                $model->acornassociated_messaging_email_notifications = Settings::get('default_email_notifications');
            if (is_null($model->acornassociated_messaging_sounds))
                $model->acornassociated_messaging_sounds = Settings::get('default_sounds');

            if (is_null($model->acornassociated_smtp_server))
                $model->acornassociated_smtp_server = Settings::get('default_SMTP_server') ?: 'smtp.stackmail.com';
            if (is_null($model->acornassociated_smtp_port))
                $model->acornassociated_smtp_port = Settings::get('default_SMTP_port') ?: 465;
            if (is_null($model->acornassociated_smtp_username))
                $model->acornassociated_smtp_username = $model->email;

            $docroot   = app()->basePath();
            $pluginDir = str_replace($docroot, '~', dirname(__FILE__));
            $form->addTabFields([
                // --------------------------------------------- IMAP
                'description_IMAP' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_section',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'type'    => 'section',
                    'comment' => 'acornassociated.messaging::lang.models.user.acornassociated_imap_comment',
                    'commentHtml' => TRUE,
                ],
                'acornassociated_imap_username' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_username',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is usually just your email address',
                    'required' => FALSE,
                ],
                'acornassociated_imap_password' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_password',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'sensitive',
                    'required' => FALSE,
                ],

                'acornassociated_imap_server' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_server',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is often just imap + your provider domain name. For example: imap.gmail.com',
                    'required' => TRUE,
                ],
                'acornassociated_imap_port' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_port',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'number',
                    'comment' => 'It is rare for this not to be the default, 993',
                    'required' => TRUE,
                ],

                'acornassociated_imap_protocol' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_protocol',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'dropdown',
                    'options' => [
                        'imap' => 'IMAP',
                        'pop3' => 'POP3',
                        'nntp' => 'NNTP',
                    ],
                    'required' => TRUE,
                ],
                'acornassociated_imap_encryption' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_encryption',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
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

                'acornassociated_imap_authentication' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_authentication',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                ],
                'acornassociated_imap_validate_cert' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_imap_validate_cert',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'checkbox',
                ],

                // --------------------------------------------- SMTP
                'description_SMTP' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_section',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'type'    => 'section',
                    'comment' => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_comment',
                    'commentHtml' => TRUE,
                ],
                'acornassociated_smtp_server' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_server',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is often just smtp + your provider domain name. For example: smtp.gmail.com',
                    'required' => FALSE,
                ],
                'acornassociated_smtp_port' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_port',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'number',
                    'required' => TRUE,
                ],
                'acornassociated_smtp_encryption' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_encryption',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'dropdown',
                    'options' => [
                        'ssl' => 'SSL',
                        'tls' => 'TLS',
                        'starttls' => 'StartTLS',
                    ],
                    'required' => TRUE,
                ],
                'acornassociated_smtp_authentication' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_authentication',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'dropdown',
                    //'readOnly' => TRUE,
                    'options' => [
                        'normal' => 'Normal Password',
                    ],
                    'required' => TRUE,
                ],
                'acornassociated_smtp_username' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_username',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is usually just your email address',
                    'required' => FALSE,
                ],
                'acornassociated_smtp_password' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_smtp_password',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'sensitive',
                    'required' => FALSE,
                ],

                // --------------------------------------------- General
                'description_general' => [
                    'label'   => 'acornassociated.messaging::lang.models.user.acornassociated_general_section',
                    'tab'     => 'acornassociated.messaging::lang.plugin.name',
                    'type'    => 'section',
                ],
                'acornassociated_messaging_email_notifications' => [
                    'label' => 'acornassociated.messaging::lang.models.settings.your_email_notifications',
                    'tab'   => 'acornassociated.messaging::lang.plugin.name',
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
                'acornassociated_messaging_sounds' => [
                    'label' => 'Sound alerts',
                    'tab'   => 'acornassociated.messaging::lang.plugin.name',
                    'type' => 'checkbox',
                    'span' => 'right',
                    'comment' => 'acornassociated.messaging::lang.models.settings.play_sound',
                ],
            ]);
        });
    }

    public function register()
    {
        $this->registerConsoleCommand('messaging.run', RunCommand::class);
    }


    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'acornassociated.messaging::lang.models.settings.settings',
                'description' => 'acornassociated.messaging::lang.models.settings.settings_description',
                'category'    => 'AcornAssociated',
                'icon'        => 'icon-wechat',
                'class'       => 'AcornAssociated\Messaging\Models\Settings',
                'order'       => 500,
                'keywords'    => 'messaging email communication',
                'permissions' => ['acornassociated_messaging_settings']
            ]
        ];
    }
}

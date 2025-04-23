<?php namespace Acorn\Messaging;

use System\Classes\PluginBase;
use Acorn\User\Controllers\Users;
use BackendAuth;
use Acorn\Messaging\Models\Settings;
use Acorn\Messaging\Console\RunCommand;

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
            if ($user = BackendAuth::user()) {
                $userHasCustomSetup = (
                       $user->acorn_smtp_server 
                    && $user->acorn_smtp_username 
                    && $user->acorn_smtp_password
                );
                if ($userHasCustomSetup) {
                    $mailer->alwaysFrom($user->acorn_smtp_username, $user->first_name);
                    $transport = $mailer->getSymfonyTransport();
                    $stream    = $transport->getStream();
                    $transport->setUsername($user->acorn_smtp_username);
                    $transport->setPassword($user->acorn_smtp_password);
                    $stream->setHost($user->acorn_smtp_server);
                    $stream->setPort($user->acorn_smtp_port);
                }
            }
        });

        Users::extendFormFields(function ($form, $model, $context) {
            // Defaults
            if (is_null($model->acorn_imap_server))
                $model->acorn_imap_server = Settings::get('default_IMAP_server') ?: 'imap.stackmail.com';
            if (is_null($model->acorn_imap_username))
                $model->acorn_imap_username = $model->email;
            if (is_null($model->acorn_imap_port))
                $model->acorn_imap_port = Settings::get('default_IMAP_port') ?: 993;
            if (is_null($model->acorn_imap_validate_cert))
                $model->acorn_imap_validate_cert = TRUE;
            if (is_null($model->acorn_messaging_email_notifications))
                $model->acorn_messaging_email_notifications = Settings::get('default_email_notifications');
            if (is_null($model->acorn_messaging_sounds))
                $model->acorn_messaging_sounds = Settings::get('default_sounds');

            if (is_null($model->acorn_smtp_server))
                $model->acorn_smtp_server = Settings::get('default_SMTP_server') ?: 'smtp.stackmail.com';
            if (is_null($model->acorn_smtp_port))
                $model->acorn_smtp_port = Settings::get('default_SMTP_port') ?: 465;
            if (is_null($model->acorn_smtp_username))
                $model->acorn_smtp_username = $model->email;

            $docroot   = app()->basePath();
            $pluginDir = str_replace($docroot, '~', dirname(__FILE__));
            $form->addTabFields([
                // --------------------------------------------- IMAP
                'description_IMAP' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_section',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'type'    => 'section',
                    'comment' => 'acorn.messaging::lang.models.user.acorn_imap_comment',
                    'commentHtml' => TRUE,
                ],
                'acorn_imap_username' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_username',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is usually just your email address',
                    'required' => FALSE,
                ],
                'acorn_imap_password' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_password',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'sensitive',
                    'required' => FALSE,
                ],

                'acorn_imap_server' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_server',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is often just imap + your provider domain name. For example: imap.gmail.com',
                    'required' => TRUE,
                ],
                'acorn_imap_port' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_port',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'number',
                    'comment' => 'It is rare for this not to be the default, 993',
                    'required' => TRUE,
                ],

                'acorn_imap_protocol' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_protocol',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'dropdown',
                    'options' => [
                        'imap' => 'IMAP',
                        'pop3' => 'POP3',
                        'nntp' => 'NNTP',
                    ],
                    'required' => TRUE,
                ],
                'acorn_imap_encryption' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_encryption',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
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

                'acorn_imap_authentication' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_authentication',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                ],
                'acorn_imap_validate_cert' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_imap_validate_cert',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'checkbox',
                ],

                // --------------------------------------------- SMTP
                'description_SMTP' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_smtp_section',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'type'    => 'section',
                    'comment' => 'acorn.messaging::lang.models.user.acorn_smtp_comment',
                    'commentHtml' => TRUE,
                ],
                'acorn_smtp_server' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_smtp_server',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is often just smtp + your provider domain name. For example: smtp.gmail.com',
                    'required' => FALSE,
                ],
                'acorn_smtp_port' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_smtp_port',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'number',
                    'required' => TRUE,
                ],
                'acorn_smtp_encryption' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_smtp_encryption',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'dropdown',
                    'options' => [
                        'ssl' => 'SSL',
                        'tls' => 'TLS',
                        'starttls' => 'StartTLS',
                    ],
                    'required' => TRUE,
                ],
                'acorn_smtp_authentication' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_smtp_authentication',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'dropdown',
                    //'readOnly' => TRUE,
                    'options' => [
                        'normal' => 'Normal Password',
                    ],
                    'required' => TRUE,
                ],
                'acorn_smtp_username' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_smtp_username',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'left',
                    'type'    => 'text',
                    'comment' => 'This is usually just your email address',
                    'required' => FALSE,
                ],
                'acorn_smtp_password' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_smtp_password',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'span'    => 'right',
                    'type'    => 'sensitive',
                    'required' => FALSE,
                ],

                // --------------------------------------------- General
                'description_general' => [
                    'label'   => 'acorn.messaging::lang.models.user.acorn_general_section',
                    'tab'     => 'acorn.messaging::lang.plugin.name',
                    'type'    => 'section',
                ],
                'acorn_messaging_email_notifications' => [
                    'label' => 'acorn.messaging::lang.models.settings.your_email_notifications',
                    'tab'   => 'acorn.messaging::lang.plugin.name',
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
                    'tab'   => 'acorn.messaging::lang.plugin.name',
                    'type' => 'checkbox',
                    'span' => 'right',
                    'comment' => 'acorn.messaging::lang.models.settings.play_sound',
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
                'label'       => 'acorn.messaging::lang.models.settings.settings',
                'description' => 'acorn.messaging::lang.models.settings.settings_description',
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

<?php namespace AcornAssociated\Messaging\Classes;

use Str;
use File;
use Carbon\Carbon;
use Input;
use Request;
use Cms\Classes\Theme;
use \AcornAssociated\Messaging\Models\Message;
use Backend\Models\User;
use Illuminate\Support\Facades\Hash;

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Winter\Storm\Database\ModelException; // User::save() validation

class IMAP
{
    protected $dataSource;

    protected $cm;
    protected $client;
    protected $folders;

    public function __construct(callable $dataSource)
    {
        $this->dataSource = $dataSource; // The IMAP account name, configured in config/imap.php

        $imapSettings = array_merge(array(
            'port'      => env('IMAP_PORT', 993),
            'protocol'  => env('IMAP_PROTOCOL', 'imap'), //might also use imap, [pop3 or nntp (untested)]
            'encryption'     => env('IMAP_ENCRYPTION', 'ssl'), // Supported: false, 'ssl', 'tls', 'notls', 'starttls'
            'validate_cert'  => env('IMAP_VALIDATE_CERT', true),
            'authentication' => env('IMAP_AUTHENTICATION', null),
            /*
            'proxy' => [
                'socket' => null,
                'request_fulluri' => false,
                'username' => null,
                'password' => null,
            ],
            "timeout" => 30,
            "extensions" => [],
            */
        ), $dataSource());

        $this->cm = new ClientManager([
            'accounts' => [
                'default' => $imapSettings
            ]
        ]);
        $this->client = $this->cm->account('default');
    }

    protected function getFolder(string $folderName): Folder
    {
        $this->connect();
        $this->getFolders();

        $imapFolder = $this->folders->get($folderName);

        return $imapFolder;
    }

    protected function getOrCreateUser(string $email, ?string $name = NULL, ?bool $autoCreateUsers = TRUE, ?string $emailNotifications = 'A')
    {
        $user = User::where('email', 'is not distinct from', $email)->first();
        if ($autoCreateUsers && is_null($user)) {
            $login     = preg_replace('/@.*$/', '', $email);
            while (User::where('login', '=', $login)->first()) $login .= 'X';
            $name      = preg_replace('/^\s+|\s+$|["\']+/', '', $name);
            $password  = Hash::make(str_random(8));
            $firstName = (preg_replace('/ .*$/', '', $name)  ?: preg_replace('/\..*$/', '', $login));
            $lastName  = (preg_replace('/^.*\s/', '', $name) ?: preg_replace('/^.*\./', '', $login));
            try {
                // Potential validation errors
                $user  = new User([
                    'login'      => $login,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'password'   => $password,
                    'password_confirmation' => $password,
                    'acornassociated_messaging_email_notifications' => $emailNotifications,
                    'acornassociated_messaging_autocreated' => TRUE,
                ]);
                $user->save();
            } catch (ModelException $ex) {
                // TODO: Validation: Email already taken
                // should not be happening, because we check (non-atomic)
                $user = NULL;
            } catch (Exception $ex) {
                $user = NULL;
            }
        }

        return $user;
    }

    protected function getFolderMessages($folder, ?User $withUser = NULL, ?Carbon $since = NULL, ?bool $autoCreateUsers = TRUE)
    {
        $imapFolder   = (is_a($folder, Folder::class) ?: $this->getFolder($folder));
        // messages() returns a WhereQuery
        //   whereFrom($email) & whereTo($email)
        //   whereNotDeleted()
        $imapQuery = $imapFolder->messages();
        if ($withUser || $since) {
            if ($withUser) $imapQuery = $imapQuery->whereFrom($withUser->email);
            if ($since)    $imapQuery = $imapQuery->whereSince($since);
        } else {
            $imapQuery = $imapQuery->all();
        }

        $messages = array();
        $imapMessages = $imapQuery->get();
        foreach ($imapMessages as $imapMessage) {
            $externalID = $imapMessage->message_id[0]; // CACUqHoy+U_JFnDcmRsBsAbi02N-9LydDZ5M4UHSwA=-4VGPURQ@mail.gmail.com
            $inReplyTo  = $imapMessage->in_reply_to[0] ?: NULL; // TODO: Threading
            $subject    = $imapMessage->subject[0];
            $created_at = $imapMessage->date[0];
            $bodyEsc    = NULL;
            $mimeType   = NULL;

            // From. Assumed Singular
            $imapFrom = &$imapMessage->from[0];
            $fromUser = $this->getOrCreateUser($imapFrom->mail, $imapFrom->personal);

            // TODO: Attachments

            // Bodies are complex these days
            if ($imapMessage->hasTextBody()) {
                $bodyEsc  = e($imapMessage->getTextBody());
                $mimeType = 'text/plain';
            } else {
                // TODO: Run HTML through relaxed LibXML Document parser
                // to ensure no tag in-balance
                $bodyEsc  = $imapMessage->getHTMLBody();
                $mimeType = 'text/html';
            }

            // Multiple to users
            $toUsers = array();
            foreach ($imapMessage->to->toArray() as $toEntry) {
                $toUser = $this->getOrCreateUser($toEntry->mail, $toEntry->personal);
                if ($toUser) array_push($toUsers, $toUser);
            }

            if ($toUsers && $fromUser && $externalID) {
                // Create all messages.
                // They should of course involve our user, as they are in her IMAP
                // however, it is not a problem if they do not
                // TODO: Take in to account email groups
                // TODO: Create labels, instead of the label field
                $attributes = array(
                    // Required
                    'externalID'   => $externalID,
                    'user_from'    => $fromUser,
                    'users'        => $toUsers,
                    // Optional
                    'subject'      => $subject,
                    'created_at'   => $created_at,
                    'source'       => 'email',
                    'labels'       => $imapFolder->path,
                    'body'         => $bodyEsc, // Potentially HTML
                    'mime_type'    => $mimeType,
                );
                array_push($messages, new Message($attributes));
            }
        }

        return $messages;
    }

    public function getAllMessages(?User $withUser = NULL, ?Carbon $since = NULL, ?bool $autoCreateUsers = TRUE, ?bool $verbose = FALSE)
    {
        $allMessages = array();
        $folders     = array();
        $sinceString = ($since ? $since->format('Y M,d H:i:s') : 'forever');
        if ($verbose) print("  Since: $sinceString\n");

        $this->getFolders();
        foreach ($this->folders as $imapFolder) {
            switch (strtolower($imapFolder->name)) {
                // TODO: Folder subscription list 
                case 'trash':
                case 'junk':
                case 'drafts':
                    break;
                default:
                    array_push($folders, $imapFolder->path);
                    $allMessages += $this->getFolderMessages(
                        $imapFolder->path, 
                        $withUser, 
                        $since,
                        $autoCreateUsers
                    );
            }
        }
        if ($verbose) print('  folders: ' . implode(',', $folders) . "\n");

        return $allMessages;
    }

    protected function connect()
    {
        // Connect to the IMAP Server
        // throws Webklex\PHPIMAP\Exceptions\ConnectionFailedException
        $this->client->connect();
        return $this;
    }

    protected function getFolders()
    {
        if (!$this->folders) {
            $this->connect();
            // @var \Webklex\PHPIMAP\Support\FolderCollection $folders
            $this->folders = $this->client->getFolders()->keyBy('path');
        }
        return $this->folders;
    }
}

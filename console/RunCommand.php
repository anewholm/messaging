<?php namespace AcornAssociated\Messaging\Console;

use Carbon\Carbon;
use AcornAssociated\Messaging\Models\Message;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Backend\Models\User;
use AcornAssociated\Messaging\Classes\IMAP;
use AcornAssociated\Messaging\Controllers\Conversations;

use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\EventNotFoundException;
use Webklex\PHPIMAP\Exceptions\GetMessagesFailedException;
use Webklex\PHPIMAP\Exceptions\ImapBadRequestException;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;
use Webklex\PHPIMAP\Exceptions\InvalidMessageDateException;
use Webklex\PHPIMAP\Exceptions\MessageContentFetchingException;
use Webklex\PHPIMAP\Exceptions\MessageFlagException;
use Webklex\PHPIMAP\Exceptions\MessageHeaderFetchingException;
use Webklex\PHPIMAP\Exceptions\MessageNotFoundException;
use Webklex\PHPIMAP\Exceptions\MessageSearchValidationException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

class RunCommand extends Command
{
    protected $name = 'messaging:run';
    protected $description = 'Runs the IMAP refresh client.';
    public    $pause = 5;

    public function handle()
    {
        $verbose = $this->option('verbose');
        $this->info("Client refreshing IMAP");

        while (TRUE) {
            foreach (User::whereNotNull('acornassociated_imap_password')
                ->where('acornassociated_imap_password', '!=', '')
                ->get()
                as $user
            ) {
                if ($verbose) $this->info("$user->acornassociated_imap_server:$user->email...");
                $newEmails = array();
                try {
                    $newEmails = $this->refreshIMAP($user);
                }
                catch (ConnectionFailedException $ex) {
                    $this->warn("  ConnectionFailedException: " . $ex->getMessage());
                }
                catch (GetMessagesFailedException $ex) {
                    $this->warn("  GetMessagesFailedException: " . $ex->getMessage());
                }
                catch (AuthFailedException $ex) {
                    $this->warn("  AuthFailedException: " . $ex->getMessage());
                }
                catch (ImapServerErrorException $ex) {
                    $this->warn("  ImapServerErrorException: " . $ex->getMessage());
                }
                catch (RuntimeException $ex) {
                    $this->warn("  RuntimeException: " . $ex->getMessage());
                }
                catch (Exception $ex) {
                    $this->warn("  Exception: " . $ex->getMessage());
                }

                $newEmailCount = count($newEmails);
                if ($verbose) $this->info("  $newEmailCount new emails");
            }
            if ($verbose) $this->info("pausing for $this->pause seconds...");
            sleep($this->pause); // seconds
        }
    }

    protected function refreshIMAP(User $user)
    {
        // IMAP emails
        // TODO: Reconsider where this should be...
        $verbose = $this->option('verbose');
        $imap    = new IMAP(function() use($user) {
            return [
                // user specific
                'username'   => $user->acornassociated_imap_username ?: $user->email,
                'password'   => $user->acornassociated_imap_password,
                // TODO: Use default_* settings, not env
                'host'       => env('IMAP_HOST',     $user->acornassociated_imap_server),
                'port'       => env('IMAP_PORT',     $user->acornassociated_imap_port ?: 993),
                'protocol'   => env('IMAP_PROTOCOL', $user->acornassociated_imap_protocol ?: 'imap'),
                'encryption' => env('IMAP_ENCRYPTION',         $user->acornassociated_imap_encryption ?: 'ssl'),
                'validate_cert'  => env('IMAP_VALIDATE_CERT',  $user->acornassociated_imap_validate_cert),
                'authentication' => env('IMAP_AUTHENTICATION', $user->acornassociated_imap_authentication ?: NULL),
            ];
        });

        // This returns new Message() objects (unsaved)
        // TODO: Get only new messages since last fetch
        $since     = ($user->acornassociated_imap_last_fetch ? new Carbon($user->acornassociated_imap_last_fetch) : NULL);
        $emails    = $imap->getAllMessages(NULL, $since, TRUE, $verbose);
        $newEmails = array();
        foreach ($emails as $email) {
            // TODO: This 1-by-1 externalID querying is very inefficient
            $exists = Message::where('externalID', 'is not distinct from', $email->externalID)->first();
            if (is_null($exists)) {
                $email->save(); // WebSockets trigger
                array_push($newEmails, $email);
            }
        }
        $user->acornassociated_imap_last_fetch = new Carbon();
        $user->save();

        return $newEmails;
    }

    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return [];
    }

}

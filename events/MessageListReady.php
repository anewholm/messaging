<?php namespace Acorn\Messaging\Events;

use Acorn\User\Models\User;
use Acorn\Collection;

class MessageListReady
{
    use \Illuminate\Foundation\Events\Dispatchable;

    public $messages;
    public $authUser;
    public $withUser;

    public function __construct(Collection $_messages, User $_authUser, User $_withUser)
    {
        $this->messages = $_messages;
        $this->authUser = $_authUser;
        $this->withUser = $_withUser;
    }
}

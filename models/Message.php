<?php namespace Acorn\Messaging\Models;

use \Acorn\Model as Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Flash;
use Acorn\User\Models\UserRole;
use Acorn\User\Models\UserGroup;
use Acorn\User\Models\User;
use Acorn\Calendar\Models\Event;
use Acorn\Messaging\Events\MessageNew;
use Acorn\Messaging\Events\MessageUpdated;
use Illuminate\Broadcasting\BroadcastException;

/**
 * Model
 */
class Message extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'acorn_messaging_message';

    public $belongsTo = [
        'user_from' => User::class
    ];

    public $belongsToMany = [
        'users' => [
            User::class,
            'table' => 'acorn_messaging_message_user',
            'order' => 'name',
        ],
        'groups' => [
            UserGroup::class,
            'table' => 'acorn_messaging_message_user_group',
            'order' => 'name',
        ],
        'roles' => [
            UserGroup::class,
            'table' => 'acorn_messaging_message_user_role',
            'order' => 'name',
        ],
    ];

    public $fillable = [
        'user_from',
        'subject',
        'body',
        'users',
        'groups',
        'roles',
        'created_at',
        'labels',
        'externalID',
        'source',
        'mime_type',
    ];

    public $rules = [
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = ['repeater1'];

    public function save(?array $options = [], $sessionKey = null)
    {
        $isNew  = !isset($this->id);
        $result = parent::save($options, $sessionKey);

        // Additional Acorn\Messaging plugin inform
        // TODO: This custom WEBSOCKET needs to be changed to the new AA generic DataChange system
        /*
        if (!isset($options['WEBSOCKET']) || $options['WEBSOCKET'] == TRUE) {
            try {
                if ($isNew) MessageNew::dispatch($this);
                else        MessageUpdated::dispatch($this);
            } catch (BroadcastException $ex) {
                // we demote this to a flash
                if (env('APP_DEBUG')) Flash::error('WebSockets failed: ' . $ex->getMessage());
            }
        }
        */

        return $result;
    }

    public function contexts()
    {
        $contexts = array();
        $userFrom = &$this->user_from;
        foreach ($this->users as &$userTo) {
            // Conversation lists
            // TODO: But these contexts only need to be updated
            // if a conversation with someone new has started
            array_push($contexts, "$userFrom->id");
            array_push($contexts, "$userTo->id");
            // Conversations
            array_push($contexts, "$userFrom->id-$userTo->id");
        }

        return array_unique($contexts);
    }

    /**
     * Relations
     */
    public function event()
    {
        return $this->belongsTo('Acorn\Calendar\Models\Event');
    }

    public function user()
    {
        return $this->belongsTo('Backend\Models\User');
    }

    public function usergroup()
    {
        return $this->belongsTo('Backend\Models\UserGroup');
    }

    public function userrole()
    {
        return $this->belongsTo('Backend\Models\UserRole');
    }

    /**
     * Form Options
     */
    public function getUserIdOptions()
    {
        $options = array();
        foreach (User::all()->all() as $user) {
            $name = trim("$user->first_name $user->last_name");
            $options[$user->id] = $name;
        }
        return $options;
    }

    public function getGroupIdOptions()
    {
        $options = array();
        foreach (UserGroup::all()->all() as $group) {
            $options[$group->id] = $group->name;
        }
        return $options;
    }

    public function getUserRoleIdOptions()
    {
        $options = array();
        foreach (UserRole::all()->all() as $group) {
            $options[$group->id] = $group->name;
        }
        return $options;
    }

    public function typeClasses()
    {
        $user = User::authUser();
        return array(
            ($this->user_from_id == $user->id ? 'sent' : 'received'),
            $this->source,
            preg_replace('/[^a-z0-9]+/', ' ', $this->labels ?: ''),
        );
    }

    /**
     * Complex Accessors
     */
    public function subjectTruncate(?int $length = 30)
    {
        return $this->truncate($this->subject, $length);
    }

    public function bodyTruncate(?int $length = 250)
    {
        return ($this->mime_type == 'text/plain'
            ? $this->truncate($this->body, $length)
            : $this->body
        );
    }

    public function truncate(string $value, ?int $length = 100)
    {
        if (strlen($value) > $length) {
            $value = substr($value, 0, $length);
            // Cut-off near last word
            $value = preg_replace('/ +[^ ]{0,8}$/', '', $value);
            $value = "$value ...";
        }

        return $value;
    }
}

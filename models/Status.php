<?php namespace Acorn\Messaging\Models;

use \Acorn\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Message;

class Status extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'acorn_messaging_status';

    public $belongsToMany = [
        'messages' => [
            Message::class,
            'table' => 'acorn_messaging_user_message_status',
        ],
    ];

    public $fillable = [
        'name',
        'description',
    ];

    public $rules = [
    ];

    public $jsonable = [];
}

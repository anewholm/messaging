<?php namespace AcornAssociated\Messaging\Models;

use \AcornAssociated\Model as AcornAssociatedModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Message;

class Status extends AcornAssociatedModel
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'acornassociated_messaging_status';

    public $belongsToMany = [
        'messages' => [
            Message::class,
            'table' => 'acornassociated_messaging_user_message_status',
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

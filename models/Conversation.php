<?php namespace Acorn\Messaging\Models;

use Acorn\User\Models\User;
use Acorn\Messaging\Models\Message;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Conversation extends User
{
    public $email_lcase;
    public $last_message_at;
    public $last_message_at_string;
    public $message_count;
    public $message_unread_count;

    public $belongsToMany = [
        'messages' => [
            Message::class,
        ],
    ];

    public static function timeDescription(\DateTime $datetime, ?string $default = 'M d, Y H:i')
    {
        $today = new Carbon();
        $diff  = $datetime->diff($today);
        $lastMessageAtString = $datetime->format($default);

        if ($datetime > $today->clone()->sub(new \DateInterval('PT1M'))) {
            $lastMessageAtString = trans('just now');
        } else if ($datetime > $today->clone()->sub(new \DateInterval('PT1H'))) {
            $lastMessageAtString = "$diff->i " . trans('mins ago');
        } else if ($datetime->format('Y-m-d') == $today->format('Y-m-d')) {
            $lastMessageAtString = $datetime->format('H:i');
        } else if ($datetime->format('Y-m-d') == $today->clone()->sub(new \DateInterval('P1D'))->format('Y-m-d')) {
            $lastMessageAtString = trans('yesterday') . ' ' . $datetime->format('H:i');
        } else if ($datetime > $today->clone()->sub(new \DateInterval('P1W'))) {
            $lastMessageAtString = trans('last') . ' ' . $datetime->format('D H:i');
        } else if ($datetime->format('Y') == $today->format('Y')) {
            $lastMessageAtString = $datetime->format('M d H:i');
        }

        return $lastMessageAtString;
    }
}

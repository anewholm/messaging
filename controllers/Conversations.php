<?php namespace Acorn\Messaging\Controllers;

use BackendAuth;
use Backend\Models\User;
use Backend\Models\UserGroup;
use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Cms\Classes\Theme; // TODO: Themes come from the CMS copied plugin. Necessary?
use DB;
use Request;
use ApplicationException;
use Carbon\Carbon;
use Winter\Storm\Database\Collection;

use Acorn\Messaging\Widgets\MessageList;
use Acorn\Messaging\Widgets\ConversationList;
use Acorn\Messaging\Classes\IMAP;
use Acorn\Messaging\Models\Message;
use Acorn\Messaging\Models\Conversation;
use Acorn\Messaging\Events\MessageListReady;

use Acorn\Calendar\Widgets\Calendars;
use Acorn\Calendar\Models\EventPart;
use Acorn\Calendar\Models\Instance;

class Conversations extends Controller
{
    // Builder implemented behaviors:
    // We do not use these for Messaging
    // because we implement our own view endpoints and widgets below
    public $implement = ['Backend\Behaviors\FormController'];

    // Builder implemented behavior configuaration files:
    // Ours uses the config_message_list.yaml in the widget below
    //
    public $formConfig = 'config_form.yaml';
    // public $listConfig = 'config_list.yaml';
    // public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Acorn.Messaging', 'messaging-menu-item', 'inbox-side-menu-item');

        $widget = new ConversationList($this, 'conversationList', function(){
            // Distinct list of users with active conversations
            // TODO: group by user_id for message count and latest date
            $authUser  = BackendAuth::user();
            $user      = NULL; // TODO: $authUser->user(); // Because messages are attached to front-end users now
            $userQuery = [];
            if ($user) {
                $userQuery = DB::select(DB::raw("select user_id, count(*) as message_count, max(created_at) as last_message_at from (
                        select mm.user_from_id as user_id, mm.created_at
                        from public.acorn_messaging_message mm
                        inner join public.acorn_messaging_message_user mu on mm.id = mu.message_id
                        where mu.user_id = '$user->ID'
                        union all
                        select mu.user_id, mm.created_at
                        from public.acorn_messaging_message mm
                        inner join public.acorn_messaging_message_user mu on mm.id = mu.message_id
                        where mm.user_from_id = '$user->ID'
                    ) s
                    group by user_id
                    order by max(created_at) desc"
                ));
            }
            // Create a Conversation to group User and other data
            $conversations = array();
            foreach ($userQuery as $userResult) {
                $lastMessageAt = new Carbon($userResult->last_message_at);
                $conversation  = Conversation::find($userResult->user_id);
                $conversation->last_message_at = $lastMessageAt;
                $conversation->last_message_at_string = Conversation::timeDescription($lastMessageAt);
                $conversation->message_count   = (int) $userResult->message_count;
                $conversation->email_lcase     = strtolower($conversation->email);
                $conversation->item_type = 'user';

                array_push($conversations, $conversation);
            }

            // Groups
            $groups = $authUser->groups()->get();
            foreach ($groups as $group) {
                $group->first_name = $group->name;
                $group->email = 'group';
                $group->message_count = 0;
                $group->item_type = 'group';

                array_push($conversations, $group);
            }

            return $conversations;
        });
        $widget->bindToController(); // Set $this->widget->conversationList
    }

    public function getConversationUserData(User $withUser)
    {
        $authUser = BackendAuth::user();

        // Message table
        // Includes emails from call above
        $messages = Message::where('user_from_id', '=', $authUser->id)
            // Messages from authUser to withUser
            ->whereHas('users', function($q) use($withUser) {
                $q->where('user_id', '=', $withUser->id);
            })
            // Messages from withUser to authUser
            ->union(
                Message::where('user_from_id', '=', $withUser->id)
                ->whereHas('users', function($q) use($authUser) {
                    $q->where('user_id', '=', $authUser->id);
                })
            )
            ->orderBy('id')
            ->get();

        // Allow other plug-ins to add in to the message stream
        // Calendar inserts events that both parties attended here
        $mixins = MessageListReady::dispatch($messages, $authUser, $withUser);
        foreach ($mixins as &$mixin) {
            if ($mixin instanceof Collection) {
                $messages = $messages->merge($mixin);
            } else {
                throw new ApplicationException('MessageListReady event received non-Collection results.');
            }
        }

        return $messages->sortBy('created_at');
    }


    /**
     * View endpoints
     * These are necessary because we are not using the in-built Builder Behaviors
     */
    public function index()
    {
        // Copied from the CMS Controller
        // winter.cmspage.js => onOpenTemplate() & onCreateMessage()
        $this->addJs('/modules/acorn/assets/js/acorn.js');
        $this->addJs('/plugins/acorn/messaging/assets/js/acorn.messaging.js', 'core');
        $this->addCss('/plugins/acorn/messaging/assets/css/acorn.messaging.css', 'core');

        // TODO: Are these necessary?
        $this->addJs('/modules/cms/assets/js/winter.dragcomponents.js', 'core');
        $this->addJs('/modules/cms/assets/js/winter.tokenexpander.js', 'core');
        $this->addCss('/modules/cms/assets/css/winter.components.css', 'core');

        // Preload the code editor class as it could be needed
        // before it loads dynamically.
        $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js', 'core');

        $this->bodyClass = 'compact-container';
        $this->pageTitle = 'Messaging';
    }


    /**
     * AJAX events
     * NOTE: We are using the TemplateList AJAX events here
     * because we are using winter.cmspage.js which has the event names hard-coded
     */
    // TODO: Move these handlers on to the conversationList widget
    public function onCreateReply()
    {
        // Create Message => open tab with form
        $message      = new Message();
        $widgetConfig = $this->makeConfig('~/plugins/acorn/messaging/models/message/fields.yaml');
        $widgetConfig->model = $message;
        $widget       = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

        $this->vars['templatePath'] = Request::input('path');
        $this->vars['lastModified'] = date('U');
        $this->vars['canCommit']    = TRUE;
        $this->vars['canReset']     = TRUE;

        $tabTitle = "New Conversation";
        return [
            'tabTitle' => $tabTitle,
            'tab' => $this->makePartial('form_message', [
                'form' => $widget,
                'type' => 'Message',
                'templateType'  => 'conversation',
                'templateTheme' => 'default',
            ])
        ];
    }

    public function onSend()
    {
        $post     = post();
        $authUser = BackendAuth::user();

        // New message
        $message = new Message();
        $post['user_from'] = $authUser;
        $message->fill($post);
        $message->save();

        // Result and partial update
        $result        = 'success';
        $conversations = $this->widget->conversationList->getData();
        Flash::success('Message Sent');

        return array(
            'result' => $result,
            'conversations' => $this->makePartial('conversations', [
                'authUser'      => $authUser,
                'conversations' => $conversations,
            ]),
        );
    }

    public function onCreateMessage()
    {
        // Create Message => open tab with form
        $message      = new Message();
        $widgetConfig = $this->makeConfig('~/plugins/acorn/messaging/models/message/fields.yaml');
        $widgetConfig->model = $message;
        $widget       = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

        $this->vars['templatePath'] = Request::input('path');
        $this->vars['lastModified'] = date('U');
        $this->vars['canCommit']    = TRUE;
        $this->vars['canReset']     = TRUE;

        $tabTitle = "New Message";
        return [
            'tabTitle' => $tabTitle,
            'tab' => $this->makePartial('form_message', [
                'form' => $widget,
                'type' => 'Message',
                'templateType'  => 'conversation',
                'templateTheme' => 'default',
            ])
        ];
    }

    public function onOpenConversationGroup()
    {
        // acorn.messaging.js => onOpen<itemType><itemSubType>()
        // MessageList click => open tab with form
        $ID       = Request::input('path');
        $authUser = BackendAuth::user();
        $inGroup  = UserGroup::find($ID);

        // TODO: Form and messages
        $messages = array();
        $widget   = NULL; 

        $tabTitle = $inGroup->name;
        if (stristr($tabTitle, 'group') === FALSE) $tabTitle .= ' group';

        return [
            'tabTitle' => $tabTitle,
            'tab'      => $this->widget->conversationList->makePartial('conversation_interface', [
                'authUser' => $authUser,
                'inGroup'  => $inGroup,
                'messages' => $messages,
                'form'     => $widget,
                'templatePath'  => "$authUser->id-$inGroup->id",
                'templateType'  => 'conversation',
                'templateSubType' => 'group',
                'templateTheme' => Theme::getEditTheme()->getDirName(),
            ])
        ];
    }
    
    public function onOpenConversationUser()
    {
        // acorn.messaging.js => onOpen<itemType><itemSubType>()
        // MessageList click => open tab with form
        $ID       = Request::input('path');
        $authUser = BackendAuth::user();
        if ($withUser = User::find($ID)) {
            // Conversation
            $messages = $this->getConversationUserData($withUser);

            // Chat form
            $newMessage = new Message(array(
                'users' => array($withUser->id))
            );
            $widgetConfig = $this->makeConfig('~/plugins/acorn/messaging/models/message/fields.yaml');
            $fieldUsers  = &$widgetConfig->tabs['fields']['users'];
            $fieldGroups = &$widgetConfig->tabs['fields']['groups'];
            $widgetConfig->model = $newMessage;
            $widget       = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

            // Title and variables
            $this->vars['templatePath'] = $withUser->id;
            $this->vars['templateType'] = 'message';
            $tabTitle = ($withUser->first_name ? $withUser->first_name : "Conversation $withUser->id");
        } else {
            throw new ApplicationException("User not found");
        }

        return [
            'tabTitle' => $tabTitle,
            'tab'      => $this->widget->conversationList->makePartial('conversation_interface', [
                'authUser' => $authUser,
                'withUser' => $withUser,
                'messages' => $messages,
                'form'     => $widget,
                'templatePath'  => "$authUser->id-$withUser->id",
                'templateType'  => 'conversation',
                'templateSubType' => 'user',
                'templateTheme' => Theme::getEditTheme()->getDirName(),
            ])
        ];
    }

    public function onOpenEmail()
    {
        return $this->onOpenMessage();
    }

    public function onOpenMessage()
    {
        // acorn.messaging.js => onOpen<itemType>()
        // MessageList click => open tab with form
        $ID = Request::input('path');
        if ($message = Message::find($ID)) {
            $widgetConfig = $this->makeConfig('~/plugins/acorn/messaging/models/message/fields.yaml');
            $widgetConfig->model = $message;
            $widget       = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);

            $this->vars['templatePath'] = $ID;

            $tabTitle = ($message->subject ? $message->subject : "Message $message->id");
        } else {
            throw new ApplicationException("Message not found");
        }

        return [
            'tabTitle' => $tabTitle,
            'tab' => $this->makePartial('form_message', [
                'form' => $widget,
                'templateType'  => 'conversation',
                'templateTheme' => 'default',
            ])
        ];
    }
}

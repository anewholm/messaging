 // Whole-website new message monitor
 $(document).ready(function(){
    if (window.Echo && window.Echo.channel)
        window.Echo
            .channel('messaging')
            .listenToAll(function(channelEvent, messageEvent) {
                acorn_onMessagingEvent(messageEvent);
            });
});

function acorn_onMessagingEvent(messageEvent){
    // Do not trigger on the Messaging plugin pages
    if (document.location.pathname.substring(0,35) != '/backend/acorn/messaging/') {
        if (window.console) console.log(messageEvent);
        if (messageEvent && messageEvent.message && messageEvent.user_from) {
            var message  = messageEvent.message;
            var userFrom = messageEvent.user_from;
            // TODO: Make the sound configurable
            var audio = new Audio('/plugins/acorn/messaging/assets/sounds/conversation-arrived.mp3');
            audio.play();

            let notifyHTML = 'New message from ' + userFrom.first_name + ': ' + message.subject + '.';
            let viewHTML   = ' <a href="/backend/acorn/messaging/conversations">View</a>.'; 
            $.wn.flashMsg({
                'text': notifyHTML + viewHTML,
                'class': 'success',
                'interval': 3
            });
        }
    }
}

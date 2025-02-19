 // Whole-website new message monitor
 $(document).ready(function(){
    if (window.Echo && window.Echo.channel)
        window.Echo
            .channel('messaging')
            .listenToAll(function(channelEvent, messageEvent) {
                acornassociated_onMessagingEvent(messageEvent);
            });
});

function acornassociated_onMessagingEvent(messageEvent){
    // Do not trigger on the Messaging plugin pages
    if (document.location.pathname.substring(0,35) != '/backend/acornassociated/messaging/') {
        if (window.console) console.log(messageEvent);
        if (messageEvent && messageEvent.message && messageEvent.user_from) {
            var message  = messageEvent.message;
            var userFrom = messageEvent.user_from;
            // TODO: Make the sound configurable
            var audio = new Audio('/plugins/acornassociated/messaging/assets/sounds/conversation-arrived.mp3');
            audio.play();

            let notifyHTML = 'New message from ' + userFrom.first_name + ': ' + message.subject + '.';
            let viewHTML   = ' <a href="/backend/acornassociated/messaging/conversations">View</a>.'; 
            $.wn.flashMsg({
                'text': notifyHTML + viewHTML,
                'class': 'success',
                'interval': 3
            });
        }
    }
}

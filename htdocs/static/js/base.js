jQuery('document').ready(function(){
	jQuery('a.share_bttn').click(function(e){
		e.preventDefault();

		share(question_title, function(request_ids){
//			if(request_ids) {
//				var rqst_id_list = '';
//				for(var r in request_ids) {
//					rqst_id_list += ','+request_ids[r]
//				}
//
//				if(request_id && request_id.length > 2){
//
//					// Send the request list to the backend to add the share records.
//				}
//			}
		});
	});
});

function share(requestCallback) {
	FB.ui(
		{
			method: 'apprequests',
			message: "Sharing message",
			max_recipients: 10,
			title: "Sharing title"
  		}, 
  		requestCallback
	);
}

function postToFeed() {
	var obj = {
		method: 'feed',
		link: 'https://developers.facebook.com/docs/reference/dialogs/',
		picture: 'http://fbrell.com/f8.jpg',
		name: 'Facebook Dialogs',
		caption: 'Reference Documentation',
		description: 'Using Dialogs to interact with users.'
	};

	function callback(response) {
		document.getElementById('msg').innerHTML = "Post ID: " + response['post_id'];
	}

	FB.ui(obj, callback);
}
function report_comments_flag(element, id, nonce) {
	var data =  {
		action: 'report_comments_flag',
		id: id,
		nonce: nonce
	};
	if (confirm(ReportCommentsJs.confirm + '?')) {
		jQuery.post(ReportCommentsJs.ajaxurl, data, function(response) {
			var $msg = jQuery(document.createElement('span'))
				.addClass('report-comment')
				.text(response);
			jQuery(element).replaceWith($msg);
		});
	}
}
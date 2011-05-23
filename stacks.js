$(function(){
	$('#button-add-stack').click(function() {
		$('.vbx-stacks form:not(.add-stack), .vbx-stacks .message').slideUp();
		$('.vbx-stacks form.add-stack').slideToggle();
		return false;
	});
	$('.vbx-stacks form:eq(0) button:eq(0)').click(function() {
		var $label = $('label.message');
		$label.last().after($('<div>').append($label.first().clone()).html());
		return false;
	});
	$('.vbx-stacks a.messages').click(function() {
		var $stack = $(this).parent().parent().parent();
		var id = $stack.attr('id');
		var $form = $('.vbx-stacks form:not(.add):visible');
		$('.vbx-stacks .message:not(.' + id + '):visible').slideUp();
		$('.vbx-stacks .message.' + id).slideToggle();
		$form[id.match(/([\d]+)/)[1] != $form.find('input[name=stack]').val() ? 'slideUp' : 'show']();
		return false;
	});
	$('.vbx-stacks .stack a.delete').click(function() {
		var $stack = $(this).parent().parent().parent();
		var id = $stack.attr('id');
		if(confirm('You are about to delete "' + $stack.children().children('span').eq(0).text() + '" and all its messages.'))
			$.ajax({
				type: 'POST',
				url: window.location,
				data: { remove: id.match(/([\d]+)/)[1] },
				success: function() {
					$stack.add('.vbx-stacks .message.' + id).hide(500);
				},
				dataType: 'text'
			});
		return false
	});
	$('.vbx-stacks .message a.delete').click(function() {
		var $message = $(this).parent().parent().parent();
		var id = $message.attr('id').split('_');
		var $stack = $('#stack_' + id[1]);
		var $num = $stack.find('span').eq(1);
		if(confirm('You are about to remove "' + $message.children().children('span').eq(0).text() + '" from "' + $stack.find('span').eq(0).text() + '".'))
			$.ajax({
				type: 'POST',
				url: window.location,
				data: { remove: id[2], stack: id[1] },
				success: function() {
					$message.hide(500);
					$num.text(parseInt($num.text()) - 1);
				},
				dataType: 'text'
			});
		return false
	});
	$('.vbx-stacks a.sms').click(function() {
		var $stack = $(this).parent().parent().parent();
		var id = $stack.attr('id');
		var stack = id.match(/([\d]+)/)[1];
		var $input = $('.vbx-stacks form.update-sms input[name=stack]');
		var $form = $('.vbx-stacks form.update-sms');
		$('.vbx-stacks form:visible').not($form).add('.vbx-stacks .message:not(.' + id + ')').slideUp();
		$form[stack == $input.val() ? 'slideToggle' : 'slideDown']();
		$form.children('h3').children('span').text($stack.children().children('span').eq(0).text());
		$input.val(stack);
		return false
	});
})

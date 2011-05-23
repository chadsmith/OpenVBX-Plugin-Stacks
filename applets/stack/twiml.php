<?php
$ci =& get_instance();
$stack = AppletInstance::getValue('stack');
$next = AppletInstance::getDropZoneUrl('next');

$response = new Response();

if(!empty($_REQUEST['From'])) {
	$number = normalize_phone_to_E164($_REQUEST['From']);
	$stack = $ci->db->query(sprintf('SELECT id, list, messages, pointers FROM subscribers_stacks WHERE id = %d', $stack))->row();
	$list = intval($stack->list);

	if(!$ci->db->query(sprintf('SELECT id FROM subscribers WHERE list = %d AND value = %s', $list, $number))->num_rows())
		$ci->db->insert('subscribers', array(
			'list' => $list,
			'value' => $number,
			'joined' => time()
		));

	$messages = unserialize($stack->messages);
	$pointers = unserialize($stack->pointers);
	$i = 0;

	if(!empty($pointers[$number]))
		$i = $pointers[$number];
	
	if(!empty($messages[$i])) {
		$response->addSms($messages[$i]);
		$i++;
	}

	$pointers[$number] = $i;
	$ci->db->update('subscribers_stacks', array('pointers' => serialize($pointers)), array('id' => $stack->id));
}

if(!empty($next))
	$response->addRedirect($next);

$response->Respond();

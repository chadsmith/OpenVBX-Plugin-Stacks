<?php
	$user = OpenVBX::getCurrentUser();
	$tenant_id = $user->values['tenant_id'];
	$ci =& get_instance();
	$queries = explode(';', file_get_contents(dirname(__FILE__) . '/db.sql'));
	foreach($queries as $query)
		if(trim($query))
			$ci->db->query($query);
	if(isset($_POST['remove'])) {
		$remove = intval($_POST['remove']);
		if(!empty($_POST['stack'])) {
			$stack = intval($_POST['stack']);
			if($stack = $ci->db->query(sprintf('SELECT id, messages, pointers FROM subscribers_stacks WHERE id = %d AND tenant = %d', $stack, $tenant_id))->row()) {
				$messages = unserialize($stack->messages);
				$pointers = unserialize($stack->pointers);
				unset($messages[$remove]);
				$messages = array_values($messages);
				if($pointers)
					foreach($pointers as $number => &$message)
						if($message > $remove)
							$message--;
				$ci->db->update('subscribers_stacks', array('messages' => serialize($messages), 'pointers' => serialize($pointers)), array('id' => $stack->id));
			}
		}
		else
			$ci->db->delete('subscribers_stacks', array('id' => $remove, 'tenant' => $tenant_id));
		die;
	}
	if(!empty($_POST['name']) && 1 < count($_POST['message'])) {
		$list = intval($_POST['list']);
		$messages = (array) $_POST['message'];
		if($ci->db->query(sprintf('SELECT id FROM subscribers_lists WHERE id = %d AND tenant = %d', $list, $tenant_id))->num_rows())
			$ci->db->insert('subscribers_stacks', array(
				'tenant' => $tenant_id,
				'list' => $list,
				'name' => htmlentities($_POST['name']),
				'messages' => serialize($messages)
			));
	}
	if(!empty($_POST['callerId'])) {
		$stack = intval($_POST['stack']);
		$callerId = normalize_phone_to_E164($_POST['callerId']);
		if($stack = $ci->db->query(sprintf('SELECT id, list, messages, pointers FROM subscribers_stacks WHERE id = %d AND tenant = %d', $stack, $tenant_id))->row()) {
			$messages = unserialize($stack->messages);
			$pointers = unserialize($stack->pointers);
			$subscribers = $ci->db->query(sprintf('SELECT value FROM subscribers WHERE list = %d', $stack->list))->result();
			require_once(APPPATH . 'libraries/twilio.php');
			$ci->twilio = new TwilioRestClient($ci->twilio_sid, $ci->twilio_token, $ci->twilio_endpoint);
			if(count($subscribers))
				foreach($subscribers as $subscriber) {
					$i = 0;
					if(!empty($pointers[$subscriber->value]))
						$i = $pointers[$subscriber->value];
					if(!empty($messages[$i])) {
						$ci->twilio->request("Accounts/{$this->twilio_sid}/SMS/Messages", 'POST', array(
							'From' => $callerId,
							'To' => $subscriber->value,
							'Body' => $messages[$i]
						));
						$i++;
					}
					$pointers[$subscriber->value] = $i;
				}
			$ci->db->update('subscribers_stacks', array('pointers' => serialize($pointers)), array('id' => $stack->id));
		}
	}
	$lists = $ci->db->query(sprintf('SELECT id, name FROM subscribers_lists WHERE tenant = %d', $tenant_id))->result();
	$stacks = $ci->db->query(sprintf('SELECT id, name, messages, pointers FROM subscribers_stacks WHERE tenant = %d', $tenant_id))->result();
	OpenVBX::addJS('stacks.js');
?>
<style>
	.vbx-stacks h3 {
		font-size: 16px;
		font-weight: bold;
		margin-top: 0;
	}
	.vbx-stacks .stack,
	.vbx-stacks .message {
		clear: both;	
		width: 95%;
		overflow: hidden;
		margin: 0 auto;
		padding: 5px 0;
		border-bottom: 1px solid #eee;
	}
	.vbx-stacks div.message {
		display: none;
		background: #ccc;
	}
	.vbx-stacks .stack span,
	.vbx-stacks .message span {
		display: inline-block;
		width: 25%;
		text-align: center;
		float: left;
		vertical-align: middle;
		line-height: 24px;
	}
	.vbx-stacks .message span.l {
		width: 75%;
	}
	.vbx-stacks .stack a {
		text-decoration: none;
		color: #111;
	}
	.vbx-stacks form {
		display: none;
		padding: 20px 5%;
		background: #eee;
		border-bottom: 1px solid #ccc;
	}
	.vbx-stacks a.sms,
	.vbx-stacks a.delete {
		display: inline-block;
		height: 24px;
		width: 24px;
		text-indent: -999em;
		background: transparent url(/assets/i/standard-icons-sprite.png) no-repeat -34px 0;
	}
	.vbx-stacks a.delete {
		background: transparent url(/assets/i/action-icons-sprite.png) no-repeat -68px 0;
	}
</style>
<div class="vbx-content-main">
	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Subscription Stacks</h2>
		<ul class="vbx-menu-items-right">
			<li class="menu-item">
				<button id="button-add-stack" class="inline-button add-button"><span>Add Stack</span></button>
			</li>
		</ul>
	</div>
	<div class="vbx-table-section vbx-stacks">
		<form class="add add-stack" method="post" action="">
			<h3>Add Stack</h3>
			<fieldset class="vbx-input-container">
<?php if(count($lists)): ?>
				<p>
					<label class="field-label">
						<select name="list" class="medium">
<?php foreach($lists as $list): ?>
							<option value="<?php echo $list->id; ?>"><?php echo $list->name; ?></option>
<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p>
					<label class="field-label">Name
						<input type="text" class="medium" name="name" />
					</label>
				</p>
				<p>
					<label class="field-label message">Message
						<textarea rows="20" cols="100" name="message[]" class="medium"></textarea>
					</label>
				</p>
				<p>
					<button type="submit" class="inline-button submit-button"><span>Add Message</span></button>
					<button type="submit" class="inline-button submit-button"><span>Save</span></button>
				</p>
<?php else: ?>
				<p>You do not have any lists!</p>
<?php endif; ?>
			</fieldset>
		</form>
		<form class="update update-sms" method="post" action="">
			<h3>Send update to <span></span></h3>
			<fieldset class="vbx-input-container">
<?php if(count($callerid_numbers)): ?>
				<p>
					<label class="field-label">Caller ID<br/>
						<select name="callerId" class="medium">
<?php foreach($callerid_numbers as $number): ?>
							<option value="<?php echo $number->phone; ?>"><?php echo $number->name; ?></option>
<?php endforeach; ?>
						</select>
					</label>
				</p>
				<p><input type="hidden" name="stack" /></p>
				<p><button type="submit" class="submit-button"><span>Send</span></button></p>
<?php else: ?>
				<p>You do not have any phone numbers!</p>
<?php endif; ?>
			</fieldset>
		</form>
<?php if(count($stacks)): ?>
		<div class="stack">
			<h3>
				<span>Name</span>
				<span>Messages</span>
				<span>SMS</span>
				<span>Delete</span>
			</h3>
		</div>
<?php foreach($stacks as $stack):
	$messages = unserialize($stack->messages);
	$pointers = unserialize($stack->pointers);
?>
		<div class="stack" id="stack_<?php echo $stack->id; ?>">
			<p>
				<span><?php echo $stack->name; ?></span>
				<span><a href="" class="messages"><?php echo count($messages); ?></a></span>
				<span><a href="" class="sms">SMS</a></span>
				<span><a href="" class="delete">X</a></span>
			</p>
		</div>
<?php if(count($messages)): ?>
<?php foreach($messages as $i => $message): $count = 0; if($pointers) foreach($pointers as $number => $id) if($id == $i + 1) $count++;?>
		<div class="message stack_<?php echo $stack->id; ?>" id="message_<?php echo $stack->id; ?>_<?php echo $i; ?>">
			<p>
				<span class="l"><?php echo htmlentities($message); ?></span>
				<span><a href="" class="delete">X</a></span>
			</p>
		</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
	</div>
</div>

<?php
	$user = OpenVBX::getCurrentUser();
	$tenant_id = $user->values['tenant_id'];
	$ci =& get_instance();
	$selected = AppletInstance::getValue('stack');
	$queries = explode(';', file_get_contents(dirname(dirname(dirname(__FILE__))).'/db.sql'));
	foreach($queries as $query)
		if(trim($query))
			$ci->db->query($query);
	$stacks = $ci->db->query(sprintf('SELECT id, name FROM subscribers_stacks WHERE tenant = %d', $tenant_id))->result();
?>
<div class="vbx-applet">
<?php if(count($stacks)): ?>
	<div class="vbx-full-pane">
		<h3>Stacks</h3>
		<fieldset class="vbx-input-container">
				<select class="medium" name="stack">
<?php foreach($stacks as $stack): ?>
					<option value="<?php echo $stack->id; ?>"<?php echo $stack->id == $selected ? ' selected="selected" ' : ''; ?>><?php echo $stack->name; ?></option>
<?php endforeach; ?>
				</select>
		</fieldset>
	</div>
	<h2>Next</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('next'); ?>
	</div>
<?php else: ?>
	<div class="vbx-full-pane">
		<h3>You need to create a subscription stack first.</h3>
	</div>
<?php endif; ?>
</div>

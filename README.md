# Stacks for OpenVBX

This plugin extends the [Subscriptions plugin][1] to allow for auto-responses and updates from a preset series of messages.

[1]: https://github.com/chadsmith/OpenVBX-Plugin-Subscriptions

## Installation

1. Install the [Subscriptions plugin][1]
2. [Download][2] this plugin and extract to /plugins

[2]: https://github.com/chadsmith/OpenVBX-Plugin-Stacks/archives/master

## Usage

Once installed, STACKS will appear in the OpenVBX sidebar

### Create a new stack (series of messages)

1. Click Manage Stacks in the OpenVBX sidebar
2. Click Add Stack
3. Select the Subscriptions list to use with the stack
4. Enter a name for your stack (this is only seen by you)
5. Enter two or more messages for responses

### Viewing stack messages

1. Click Manage Stacks in the OpenVBX sidebar
2. Find the stack you want to view
3. Click the number of messages

The number next to each message is the number of subscribers at that position in the stack.

### Removing individual stack messages

1. Click Manage Stacks in the OpenVBX sidebar
2. Find the stack you want to view
3. Click the number of messages
4. Click the trash icon next to the message to remove

### Sending an auto-response from a flow

1. Add the Stack applet to your SMS flow
2. Select the Stack

The sender will automatically be added to the list you selected earlier (if they're not already a member) and will receive the first or next message in the stack.

### Sending an auto-response to all list members

1. Click Manage Stacks in the OpenVBX sidebar
2. Find the stack you want to send
3. Click the SMS icon next to the stack
4. Select the caller ID (OpenVBX number) to send with

Each member of the list you selected earlier will receive the first or next message in the stack.

<?php

require_once __DIR__ . '/libs/base.php';

$validation_rules = [
    'receiver-id' => 'exists:users,id,not',
    'message' => 'filled|long:4',
];

$user = get_user($connection);

if ($user === null) {
    header("Location: index.php");
    exit();
}
$title = $site_name . ': Сообщения';
$add_post_button = true;
$form['errors'] = [];

if (count($_POST) > 0 && isset($_POST['receiver-id']) && ($_POST['receiver-id'] !== $user['id'])) {
    $receiver_id = (int) $_POST['receiver-id'];
    $form['values'] = $_POST;
    $form['errors'] = validate($form['values'], $validation_rules, $connection);
    $form['errors'] = array_filter($form['errors']);
    if (empty($form['errors'])) {
        $message = add_message($connection, $user['id'], $receiver_id, $_POST['message']);
        header("Location: " . $_SERVER['PHP_SELF']);
    } else {
        $errors = $form['errors'];
    }
}

$dialogs = get_dialogs($connection, $user['id']);
if (($dialogs !== null) || ($GET['id'] !== null)) {
    $active_dialog_id = (int) ($_GET['id'] ?? array_key_first($dialogs));
    read_messages($connection, $active_dialog_id, $user['id']);
}

$messages = get_messages($connection, $user['id']);
foreach ($messages as $message) {
    array_push($dialogs[$message['dialog']]['messages'], $message);
}

$page_content = include_template(
    'messages-template.php',
    [
        'user' => $user,
        'dialogs' => $dialogs,
        'messages' => $messages,
        'message_errors' => $errors,
        'active_dialog_id' => $active_dialog_id,
        'now_time' => $now_time,
    ]
);
$layout_content = include_template(
    'layout.php',
    [
        'title' => $title,
        'user' => $user,
        'content' => $page_content,
        'active_section' => 'messages',
        'add_post_button' => $add_post_button,
    ]
);
print($layout_content);
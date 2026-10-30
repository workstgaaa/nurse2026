<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$to = 'info@lumileight.com';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid request']);
    exit;
}

function clean($v) {
    $v = trim((string)$v);
    $v = str_replace(["\r", "\n"], ['', ' '], $v);
    return mb_convert_encoding($v, 'UTF-8', 'UTF-8');
}

$name = clean($data['name'] ?? '');
$email = clean($data['email'] ?? '');
$tel = clean($data['tel'] ?? '');
$type = clean($data['type'] ?? '');
$replyMethod = clean($data['replyMethod'] ?? '');
$message = trim((string)($data['message'] ?? ''));

if ($name === '' || $email === '' || $tel === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing or invalid fields']);
    exit;
}

$subject = mb_encode_mimeheader('【Lumileight】お問い合わせ: ' . $name, 'UTF-8');

$body = "Lumileight サイトからのお問い合わせです。\n\n";
$body .= "お名前: {$name}\n";
$body .= "メールアドレス: {$email}\n";
$body .= "電話番号: {$tel}\n";
$body .= "ご希望の返信方法: " . ($replyMethod !== '' ? $replyMethod : '(未選択)') . "\n";
$body .= "お問い合わせ種別: " . ($type !== '' ? $type : '(未選択)') . "\n";
$body .= "お問い合わせ内容:\n{$message}\n";

$headers = "From: Lumileight Website <noreply@lumileight.jp>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$ok = mb_send_mail($to, $subject, $body, $headers);

if ($ok) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'mail failed']);
}

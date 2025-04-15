<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // フォームからのデータを取得
  $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
  $company = htmlspecialchars($_POST['company'], ENT_QUOTES, 'UTF-8');
  $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
  $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
  $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
  $recaptchaToken = $_POST['recaptcha_token'];

  // reCAPTCHAの検証
  $recaptchaSecret = "6LflXFMqAAAAAFb4-jhcW3PDQaVIxTfZklKc8nPo";
  $recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify";
  $recaptchaData = [
    'secret' => $recaptchaSecret,
    'response' => $recaptchaToken
  ];

  $options = [
    'http' => [
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method'  => 'POST',
      'content' => http_build_query($recaptchaData)
    ]
  ];

  $context  = stream_context_create($options);
  $result = file_get_contents($recaptchaUrl, false, $context);
  $resultJson = json_decode($result, true);

  if (!$resultJson['success']) {
    echo "reCAPTCHAの検証に失敗しました。";
    exit;
  }

  // 送信先メールアドレス
  $to = "register@g-hill.jp";

  // メールの件名
  $subject = "お問い合わせがありました";

  // メール本文
  $body = "以下の内容でお問い合わせがありました。\n\n";
  $body .= "お名前: $name\n";
  $body .= "会社名: $company\n";
  $body .= "メールアドレス: $email\n";
  $body .= "電話番号: $phone\n";
  $body .= "お問い合わせ内容:\n$message\n";

  // メールヘッダー
  $headers = "From: $email\r\n";
  $headers .= "Reply-To: $email\r\n";

  // メール送信
  if (mail($to, $subject, $body, $headers)) {
    $autoReplySubject = "お問い合わせありがとうございます";

    // 自動返信メール本文
    $autoReplyBody = "$name 様\n\n";
    $autoReplyBody .= "この度はお問い合わせいただきありがとうございます。\n";
    $autoReplyBody .= "以下の内容でお問い合わせを受け付けました。\n\n";
    $autoReplyBody .= "----------------------------\n";
    $autoReplyBody .= "お名前: $name\n";
    $autoReplyBody .= "会社名: $company\n";
    $autoReplyBody .= "メールアドレス: $email\n";
    $autoReplyBody .= "電話番号: $phone\n";
    $autoReplyBody .= "お問い合わせ内容:\n$message\n";
    $autoReplyBody .= "----------------------------\n\n";
    $autoReplyBody .= "担当者より追ってご連絡いたしますので、今しばらくお待ちください。\n";
    $autoReplyBody .= "よろしくお願いいたします。\n";

    // 自動返信メールヘッダー
    $autoReplyHeaders = "From: register@g-hill.jp\r\n";

    // 自動返信メール送信
    mail($email, $autoReplySubject, $autoReplyBody, $autoReplyHeaders);

    // thanks.html へリダイレクト
    header("Location: thanks.html");
    exit;
  } else {
    echo "メールの送信に失敗しました。";
  }
}
?>
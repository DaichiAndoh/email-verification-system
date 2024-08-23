<?php

namespace Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Helpers\Settings;

class MailSend {
    public static function sendVerificationMail(string $signedURL, string $toAddress, string $toName): bool {
        $mail = new PHPMailer(true);

        try {
            // サーバの設定
            $mail->isSMTP(); // SMTPを使用するようにメーラーを設定
            $mail->Host = 'smtp.gmail.com'; // GmailのSMTPサーバ
            $mail->SMTPAuth = true; // SMTP認証を有効化
            $mail->Username = Settings::env('SMPT_USER'); // SMTPユーザー名
            $mail->Password = Settings::env('SMPT_PASSWORD'); // SMTPパスワード
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS暗号化を有効化
            $mail->Port = 587; // 接続先のTCPポート

            $mail->setFrom(Settings::env('MAIL_FROM_ADDRESS'), Settings::env('MAIL_FROM_NAME')); // 送信者を設定
            $mail->addAddress($toAddress, $toName); // 受信者を追加

            $mail->Subject = 'Verify Your Email Address';

            $mail->isHTML(); // メール形式をHTMLに設定
            ob_start();
            extract(['signedURL' => $signedURL]);
            include('../Views/mail/verify.php');
            $mail->Body = ob_get_clean();

            $textBody = file_get_contents('../Views/mail/verify.txt');
            $textBody = str_replace('[SignedURL]', $signedURL, $textBody);
            $mail->AltBody = $textBody;

            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}

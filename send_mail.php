<?php
/*
 send_mail.php

 Minimalny szablon endpointa do obsługi formularza kontaktowego.
 Wysyła wiadomość przez SMTP (PHPMailer) i zwraca JSON.

 Instrukcje:
 - Umieść ten plik w katalogu publicznym (np. public_html) jako /send_mail.php
 - Zainstaluj PHPMailer obok tego pliku (np. skopiuj folder PHPMailer/ lub użyj composera)
 - Uzupełnij zmienne $smtpUser, $smtpPass oraz $recipientEmail poniżej
 - Nie commituj haseł do repo. Lepiej umieścić je w pliku konfiguracyjnym poza webroot lub jako zmienne środowiskowe.

 Bezpieczeństwo:
 - Skrypt sprawdza pole honeypot 'hp' i odrzuca wypełnione (prosta ochrona antyspam).
 - Dodatkowo validatesuje e-mail i wyjściowe dane.
*/

header('Content-Type: application/json; charset=utf-8');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Simple helper to fetch raw POST fields (works with application/x-www-form-urlencoded)
// If your front-end sends FormData via fetch, PHP will populate $_POST accordingly.
function get_post($key){
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$hp = get_post('hp'); // honeypot
if (!empty($hp)) {
    // probable bot
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Spam detected']);
    exit;
}

$name = htmlspecialchars(get_post('name'));
$email = htmlspecialchars(get_post('email'));
$subject = htmlspecialchars(get_post('subject') ?: 'Kontakt ze strony');
$message = htmlspecialchars(get_post('message'));

$errors = [];
if (empty($name)) $errors[] = 'Brak imienia.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Niepoprawny email.';
if (empty($message)) $errors[] = 'Brak treści wiadomości.';

if (!empty($errors)){
    http_response_code(400);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

/*
 SMTP configuration - uzupełnij wartości poniżej. Nie commituj haseł.
*/
$smtpHost = 's54.cyber-folks.pl';
$smtpPort = 587; // 587 (STARTTLS) lub 465 (SSL)
$smtpSecure = 'tls'; // 'tls' for STARTTLS (port 587) or 'ssl' for port 465
$smtpUser = 'kontakt@yourdomain.com'; // <-- zamień na swój adres
$smtpPass = 'CHANGE_ME'; // <-- uzupełnij hasło bez commitu
$recipientEmail = 'kontakt@yourdomain.com'; // <-- gdzie ma przychodzić wiadomość

// Load PHPMailer (try composer autoload first, then fallback to bundled files)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
} else {
    // expects PHPMailer files in PHPMailer/src/
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    if ($smtpSecure === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    $mail->Port = (int)$smtpPort;

    // Recipients
    $mail->setFrom($smtpUser, 'Kontakt ze strony');
    $mail->addAddress($recipientEmail);
    // Reply-To na adres podany przez użytkownika
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($email, $name);
    }

    // Content
    $mail->isHTML(false);
    $body = "Imię i nazwisko: {$name}\nE-mail: {$email}\n\n" . $message;
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();

    echo json_encode(['ok' => true]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $mail->ErrorInfo]);
    exit;
}

?>

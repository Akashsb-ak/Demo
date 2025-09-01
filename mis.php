<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

// Load SMTP credentials from config.php
$config = require __DIR__ . '/config.php';

$mail = new PHPMailer(true);

try {
        // Replace with your actual secret key from Google reCAPTCHA Admin Console
        $secretKey = '6LeaVYoqAAAAABhYT9mXhVtpo-A4JCANWK9zHDwF'; 
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
            // Check if reCAPTCHA response is set
            if (empty($_POST['g-recaptcha-response'])) {
                echo "<script>alert('reCAPTCHA response missing. Please complete the reCAPTCHA.'); window.location.href = 'quote.html';</script>";
                exit;
            }
        
            // reCAPTCHA response verification
            $captchaResponse = $_POST['g-recaptcha-response'];
            $remoteIp = $_SERVER['REMOTE_ADDR'];
        
            // Google reCAPTCHA API URL
            $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
        
            // Prepare data to send to Google reCAPTCHA API
            $recaptchaData = [
                'secret' => $secretKey,
                'response' => $captchaResponse,
                'remoteip' => $remoteIp
            ];
        
            // Use cURL to verify reCAPTCHA response
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $recaptchaUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($recaptchaData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            // Get response from Google reCAPTCHA API
            $recaptchaResponse = curl_exec($ch);
            curl_close($ch);
        
            // Decode the response from Google
            $recaptchaResult = json_decode($recaptchaResponse, true);
        
            // Check if reCAPTCHA verification was successful
            if (!$recaptchaResult['success']) {
                // Debug: Print the error codes from Google
                echo '<pre>';
                print_r($recaptchaResult);
                echo '</pre>';
                exit;
            }
        
            // If reCAPTCHA verification is successful, proceed with PHPMailer
            $mail = new PHPMailer(true);

    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Set to SMTP::DEBUG_SERVER for verbose output
    $mail->isSMTP();
    $mail->Host = $config['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['SMTP_USERNAME'];
    $mail->Password = $config['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['SMTP_PORT'];

    // Sender settings
    $mail->setFrom($config['SMTP_FROM_EMAIL'], $config['SMTP_FROM_NAME']);
    $mail->addAddress('info@bezatechnology.com', 'Beza Technology');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "Product Enquiry from Website";
    $messageBody = "Beza Technology : Product Enquiry from Website<hr><br>
                    Hello <b>Admin,</b><br><br>
                    A Product Enquiry from Client.<br><br>
                    Name: " . $_POST["name"] . "<br>
                    Email: " . $_POST["email"] . "<br>
                    Phone: " . $_POST["phone"] . "<br>
                    Subject: " . $_POST["subject"] . "<br>
                    Message: " . $_POST["message"] . "<br>
                    Website :: Beza Technology";

    $mail->Body = $messageBody;
    $mail->AltBody = 'Plain text message body for non-HTML email client.';

    // Send email
    $mail->send();
    echo "<script>alert('Email message sent successfully.'); window.location.href = 'quote.html';</script>";
} catch (Exception $e) {
    echo "Error in sending email. Mailer Error: {$mail->ErrorInfo}";
}
?>

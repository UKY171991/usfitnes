<?php
/**
 * SMTP Email Configuration for PathLab Pro
 * This file contains SMTP settings and email utility functions
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// SMTP Settings
define('SMTP_HOST', 'mail.umakant.online');
define('SMTP_USERNAME', 'info@umakant.online');
define('SMTP_PASSWORD', 'Uma@171991');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
define('FROM_EMAIL', 'info@umakant.online');
define('FROM_NAME', 'PathLab Pro');

/**
 * Send email using SMTP
 */
function sendEmail($to_email, $to_name, $subject, $body, $is_html = true) {
    // Include PHPMailer (you need to install via Composer or download manually)
    // For now, I'll use a fallback method if PHPMailer is not available
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendEmailWithPHPMailer($to_email, $to_name, $subject, $body, $is_html);
    } else {
        return sendEmailWithSocket($to_email, $to_name, $subject, $body, $is_html);
    }
}

/**
 * Send email using PHPMailer (preferred method)
 */
function sendEmailWithPHPMailer($to_email, $to_name, $subject, $body, $is_html = true) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        if (!$is_html) {
            $mail->AltBody = $body;
        }
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return ['success' => false, 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo];
    }
}

/**
 * Send email using socket connection (fallback method)
 */
function sendEmailWithSocket($to_email, $to_name, $subject, $body, $is_html = true) {
    try {
        // Create socket connection
        $socket = fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
        
        if (!$socket) {
            return ['success' => false, 'message' => "Failed to connect to SMTP server: $errstr ($errno)"];
        }
        
        // Read initial response
        $response = fgets($socket);
        
        // SMTP commands
        $commands = [
            "EHLO " . $_SERVER['HTTP_HOST'] ?? 'localhost',
            "AUTH LOGIN",
            base64_encode(SMTP_USERNAME),
            base64_encode(SMTP_PASSWORD),
            "MAIL FROM: <" . FROM_EMAIL . ">",
            "RCPT TO: <$to_email>",
            "DATA"
        ];
        
        foreach ($commands as $command) {
            fwrite($socket, $command . "\r\n");
            $response = fgets($socket);
            
            // Check for errors
            if (substr($response, 0, 1) == '4' || substr($response, 0, 1) == '5') {
                fclose($socket);
                return ['success' => false, 'message' => 'SMTP Error: ' . $response];
            }
        }
        
        // Prepare email headers and body
        $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
        $headers .= "To: $to_name <$to_email>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        
        if ($is_html) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        $headers .= "\r\n";
        
        // Send email content
        fwrite($socket, $headers . $body . "\r\n.\r\n");
        $response = fgets($socket);
        
        // Send QUIT command
        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        
        if (substr($response, 0, 1) == '2') {
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to send email: ' . $response];
        }
        
    } catch (Exception $e) {
        error_log("SMTP Socket Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
    }
}

/**
 * Generate OTP
 */
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Send OTP email
 */
function sendOTPEmail($email, $name, $otp) {
    $subject = "Email Verification - PathLab Pro";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { color: #2c5aa0; font-size: 24px; font-weight: bold; }
            .otp-code { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; border-left: 4px solid #2c5aa0; }
            .otp-number { font-size: 32px; font-weight: bold; color: #2c5aa0; letter-spacing: 5px; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>PathLab Pro</div>
                <h2 style='color: #333; margin: 10px 0;'>Email Verification</h2>
            </div>
            
            <p>Hello $name,</p>
            <p>Thank you for registering with PathLab Pro. To complete your registration, please verify your email address using the OTP code below:</p>
            
            <div class='otp-code'>
                <p style='margin: 0 0 10px 0; color: #666;'>Your Verification Code</p>
                <div class='otp-number'>$otp</div>
                <p style='margin: 10px 0 0 0; color: #666; font-size: 14px;'>This code will expire in 10 minutes</p>
            </div>
            
            <p>If you didn't request this verification, please ignore this email.</p>
            
            <div class='footer'>
                <p>This is an automated message from PathLab Pro<br>
                Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $name, $subject, $body, true);
}

/**
 * Send welcome email after successful registration
 */
function sendWelcomeEmail($email, $name) {
    $subject = "Welcome to PathLab Pro!";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { color: #2c5aa0; font-size: 24px; font-weight: bold; }
            .welcome-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px; }
            .btn { display: inline-block; background: #2c5aa0; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>PathLab Pro</div>
            </div>
            
            <div class='welcome-box'>
                <h2 style='margin: 0 0 10px 0;'>Welcome to PathLab Pro!</h2>
                <p style='margin: 0;'>Your account has been successfully created</p>
            </div>
            
            <p>Hello $name,</p>
            <p>Congratulations! Your PathLab Pro account has been successfully created and verified. You can now access all our laboratory management features.</p>
            
            <p><strong>What you can do now:</strong></p>
            <ul>
                <li>Manage patient records</li>
                <li>Create and track test orders</li>
                <li>Generate comprehensive reports</li>
                <li>Monitor laboratory equipment</li>
                <li>Access advanced analytics</li>
            </ul>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='https://usfitnes.com/login.php' class='btn'>Login to Your Account</a>
            </div>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <div class='footer'>
                <p>Thank you for choosing PathLab Pro<br>
                Advanced Laboratory Management System</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $name, $subject, $body, true);
}
?>

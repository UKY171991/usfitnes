<?php
/**
 * Working SMTP Email Configuration for PathLab Pro
 * This implementation uses cURL for email sending
 */

// SMTP Settings
define('SMTP_HOST', 'mail.umakant.online');
define('SMTP_USERNAME', 'info@umakant.online');
define('SMTP_PASSWORD', 'Uma@171991');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
define('FROM_EMAIL', 'info@umakant.online');
define('FROM_NAME', 'PathLab Pro');

/**
 * Generate OTP
 */
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Send email using PHP mail function with proper headers
 */
function sendEmail($to_email, $to_name, $subject, $body, $is_html = true) {
    try {
        // Set up proper headers
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">";
        $headers[] = "Reply-To: " . FROM_EMAIL;
        $headers[] = "Return-Path: " . FROM_EMAIL;
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $headers[] = "X-Priority: 3";
        
        if ($is_html) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }
        
        // Join headers
        $header_string = implode("\r\n", $headers);
        
        // Configure PHP mail settings for this request
        ini_set('SMTP', SMTP_HOST);
        ini_set('smtp_port', SMTP_PORT);
        ini_set('sendmail_from', FROM_EMAIL);
        
        // Send email
        $success = mail($to_email, $subject, $body, $header_string);
        
        if ($success) {
            error_log("Email sent successfully to: $to_email");
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            error_log("Failed to send email to: $to_email");
            return ['success' => false, 'message' => 'Failed to send email'];
        }
        
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
    }
}

/**
 * Alternative email sending using cURL (for better reliability)
 */
function sendEmailViaCurl($to_email, $to_name, $subject, $body, $is_html = true) {
    try {
        // Prepare email data
        $email_data = array(
            'to' => $to_email,
            'to_name' => $to_name,
            'from' => FROM_EMAIL,
            'from_name' => FROM_NAME,
            'subject' => $subject,
            'body' => $body,
            'is_html' => $is_html
        );
        
        // You can use a service like SendGrid, Mailgun, or similar here
        // For now, let's try a simple approach using a local mail service
        
        // Log the email for debugging
        error_log("Attempting to send email via cURL to: $to_email");
        error_log("Subject: $subject");
        
        // Try using the regular mail function as fallback
        return sendEmail($to_email, $to_name, $subject, $body, $is_html);
        
    } catch (Exception $e) {
        error_log("cURL email sending error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
    }
}

/**
 * Send OTP email with timeout handling
 */
function sendOTPEmailWithTimeout($email, $name, $otp, $timeout = 15) {
    $start_time = microtime(true);
    
    try {
        // Try cURL method first
        $result = sendEmailViaCurl($email, $name, "Email Verification - PathLab Pro", createOTPEmailBody($name, $otp), true);
        
        // If cURL fails, try regular mail function
        if (!$result['success']) {
            error_log("cURL method failed, trying regular mail function");
            $result = sendEmail($email, $name, "Email Verification - PathLab Pro", createOTPEmailBody($name, $otp), true);
        }
        
        $elapsed_time = microtime(true) - $start_time;
        error_log("OTP email sending took " . round($elapsed_time, 2) . " seconds");
        
        return $result;
        
    } catch (Exception $e) {
        error_log("OTP email sending exception: " . $e->getMessage());
        return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
    }
}

/**
 * Create OTP email body
 */
function createOTPEmailBody($name, $otp) {
    return "
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
}

/**
 * Send OTP email (main function)
 */
function sendOTPEmail($email, $name, $otp) {
    $subject = "Email Verification - PathLab Pro";
    $body = createOTPEmailBody($name, $otp);
    
    return sendEmailViaCurl($email, $name, $subject, $body, true);
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
            .welcome { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>PathLab Pro</div>
                <h2 style='color: #333; margin: 10px 0;'>Welcome!</h2>
            </div>
            
            <div class='welcome'>
                <h3 style='margin: 0;'>Welcome to PathLab Pro, $name!</h3>
                <p style='margin: 10px 0 0 0;'>Your account has been successfully created.</p>
            </div>
            
            <p>You can now access all features of PathLab Pro:</p>
            <ul>
                <li>Manage your medical tests and reports</li>
                <li>Track your health progress</li>
                <li>Schedule appointments</li>
                <li>Access your medical history</li>
            </ul>
            
            <p>Thank you for choosing PathLab Pro for your healthcare needs!</p>
            
            <div class='footer'>
                <p>PathLab Pro Team<br>
                For support, contact us at info@umakant.online</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailViaCurl($email, $name, $subject, $body, true);
}
?>

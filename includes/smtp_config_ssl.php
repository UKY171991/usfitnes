<?php
/**
 * Advanced SMTP Email Configuration for PathLab Pro
 * This implementation handles SSL SMTP connections properly
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
 * Send email using SSL SMTP connection
 */
function sendEmail($to_email, $to_name, $subject, $body, $is_html = true) {
    try {
        // Create SSL context
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        // Connect to SMTP server with SSL
        $smtp_server = 'ssl://' . SMTP_HOST . ':' . SMTP_PORT;
        $socket = stream_socket_client($smtp_server, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        
        if (!$socket) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return ['success' => false, 'message' => "SMTP connection failed: $errstr"];
        }
        
        // Set timeout
        stream_set_timeout($socket, 10);
        
        // Read initial response
        $response = '';
        while ($line = fgets($socket)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break; // End of multi-line response
        }
        
        if (!$response || substr($response, 0, 3) !== '220') {
            fclose($socket);
            return ['success' => false, 'message' => 'Invalid SMTP response: ' . $response];
        }
        
        // Function to read SMTP response (handles multi-line responses)
        $readResponse = function() use ($socket) {
            $response = '';
            while ($line = fgets($socket)) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') break; // End of multi-line response
            }
            return $response;
        };
        
        // SMTP conversation
        $hostname = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $commands = [
            "EHLO $hostname",
            "AUTH LOGIN",
            base64_encode(SMTP_USERNAME),
            base64_encode(SMTP_PASSWORD),
            "MAIL FROM: <" . FROM_EMAIL . ">",
            "RCPT TO: <$to_email>",
            "DATA"
        ];
        
        foreach ($commands as $i => $command) {
            fputs($socket, $command . "\r\n");
            $response = $readResponse();
            
            error_log("SMTP Command: $command");
            error_log("SMTP Response: " . trim($response));
            
            // Check for errors based on command
            $code = substr($response, 0, 3);
            
            if ($i == 0 && !in_array($code, ['220', '250'])) { // EHLO
                fclose($socket);
                return ['success' => false, 'message' => "EHLO failed: $response"];
            }
            if ($i == 1 && $code !== '334') { // AUTH LOGIN
                fclose($socket);
                return ['success' => false, 'message' => "AUTH LOGIN failed: $response"];
            }
            if ($i == 2 && $code !== '334') { // Username
                fclose($socket);
                return ['success' => false, 'message' => "Username failed: $response"];
            }
            if ($i == 3 && $code !== '235') { // Password
                fclose($socket);
                return ['success' => false, 'message' => "Authentication failed: $response"];
            }
            if ($i == 4 && $code !== '250') { // MAIL FROM
                fclose($socket);
                return ['success' => false, 'message' => "MAIL FROM failed: $response"];
            }
            if ($i == 5 && $code !== '250') { // RCPT TO
                fclose($socket);
                return ['success' => false, 'message' => "RCPT TO failed: $response"];
            }
            if ($i == 6 && $code !== '354') { // DATA
                fclose($socket);
                return ['success' => false, 'message' => "DATA command failed: $response"];
            }
        }
        
        // Send email headers and body
        $email_content = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
        $email_content .= "To: $to_name <$to_email>\r\n";
        $email_content .= "Subject: $subject\r\n";
        $email_content .= "Date: " . date('r') . "\r\n";
        
        if ($is_html) {
            $email_content .= "MIME-Version: 1.0\r\n";
            $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $email_content .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        $email_content .= "\r\n" . $body . "\r\n.\r\n";
        
        fputs($socket, $email_content);
        $response = $readResponse();
        
        // Send QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        if (substr($response, 0, 3) === '250') {
            error_log("Email sent successfully to: $to_email");
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            error_log("Email sending failed: $response");
            return ['success' => false, 'message' => 'Email sending failed: ' . $response];
        }
        
    } catch (Exception $e) {
        error_log("Email sending exception: " . $e->getMessage());
        return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
    }
}

/**
 * Send OTP email with timeout handling
 */
function sendOTPEmailWithTimeout($email, $name, $otp, $timeout = 15) {
    $start_time = microtime(true);
    
    // Set execution time limit
    set_time_limit($timeout + 5);
    
    try {
        $result = sendOTPEmail($email, $name, $otp);
        
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
    
    return sendEmail($email, $name, $subject, $body, true);
}
?>

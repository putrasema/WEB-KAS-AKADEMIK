<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once __DIR__ . '/../Config/email_config.php';

class NotificationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Send email notification via Gmail SMTP
     * 
     * @param int $userId User ID
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $htmlBody HTML email body
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendEmail($userId, $toEmail, $toName, $subject, $htmlBody)
    {

        if (!$this->isEmailConfigured()) {
            $errorMsg = "⚠️ Konfigurasi email belum diatur!\n\n" .
                "Silakan update file config/email_config.php dengan:\n" .
                "1. Email Gmail Anda\n" .
                "2. App Password dari Google\n\n" .
                "Panduan: Buka SETUP_EMAIL.md untuk instruksi lengkap.";

            $this->logNotification($userId, $subject, $errorMsg, 'email', 'failed');

            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = SMTP_AUTH;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = EMAIL_CHARSET;

            if (EMAIL_DEBUG > 0) {
                $mail->SMTPDebug = EMAIL_DEBUG;
            }


            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);


            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();


            $this->logNotification($userId, $subject, 'Email sent to: ' . $toEmail, 'email', 'sent');

            return [
                'success' => true,
                'message' => 'Email berhasil dikirim ke ' . $toEmail
            ];

        } catch (Exception $e) {

            $errorMsg = "Email gagal dikirim.\n\n";


            if (strpos($mail->ErrorInfo, 'authenticate') !== false) {
                $errorMsg .= "❌ SMTP Authentication Error\n\n" .
                    "Kemungkinan penyebab:\n" .
                    "1. Email atau App Password salah\n" .
                    "2. Belum menggunakan App Password (harus generate dari Google)\n" .
                    "3. 2-Step Verification belum aktif\n\n" .
                    "Solusi: Buka SETUP_EMAIL.md untuk panduan setup.";
            } elseif (strpos($mail->ErrorInfo, 'connect') !== false) {
                $errorMsg .= "❌ Connection Error\n\n" .
                    "Tidak dapat terhubung ke Gmail SMTP.\n" .
                    "Pastikan koneksi internet aktif.";
            } else {
                $errorMsg .= "Error: " . $mail->ErrorInfo;
            }

            $this->logNotification($userId, $subject, $errorMsg, 'email', 'failed');

            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }
    }

    /**
     * Check if email configuration is properly set
     * 
     * @return bool
     */
    private function isEmailConfigured()
    {

        $placeholderValues = [
            'your-email@gmail.com',
            'your-app-password-here',
            'xxxx xxxx xxxx xxxx'
        ];

        if (
            in_array(SMTP_USERNAME, $placeholderValues) ||
            in_array(SMTP_PASSWORD, $placeholderValues) ||
            in_array(SMTP_FROM_EMAIL, $placeholderValues)
        ) {
            return false;
        }


        if (!filter_var(SMTP_USERNAME, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    /**
     * Send payment reminder email to student
     * 
     * @param array $studentData Student information
     * @param string $categoryName Payment category name
     * @param string $monthYear Month and year (e.g., "December 2025")
     * @param string $paymentLink Link to payment page
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendPaymentReminder($studentData, $categoryName, $monthYear, $paymentLink = '')
    {

        if (empty($studentData['email'])) {
            return [
                'success' => false,
                'message' => 'Email mahasiswa tidak tersedia'
            ];
        }


        $template = file_get_contents(__DIR__ . '/../Includes/email_template.php');


        $htmlBody = str_replace(
            ['{{STUDENT_NAME}}', '{{STUDENT_NIM}}', '{{CATEGORY_NAME}}', '{{MONTH_YEAR}}', '{{PAYMENT_LINK}}', '{{CURRENT_YEAR}}'],
            [
                $studentData['full_name'],
                $studentData['student_id_number'],
                $categoryName,
                $monthYear,
                $paymentLink ?: '#',
                date('Y')
            ],
            $template
        );

        $subject = "Pengingat Pembayaran {$categoryName} - {$monthYear}";


        $userId = $studentData['user_id'] ?? 0;

        return $this->sendEmail(
            $userId,
            $studentData['email'],
            $studentData['full_name'],
            $subject,
            $htmlBody
        );
    }

    /**
     * Send password reset email to user
     * 
     * @param array $userData User information
     * @param string $resetToken Reset token
     * @param string $resetLink Full reset link URL
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendPasswordResetEmail($userData, $resetToken, $resetLink)
    {

        if (empty($userData['email'])) {
            return [
                'success' => false,
                'message' => 'Email pengguna tidak tersedia'
            ];
        }


        $htmlBody = '
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: 600;
                }
                .email-body {
                    padding: 30px;
                }
                .alert-box {
                    background-color: #e7f3ff;
                    border-left: 4px solid #2196F3;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .alert-box h3 {
                    margin-top: 0;
                    color: #1976D2;
                    font-size: 18px;
                }
                .btn {
                    display: inline-block;
                    padding: 12px 30px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                    font-weight: 600;
                }
                .email-footer {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                .warning-text {
                    color: #d32f2f;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>🔐 Reset Password</h1>
                </div>
                <div class="email-body">
                    <p>Halo <strong>' . htmlspecialchars($userData['full_name']) . '</strong>,</p>
                    
                    <p>Kami menerima permintaan untuk mereset password akun Anda di <strong>Sistem Kas Akademik</strong>.</p>
                    
                    <div class="alert-box">
                        <h3>🔑 Link Reset Password</h3>
                        <p>Klik tombol di bawah ini untuk mereset password Anda:</p>
                    </div>
                    
                    <center>
                        <a href="' . htmlspecialchars($resetLink) . '" class="btn">🔓 Reset Password Saya</a>
                    </center>
                    
                    <p style="margin-top: 30px; font-size: 14px; color: #666;">
                        Link ini akan <span class="warning-text">kadaluarsa dalam 1 jam</span> untuk keamanan akun Anda.
                    </p>
                    
                    <p style="font-size: 14px; color: #666;">
                        Jika Anda tidak meminta reset password, mohon abaikan email ini dan password Anda tidak akan berubah.
                    </p>
                    
                    <p style="font-size: 12px; color: #999; margin-top: 20px;">
                        <strong>Catatan Keamanan:</strong> Jangan bagikan link ini kepada siapapun. Tim kami tidak akan pernah meminta password Anda.
                    </p>
                </div>
                <div class="email-footer">
                    <p><strong>Sistem Kas Akademik</strong></p>
                    <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                    <p style="margin-top: 10px; color: #999;">© ' . date('Y') . ' Sistem Kas Akademik. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';

        $subject = "Reset Password - Sistem Kas Akademik";

        return $this->sendEmail(
            $userData['id'],
            $userData['email'],
            $userData['full_name'],
            $subject,
            $htmlBody
        );
    }


    /**
     * Send bulk payment reminders to multiple students
     * 
     * @param array $students Array of student data
     * @param string $categoryName Payment category name
     * @param string $monthYear Month and year
     * @param string $paymentLink Link to payment page
     * @return array ['total' => int, 'success' => int, 'failed' => int, 'details' => array]
     */
    public function sendBulkPaymentReminders($students, $categoryName, $monthYear, $paymentLink = '')
    {
        $results = [
            'total' => count($students),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($students as $student) {
            $result = $this->sendPaymentReminder($student, $categoryName, $monthYear, $paymentLink);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'student_name' => $student['full_name'],
                'email' => $student['email'] ?? 'N/A',
                'success' => $result['success'],
                'message' => $result['message']
            ];
        }

        return $results;
    }

    public function sendWebPush($userId, $title, $message)
    {

        $this->logNotification($userId, $title, $message, 'web_push', 'pending');
        return true;
    }

    private function logNotification($userId, $title, $message, $type, $status)
    {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, status, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $title, $message, $type, $status]);
    }

    public function getNotifications($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}


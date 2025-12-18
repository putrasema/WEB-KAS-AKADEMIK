<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .alert-box h3 {
            margin-top: 0;
            color: #856404;
            font-size: 18px;
        }

        .info-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .info-table td:first-child {
            font-weight: 600;
            width: 40%;
            color: #666;
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

        .highlight {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🔔 Pengingat Pembayaran Uang Kas</h1>
        </div>

        <div class="email-body">
            <p>Halo <strong>{{STUDENT_NAME}}</strong>,</p>

            <p>Ini adalah pengingat otomatis dari <strong>Sistem Kas Akademik</strong>.</p>

            <div class="alert-box">
                <h3>⚠️ Pembayaran Belum Diterima</h3>
                <p>Kami ingin mengingatkan bahwa pembayaran <strong>{{CATEGORY_NAME}}</strong> untuk bulan
                    <strong>{{MONTH_YEAR}}</strong> belum kami terima.</p>
            </div>

            <table class="info-table">
                <tr>
                    <td>NIM</td>
                    <td>{{STUDENT_NIM}}</td>
                </tr>
                <tr>
                    <td>Nama Lengkap</td>
                    <td>{{STUDENT_NAME}}</td>
                </tr>
                <tr>
                    <td>Kategori</td>
                    <td>{{CATEGORY_NAME}}</td>
                </tr>
                <tr>
                    <td>Periode</td>
                    <td>{{MONTH_YEAR}}</td>
                </tr>
            </table>

            <p>Mohon segera melakukan pembayaran untuk menghindari keterlambatan.</p>

            <center>
                <a href="{{PAYMENT_LINK}}" class="btn">💳 Bayar Sekarang</a>
            </center>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                Jika Anda sudah melakukan pembayaran, mohon abaikan email ini.
                Untuk pertanyaan lebih lanjut, silakan hubungi administrator.
            </p>
        </div>

        <div class="email-footer">
            <p><strong>Sistem Kas Akademik</strong></p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            <p style="margin-top: 10px; color: #999;">© {{CURRENT_YEAR}} Sistem Kas Akademik. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
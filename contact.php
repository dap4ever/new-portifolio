<?php
// Carrega configurações sensíveis (não versionado no git)
require __DIR__ . '/config.php';

// Carrega as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';


header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Método inválido."]);
    exit;
}

$name = strip_tags(trim($_POST["name"] ?? ""));
$email = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
$subject = strip_tags(trim($_POST["subject"] ?? ""));
$message = strip_tags(trim($_POST["message"] ?? ""));

if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Por favor, preencha todos os campos corretamente."]);
    exit;
}

// Verificar reCAPTCHA
$recaptchaSecret = RECAPTCHA_SECRET;
$recaptchaToken  = $_POST['g-recaptcha-response'] ?? '';

if (empty($recaptchaToken)) {
    echo json_encode(["success" => false, "message" => "Por favor, complete o reCAPTCHA."]);
    exit;
}

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaToken}");
$captchaResult = json_decode($verify);

if (!$captchaResult->success || $captchaResult->score < 0.5) {
    echo json_encode(["success" => false, "message" => "Falha na verificação reCAPTCHA. Tente novamente."]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // Configurações do servidor SMTP (Hostinger)
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Remetente e destinatário
    $mail->setFrom(SMTP_USER, 'Portfólio - Danilo Pérez');
    $mail->addAddress(MAIL_TO, 'Danilo Pérez');
    $mail->addReplyTo($email, $name);

    // Conteúdo do e-mail
    $mail->isHTML(true);
    $mail->Subject = "Portfólio | $subject";
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 30px; border: 1px solid #eee; border-radius: 12px;'>
            <h2 style='color: #333;'>📩 Nova mensagem do Portfólio</h2>
            <hr style='border: none; border-top: 1px solid #eee;'>
            <p><strong>Nome:</strong> {$name}</p>
            <p><strong>E-mail:</strong> <a href='mailto:{$email}'>{$email}</a></p>
            <p><strong>Assunto:</strong> {$subject}</p>
            <hr style='border: none; border-top: 1px solid #eee;'>
            <p><strong>Mensagem:</strong></p>
            <p style='background: #f9f9f9; padding: 15px; border-radius: 8px;'>" . nl2br(htmlspecialchars($message)) . "</p>
        </div>
    ";
    $mail->AltBody = "Nome: $name\nE-mail: $email\nAssunto: $subject\n\nMensagem:\n$message";

    $mail->send();

    // === AUTO-RESPOSTA PARA O CLIENTE (Multilíngue) ===
    $lang = in_array($_POST['lang'] ?? 'pt', ['pt','en','es']) ? $_POST['lang'] : 'pt';

    // Textos por idioma
    $i18n = [
        'pt' => [
            'subject'    => 'Obrigado pelo seu contato!',
            'label'      => 'Portfólio Profissional',
            'role'       => 'Desenvolvedor Web Full Stack',
            'greeting'   => "Olá, {$name}!",
            'headline'   => 'Recebi sua mensagem! 🚀',
            'p1'         => 'Obrigado por entrar em contato comigo. Já recebi sua mensagem e entrarei em contato o mais breve possível para conversarmos sobre o seu projeto.',
            'p2'         => 'Enquanto isso, fique à vontade para explorar meu portfólio e conhecer meus trabalhos recentes.',
            'msg_label'  => 'Sua mensagem',
            'cta'        => 'Ver Portfólio →',
            'altbody'    => "Olá, {$name}!\n\nRecebi sua mensagem e entrarei em contato em breve.\n\nAtenciosamente,\nDanilo Pérez\ndevwebwizard.com",
        ],
        'en' => [
            'subject'    => 'Thank you for reaching out!',
            'label'      => 'Professional Portfolio',
            'role'       => 'Full Stack Web Developer',
            'greeting'   => "Hello, {$name}!",
            'headline'   => 'Got your message! 🚀',
            'p1'         => "Thank you for contacting me. I've already received your message and will get back to you as soon as possible to discuss your project.",
            'p2'         => 'In the meantime, feel free to explore my portfolio and check out my recent work.',
            'msg_label'  => 'Your message',
            'cta'        => 'View Portfolio →',
            'altbody'    => "Hello, {$name}!\n\nThank you for your message. I'll get back to you shortly.\n\nBest regards,\nDanilo Pérez\ndevwebwizard.com",
        ],
        'es' => [
            'subject'    => '¡Gracias por tu contacto!',
            'label'      => 'Portafolio Profesional',
            'role'       => 'Desarrollador Web Full Stack',
            'greeting'   => "¡Hola, {$name}!",
            'headline'   => '¡Recibí tu mensaje! 🚀',
            'p1'         => 'Gracias por contactarme. Ya recibí tu mensaje y me pondré en contacto contigo lo antes posible para hablar sobre tu proyecto.',
            'p2'         => 'Mientras tanto, siéntete libre de explorar mi portafolio y conocer mis trabajos más recientes.',
            'msg_label'  => 'Tu mensaje',
            'cta'        => 'Ver Portafolio →',
            'altbody'    => "¡Hola, {$name}!\n\nHe recibido tu mensaje y me pondré en contacto en breve.\n\nAtentamente,\nDanilo Pérez\ndevwebwizard.com",
        ],
    ];
    $t = $i18n[$lang];
    $msgPreview = htmlspecialchars(mb_substr($message, 0, 200)) . (mb_strlen($message) > 200 ? '...' : '');

    $autoReply = new PHPMailer(true);
    $autoReply->isSMTP();
    $autoReply->Host       = SMTP_HOST;
    $autoReply->SMTPAuth   = true;
    $autoReply->Username   = SMTP_USER;
    $autoReply->Password   = SMTP_PASS;
    $autoReply->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $autoReply->Port       = SMTP_PORT;
    $autoReply->CharSet    = 'UTF-8';

    $autoReply->setFrom(SMTP_USER, 'Danilo Pérez | Dev');
    $autoReply->addAddress($email, $name);
    $autoReply->isHTML(true);
    $autoReply->Subject = $t['subject'];
    $autoReply->Body = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#0f0f1a;font-family:Arial,sans-serif;'>
      <table width='100%' cellpadding='0' cellspacing='0' style='background:#0f0f1a;padding:40px 0;'>
        <tr><td align='center'>
          <table width='600' cellpadding='0' cellspacing='0' style='max-width:600px;width:100%;background:#16162a;border-radius:20px;overflow:hidden;border:1px solid rgba(255,255,255,0.08);'>
            <tr>
              <td style='background:linear-gradient(135deg,#0d0d22 0%,#1a1a3e 100%);padding:48px 40px 36px;text-align:center;border-bottom:2px solid #00d9c0;'>
                <p style='margin:0 0 12px;font-size:13px;color:#00d9c0;letter-spacing:3px;text-transform:uppercase;'>{$t['label']}</p>
                <h1 style='margin:0;font-size:32px;font-weight:700;color:#ffffff;'>Danilo Pérez</h1>
                <p style='margin:8px 0 0;font-size:13px;color:#a0a0c0;'>{$t['role']}</p>
              </td>
            </tr>
            <tr>
              <td style='padding:44px 40px 36px;'>
                <p style='margin:0 0 8px;font-size:13px;color:#00d9c0;font-weight:600;letter-spacing:1px;text-transform:uppercase;'>{$t['greeting']}</p>
                <h2 style='margin:0 0 24px;font-size:24px;font-weight:700;color:#ffffff;line-height:1.3;'>{$t['headline']}</h2>
                <p style='margin:0 0 16px;font-size:15px;color:#b0b0cc;line-height:1.7;'>{$t['p1']}</p>
                <p style='margin:0 0 32px;font-size:15px;color:#b0b0cc;line-height:1.7;'>{$t['p2']}</p>
                <div style='background:#0f0f22;border-left:3px solid #00d9c0;border-radius:0 12px 12px 0;padding:20px 24px;margin-bottom:36px;'>
                  <p style='margin:0 0 8px;font-size:12px;color:#00d9c0;letter-spacing:1px;text-transform:uppercase;font-weight:600;'>{$t['msg_label']}</p>
                  <p style='margin:0;font-size:14px;color:#8080a0;font-style:italic;line-height:1.6;'>{$msgPreview}</p>
                </div>
                <div style='text-align:center;margin-bottom:8px;'>
                  <a href='https://devwebwizard.com' style='display:inline-block;background:linear-gradient(135deg,#00d9c0,#00a896);color:#0f0f1a;text-decoration:none;padding:16px 36px;border-radius:50px;font-size:15px;font-weight:700;letter-spacing:0.5px;'>{$t['cta']}</a>
                </div>
              </td>
            </tr>
            <tr>
              <td style='background:#0d0d1e;padding:28px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.06);'>
                <p style='margin:0 0 12px;font-size:13px;color:#505070;'>
                  <a href='https://www.linkedin.com/in/danilo-alves-perez' style='color:#00d9c0;text-decoration:none;margin:0 8px;'>LinkedIn</a> &bull;
                  <a href='https://github.com/dap4ever' style='color:#00d9c0;text-decoration:none;margin:0 8px;'>GitHub</a> &bull;
                  <a href='mailto:contact@devwebwizard.com' style='color:#00d9c0;text-decoration:none;margin:0 8px;'>E-mail</a>
                </p>
                <p style='margin:0;font-size:12px;color:#404060;'>&copy; 2026 Danilo P&eacute;rez &mdash; devwebwizard.com</p>
              </td>
            </tr>
          </table>
        </td></tr>
      </table>
    </body></html>
    ";
    $autoReply->AltBody = $t['altbody'];
    $autoReply->send();
    // === FIM AUTO-RESPOSTA ===

    echo json_encode(["success" => true, "message" => "Mensagem enviada com sucesso!"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro ao enviar: " . $mail->ErrorInfo]);
}

<?php
namespace Goat;

use GoatCore\GoatCore;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    protected $config;

    protected $mailer;

    public function __construct($config, $mailer)
    {
        $this->config = $config;
        $this->mailer = $mailer;
    }


    public function confiure($config)
    {
        $this->config = $config;
    }


    public function send($data)
    {
        try {

            //$data['subject'] ='=?UTF-8?B?'.base64_encode($data['subject']).'?=';
            //$data['nameFrom'] ='=?UTF-8?B?'.base64_encode($data['nameFrom']).'?=';
            $data['nameTo'] = mb_strlen($data['nameTo'])>0 ? $data['nameTo']: $data['mailTo'];

            $smtp = $this->config['smtp']['useSMTP'];

            if ($smtp === true) {

               $this->mailer->isSMTP();
               $this->mailer->Host       = $this->config['smtp']['host'];
               $this->mailer->SMTPAuth   = $this->config['smtp']['auth'];
               $this->mailer->Username   = $this->config['smtp']['user'];
               $this->mailer->Password   = $this->config['smtp']['password'];
               $this->mailer->SMTPSecure = $this->config['smtp']['security'];
               $this->mailer->Port       = $this->config['smtp']['port'];
            }

            //Ask for HTML-friendly debug output
            //$mail->SMTPDebug = 2;
            //$mail->Debugoutput = 'html';

            //Set who the message is to be sent from
            if (isset($data['nameFrom'])) {

               $this->mailer->setFrom($data['mailFrom'], $data['nameFrom']);
                //Set an alternative reply-to address
               $this->mailer->addReplyTo(isset($data['replyTo'])>0 ? $data['replyTo']: $data['mailFrom'], $data['nameFrom']);
            } else {

               $this->mailer->setFrom($data['mailFrom']);
                //Set an alternative reply-to address
               $this->mailer->addReplyTo(isset($data['replyTo'])>0 ? $data['replyTo']: $data['mailFrom']);
            }

            //Set who the message is to be sent to
            if (isset($data['nameFrom'])) {

               $this->mailer->addAddress($data['mailTo'], $data['nameTo']);
            } else {

               $this->mailer->addAddress($data['mailTo']);
            }

            //Set the subject line
           $this->mailer->isHTML(true);
           $this->mailer->Subject = $data['subject'];
           $this->mailer->CharSet = "utf-8";

            //Read an HTML message body
           $this->mailer->Body = $data['body'];

            //Replace the plain text body
           $this->mailer->AltBody = strip_tags($data['body']);

            //Attach files

            if (isset($data['attachments'])) {

                foreach ($data['attachments'] as $attFile) {

                    if (file_exists($attFile) && is_file($attFile)) {

                       $this->mailer->addAttachment($attFile);
                    }
                }
            }
           $this->mailer->send();
            $result = true;

        } catch (\Exception $e) {

            $result = false;
            $errorMessages = 'Server error';
        }

        return $result;
    }
}

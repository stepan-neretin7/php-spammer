<?php


namespace Stepan7\Spammer;

use PHPMailer\PHPMailer\PHPMailer;

require "../vendor/autoload.php";

class PHPSpammer
{
    private $username;
    private $password;
    private $from;
    private $subject;
    private $emails;
    private $log;
    private $attachments;

    /**
     * PHPSpammer constructor.
     * @param string $username
     * @param string $password
     * @param string $from
     * @param string $subject
     * @param string $emails
     * @param bool|null $log
     */
    public function __construct(string $username, string $password, string $from, string $subject, string $emails, bool
    $log = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->from = $from;
        $this->subject = $subject;
        $this->emails = $emails;
        $this->log = $log;
        $this->attachments = [];
    }


    /**
     * @param $path
     * @param $name
     */
    public function setAttachment($path, $name)
    {
        $this->attachments[$path] = $name;
    }


    /**
     * @param $file
     * @return mixed
     */
    private function search($file)
    {
        $fileText = file_get_contents($file);
        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
        preg_match_all($pattern, $fileText, $matches);
        $emails = $matches[0];
        return $emails;
    }

    private function log($message)
    {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/logs')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/logs/', 0644, true);
        }
        $date = date('d.m.Y H:i:s');
        $newFile = fopen("logs/log" . $date . ".txt", "w");
        fwrite($newFile, $message);
        fclose($newFile);
    }


    /**
     * @param $body
     * @param string $message
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send($body, $message = '')
    {
        $this->body = file_get_contents($body);
        $emails = $this->search($this->emails);
        foreach ($emails as $match) {
            $mail = new PHPMailer();
            //$mail->SMTPDebug = 2; //Alternative to above constant
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($this->username, $this->username);
            $mail->Host = "ssl://smtp.mail.ru";
            $mail->Port = 465;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->Subject = $this->subject;
            foreach ($this->attachments as $path => $name) {
                $mail->addAttachment($path, $name);
            }
            $body = $this->body;
            $mail->msgHTML($body);
            $mail->addAddress($match);
            if (!$mail->send()) {
                $message .= "Сообщение не было отправлено. Ошибка из почты:" . $mail->ErrorInfo;
                echo "Ошибка.Письмо не было доставлено по адресу" . $match . "Ошибка-" . $mail->ErrorInfo . "<br>";
            } else {
                $message .= "Сообщение отправлено " . $match . " " . date('Y-m-d H:i:s') . "\n";
                echo "Сообщение было отправлено " . $match . date('Y-m-d H:i:s') . "<br>";
            }
        }
        if ($this->log != null) {
            $this->log($message);
        }
    }


}
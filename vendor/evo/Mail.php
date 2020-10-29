<?php


namespace Evo;

use Evo;
/**
 * Description of mail
 *
 * @author frolov
 */
class Mail
{
    public $mailer = null;
    public $failure = [];

    protected $message = null;
    protected $subject = null;
    protected $from = null;
    protected $to = null;
    protected $body = null;
    protected $type = '';
    protected $headers = [];


    protected $headerMap = [
        'Message-ID' => 'setId',
        'Return-Path' => 'setReturnPath',
        'From' => 'setFrom',
        'Sender' => 'setSender',
        'To' => 'setTo',
        'Cc' => 'setCc',
        'Reply-To' => 'setReplyTo',
        'Subject' => 'setSubject',
        'Content-Transfer-Encoding' => 'setEncoder',
    ];

    public function __construct()
    {

        $config = Evo::getConfig('mail', '');

        $type = 'mail';
        if(!empty($config) && !empty($config[0])) {
            $type = $config[0];
        }

        switch($type) {
            case 'smtp' :
                $transport = new \Swift_SmtpTransport($config[1], $config[4], !empty($config[5]) ? $config[5] : null);
                $transport->setUsername($config[2])
                    ->setPassword($config[3]);
                break;
            case 'sendmail' :
                $transport = new \Swift_SendmailTransport($config[1]);
                break;
        }

        $this->mailer = new \Swift_Mailer($transport);

    }

    public function subject($subject=null)
    {
        $this->subject = $subject;
    }

    public function from($from)
    {
        $this->from = $from;
    }

    public function to($to)
    {
        $this->to = $to;
    }

    public function body($body, $type="text/plain")
    {
        $this->body = $body;
        $_type = preg_split('/\s*;\s*/', $type);
        $this->type = array_shift($_type);
        foreach($_type as $header) {
            list($key, $value) = array_pad(preg_split('/\s*:\s*/', $header), 2, '');
            $this->headers[$key] = $value;
        }
    }

    public function getMessage()
    {
        if(!$this->message) {
            $this->message = new \Swift_Message();
            $this->message
                ->setSubject($this->subject)
                ->setFrom($this->from)
                ->setTo($this->to)
                ->setBody($this->body, $this->type);
        }
        return $this->message;
    }

    public function send()
    {

        $this->getMessage();

        $headers = $this->message->getHeaders();
        if($this->headers) {

            foreach($this->headers as $key => $value) {
                $headers->addTextHeader($key, $value);
            }
        }

        $result = $this->mailer->send($this->message, $this->failure);

        return $result;
    }


}

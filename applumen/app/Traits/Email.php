<?php
/**
 * Created by IntelliJ IDEA.
 * User: Prastiyo Beka
 * Date: 03/11/2017
 * Time: 9:09
 */
namespace App\Traits;
use App\Traits\message;
use DB;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

Trait Email
{
    use message;

    /**
     * @var
     */

    //for source
    protected $source_email = "php.jasamedika@gmail.com";
    protected $source_username = "php.jasamedika@gmail.com";
    protected $source_password = "team4php";

    //for destination
    protected $emailTo = "";
    protected $emailFrom = "";
    protected $first_name = "";
    protected $subject = "";
    protected $body = "";

    protected function send_email(){
        $config = array(
            'driver' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            //  'from' => array('address' => 'yusuf.sutana@gmail.com', 'name' => 'From_name'),
            'encryption' => 'tls',
            'username' => $this->source_email,
            'password' => $this->source_password,
            // 'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
            'stream' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]
        );
        Config::set('mail',$config);
        $data = array(
            'email_to' => $this->emailTo,
            'email_from' => $this->emailFrom,
            'first_name' => $this->first_name,
            'subject' => $this->subject,
            'body' => $this->body
        );
        $results = array();
        try{
            Mail::raw($data['body'], function($msg) use ($data) {
                $msg->to( $data['email_to'] )
                    ->from( $data['email_from'], $data['first_name'] )
                    ->subject( $data['subject'] );
            });
            $results = array(
                'status' => 200,
                'msg' => "Mail Sent Successfully"
            );
        }catch(Exception $e){
            $results = array(
                'status' => 500,
                'msg' => "error Sending Mail"
            );
        }
        return $results;
    }
}
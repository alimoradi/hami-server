<?php
namespace App\Libraries\Notifications;

use App\Session;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class ReferralResult extends Notification
{
   
    private $session;
    private $message;
    private $notificationCode = 0;
     function __construct($session)
    {
        $this->session = json_encode($session);
        if($session->referral_status == Session::SESSION_REFERRAL_STATUS_REJECTED)
        {
            $this->message = 'درخواست استرداد هزینه جلسه رد شد.';
        }
        else if($session->referral_status == Session::SESSION_REFERRAL_STATUS_CONFIRMED)
        {
            $this->message = 'درخواست استرداد هزینه جلسه تأیید شد.';
        }
    }   
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setName('ReferralResult')
            ->setData([ 'notification_code' => '5', 'session'=> $this->session])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle ('نتیجه ارجاع به سوپروایزر')
                ->setBody($this->message)
                ->setImage('http://example.com/url-to-image-here.png'))
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(AndroidNotification::create()->setColor('#0A0A0A'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios')));
    }
}
?>
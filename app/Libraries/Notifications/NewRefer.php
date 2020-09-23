<?php
namespace App\Libraries\Notifications;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class NewRefer extends Notification
{
   
    private $session;
    private $notificationCode = 0;
     function __construct($session)
    {
        
        $this->session = $session;
    }   
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setName('NewRefer')
            ->setData([ 'notification_code' => '4', 'session'=> $this->session])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle ('مشاوره ارجاعی جدید')
                ->setBody('یک مشاوره جدید برای بررسی به شما ارجاع داده شده است.')
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
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

class SessionUpdated extends Notification
{
    private $session;
    private $sender;
    private $notificationCode = 1;
     function __construct($session, $sender )
    {
        $this->sessionId = $session;
        $this->$sender = $sender;
    }
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData(['notification_code' => '1', 'sender' => $this->sender, 'session'=> $this->session])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Account Activated')
                ->setBody($this->sessionId)
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

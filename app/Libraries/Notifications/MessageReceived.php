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

class MessageReceived extends Notification
{

    private $sender;
    private $chatTopic;
    private $notificationCode = 0;
     function __construct( $sender, $topic)
    {

        $this->sender = $sender;
        $this->chatTopic = $topic;
    }
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setName('MessageReceived')
            ->setData(['sender' => $this->sender, 'notification_code' => '0', 'topic'=> $this->chatTopic])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('پیام جدید')
                ->setBody('برای شما پیام جدیدی ارسال شده است.')
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

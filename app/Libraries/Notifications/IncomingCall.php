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

class IncomingCall extends Notification
{

    private $accessToken;
    private $callId;
    private $caller;
    private $maxDuration;
    private $sessionId;
    private $notificationCode = 0;
     function __construct( $accessToken, $callId, $caller, $maxDuration, $sessionId)
    {

        $this->accessToken = $accessToken;
        $this->callId = $callId;
        $this->caller = $caller;
        $this->maxDuration = $maxDuration;
        $this->sessionId = $sessionId;
    }
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setName('IncomingCall')
            ->setData([ 'notification_code' => '2', 'access_token'=> $this->accessToken, 'call_id' =>$this->callId, 'caller'=> $this->caller
            , 'max_duration' => $this->maxDuration
            ,'session_id' => $this->sessionId])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('تماس جدید')
                ->setBody('شما یک تماس دریافتی دارید.')
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

<?php
/**
 * Created by PhpStorm.
 * User: darkf
 * Date: 6/29/2019
 * Time: 9:03 AM
 */

namespace Herpaderpaldent\Seat\SeatNotifications\Jobs;

use Herpaderpaldent\Seat\SeatNotifications\Models\NotificationRecipient;
use Herpaderpaldent\Seat\SeatNotifications\Notifications\CharacterNotification\AbstractCharacterNotification;
use Herpaderpaldent\Seat\SeatNotifications\Notifications\KillMail\AbstractKillMailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Seat\Eveapi\Models\Character\CharacterNotification;

class CharacterNotificationDispatcher extends SeatNotificationsJobBase
{
    /**
     * @var array
     */
    protected $tags = ['character_notification', 'dispatcher'];

    /**
     * @var
     */
    private $notification_id;

    /**
     * @var
     */
    private $character_notification;

    /**
     * CharacterNotificationDispatcher constructor.
     *
     * @param CharacterNotification $character_notification
     */
    public function __construct(CharacterNotification $character_notification)
    {
        logger()->debug('Construct CharacterNotificationDispatcher: ' . $character_notification->notification_id);

        $this->character_notification = $character_notification;
        $this->notification_id = $character_notification->notification_id;

        array_push($this->tags, 'notification_id: ' . $this->notification_id);
    }

    public function handle()
    {
        switch($this->character_notification->type) {
            case 'StructureUnderAttack':
                $this->dispatchNotification();
                break;
            default:
                logger()->debug('This character notification is not handled ' . $this->notification_id);
                $this->delete();
                break;
        }
    }

    private function dispatchNotification()
    {
        Redis::funnel('notification_id_' . $this->notification_id)->limit(1)->then(function () {
            logger()->debug('Character notification for ID: ' . $this->notification_id);

            $recipients = NotificationRecipient::all()
                ->filter(function ($recepient) {
                    return $recepient->shouldReceive(AbstractCharacterNotification::class);
                });

            if($recipients->isEmpty()){
                logger()->debug('No Receiver found for this Notification. This job is going to be deleted.');
                $this->delete();
            }

            $recipients->groupBy('driver')
                ->each(function ($grouped_recipients) {
                    $driver = (string) $grouped_recipients->first()->driver;
                    $notification_class = AbstractCharacterNotification::getDriverImplementation($driver);

                    Notification::send($grouped_recipients, (new $notification_class($this->notification_id)));
                });

        }, function () {

            logger()->debug('A character notification job is already running for ' . $this->notification_id);
            $this->delete();
        });
    }
}
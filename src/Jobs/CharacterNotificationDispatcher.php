<?php
/**
 * Created by PhpStorm.
 * User: darkf
 * Date: 6/29/2019
 * Time: 9:03 AM
 */

namespace Herpaderpaldent\Seat\SeatNotifications\Jobs;

use Herpaderpaldent\Seat\SeatNotifications\Models\NotificationRecipient;
use Herpaderpaldent\Seat\SeatNotifications\Notifications\CharacterNotifications\SovCommandNodeEventStarted\AbstractSovCommandNodeEventStartedNotification;
use Herpaderpaldent\Seat\SeatNotifications\Notifications\CharacterNotifications\StructureAnchoring\AbstractStructureAnchoringNotification;
use Herpaderpaldent\Seat\SeatNotifications\Notifications\CharacterNotifications\StructureUnderAttack\AbstractStructureUnderAttackNotification;
use Illuminate\Support\Facades\Cache;
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

        array_push($this->tags, 'notification_id_' . $this->notification_id);
    }

    public function handle()
    {
        switch($this->character_notification->type) {
            case 'StructureUnderAttack':
                $abstractClass = AbstractStructureUnderAttackNotification::class;
                break;
            case 'StructureAnchoring':
                $abstractClass = AbstractStructureAnchoringNotification::class;
                break;
            case 'SovCommandNodeEventStarted':
                $abstractClass = AbstractSovCommandNodeEventStartedNotification::class;
                break;
            default:
                return;
        }

        $this->dispatchNotification($abstractClass);
    }

    private function dispatchNotification($abstractClass)
    {
        Cache::lock('notification_id_'.$this->notification_id)->get(function () use ($abstractClass) {
            if(Cache::get('notification_id_'.$this->notification_id)) {
                logger()->debug('A character notification job is already running for ' . $this->notification_id);
                return;
            }
            Cache::put('notification_id_'.$this->notification_id, true, 7201);

            Redis::funnel('notification_id_' . $this->notification_id)->limit(1)->then(function () use ($abstractClass) {
                $recipients = NotificationRecipient::all()
                    ->filter(function ($recepient) use ($abstractClass) {
                        return $recepient->shouldReceive($abstractClass);
                    });

                if($recipients->isEmpty()){
                    logger()->debug('No Receiver found for this Notification. This job is going to be deleted.');
                    $this->delete();
                }

                $recipients->groupBy('driver')
                    ->each(function ($grouped_recipients) use ($abstractClass) {
                        $driver = (string) $grouped_recipients->first()->driver;
                        $notification_class = $abstractClass::getDriverImplementation($driver);

                        Notification::send($grouped_recipients, (new $notification_class($this->notification_id)));
                    });

            }, function () {

                logger()->debug('A character notification job is already running for ' . $this->notification_id);
                $this->delete();
            });
        });
    }
}
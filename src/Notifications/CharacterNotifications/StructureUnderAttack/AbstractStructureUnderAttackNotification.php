<?php
/**
 * MIT License.
 *
 * Copyright (c) 2019. Felix Huber
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Herpaderpaldent\Seat\SeatNotifications\Notifications\CharacterNotifications\StructureUnderAttack;

use Herpaderpaldent\Seat\SeatNotifications\Notifications\AbstractNotification;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Character\CharacterNotification;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Sde\MapDenormalize;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractStructureUnderAttackNotification extends AbstractNotification
{
    /**
     * @var string
     */
    public $character_notification;

    /**
     * @var string
     */
    public $image;

    public $parsed_text;

    /**
     * AbstractKillMailNotification constructor.
     */
    public function __construct(int $notification_id)
    {

        parent::__construct();

        $this->character_notification = CharacterNotification::where('notification_id', $notification_id)->first();
        $this->parsed_text = Yaml::parse($this->character_notification->text, Yaml::PARSE_OBJECT_FOR_MAP);
        $this->image = sprintf('https://imageserver.eveonline.com/Type/%d_64.png', $this->parsed_text->structureShowInfoData[1]);

        array_push($this->tags, 'notification_id:' . $notification_id);
    }

    /**
     * @return string
     */
    final public static function getTitle(): string
    {

        return 'Structure Under Attack Notification';
    }

    /**
     * @return string
     */
    final public static function getDescription(): string
    {

        return 'Receive a notification about structure under attack notifications.';
    }

    /**
     * @return bool
     */
    final public static function isPublic(): bool
    {

        return true;
    }

    /**
     * @return bool
     */
    final public static function isPersonal(): bool
    {

        return false;
    }

    /**
     * @return array
     */
    final public static function getFilters(): ?string
    {

        return null;
    }

    /**
     * Determine the permission needed to represent driver buttons.
     * @return string
     */
    public static function getPermission(): string
    {
        return 'seatnotifications.character_notification';
    }

    /**
     * @param $notifiable
     *
     * @return mixed
     */
    abstract public function via($notifiable);

    public function getSolarSystemName()
    {
        return MapDenormalize::where('itemID', $this->parsed_text->solarsystemID)->first()->name ?? $this->parsed_text->solarsystemID;
    }

    public function getOwnerCorporation()
    {
        return CorporationInfo::find(CharacterInfo::find($this->character_notification->character_id)->corporation_id ?? 0)->name ?? '';
    }
}

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

namespace Herpaderpaldent\Seat\SeatNotifications\Notifications\CharacterNotifications\StructureAnchoring;

use Herpaderpaldent\Seat\SeatNotifications\Channels\Discord\DiscordChannel;
use Herpaderpaldent\Seat\SeatNotifications\Channels\Discord\DiscordMessage;
use Seat\Eveapi\Models\Sde\InvType;

class DiscordStructureAnchoringNotification extends AbstractStructureAnchoringNotification
{
    const DANGER_COLOR = '14502713';

    const KILL_COLOR = '42586';

    public function via($notifiable)
    {

        array_push($this->tags, is_null($notifiable->group_id) ? 'to channel' : 'private to: ' . $this->getMainCharacter(Group::find($notifiable->group_id))->name);

        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {

        return (new DiscordMessage)
            ->embed(function ($embed) use ($notifiable) {

                $embed->title('Structure Anchoring')
                    ->thumbnail($this->image)
                    ->color(self::DANGER_COLOR)
                    ->field('Eve Time', $this->character_notification->timestamp, true)
                    ->field('Structure', InvType::find($this->parsed_text->structureShowInfoData[1])->typeName ?? '')
                    ->field('System', $this->getSolarSystemName(), true)
                    ->field('Owner', $this->parsed_text->ownerCorpName, true)
                    ->field('Time Left', gmdate("H:i:s",intval($this->parsed_text->timeLeft) / 100000000), true)
                    ->field('Vulnerable Time', gmdate("H:i:s",intval($this->parsed_text->vulnerableTime) / 100000000), true);
            });
    }
}

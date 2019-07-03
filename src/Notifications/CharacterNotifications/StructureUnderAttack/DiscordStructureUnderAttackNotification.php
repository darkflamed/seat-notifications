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

use Herpaderpaldent\Seat\SeatNotifications\Channels\Discord\DiscordChannel;
use Herpaderpaldent\Seat\SeatNotifications\Channels\Discord\DiscordMessage;
use Seat\Eveapi\Models\Sde\InvType;

class DiscordStructureUnderAttackNotification extends AbstractStructureUnderAttackNotification
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

                $embed->title('Structure Under Attack')
                    ->thumbnail($this->image)
                    ->color(self::DANGER_COLOR)
                    ->field('Eve Time', $this->character_notification->timestamp, true)
                    ->field('Structure', InvType::find($this->parsed_text->structureTypeID ?? 0)->typeName ?? '')
                    ->field('Owner', $this->getOwnerCorporation(), true)
                    ->field('System', $this->getSolarSystemName(), true)
                    ->field('Hull', number_format($this->parsed_text->hullPercentage, 2).'%', true)
                    ->field('Shield', number_format($this->parsed_text->shieldPercentage, 2).'%', true)
                    ->field('Attacker Alliance', $this->parsed_text->allianceName ?? 'None')
                    ->field('Attacker Corporation', $this->parsed_text->corpName ?? 'None', true);
            });
    }
}

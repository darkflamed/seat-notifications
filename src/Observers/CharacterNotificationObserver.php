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

namespace Herpaderpaldent\Seat\SeatNotifications\Observers;

use Herpaderpaldent\Seat\SeatNotifications\Jobs\CharacterNotificationDispatcher;
use Seat\Eveapi\Models\Character\CharacterNotification;
use Carbon\Carbon;

class CharacterNotificationObserver
{
    public $test = false;

    public function created(CharacterNotification $character_notification)
    {
        if($this->test || (($character_notification->timestamp ?? null) && Carbon::parse($character_notification->timestamp)->gte(Carbon::now()->subHours(2)))) {

            $job = new CharacterNotificationDispatcher($character_notification);
            dispatch($job)->onQueue('high');
        }

        return true;
    }

    public function test()
    {
        $this->test = true;
        $character_notification = CharacterNotification::where('type', 'like', 'StructureUnderAttack')->orderBy('timestamp', 'desc')->first();
        $this->created($character_notification);

        $character_notification = CharacterNotification::where('type', 'like', 'StructureAnchoring')->orderBy('timestamp', 'desc')->first();
        $this->created($character_notification);
    }
}

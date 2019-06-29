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

namespace Herpaderpaldent\Seat\SeatNotifications\Commands;

use Herpaderpaldent\Seat\SeatNotifications\Observers\KillmailDetailObserver;
use Herpaderpaldent\Seat\SeatNotifications\Observers\RefreshTokenObserver;
use Herpaderpaldent\Seat\SeatNotifications\Observers\CharacterNotificationObserver;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification;

class SeatNotificationsTest extends Command
{

    protected $signature = 'seat-notifications:test';

    protected $description = 'This command adds and removes roles to all users depending on their SeAT-Group Association';

    public function __construct()
    {

        parent::__construct();
    }

    public function handle(CharacterNotificationObserver $action)
    {

        $this->info('Test');
        $action->test();

        /*app('discord')->channel->createMessage([
            'channel.id' => 441330906356121622,
            'content' => 'Test my newly awesome bot'
        ]);*/

        //$this->notify(new CharacterNotification());
        //Notification::send(new RefreshTokenObserver() ,new RefreshTokenDeleted());

        //$when = now()->addMinutes(10);
        //$this->notify((new RefreshTokenDeleted())->delay($when)); //TODO: Check if that is working, according to docs it should
    }
}

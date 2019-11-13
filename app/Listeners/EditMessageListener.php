<?php

namespace App\Listeners;

use App\Events\EditMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class EditMessageListener implements ShouldQueue
{
  /**
   * Create the event listener.
   *
   * @return void
   */
  public function __construct()
  {
      //
  }

  /**
   * Handle the event.
   *
   * @param  \App\Events\OrderShipped  $event
   * @return void
   */
  public function handle(EditMessage $event)
  {
      // Access the order using $event->order...
  }
}
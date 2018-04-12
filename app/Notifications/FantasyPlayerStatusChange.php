<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class FantasyPlayerStatusChange extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->line('Player: ' . $this->data['playerName'])
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

/**
 * Get the Slack representation of the notification.
 *
 * @param  mixed  $notifiable
 * @return SlackMessage
 */
    public function toSlack($notifiable)
    {
        $data=$this->data;
        return (new SlackMessage)
            ->from('TryBot',':trybot:')
            ->to('@jakebathman')
            ->content("Your player's status has changed! Player: " . $data['playerName'])
                ->attachment(function ($attachment) use ($data) {
                    $attachment->title($data['playerName'] . " is *out*")
                               ->content('Now listed as *suspended*')
                               ->markdown(['title','text']);
                })
                // ->error()
                ->success();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

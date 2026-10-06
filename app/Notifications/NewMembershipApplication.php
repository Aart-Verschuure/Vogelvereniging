<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Melding aan de administratie dat er een nieuwe aanmelding in quarantaine staat.
 */
class NewMembershipApplication extends Notification
{
    use Queueable;

    public function __construct(public Member $member) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $member = $this->member;

        return (new MailMessage)
            ->subject('Nieuwe aanmelding als lid: '.$member->fullName())
            ->greeting('Nieuwe aanmelding')
            ->line($member->fullName().' heeft zich via de website aangemeld als '.$member->memberType->name.'.')
            ->line('Datum van opgave: '.$member->registration_date->format('d-m-Y'))
            ->line('Lid per: '.membership_start_date($member->registration_date)->format('d-m-Y'))
            ->line('Contributie dit jaar: € '.number_format(member_contribution($member), 2, ',', '.'))
            ->line('De aanmelding staat in quarantaine en moet nog worden verwerkt.')
            ->action('Aanmelding bekijken', route('dashboard'));
    }
}

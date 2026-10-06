<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Melding aan de administratie dat een lid zich heeft afgemeld.
 */
class MembershipCancelled extends Notification
{
    use Queueable;

    /**
     * @param  bool  $invoiceWasPaid  true: het bedrag moet worden terugbetaald, false: de openstaande factuur is verlaagd
     */
    public function __construct(public Member $member, public float $refund, public bool $invoiceWasPaid = true) {}

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
            ->subject('Afmelding lid: '.$member->fullName())
            ->greeting('Afmelding')
            ->line($member->fullName().' (NBvV-nummer: '.($member->nbvv_number ?? '-').') heeft zich via de website afgemeld.')
            ->line('Datum van afmelding: '.$member->cancellation_requested_at->format('d-m-Y'))
            ->line('Geen lid meer per: '.$member->membership_end_date->format('d-m-Y'))
            ->line(($this->invoiceWasPaid ? 'Terug te betalen: € ' : 'Openstaande factuur verlaagd met: € ').number_format($this->refund, 2, ',', '.'))
            ->line('Reden: '.($member->cancellation_reason ?: '-'))
            ->action('Afmeldingen bekijken', route('dashboard'));
    }
}

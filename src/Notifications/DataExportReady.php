<?php

namespace Rylxes\Gdpr\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Rylxes\Gdpr\Models\DataExport;
use Rylxes\Gdpr\Support\DownloadLinkGenerator;

class DataExportReady extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected DataExport $export,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if (config('gdpr.notifications.export_ready.mail_enabled', true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $downloadUrl = app(DownloadLinkGenerator::class)->generate($this->export);
        $expiryMinutes = config('gdpr.export.download_link_expiry_minutes', 60);

        $mail = (new MailMessage)
            ->subject('Your Data Export is Ready')
            ->greeting('Hello!')
            ->line('Your personal data export has been prepared and is ready for download.')
            ->line("Format: " . strtoupper($this->export->format))
            ->line("Size: " . ($this->export->fileSizeForHumans() ?? 'Unknown'))
            ->action('Download Your Data', $downloadUrl)
            ->line("This download link will expire in {$expiryMinutes} minutes.")
            ->line('If you did not request this export, please contact support immediately.');

        $fromAddress = config('gdpr.notifications.export_ready.from_address');
        $fromName = config('gdpr.notifications.export_ready.from_name');

        if ($fromAddress) {
            $mail->from($fromAddress, $fromName);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'export_id' => $this->export->id,
            'format' => $this->export->format,
            'file_size_bytes' => $this->export->file_size_bytes,
        ];
    }
}

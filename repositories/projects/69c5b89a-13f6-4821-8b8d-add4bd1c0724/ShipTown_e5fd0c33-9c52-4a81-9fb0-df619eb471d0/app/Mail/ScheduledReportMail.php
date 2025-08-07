<?php

namespace App\Mail;

use App\Modules\Reports\src\Models\InventoryMovementsReport;
use App\Modules\Reports\src\Models\Report;
use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Str;

// TODO: use mail template
class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $scheduledReport;

    /**
     * Create a new message instance.
     */
    public function __construct(ScheduledReport $scheduledReport)
    {
        $this->scheduledReport = $scheduledReport;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Report - " . $this->scheduledReport->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->scheduledReport->name,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        $reportClass = $this->getReportClass();

        if (!is_null($reportClass)) {
            $content =  $reportClass->streamCsvFile();
            $fileName = $this->scheduledReport->name . " - " . $this->scheduledReport->created_at->format('Y-m-d H:i:s') . ".csv";

            $attachments[] = Attachment::fromData(fn () => $content, $fileName);
        }

        return $attachments;
    }

    private function getReportClass(): Report|null
    {
        $uri = $this->scheduledReport->uri;

        return match (true) {
            Str::contains($uri, 'reports/inventory-movements') => new InventoryMovementsReport,
            default => null,
        };
    }
}

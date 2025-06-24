<?php

namespace App\Mail;

use App\Models\Reimbursement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ReimbursementApproved extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Reimbursement $reimbursement,
        public User $employee,
        public ?User $approver = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: '✅ Reimbursement Approved - ' . $this->reimbursement->title,
            tags: ['reimbursement', 'approved', 'employee'],
            metadata: [
                'reimbursement_id' => $this->reimbursement->id,
                'user_id' => $this->employee->id,
                'approver_id' => $this->approver?->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.approved.approved',
            text: 'emails.reimbursement.approved.approved-text',
            with: [
                'reimbursement' => $this->reimbursement,
                'employee' => $this->employee,
                'approver' => $this->approver,
                'category' => $this->reimbursement->category,
                'appUrl' => config('app.url'),
                'formattedAmount' => 'IDR ' . number_format($this->reimbursement->amount, 0, ',', '.'),
                'submissionDate' => $this->reimbursement->submitted_at->format('d F Y, H:i'),
                'approvalDate' => $this->reimbursement->approved_at?->format('d F Y, H:i'),
                'referenceNumber' => 'REF-' . str_pad($this->reimbursement->id, 6, '0', STR_PAD_LEFT),
                'processingTime' => $this->getProcessingTime(),
            ],
        );
    }

    /**
     * Get processing time in human readable format
     */
    private function getProcessingTime(): string
    {
        if (!$this->reimbursement->approved_at) {
            return 'N/A';
        }

        $diff = $this->reimbursement->submitted_at->diff($this->reimbursement->approved_at);

        if ($diff->days > 0) {
            return $diff->days . ' day(s)';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour(s)';
        } else {
            return $diff->i . ' minute(s)';
        }
    }

    public function attachments(): array
    {
        return [];
    }
}

// ==========================================
// REJECTION EMAIL
// ==========================================

class ReimbursementRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reimbursement $reimbursement,
        public User $employee,
        public ?User $rejector = null,
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: '❌ Reimbursement Rejected - ' . $this->reimbursement->title,
            tags: ['reimbursement', 'rejected', 'employee'],
            metadata: [
                'reimbursement_id' => $this->reimbursement->id,
                'user_id' => $this->employee->id,
                'rejector_id' => $this->rejector?->id,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.rejected.rejected',
            text: 'emails.reimbursement.rejected.rejected-text',
            with: [
                'reimbursement' => $this->reimbursement,
                'employee' => $this->employee,
                'rejector' => $this->rejector,
                'reason' => $this->reason,
                'category' => $this->reimbursement->category,
                'appUrl' => config('app.url'),
                'formattedAmount' => 'IDR ' . number_format($this->reimbursement->amount, 0, ',', '.'),
                'submissionDate' => $this->reimbursement->submitted_at->format('d F Y, H:i'),
                'rejectionDate' => $this->reimbursement->updated_at->format('d F Y, H:i'),
                'referenceNumber' => 'REF-' . str_pad($this->reimbursement->id, 6, '0', STR_PAD_LEFT),
                'processingTime' => $this->getProcessingTime(),
            ],
        );
    }

    private function getProcessingTime(): string
    {
        $diff = $this->reimbursement->submitted_at->diff($this->reimbursement->updated_at);

        if ($diff->days > 0) {
            return $diff->days . ' day(s)';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour(s)';
        } else {
            return $diff->i . ' minute(s)';
        }
    }

    public function attachments(): array
    {
        return [];
    }
}

// ==========================================
// NOTIFICATION EMAILS FOR ADMIN
// ==========================================

class ReimbursementApprovedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reimbursement $reimbursement,
        public User $admin,
        public ?User $approver = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: '[ADMIN] Reimbursement Approved - ' . $this->reimbursement->title,
            tags: ['reimbursement', 'approved', 'admin', 'notification'],
            metadata: [
                'reimbursement_id' => $this->reimbursement->id,
                'admin_id' => $this->admin->id,
                'approver_id' => $this->approver?->id,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.approved.approved-notification',
            text: 'emails.reimbursement.approved.approved-notification-text',
            with: [
                'reimbursement' => $this->reimbursement,
                'admin' => $this->admin,
                'approver' => $this->approver,
                'employee' => $this->reimbursement->user,
                'category' => $this->reimbursement->category,
                'appUrl' => config('app.url'),
                'formattedAmount' => 'IDR ' . number_format($this->reimbursement->amount, 0, ',', '.'),
                'submissionDate' => $this->reimbursement->submitted_at->format('d F Y, H:i'),
                'approvalDate' => $this->reimbursement->approved_at?->format('d F Y, H:i'),
                'referenceNumber' => 'REF-' . str_pad($this->reimbursement->id, 6, '0', STR_PAD_LEFT),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

class ReimbursementRejectedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reimbursement $reimbursement,
        public User $admin,
        public ?User $rejector = null,
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: '[ADMIN] Reimbursement Rejected - ' . $this->reimbursement->title,
            tags: ['reimbursement', 'rejected', 'admin', 'notification'],
            metadata: [
                'reimbursement_id' => $this->reimbursement->id,
                'admin_id' => $this->admin->id,
                'rejector_id' => $this->rejector?->id,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.rejected.rejected-notification',
            text: 'emails.reimbursement.rejected.rejected-notification-text',
            with: [
                'reimbursement' => $this->reimbursement,
                'admin' => $this->admin,
                'rejector' => $this->rejector,
                'reason' => $this->reason,
                'employee' => $this->reimbursement->user,
                'category' => $this->reimbursement->category,
                'appUrl' => config('app.url'),
                'formattedAmount' => 'IDR ' . number_format($this->reimbursement->amount, 0, ',', '.'),
                'submissionDate' => $this->reimbursement->submitted_at->format('d F Y, H:i'),
                'rejectionDate' => $this->reimbursement->updated_at->format('d F Y, H:i'),
                'referenceNumber' => 'REF-' . str_pad($this->reimbursement->id, 6, '0', STR_PAD_LEFT),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

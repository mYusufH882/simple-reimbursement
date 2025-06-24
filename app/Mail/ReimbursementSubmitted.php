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

class ReimbursementSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Reimbursement $reimbursement,
        public User $manager
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
            subject: 'New Reimbursement Submission - ' . $this->reimbursement->title,
            tags: ['reimbursement', 'submission', 'manager'],
            metadata: [
                'reimbursement_id' => $this->reimbursement->id,
                'user_id' => $this->reimbursement->user_id,
                'manager_id' => $this->manager->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.submitted',
            text: 'emails.reimbursement.submitted-text',
            with: [
                'reimbursement' => $this->reimbursement,
                'manager' => $this->manager,
                'employee' => $this->reimbursement->user,
                'category' => $this->reimbursement->category,
                'appUrl' => config('app.url'),
                'formattedAmount' => 'IDR ' . number_format($this->reimbursement->amount, 0, ',', '.'),
                'submissionDate' => $this->reimbursement->submitted_at->format('d F Y, H:i'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

// ==========================================
// CONFIRMATION EMAIL FOR EMPLOYEE
// ==========================================

class ReimbursementSubmittedConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reimbursement $reimbursement,
        public User $employee
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: 'Reimbursement Submitted Successfully - ' . $this->reimbursement->title,
            tags: ['reimbursement', 'confirmation', 'employee'],
            metadata: [
                'reimbursement_id' => $this->reimbursement->id,
                'user_id' => $this->employee->id,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.submitted-confirmation',
            text: 'emails.reimbursement.submitted-confirmation-text',
            with: [
                'reimbursement' => $this->reimbursement,
                'employee' => $this->employee,
                'category' => $this->reimbursement->category,
                'appUrl' => config('app.url'),
                'formattedAmount' => 'IDR ' . number_format($this->reimbursement->amount, 0, ',', '.'),
                'submissionDate' => $this->reimbursement->submitted_at->format('d F Y, H:i'),
                'referenceNumber' => 'REF-' . str_pad($this->reimbursement->id, 6, '0', STR_PAD_LEFT),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

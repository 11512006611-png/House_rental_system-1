<?php

namespace App\Services;

use App\Models\LeaseAgreement;
use App\Models\Payment;
use App\Models\Rental;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LeaseAgreementService
{
    /**
     * Build a stable agreement code for a rental.
     */
    public static function generateAgreementId(Rental $rental): string
    {
        return 'AGR-' . now()->format('Ymd') . '-' . str_pad((string) $rental->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create or refresh lease agreement after successful payment verification.
     */
    public static function createOrRefreshAgreement(Rental $rental, Payment $payment = null): LeaseAgreement
    {
        $rental->loadMissing(['tenant', 'house.owner', 'house.locationModel']);

        // Calculate total advance amount from all verified payments for this rental
        $totalAdvanceAmount = $rental->payments()
            ->where('verification_status', 'verified')
            ->sum('amount');

        $agreement = LeaseAgreement::updateOrCreate(
            ['rental_id' => $rental->id],
            [
                'agreement_id' => self::generateAgreementId($rental),
                'owner_id' => $rental->house->owner_id,
                'tenant_id' => $rental->tenant_id,
                'house_id' => $rental->house_id,
                'monthly_rent' => (float) $rental->monthly_rent,
                'deposit_amount' => $totalAdvanceAmount,
                'security_deposit_amount' => $rental->payments()
                    ->where('payment_type', 'security_deposit')
                    ->where('verification_status', 'verified')
                    ->sum('amount'),
                'payment_status' => $totalAdvanceAmount > 0 ? 'paid' : 'pending',
                'lease_start_date' => $rental->rental_date,
                'lease_end_date' => $rental->end_date,
                'generated_at' => now(),
                'uploaded_at' => now(),
            ]
        );

        self::generatePdf($agreement);

        return $agreement->fresh();
    }

    /**
     * Re-generate PDF for signature/status updates.
     */
    public static function regeneratePdf(LeaseAgreement $agreement): void
    {
        self::generatePdf($agreement);
    }

    /**
     * Render and save the agreement PDF into public storage.
     */
    private static function generatePdf(LeaseAgreement $agreement): void
    {
        $agreement->loadMissing(['owner', 'tenant', 'house.locationModel']);

        $pdf = Pdf::loadView('pdf.lease-agreement', [
            'agreement' => $agreement,
        ]);

        $safeAgreementId = str_replace(' ', '-', strtolower((string) $agreement->agreement_id));
        $filename = $safeAgreementId . '.pdf';
        $path = 'lease-agreements/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        $agreement->update([
            'file_path' => $path,
            'original_name' => strtoupper((string) $agreement->agreement_id) . '.pdf',
            'uploaded_at' => now(),
            'generated_at' => now(),
        ]);
    }

    /**
     * Backward-compatible HTML preview renderer for owner preview pages.
     */
    public static function generateLeaseHTML(Rental $rental, ?string $advanceAmount = null): string
    {
        $rental->loadMissing(['tenant', 'house.owner', 'house.locationModel']);

        $previewAgreement = new LeaseAgreement([
            'agreement_id' => self::generateAgreementId($rental),
            'monthly_rent' => (float) $rental->monthly_rent,
            'deposit_amount' => (float) ($advanceAmount ?? 0),
            'payment_status' => 'pending',
            'lease_start_date' => $rental->rental_date,
            'lease_end_date' => $rental->end_date,
            'generated_at' => now(),
        ]);

        $previewAgreement->setRelation('owner', $rental->house->owner);
        $previewAgreement->setRelation('tenant', $rental->tenant);
        $previewAgreement->setRelation('house', $rental->house);

        return view('pdf.lease-agreement', ['agreement' => $previewAgreement])->render();
    }

    /**
     * Backward-compatible HTML storage helper used by older flows.
     */
    public static function storeLease(Rental $rental, ?string $advanceAmount = null): string
    {
        $html = self::generateLeaseHTML($rental, $advanceAmount);
        $filename = 'lease_rental_' . $rental->id . '_' . $rental->tenant_id . '_' . now()->timestamp . '.html';
        $path = 'leases/' . $filename;

        Storage::disk('public')->put($path, $html);

        return $path;
    }
}

<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class LeaseAgreementService
{
    /**
     * Generate HTML lease agreement content
     */
    public static function generateLeaseHTML(Rental $rental, ?string $advanceAmount = null): string
    {
        $owner = $rental->house->owner;
        $tenant = $rental->tenant;
        $house = $rental->house;
        
        $startDate = $rental->rental_date->format('d F Y');
        $endDate = $rental->end_date ? $rental->end_date->format('d F Y') : 'To be determined';
        $monthlyRent = number_format($rental->monthly_rent, 2);
        $advance = $advanceAmount ? number_format($advanceAmount, 2) : '0.00';
        
        $bankName = $owner->bank_name ?: '[Bank Name]';
        $accountNumber = $owner->account_number ?: '[XXXXXXX]';
        $accountHolder = $owner->account_holder_name ?: $owner->name;
        $locationName = $house->locationModel ? $house->locationModel->name : 'N/A';
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                    background-color: #f5f5f5;
                }
                .container {
                    max-width: 8.5in;
                    height: 11in;
                    background-color: white;
                    margin: 0 auto;
                    padding: 40px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #2c3e50;
                    padding-bottom: 15px;
                    margin-bottom: 30px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    color: #2c3e50;
                }
                .header p {
                    margin: 5px 0;
                    font-size: 12px;
                    color: #7f8c8d;
                }
                .section {
                    margin-bottom: 20px;
                }
                .section-title {
                    font-weight: bold;
                    font-size: 14px;
                    color: #2c3e50;
                    margin-top: 20px;
                    margin-bottom: 10px;
                    border-left: 4px solid #3498db;
                    padding-left: 10px;
                }
                .parties {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                }
                .party {
                    flex: 1;
                    margin-right: 20px;
                }
                .party-label {
                    font-weight: bold;
                    color: #2c3e50;
                    margin-bottom: 5px;
                }
                .party-info {
                    font-size: 13px;
                    color: #555;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                td {
                    padding: 8px;
                    border-bottom: 1px solid #ecf0f1;
                }
                .label {
                    font-weight: bold;
                    color: #2c3e50;
                    width: 30%;
                    background-color: #ecf0f1;
                }
                .value {
                    color: #333;
                }
                .terms {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                .terms ol {
                    margin: 10px 0;
                    padding-left: 25px;
                }
                .terms li {
                    margin-bottom: 8px;
                    font-size: 13px;
                    line-height: 1.4;
                }
                .payment-details {
                    background-color: #e8f4f8;
                    padding: 15px;
                    border-left: 4px solid #3498db;
                    margin-bottom: 20px;
                    border-radius: 3px;
                }
                .payment-details h4 {
                    margin-top: 0;
                    color: #2c3e50;
                }
                .payment-details p {
                    margin: 5px 0;
                    font-size: 13px;
                }
                .signature-section {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-block {
                    flex: 1;
                    text-align: center;
                }
                .signature-line {
                    border-top: 1px solid #333;
                    margin-top: 20px;
                    padding-top: 5px;
                    font-size: 12px;
                    font-weight: bold;
                    color: #2c3e50;
                }
                .date-line {
                    margin-top: 15px;
                    border-top: 1px solid #333;
                    padding-top: 5px;
                    font-size: 12px;
                    color: #2c3e50;
                    text-align: center;
                }
                .important {
                    color: #e74c3c;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>DIGITAL LEASE AGREEMENT</h1>
                    <p>House Rental Management System</p>
                </div>

                <div class="section">
                    <p style="font-size: 13px; color: #555; margin: 0;">
                        This Lease Agreement ("<strong>Agreement</strong>") is entered into and agreed upon by and between:
                    </p>
                </div>

                <div class="parties">
                    <div class="party">
                        <div class="party-label">🏠 OWNER (LANDLORD)</div>
                        <div class="party-info">
                            <strong>{$accountHolder}</strong><br>
                            Email: {$owner->email}<br>
                            Phone: {$owner->phone}
                        </div>
                    </div>
                    <div class="party">
                        <div class="party-label">👤 TENANT</div>
                        <div class="party-info">
                            <strong>{$tenant->name}</strong><br>
                            Email: {$tenant->email}<br>
                            Phone: {$tenant->phone}
                        </div>
                    </div>
                </div>

                <div class="section-title">📍 PROPERTY DETAILS</div>
                <table>
                    <tr>
                        <td class="label">Property Title:</td>
                        <td class="value"><strong>{$house->title}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Location:</td>
                        <td class="value">{$locationName}</td>
                    </tr>
                    <tr>
                        <td class="label">Property ID:</td>
                        <td class="value">#{$house->id}</td>
                    </tr>
                </table>

                <div class="section-title">📋 LEASE TERMS & CONDITIONS</div>
                <div class="terms">
                    <ol>
                        <li><strong>Lease Duration:</strong> The lease will commence on <strong>{$startDate}</strong> and terminate on <strong>{$endDate}</strong> (unless extended by mutual written agreement).</li>
                        
                        <li><strong>Monthly Rent:</strong> The Tenant agrees to pay a monthly rent of <strong>Nu. {$monthlyRent}</strong> on or before the 1st day of each month.</li>
                        
                        <li><strong>Advance Payment:</strong> The Tenant must pay an advance of <strong>Nu. {$advance}</strong> before moving in. This advance will be held as a security deposit and may be adjusted based on property condition at the end of the lease.</li>
                        
                        <li><strong>Payment Method:</strong> All payments must be made to the Owner's bank account:
                            <div style="background-color: white; padding: 10px; margin: 8px 0; border-radius: 3px; border-left: 3px solid #3498db;">
                                <p style="margin: 3px 0;"><strong>Account Name:</strong> {$accountHolder}</p>
                                <p style="margin: 3px 0;"><strong>Account Number:</strong> {$accountNumber}</p>
                                <p style="margin: 3px 0;"><strong>Bank Name:</strong> {$bankName}</p>
                                <p style="margin: 3px 0;"><strong>Transaction ID/Reference:</strong> [To be provided with each payment]</p>
                            </div>
                        </li>
                        
                        <li><strong>Payment Proof:</strong> Tenant must upload payment proof (bank transfer receipt/screenshot) within 3 days of payment for verification by the Owner.</li>
                        
                        <li><strong>Lease Confirmation:</strong> The lease will only be considered <strong>ACTIVE</strong> after the Owner verifies the advance payment.</li>
                        
                        <li><strong>Tenant Responsibilities:</strong>
                            <ul style="margin: 5px 0; padding-left: 20px;">
                                <li>Maintain the property in good condition</li>
                                <li>Pay rent on time without delay</li>
                                <li>Comply with all rules and regulations</li>
                                <li>Notify Owner of any damages or maintenance issues</li>
                                <li>Return the property in original condition upon move-out</li>
                            </ul>
                        </li>
                        
                        <li><strong>Owner Responsibilities:</strong>
                            <ul style="margin: 5px 0; padding-left: 20px;">
                                <li>Ensure the property is in good habitable condition</li>
                                <li>Perform necessary maintenance and repairs</li>
                                <li>Respect Tenant's privacy and right of quiet enjoyment</li>
                                <li>Provide safe and secure living environment</li>
                            </ul>
                        </li>
                        
                        <li><strong>Lease Termination:</strong> Either party must provide <span class="important">30 days written notice</span> before ending this agreement. Failure to do so may result in penalties.</li>
                        
                        <li><strong>Late Payment Penalties:</strong> If rent is not paid by the due date, a late fee of <strong>5% of monthly rent</strong> will be applicable per week of delay.</li>
                    </ol>
                </div>

                <div class="section-title">💰 FINANCIAL SUMMARY</div>
                <div class="payment-details">
                    <h4>Payment Information</h4>
                    <p><strong>Monthly Rent:</strong> Nu. {$monthlyRent}</p>
                    <p><strong>Advance Payment:</strong> Nu. {$advance}</p>
                    <p><strong>Total Due Before Moving In:</strong> Nu. {$advance}</p>
                    <p style="color: #e74c3c;"><strong>⚠️ All payments must be made before lease activation!</strong></p>
                </div>

                <div class="section-title">✅ CONFIRMATION & SIGNATURES</div>
                <p style="font-size: 12px; color: #555; background-color: #fef9e7; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
                    Both parties hereby acknowledge that they have read, understood, and agree to be bound by the terms and conditions of this Lease Agreement. 
                    By signing below digitally or uploading payment proof, you are legally binding yourself to this agreement.
                </p>

                <div class="signature-section">
                    <div class="signature-block">
                        <p style="margin-bottom: 25px; font-size: 12px;"><strong>Owner (Landlord)</strong></p>
                        <div class="signature-line">
                            {$accountHolder}
                        </div>
                    </div>
                    <div class="signature-block">
                        <p style="margin-bottom: 25px; font-size: 12px;"><strong>Tenant</strong></p>
                        <div class="signature-line">
                            {$tenant->name}
                        </div>
                    </div>
                </div>

                <div class="date-line">
                    Date: ___________________
                </div>

                <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ecf0f1; font-size: 11px; color: #7f8c8d; text-align: center;">
                    <p>Lease agreement generated by House Rental Management System | Generated on {$rental->created_at->format('d F Y H:i:s')}</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Store lease agreement as HTML file
     */
    public static function storeLease(Rental $rental, ?string $advanceAmount = null): string
    {
        $html = self::generateLeaseHTML($rental, $advanceAmount);
        $filename = "lease_rental_{$rental->id}_{$rental->tenant_id}_" . now()->timestamp . ".html";
        
        Storage::disk('public')->put("leases/{$filename}", $html);
        
        return "leases/{$filename}";
    }
}

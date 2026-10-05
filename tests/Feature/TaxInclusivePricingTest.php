<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\Documents\DocumentRenderer;
use App\Services\Manuscripts\FeeCalculator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Every fee is inclusive of all taxes: the payer is charged exactly the fee; for Indian
 * billing addresses the GST inside it is shown on the invoice.
 */
class TaxInclusivePricingTest extends TestCase
{
    #[Test]
    public function the_tax_is_carved_out_of_the_fee_not_added_to_it(): void
    {
        $fees = app(FeeCalculator::class);

        $india = $fees->withTax(100, 'IN');            // 18% default
        $this->assertSame(100.0, $india['total']);
        $this->assertSame(15.25, $india['tax']);
        $this->assertSame(84.75, $india['base']);
        $this->assertEqualsWithDelta(100.0, $india['base'] + $india['tax'], 0.001);

        $abroad = $fees->withTax(100, 'US');
        $this->assertSame(['rate' => 0.0, 'base' => 100.0, 'tax' => 0.0, 'total' => 100.0], $abroad);
    }

    #[Test]
    public function the_invoice_shows_the_breakup_of_the_inclusive_fee(): void
    {
        $payment = Payment::factory()->create(['amount' => 127.12, 'tax_amount' => 22.88, 'tax_rate' => 18]);

        // Same view data the PDF renderer uses.
        $common = app(DocumentRenderer::class)->common();
        $html = view('pdf.invoice', ['payment' => $payment->fresh()] + $common)->render();

        $this->assertStringContainsString('taxable value', $html);
        $this->assertStringContainsString('127.12', $html);
        $this->assertStringContainsString('GST @ 18%', $html);
        $this->assertStringContainsString('22.88', $html);
        $this->assertStringContainsString('Total (inclusive of all taxes)', $html);
        $this->assertStringContainsString('150.00', $html);
    }

    private function indianPayment(string $state, ?string $taxId = null): Payment
    {
        $submission = \App\Models\ManuscriptSubmission::factory()->create();
        $address = $submission->author->address()->create([
            'recipient_name' => 'Asha', 'address_line1' => '1 Road', 'city' => 'City', 'state' => $state,
            'country_code' => 'IN', 'tax_id_number' => $taxId,
        ]);

        return app(\App\Services\Payments\PaymentService::class)
            ->createPending($submission->author, $submission, \App\Enums\PaymentPurpose::Prescreening, 150, $address)->fresh();
    }

    #[Test]
    public function buyers_in_maharashtra_get_cgst_and_sgst_others_get_igst(): void
    {
        $local = $this->indianPayment('Maharashtra', '27AAPFU0939F1ZV');
        $this->assertSame('intra', $local->gst_type);
        $this->assertSame([
            ['label' => 'CGST', 'rate' => 9.0, 'amount' => 11.44],
            ['label' => 'SGST', 'rate' => 9.0, 'amount' => 11.44],
        ], $local->taxLines());
        $this->assertSame('gst', $local->billing_details['tax_id_type']);

        $other = $this->indianPayment('Karnataka');
        $this->assertSame('inter', $other->gst_type);
        $this->assertSame([['label' => 'IGST', 'rate' => 18.0, 'amount' => 22.88]], $other->taxLines());

        // Invoice: the split and the buyer's GSTIN.
        $html = view('pdf.invoice', ['payment' => $local] + app(DocumentRenderer::class)->common())->render();
        $this->assertStringContainsString('CGST @ 9%', $html);
        $this->assertStringContainsString('SGST @ 9%', $html);
        $this->assertStringContainsString('GSTIN: 27AAPFU0939F1ZV', $html);
        $this->assertStringNotContainsString('IGST', $html);
    }

    #[Test]
    public function the_business_state_comes_from_settings(): void
    {
        \App\Models\Setting::updateOrCreate(['setting_group' => 'payment', 'setting_key' => 'business_state'], ['setting_value' => 'Karnataka']);
        app(\App\Support\Settings::class)->flush();

        $this->assertSame('intra', $this->indianPayment('Karnataka')->gst_type);
        $this->assertSame('inter', $this->indianPayment('Maharashtra')->gst_type);
    }

    #[Test]
    public function public_pages_say_inclusive_of_taxes_not_plus_tax(): void
    {
        $this->actingAs($this->author())->get(page_url('plagiarism_checker'))->assertOk()
            ->assertDontSee('+ tax')->assertSee('inclusive of all taxes');
    }
}

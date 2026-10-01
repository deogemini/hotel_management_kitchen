<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_managers_and_owners_can_invoice_using_companies_registered_outside_the_guests_lodge(): void
    {
        $lodge = \App\Models\Lodge::create(['name' => 'Guest Lodge']);
        $otherLodge = \App\Models\Lodge::create(['name' => 'Company Lodge']);
        $guest = Guest::create(['full_name' => 'Company Guest', 'lodge_id' => $lodge->id]);
        foreach (['hotel_manager', 'Owner'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'lodge_id' => null]));
            $this->post(route('companies.store'), ['name' => $role.' Registered Company'])->assertSessionHasNoErrors();
            $unassigned = Company::where('name', $role.' Registered Company')->firstOrFail();
            $assigned = Company::create(['name' => $role.' Other Lodge Company', 'lodge_id' => $otherLodge->id]);
            foreach ([$unassigned, $assigned] as $company) {
                $this->get(route('companies.index'))->assertOk()->assertSee($company->name);
                $this->get(route('invoices.create', $guest))->assertOk()->assertSee($company->name);
                $payload = $this->payload();
                $payload['billing_type'] = 'company';
                $payload['company_id'] = $company->id;
                $this->post(route('invoices.store', $guest), $payload)->assertSessionHasNoErrors()->assertRedirect();
                $invoice = Invoice::latest('id')->firstOrFail();
                $this->assertSame($company->id, $invoice->company_id);
                $this->assertSame($company->name, $invoice->bill_to['name']);
            }
        }
    }

    public function test_cashier_company_selection_remains_limited_to_their_lodge(): void
    {
        $lodge = \App\Models\Lodge::create(['name' => 'Cashier Lodge']);
        $otherLodge = \App\Models\Lodge::create(['name' => 'Other Company Lodge']);
        $guest = Guest::create(['full_name' => 'Cashier Guest', 'lodge_id' => $lodge->id]);
        $own = Company::create(['name' => 'Available Company', 'lodge_id' => $lodge->id]);
        $other = Company::create(['name' => 'Restricted Company', 'lodge_id' => $otherLodge->id]);
        $this->actingAs(User::factory()->create(['role' => 'cashier', 'lodge_id' => $lodge->id]));
        $this->get(route('invoices.create', $guest))->assertOk()->assertSee($own->name)->assertDontSee($other->name);
        $payload = $this->payload();
        $payload['billing_type'] = 'company';
        $payload['company_id'] = $other->id;
        $this->post(route('invoices.store', $guest), $payload)->assertSessionHasErrors('company_id');
        $this->assertDatabaseCount('invoices', 0);
        $payload['company_id'] = $own->id;
        $this->post(route('invoices.store', $guest), $payload)->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_logo_upload_is_preserved_on_issued_invoices(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
        $upload = \Illuminate\Http\UploadedFile::fake()->createWithContent('logo.png', $png);
        $this->put(route('settings.invoice.update'), array_replace(InvoiceSetting::details(), ['logo' => $upload]))->assertSessionHasNoErrors();
        $path = InvoiceSetting::details()['logo_path'];
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($path);
        $this->get(route('settings.invoice.edit'))->assertOk()->assertSee('data:image/png;base64,', false);

        $guest = Guest::create(['full_name' => 'Logo Guest']);
        $this->post(route('invoices.store', $guest), $this->payload())->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $this->assertSame($path, $invoice->issuer_details['logo_path']);

        $this->put(route('settings.invoice.update'), InvoiceSetting::details())->assertSessionHasNoErrors();
        $this->assertSame($path, InvoiceSetting::details()['logo_path']);
        $this->put(route('settings.invoice.update'), array_replace(InvoiceSetting::details(), ['remove_logo' => 1]))->assertSessionHasNoErrors();
        $this->assertNull(InvoiceSetting::details()['logo_path']);
        $this->get(route('invoices.print', $invoice))->assertOk()->assertSee('data:image/png;base64,', false);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($path);
    }

    public function test_logo_upload_rejects_non_images_and_untrusted_paths(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
        $this->put(route('settings.invoice.update'), array_replace(InvoiceSetting::details(), [
            'logo' => \Illuminate\Http\UploadedFile::fake()->createWithContent('logo.php', '<?php echo 1;'),
        ]))->assertSessionHasErrors('logo');
        $this->put(route('settings.invoice.update'), array_replace(InvoiceSetting::details(), ['logo_path' => 'arbitrary.png']))->assertSessionHasNoErrors();
        $this->assertNull(InvoiceSetting::details()['logo_path']);
    }

    private function payload(): array
    {
        return ['billing_type' => 'guest', 'bill_to' => ['name' => 'Customer Name', 'tin' => '123-456'],
            'issued_at' => '2026-10-01', 'due_date' => '2026-10-08',
            'items' => [['description' => 'Accommodation', 'quantity' => 3, 'unit_price' => '50000.25'],
                ['description' => 'Laundry', 'quantity' => 1, 'unit_price' => '1000.10']]];
    }

    public function test_guest_invoice_totals_printing_and_payment(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']));
        $guest = Guest::create(['full_name' => 'Guest']);
        $this->get(route('invoices.create', $guest))->assertOk()->assertSee('Customer details');
        $this->post(route('invoices.store', $guest), $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        $invoice = Invoice::firstOrFail();
        $this->assertSame('151000.85', $invoice->subtotal);
        $this->assertCount(2, $invoice->items);
        $this->get(route('invoices.print', $invoice))->assertOk()->assertSee('Customer Name')->assertSee('121-013-479')->assertSee('61253910')->assertSee('SELCOME')->assertSee('Amount in words');
        $this->get(route('guests.show', $guest))->assertOk()->assertSee($invoice->invoice_number);
        $this->post(route('payments.store'), ['target_type' => 'invoice', 'target_id' => $invoice->id, 'amount' => 1000, 'payment_method' => 'Cash'])->assertSessionHasNoErrors();
        $this->assertSame('150000.85', $invoice->fresh()->balance_amount);
    }

    public function test_company_and_issuer_details_are_snapshotted(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hotel_manager']));
        $this->post(route('companies.store'), ['name' => 'Example Organization', 'tin' => '999-111'])->assertSessionHasNoErrors();
        $company = Company::firstOrFail();
        $guest = Guest::create(['full_name' => 'Guest']);
        $payload = $this->payload();
        unset($payload['bill_to']);
        $payload['billing_type'] = 'company';
        $payload['company_id'] = $company->id;
        $this->post(route('invoices.store', $guest), $payload)->assertSessionHasNoErrors();
        $invoice = Invoice::firstOrFail();
        $company->update(['name' => 'Changed Organization']);
        $this->put(route('settings.invoice.update'), array_replace(InvoiceSetting::details(), ['name' => 'Changed Hotel']))->assertSessionHasNoErrors();
        $this->get(route('invoices.print', $invoice))->assertOk()->assertSee('Example Organization')->assertSee('HARD ROCK LODGE')->assertDontSee('Changed Hotel')->assertDontSee('Changed Organization');
        $this->get(route('companies.index'))->assertOk();
        $this->get(route('companies.edit', $company))->assertOk();
        $this->get(route('settings.invoice.edit'))->assertOk();
    }

    public function test_invalid_items_and_company_do_not_create_invoices(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'cashier']));
        $guest = Guest::create(['full_name' => 'Guest']);
        $payload = $this->payload();
        $payload['items'][0]['quantity'] = -1;
        $this->post(route('invoices.store', $guest), $payload)->assertSessionHasErrors('items.0.quantity');
        $payload = $this->payload();
        $payload['billing_type'] = 'company';
        $payload['company_id'] = 999;
        $this->post(route('invoices.store', $guest), $payload)->assertSessionHasErrors('company_id');
        $this->assertDatabaseCount('invoices', 0);
        $this->get(route('settings.invoice.edit'))->assertForbidden();
    }

    public function test_cashier_cannot_access_another_lodges_guest_or_invoice(): void
    {
        $lodge = \App\Models\Lodge::create(['name' => 'Other Lodge']);
        $guest = Guest::create(['full_name' => 'Other Guest', 'lodge_id' => $lodge->id]);
        $invoice = Invoice::create(['invoice_number' => 'OTHER-1', 'guest_id' => $guest->id, 'lodge_id' => $lodge->id]);
        $this->actingAs(User::factory()->create(['role' => 'cashier', 'lodge_id' => null]));
        $this->get(route('invoices.create', $guest))->assertForbidden();
        $this->post(route('invoices.store', $guest), $this->payload())->assertForbidden();
        $this->get(route('invoices.print', $invoice))->assertForbidden();
    }
}

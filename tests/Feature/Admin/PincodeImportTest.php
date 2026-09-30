<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class PincodeImportTest extends TestCase
{
    public function test_guest_cannot_import_pincodes(): void
    {
        $this->post(route('admin.pincodes.import'))->assertRedirect();
    }

    public function test_guest_cannot_download_template(): void
    {
        $this->get(route('admin.pincodes.template'))->assertRedirect();
    }

    public function test_non_admin_cannot_import_pincodes(): void
    {
        $user = new User(['role' => User::ROLE_USER, 'name' => 'Buyer', 'email' => 'buyer@example.test']);
        $user->id = 9;

        $this->actingAs($user)->post(route('admin.pincodes.import'))->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_teacher_creation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->post('/admin/teachers', [
            'user_id' => $user->id,
            'specialization' => 'Mathematics',
        ]);

        fwrite(STDERR, 'Status: '.$response->status()."\n");
        fwrite(STDERR, 'Redirect: '.$response->headers->get('Location')."\n");

        if ($response->exception) {
            fwrite(STDERR, 'Exception: '.$response->exception->getMessage()."\n");
            fwrite(STDERR, "Exception trace:\n".$response->exception->getTraceAsString()."\n");
        }

        $errors = session('errors');
        fwrite(STDERR, 'Session errors type: '.(is_object($errors) ? get_class($errors) : gettype($errors))."\n");
        fwrite(STDERR, 'Session errors: '.json_encode($errors)."\n");
        fwrite(STDERR, 'Session status: '.(session('status') ?? 'none')."\n");

        $this->assertTrue(true);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\User;
use App\Models\Student;
use Illuminate\Support\Facades\Schema;

class UserTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    /**
     * To test the register and get students
     * @return void
     */
    public function test_register_and_get_students()
    {
        //Filling database

        DB::table('students')->insert(['firstname' => 'Brayan']);
        DB::table('students')->insert(['firstname' => 'Miranda']);
        DB::table('students')->insert(['firstname' => 'Carlos']);

        //Register

        $response = $this->post('/api/register',
        ['name' => 'BrayanD',
        'email' => 'brayanduranmedina@gmail.com',
        'password' => '12345678']);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'user' => ['name', 'email', 'id'],
            'access_token',
            'token_type',
        ]);

        $response->assertJsonPath('user.id', 1);
        $response->assertJsonPath('user.name', 'BrayanD');
        $response->assertJsonPath('user.email', 'brayanduranmedina@gmail.com');
        $response->assertJsonPath('token_type', 'Bearer');

        //Get Token

        $token = $response['access_token'];

        //Getting students

        $response = $this->get('/api/students',
        ['Authorization' => 'Bearer '.$token]);

        $response->assertStatus(200);

        $response->assertJsonPath('students.0.firstname', 'Brayan');
        $response->assertJsonPath('students.1.firstname', 'Miranda');
        $response->assertJsonPath('students.2.firstname', 'Carlos');
    }
}

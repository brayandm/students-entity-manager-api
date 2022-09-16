<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\User;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UserTest extends TestCase
{
    use DatabaseMigrations, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function register()
    {
        $response = $this->post(
            '/api/register',
            [
                'name' => 'BrayanD',
                'email' => 'brayanduranmedina@gmail.com',
                'password' => '12345678'
            ]
        );

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

        $this->app->get('auth')->forgetGuards();

        return $response;
    }

    public function test_register_and_get_students()
    {
        //Filling database

        DB::table('students')->insert(['firstname' => 'Brayan']);
        DB::table('students')->insert(['firstname' => 'Miranda']);
        DB::table('students')->insert(['firstname' => 'Carlos']);

        //Register

        $response = $this->register();

        //Get token

        $token = $response['access_token'];

        //Getting students

        $response = $this->get(
            '/api/students',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);

        $response->assertJsonPath('students.0.firstname', 'Brayan');
        $response->assertJsonPath('students.1.firstname', 'Miranda');
        $response->assertJsonPath('students.2.firstname', 'Carlos');

        //Logout

        $response = $this->post('/api/logout', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);

        $response->assertJsonPath('message', 'Successful logout');

        $this->app->get('auth')->forgetGuards();

        //Getting students and fails

        $response = $this->get(
            '/api/students',
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ]
        );

        $response->assertStatus(401);

        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_get_student_by_id()
    {
        //Filling database

        Student::factory(10)->create();

        //Register

        $response = $this->register();

        //Get token

        $token = $response['access_token'];

        //Getting students

        $response = $this->get(
            '/api/students',
            ['Authorization' => 'Bearer ' . $token]
        );

        $this->assertEquals(10, count($response['students']));

        //Getting students by id

        $response = $this->get(
            '/api/students/1',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertJsonStructure([
            'students' =>
            ['id',
            'firstname',
            'lastname',
            'email',
            'photo',
            'birthdate',
            'address',
            'score',
            'created_at',
            'updated_at']
        ]);
    }

    public function test_delete_students()
    {
        //Filling database

        Student::factory(10)->create();

        //Register

        $response = $this->register();

        //Get token

        $token = $response['access_token'];

        //Getting students

        $response = $this->get(
            '/api/students',
            ['Authorization' => 'Bearer ' . $token]
        );

        $this->assertEquals(10, count($response['students']));

        //Deleting students

        for ($i = 1; $i <=10; $i++)
        {
            $response = $this->delete(
                '/api/students/'.$i,
                ['Authorization' => 'Bearer ' . $token]
            );
        }

        //Counting students

        $response = $this->get(
            '/api/students',
            ['Authorization' => 'Bearer ' . $token]
        );

        $this->assertEquals(0, count($response['students']));
    }

    public function test_add_student()
    {
        //Register

        $response = $this->register();

        //Get token

        $token = $response['access_token'];

        //Adding student

        $response = $this->post('/api/students',[
            'firstname' => 'Brayan',
            'lastname' => 'Duran',
            'email' => 'brayanduranmedina@gmail.com',
            'photo' => UploadedFile::fake()->image('image.jpg'),
            'birthdate' => '2001-9-22',
            'birthdate' => '2001-9-22',
            'address' => 'Carrer del Clot',
            'score' => '10',
        ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);

        //Counting students

        $response = $this->get(
            '/api/students',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);

        $this->assertEquals(1, count($response['students']));

        //Checking storage

        $this->assertTrue(Storage::exists('public/pictures/1.jpg'));
    }

    public function test_login_and_logout()
    {
        //Register format incorrect

        $response = $this->post(
            '/api/register',
            [
                'name' => 'BrayanD',
                'email' => 'brayanduranmedina',
                'password' => '12345678'
            ]
        );

        $response->assertStatus(400);

        //Register

        $response = $this->register();

        //Login format incorrect

        $response = $this->post('/api/login', [
            'email' => 'brayanduranmedina',
            'password' => '12345678'
        ]);

        $response->assertStatus(400);

        $this->app->get('auth')->forgetGuards();

        //Login unauthenticated

        $response = $this->post('/api/login', [
            'email' => 'brayanduranmedina@gmail.com',
            'password' => '123456789'
        ]);

        $response->assertStatus(401);

        $this->app->get('auth')->forgetGuards();

        //Login

        $response = $this->post('/api/login', [
            'email' => 'brayanduranmedina@gmail.com',
            'password' => '12345678'
        ]);

        $response->assertStatus(200);

        $this->app->get('auth')->forgetGuards();

        //Get token

        $token = $response['access_token'];

        //Logout

        $response = $this->post('/api/logoutall', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);

        $response->assertJsonPath('message', 'Successful logout');

        $this->app->get('auth')->forgetGuards();
    }

    public function test_get_add_delete_edit_fails()
    {
        //Register

        $response = $this->register();

        //Get token

        $token = $response['access_token'];

        //Get student

        $response = $this->get('/api/students/1', ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(400);

        //Add student

        $response = $this->post('/api/students', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(400);

        //Delete student

        $response = $this->delete('/api/students/1', ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(400);

        //Edit student

        $response = $this->post('/api/students/1', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(400);
    }

    public function test_edit()
    {
        //Register

        $response = $this->register();

        //Get token

        $token = $response['access_token'];

        //Adding student

        $response = $this->post('/api/students',[
            'firstname' => 'Brayan',
            'lastname' => 'Duran',
            'email' => 'brayanduranmedina@gmail.com',
            'photo' => UploadedFile::fake()->image('image.jpg'),
            'birthdate' => '2001-9-22',
            'birthdate' => '2001-9-22',
            'address' => 'Carrer del Clot',
            'score' => '10',
        ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);

        //Editing student fails incorrect email

        $response = $this->post('/api/students/1',[
            'firstname' => 'Brayan',
            'lastname' => 'Duran',
            'email' => 'brayanduranmedina',
            'photo' => UploadedFile::fake()->image('image.jpg'),
            'birthdate' => '2001-9-22',
            'birthdate' => '2001-9-22',
            'address' => 'Carrer del Clot',
            'score' => '10',
        ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(400);

        //Editing student

        $response = $this->post('/api/students/1',[
            'firstname' => 'Brayan',
            'lastname' => 'Duran',
            'email' => 'brayanduranmedina@gmail.com',
            'photo' => UploadedFile::fake()->image('image.jpg'),
            'birthdate' => '2001-9-22',
            'birthdate' => '2001-9-22',
            'address' => 'Carrer del Clot',
            'score' => '10',
        ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);
    }

    public function test_get_photo()
    {
        //Register

        $response = $this->register();

        //Get token

        $token = $response['access_token'];

        //Adding student

        $response = $this->post('/api/students',[
            'firstname' => 'Brayan',
            'lastname' => 'Duran',
            'email' => 'brayanduranmedina@gmail.com',
            'photo' => UploadedFile::fake()->image('image.jpg'),
            'birthdate' => '2001-9-22',
            'birthdate' => '2001-9-22',
            'address' => 'Carrer del Clot',
            'score' => '10',
        ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);

        //Getting photo

        $response = $this->get('/api/pictures/1.jpg',
         ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(200);
    }
}

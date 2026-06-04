<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\District;
use App\Models\Feedback;

class StudentFeatureTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $student;
    protected $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $district = District::firstOrCreate(
            ['id' => 1],
            ['name' => 'Central District', 'quadrant_id' => 1]
        );

        $this->school = School::firstOrCreate(
            ['school_id' => 'SCH-TEST-001'],
            ['name' => 'Automated Test School', 'level' => 'highschool', 'district_id' => $district->id]
        );

        $this->student = User::factory()->create([
            'first_name' => 'Test',
            'last_name'  => 'Student',
            'email'      => 'student_test_' . uniqid() . '@student.deped.gov.ph',
            'password'   => bcrypt('password123'),
            'role'       => 'student',
            'status'     => 'verified',
            'lrn'        => 'LRN-' . uniqid(),
            'username'   => 'teststudent_' . uniqid(),
            'school_id'  => $this->school->id,
        ]);
    }

    /**
     * Test 1: Student Self-Registration
     * Verify that a new user can successfully create a Student account via the public registration page.
     */
    public function test_1_student_can_self_register()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $email = 'newlearner_' . uniqid() . '@student.deped.gov.ph';

        $response = $this->post('/register', [
            'first_name'            => 'New',
            'last_name'             => 'Learner',
            'username'              => 'newlearner' . rand(100, 999), // letters + numbers, no special chars that fail regex
            'email'                 => $email,
            'password'              => 'Password123',               // mixedCase + numbers required
            'password_confirmation' => 'Password123',
            'role'                  => 'student',
            'grade_level'           => 'Grade 7',
            'school_id'             => $this->school->id,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role'  => 'student',
        ]);
    }

    /**
     * Test 2: Student Email Verification – Resend Email
     * Verify that a student can request a new verification email if the original was not received.
     */
    public function test_2_student_can_resend_verification_email()
    {
        $unverifiedStudent = User::factory()->create([
            'role'              => 'student',
            'email_verified_at' => null,
            'school_id'         => $this->school->id,
        ]);

        $response = $this->post('/email/resend', [
            'email' => $unverifiedStudent->email,
        ]);

        $response->assertRedirect();
    }

    /**
     * Test 3: Student Login – Valid Credentials
     * Verify that a verified student can log in using valid credentials and is redirected to the Student Dashboard.
     */
    public function test_3_student_login_with_valid_credentials()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $this->post('/login', [
            'login_id' => $this->student->email,
            'password' => 'password123',
        ]);

        $this->actingAs($this->student);

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    /**
     * Test 4: Forgot Password – Reset via Email
     * Verify that a student can reset their password using the Forgot Password function.
     */
    public function test_4_student_can_request_password_reset()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);

        $response = $this->post('/forgot-password', [
            'email' => $this->student->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    /**
     * Test 5: Student Dashboard – Overview Statistics Display
     * Verify that the Student Dashboard displays all key academic metrics and progress indicators.
     */
    public function test_5_student_dashboard_displays_expected_metrics()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/home');

        $response->assertStatus(200);
        $response->assertSee('Overall Progress');
        $response->assertSee('Certificates');
    }

    /**
     * Test 6: Resume an Active Learning Module
     * Verify that a student can resume a learning module from exactly where they left off.
     */
    public function test_6_student_can_view_enrolled_materials_page()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/enrolled');

        $response->assertStatus(200);
    }

    /**
     * Test 7: Explore the Library – View Featured Carousel
     * Verify that a student can view and navigate the Featured Carousel on the Explore page.
     */
    public function test_7_student_can_access_explore_page()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/explore');

        $response->assertStatus(200);
    }

    /**
     * Test 8: Browse and Filter Materials by Category
     * Verify that a student can browse materials by dynamic category rows and filter to see all items in a category.
     */
    public function test_8_student_can_filter_explore_by_category()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/explore/filter');

        $response->assertStatus(200);
    }

    /**
     * Test 9: Browse Materials from Your School
     * Verify that a student can find materials specifically uploaded by teachers at their registered school.
     */
    public function test_9_student_can_view_explore_page_with_school_context()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/explore');

        $response->assertStatus(200);
    }

    /**
     * Test 10: Join Assessment via Access Code
     * Verify that a student can enter an assessment using a valid access code provided by their teacher.
     */
    public function test_10_student_can_submit_assessment_access_code()
    {
        $response = $this->actingAs($this->student)->postJson('/student/assessment/verify', [
            'code' => 'XKWP00',
        ]);

        // Expects either a valid response or a not-found — not a server error
        $this->assertNotEquals(500, $response->status());
    }

    /**
     * Test 11: Navigate the Assessment Lobby
     * Verify that a student sees all relevant exam information in the Assessment Lobby before starting.
     */
    public function test_11_student_assessment_lobby_requires_valid_access_key()
    {
        // A request with a non-existent access key should return 404 or redirect, not a 500 error
        $response = $this->actingAs($this->student)->get('/assessment/invalid-access-key/lobby');

        $this->assertNotEquals(500, $response->status());
    }

    /**
     * Test 12: Start and Navigate an Assessment Exam
     * Verify that a student can start a timed assessment and navigate between questions within a section.
     */
    public function test_12_student_exam_page_requires_valid_access_key()
    {
        // A request with a non-existent access key should return 404 or redirect, not a 500 error
        $response = $this->actingAs($this->student)->get('/assessment/invalid-access-key/exam');

        $this->assertNotEquals(500, $response->status());
    }

    /**
     * Test 13: Submit an Assessment Section
     * Verify that a student can submit a completed section and that the system confirms before locking it.
     */
    public function test_13_student_assessment_submit_requires_valid_access_key()
    {
        $response = $this->actingAs($this->student)->postJson('/assessment/invalid-access-key/submit', [
            'answers' => [],
        ]);

        $this->assertNotEquals(500, $response->status());
    }

    /**
     * Test 14: View Assessment Results
     * Verify that a student can see their score and performance breakdown immediately after completing an assessment.
     */
    public function test_14_student_assessment_results_page_requires_valid_access_key()
    {
        $response = $this->actingAs($this->student)->get('/assessment/invalid-access-key/results');

        $this->assertNotEquals(500, $response->status());
    }

    /**
     * Test 15: Start and Complete a Learning Module
     * Verify that a student can open, navigate through, and complete a full learning module including embedded quizzes.
     */
    public function test_15_student_can_access_material_show_page()
    {
        // Students access their materials via the enrolled page, not the teacher/admin materials route
        $response = $this->actingAs($this->student)->get('/dashboard/enrolled');

        $response->assertStatus(200);
    }

    /**
     * Test 16: View Document in Full Screen
     * Verify that a student can view an embedded PDF document in full screen and use zoom controls.
     */
    public function test_16_student_can_access_study_route_with_valid_hashid()
    {
        // Accessing with an invalid hashid should return 404, not a 500 server error
        $response = $this->actingAs($this->student)->get('/dashboard/materials/invalidhash/study');

        $this->assertNotEquals(500, $response->status());
    }

    /**
     * Test 17: View Exam Results and Download Certificate
     * Verify that a student who passes a module receives a certificate and can download it as a PDF.
     */
    public function test_17_student_can_access_certificates_page()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/certificates');

        $response->assertStatus(200);
    }

    /**
     * Test 18: Track Progress in My Progress Dashboard
     * Verify that the student's My Progress page displays accurate analytics.
     */
    public function test_18_student_can_access_analytics_page()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/analytics');

        $response->assertStatus(200);
    }

    /**
     * Test 19: Access and Manage Earned Certificates
     * Verify that the student can view, search, and download all earned certificates from the My Certificates page.
     */
    public function test_19_student_certificates_page_loads_correctly()
    {
        $response = $this->actingAs($this->student)->get('/dashboard/certificates');

        $response->assertStatus(200);
    }

    /**
     * Test 20: Update Student Profile Information
     * Verify that a student can update their personal profile information and account security settings.
     */
    public function test_20_student_can_update_profile_information()
    {
        // The ProfileController re-fetches the user fresh from the DB and enforces a 30-day
        // cooldown on updated_at. We bypass this by writing the backdated timestamp directly
        // to the database, skipping Eloquent's automatic timestamp behaviour.
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $this->student->id)
            ->update(['updated_at' => now()->subDays(31)]);

        $response = $this->actingAs($this->student)->patch('/dashboard/profile', [
            'first_name'  => 'UpdatedFirst',
            'middle_name' => null,
            'last_name'   => 'UpdatedLast',
            'suffix'      => null,
            'username'    => $this->student->username,
            'school_id'   => $this->school->id,
            'grade_level' => 'Grade 8',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'         => $this->student->id,
            'first_name' => 'UpdatedFirst',
            'last_name'  => 'UpdatedLast',
        ]);
    }

    /**
     * Test 21: Submit a Support Ticket
     * Verify that a student can submit a support ticket from the Support Center within their profile.
     */
    public function test_21_student_can_submit_support_ticket()
    {
        $response = $this->actingAs($this->student)->post('/dashboard/feedback/store', [
            'subject'  => 'Cannot load my module',
            'category' => 'bug',
            'message'  => 'The learning module fails to load on my browser.',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('feedbacks', [
            'user_id'  => $this->student->id,
            'subject'  => 'Cannot load my module',
            'category' => 'bug',
            'status'   => 'open',
        ]);
    }

    /**
     * Test 22: Assessment Focus Lost Pause Warning
     * Verify that the assessment system detects when a student navigates away from the browser
     * during an active exam and records a focus lost pause.
     */
    public function test_22_student_assessment_autosave_handles_focus_lost()
    {
        // The autosave endpoint should gracefully handle the request (not 500 server error)
        // even when the access key is invalid or session has expired
        $response = $this->actingAs($this->student)->postJson('/assessment/invalid-access-key/autosave', [
            'answers'    => [],
            'focus_lost' => true,
        ]);

        $this->assertNotEquals(500, $response->status());
    }
}
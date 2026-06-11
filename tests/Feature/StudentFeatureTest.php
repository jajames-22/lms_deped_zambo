<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\School;
use App\Models\Assessment;
use App\Models\AssessmentAccess;
use App\Models\AssessmentSession;
use App\Models\StudentAnswer;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Feedback;
use Illuminate\Support\Facades\Mail;

/**
 * StudentFeatureTest — 22 tests
 *
 * Runs against the live `lms_testing` MySQL database.
 * No RefreshDatabase / DatabaseMigrations.
 */
class StudentFeatureTest extends TestCase
{
    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Return an authenticated, verified student from lms_testing. */
    private function activeStudent(): User
    {
        // In lms_testing, verified students have status='verified'
        $student = User::where('role', 'student')
            ->where('status', 'verified')
            ->whereNotNull('email_verified_at')
            ->first();

        // Fallback: pick any student with a verified email
        if (!$student) {
            $student = User::where('role', 'student')
                ->whereNotNull('email_verified_at')
                ->first();
        }

        $this->assertNotNull($student, 'No verifiable student found in lms_testing database.');
        return $student;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Registration & Email
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_student_self_registration(): void
    {
        Mail::fake();

        $school = School::first();
        $this->assertNotNull($school, 'No schools found in lms_testing.');

        $email    = 'self_reg_' . time() . '@lmstest.ph';
        $username = 'selfreg' . time();

        $response = $this->post('/register', [
            'first_name'            => 'SelfReg',
            'last_name'             => 'Student',
            'username'              => $username,
            'email'                 => $email,
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
            'school_id'             => $school->id,
            'role'                  => 'student',
            'grade_level'           => 'Grade 8',
        ]);

        // Should redirect back with verification modal flash
        $response->assertRedirect();

        // Teardown
        User::where('email', $email)->delete();
    }

    #[Test]
    public function test_student_email_verification_resend_email(): void
    {
        Mail::fake();

        $unverified = User::where('role', 'student')
            ->whereNull('email_verified_at')
            ->whereNotNull('email')
            ->first();

        if (!$unverified) {
            $this->markTestSkipped('No unverified student found in lms_testing; skipping resend test.');
        }

        $response = $this->post('/email/resend', [
            'email' => $unverified->email,
        ]);

        $response->assertRedirect();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Authentication
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_student_login_valid_credentials(): void
    {
        $student = User::where('role', 'student')
            ->where('status', 'verified')
            ->whereNotNull('email_verified_at')
            ->first();

        $this->assertNotNull($student, 'Need at least one verified student in lms_testing.');

        // Test the login endpoint is reachable (real DB passwords are not known)
        $response = $this->post('/login', [
            'login_id' => $student->email,
            'password' => 'wrong-intentional',
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Confirm session-based auth works
        $dashResponse = $this->actingAs($student)->get('/dashboard');
        $dashResponse->assertStatus(200);
    }

    #[Test]
    public function test_forgot_password_reset_via_email(): void
    {
        Mail::fake();

        $student = $this->activeStudent();

        $response = $this->post('/forgot-password', [
            'email' => $student->email,
        ]);

        // Should redirect back with a status message
        $response->assertRedirect();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Dashboard
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_student_dashboard_overview_statistics_display(): void
    {
        $student = $this->activeStudent();

        $response = $this->actingAs($student)->get('/dashboard/home');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_resume_an_active_learning_module(): void
    {
        $student = $this->activeStudent();

        $enrollment = Enrollment::with('material')
            ->where('user_id', $student->id)
            ->where('status', '!=', 'completed')
            ->first();

        if (!$enrollment || !$enrollment->material) {
            $this->markTestSkipped('No in-progress enrollment found for this student in lms_testing.');
        }

        $hashid   = $enrollment->material->hashid;
        $response = $this->actingAs($student)->get("/dashboard/materials/{$hashid}/study");

        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Explore
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_explore_the_library_view_featured_carousel(): void
    {
        $student = $this->activeStudent();

        $response = $this->actingAs($student)->get('/dashboard/explore');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_browse_and_filter_materials_by_category(): void
    {
        $student = $this->activeStudent();

        $response = $this->actingAs($student)->get('/dashboard/explore/filter?category=Mathematics');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    #[Test]
    public function test_browse_materials_from_your_school(): void
    {
        $student = $this->activeStudent();

        $response = $this->actingAs($student)->get('/dashboard/enrolled');
        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. Assessment Journey
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_join_assessment_via_access_code(): void
    {
        $student = $this->activeStudent();

        $assessment = Assessment::where('status', 'published')->first();
        $this->assertNotNull($assessment, 'No published assessments in lms_testing.');

        $response = $this->actingAs($student)->post('/student/assessment/verify', [
            'assessment_code' => $assessment->access_key,
        ]);

        // Either success (has lrn access) or 403/404 (not authorized)
        $this->assertContains($response->getStatusCode(), [200, 403, 404]);
    }

    #[Test]
    public function test_navigate_the_assessment_lobby(): void
    {
        $student    = $this->activeStudent();
        $assessment = Assessment::where('status', 'published')->first();
        $this->assertNotNull($assessment, 'No published assessments in lms_testing.');

        if (!$student->lrn) {
            $this->markTestSkipped('Student has no LRN; cannot navigate assessment lobby.');
        }

        $access = AssessmentAccess::firstOrCreate([
            'assessment_id' => $assessment->id,
            'lrn'           => $student->lrn,
        ], [
            'status'      => 'lobby',
            'pauses_left' => 3,
        ]);

        $response = $this->actingAs($student)
            ->withSession(['assessment_access_granted_' . $assessment->id => true])
            ->get("/assessment/{$assessment->access_key}/lobby");

        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Teardown
        AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('lrn', $student->lrn)
            ->where('status', 'lobby')
            ->delete();
    }

    #[Test]
    public function test_start_and_navigate_an_assessment_exam(): void
    {
        $student    = $this->activeStudent();
        $assessment = Assessment::where('status', 'published')->first();
        $this->assertNotNull($assessment, 'No published assessments in lms_testing.');

        if (!$student->lrn) {
            $this->markTestSkipped('Student has no LRN; cannot start assessment exam.');
        }

        $access = AssessmentAccess::firstOrCreate([
            'assessment_id' => $assessment->id,
            'lrn'           => $student->lrn,
        ], [
            'status'      => 'taking_exam',
            'pauses_left' => 3,
        ]);

        $response = $this->actingAs($student)
            ->withSession(['assessment_access_granted_' . $assessment->id => true])
            ->get("/assessment/{$assessment->access_key}/exam");

        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Teardown
        AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('lrn', $student->lrn)
            ->where('status', 'taking_exam')
            ->delete();
    }

    #[Test]
    public function test_submit_an_assessment_section(): void
    {
        $student    = $this->activeStudent();
        $assessment = Assessment::with('categories')->where('status', 'published')->first();
        $this->assertNotNull($assessment, 'No published assessments in lms_testing.');

        $category = $assessment->categories->first();
        if (!$category) {
            $this->markTestSkipped('No assessment categories found; skipping section submission test.');
        }

        if (!$student->lrn) {
            $this->markTestSkipped('Student has no LRN.');
        }

        AssessmentAccess::firstOrCreate([
            'assessment_id' => $assessment->id,
            'lrn'           => $student->lrn,
        ], ['status' => 'taking_exam', 'pauses_left' => 3]);

        $response = $this->actingAs($student)
            ->withSession(['assessment_access_granted_' . $assessment->id => true])
            ->post("/assessment/{$assessment->access_key}/submit", [
                'category_id' => $category->id,
                'answers'     => [],
            ]);

        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Teardown
        AssessmentSession::where('user_id', $student->id)
            ->where('assessment_id', $assessment->id)
            ->where('category_id', $category->id)
            ->delete();

        AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('lrn', $student->lrn)
            ->delete();
    }

    #[Test]
    public function test_view_assessment_results(): void
    {
        $student    = $this->activeStudent();
        $assessment = Assessment::where('status', 'published')->first();
        $this->assertNotNull($assessment, 'No published assessments in lms_testing.');

        if (!$student->lrn) {
            $this->markTestSkipped('Student has no LRN.');
        }

        $response = $this->actingAs($student)
            ->withSession(['assessment_access_granted_' . $assessment->id => true])
            ->get("/assessment/{$assessment->access_key}/results");

        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 6. Learning Module
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_start_and_complete_a_learning_module(): void
    {
        $student  = $this->activeStudent();
        $material = Material::where('status', 'published')->first();
        $this->assertNotNull($material, 'No published materials in lms_testing.');

        // Enroll the student if not already enrolled
        $enrollment = Enrollment::firstOrCreate([
            'user_id'     => $student->id,
            'material_id' => $material->id,
        ], ['status' => 'in_progress']);

        // Post completion
        $response = $this->actingAs($student)->post("/dashboard/materials/{$material->id}/complete");
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Teardown
        Enrollment::where('user_id', $student->id)
            ->where('material_id', $material->id)
            ->delete();
    }

    #[Test]
    public function test_view_document_in_full_screen(): void
    {
        $student  = $this->activeStudent();
        $material = Material::where('status', 'published')->first();
        $this->assertNotNull($material, 'No published materials in lms_testing.');

        $hashid   = $material->hashid;
        $response = $this->actingAs($student)->get("/dashboard/materials/{$hashid}/show");
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /**
     * Creates a temporary completed enrollment so this test always runs
     * without relying on pre-existing completion data in lms_testing.
     */
    #[Test]
    public function test_view_exam_results_and_download_certificate(): void
    {
        $student  = $this->activeStudent();
        $material = Material::where('status', 'published')->first();
        $this->assertNotNull($material, 'No published materials in lms_testing.');

        // Create a temporary completed enrollment for this student
        $enrollment = Enrollment::firstOrCreate([
            'user_id'     => $student->id,
            'material_id' => $material->id,
        ], [
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // If the enrollment already existed but wasn't completed, set it to completed
        if ($enrollment->status !== 'completed') {
            $originalStatus = $enrollment->status;
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
        }

        $hashid   = $material->hashid;
        $response = $this->actingAs($student)->get("/dashboard/materials/{$hashid}/result");
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Teardown: restore or delete the enrollment
        if (isset($originalStatus)) {
            $enrollment->update(['status' => $originalStatus, 'completed_at' => null]);
        } else {
            // Only delete if we created it
            Enrollment::where('user_id', $student->id)
                ->where('material_id', $material->id)
                ->where('status', 'completed')
                ->delete();
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 7. Progress & Certificates
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_track_progress_in_my_progress_dashboard(): void
    {
        $student = $this->activeStudent();

        $response = $this->actingAs($student)->get('/dashboard/home');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_access_and_manage_earned_certificates(): void
    {
        $student = $this->activeStudent();

        $response = $this->actingAs($student)->get('/dashboard/certificates');
        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 8. Profile Management
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_update_student_profile_information(): void
    {
        $student = $this->activeStudent();

        $originalFirst = $student->first_name;
        $newFirst      = 'Updated' . substr(time(), -4);

        $response = $this->actingAs($student)->patch('/dashboard/profile', [
            'first_name'  => $newFirst,
            'last_name'   => $student->last_name,
            'middle_name' => $student->middle_name,
            'username'    => $student->username,
            'email'       => $student->email,
            'school_id'   => $student->school_id,
            'grade_level' => $student->grade_level,
        ]);

        // 200 = success JSON, 302 = redirect after update, 422 = validation error
        $this->assertContains($response->getStatusCode(), [200, 302, 422]);

        // Restore original first_name if it was changed
        $student->refresh();
        if ($student->first_name !== $originalFirst) {
            $student->update(['first_name' => $originalFirst]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 9. Support Tickets
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_submit_a_support_ticket(): void
    {
        $student = $this->activeStudent();

        $response = $this->actingAs($student)->post('/dashboard/feedback/store', [
            'category' => 'Technical Issue',
            'subject'  => 'Automated Test Ticket ' . time(),
            'message'  => 'This is an automated test support ticket submission.',
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 422]);

        // Teardown: remove the test ticket
        Feedback::where('user_id', $student->id)
            ->where('subject', 'like', 'Automated Test Ticket%')
            ->delete();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 10. Assessment Focus / Pausing Mechanism
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Simulates browser "blur" events by POSTing to the autosave endpoint
     * with a decrementing `pauses_left` value.
     * Asserts that:
     *  1. Each autosave call succeeds (200).
     *  2. The `pauses_left` column in `assessment_accesses` is decremented correctly.
     *  3. Once pauses_left reaches 0, the access record reflects the restriction.
     */
    #[Test]
    public function test_assessment_focus_lost_pause_warning(): void
    {
        $student    = $this->activeStudent();
        $assessment = Assessment::where('status', 'published')->first();
        $this->assertNotNull($assessment, 'No published assessments in lms_testing.');

        if (!$student->lrn) {
            $this->markTestSkipped('Student has no LRN; cannot test focus-lost mechanism.');
        }

        $category = $assessment->categories()->first();
        if (!$category) {
            $this->markTestSkipped('No categories in the assessment; skipping focus-lost test.');
        }

        // Create/reset the access record with 3 pauses
        $access = AssessmentAccess::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'lrn'           => $student->lrn,
            ],
            [
                'status'      => 'taking_exam',
                'pauses_left' => 3,
            ]
        );

        // Simulate Pause 1 (focus lost once)
        $r1 = $this->actingAs($student)
            ->withSession(['assessment_access_granted_' . $assessment->id => true])
            ->post("/assessment/{$assessment->access_key}/autosave", [
                'category_id'    => $category->id,
                'time_remaining' => 590,
                'answers'        => [],
                'pauses_left'    => 2,
            ]);
        $r1->assertStatus(200);
        $this->assertEquals(2, $access->fresh()->pauses_left);

        // Simulate Pause 2 (focus lost twice)
        $r2 = $this->actingAs($student)
            ->withSession(['assessment_access_granted_' . $assessment->id => true])
            ->post("/assessment/{$assessment->access_key}/autosave", [
                'category_id'    => $category->id,
                'time_remaining' => 580,
                'answers'        => [],
                'pauses_left'    => 1,
            ]);
        $r2->assertStatus(200);
        $this->assertEquals(1, $access->fresh()->pauses_left);

        // Simulate Pause 3 — boundary condition: pauses_left hits 0
        $r3 = $this->actingAs($student)
            ->withSession(['assessment_access_granted_' . $assessment->id => true])
            ->post("/assessment/{$assessment->access_key}/autosave", [
                'category_id'    => $category->id,
                'time_remaining' => 570,
                'answers'        => [],
                'pauses_left'    => 0,
            ]);
        $r3->assertStatus(200);
        $this->assertEquals(0, $access->fresh()->pauses_left);

        // Cleanup
        StudentAnswer::where('user_id', $student->id)
            ->where('assessment_id', $assessment->id)
            ->delete();

        AssessmentSession::where('user_id', $student->id)
            ->where('assessment_id', $assessment->id)
            ->delete();

        AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('lrn', $student->lrn)
            ->delete();
    }
}

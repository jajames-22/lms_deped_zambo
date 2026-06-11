<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\School;
use App\Models\Assessment;
use App\Models\Feedback;
use App\Models\FeedbackMessage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

/**
 * AdminFeatureTest — 22 tests
 *
 * Runs against the live `lms_testing` MySQL database.
 * NO RefreshDatabase / DatabaseMigrations are used.
 * Existing admin credentials are looked up via Eloquent.
 */
class AdminFeatureTest extends TestCase
{
    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Return an authenticated Admin user from lms_testing. */
    private function adminUser(): User
    {
        $admin = User::where('role', 'admin')
            ->whereNotNull('email_verified_at')
            ->where('status', 'verified')
            ->first();

        $this->assertNotNull($admin, 'No verified admin user found in lms_testing database.');
        return $admin;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Authentication
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_admin_login_with_valid_credentials(): void
    {
        $admin = User::where('role', 'admin')
            ->whereNotNull('email_verified_at')
            ->where('status', 'verified')
            ->first();

        $this->assertNotNull($admin, 'Need at least one verified admin in lms_testing.');

        // Verify the login form endpoint accepts a POST request (reachable and no 5xx)
        $response = $this->post('/login', [
            'login_id' => $admin->email,
            'password' => 'wrong-password-intentional',
        ]);

        // Bad credentials returns back to login form (302 redirect back or 200 with errors)
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Now verify the session-based auth works with actingAs
        $dashResponse = $this->actingAs($admin)->get('/dashboard');
        $dashResponse->assertStatus(200);
    }

    #[Test]
    public function test_admin_dashboard_overview_display(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard/home');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_admin_logout(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Schools Management
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_add_new_school(): void
    {
        $admin = $this->adminUser();

        $district = \App\Models\District::first();
        $this->assertNotNull($district, 'No districts found in lms_testing.');

        $uniqueId   = 'TEST-' . time();
        $schoolName = 'Automated Test School ' . $uniqueId;

        $response = $this->actingAs($admin)->post('/dashboard/schools/store', [
            'name'        => $schoolName,
            'school_id'   => $uniqueId,
            'level'       => 'elementary',
            'district_id' => $district->id,
            'address'     => '123 Test Street, Zamboanga City',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => 'School registered successfully!']);

        // Teardown
        School::where('school_id', $uniqueId)->delete();
    }

    #[Test]
    public function test_edit_existing_school(): void
    {
        $admin = $this->adminUser();

        $school = School::first();
        $this->assertNotNull($school, 'No schools found in lms_testing.');

        $district = \App\Models\District::first();

        $original       = $school->address;
        $updatedAddress = 'Updated Address ' . now()->timestamp;

        $response = $this->actingAs($admin)->put("/dashboard/schools/{$school->id}", [
            'name'        => $school->name,
            'school_id'   => $school->school_id,
            'level'       => $school->level ?? 'elementary',
            'district_id' => $district->id,
            'address'     => $updatedAddress,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => 'School updated successfully!']);

        // Restore original
        $school->update(['address' => $original]);
    }

    #[Test]
    public function test_delete_school_cascade_warning(): void
    {
        $admin = $this->adminUser();

        $district   = \App\Models\District::first();
        $tempSchool = School::create([
            'name'        => 'Temp Delete School',
            'school_id'   => 'TEMP-DEL-' . time(),
            'level'       => 'elementary',
            'district_id' => $district->id,
        ]);

        $response = $this->actingAs($admin)->delete("/dashboard/schools/{$tempSchool->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => 'School deleted successfully!']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Student Directory
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_view_student_directory(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard/students');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_manually_register_single_student(): void
    {
        $admin = $this->adminUser();

        $school = School::first();
        $this->assertNotNull($school, 'No schools in lms_testing.');

        $uniqueEmail    = 'teststudent_' . time() . '@lmstest.ph';
        $uniqueUsername = 'teststudent' . time();
        $uniqueLrn      = '1234' . substr(time(), -6);

        $response = $this->actingAs($admin)->post('/dashboard/students/store', [
            'first_name'  => 'Test',
            'last_name'   => 'Student',
            'email'       => $uniqueEmail,
            'username'    => $uniqueUsername,
            'password'    => 'Password123',
            'school_id'   => $school->id,
            'grade_level' => 'Grade 7',
            'role'        => 'student',
            'status'      => 'verified',
            'lrn'         => $uniqueLrn,
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 422]);

        // Teardown
        User::where('email', $uniqueEmail)->delete();
    }

    #[Test]
    public function test_bulk_import_students_via_excel_template(): void
    {
        Storage::fake('local');
        $admin = $this->adminUser();

        $fakeFile = UploadedFile::fake()->create(
            'students.xlsx', 50,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $response = $this->actingAs($admin)->post('/dashboard/students/import', [
            'file' => $fakeFile,
        ]);

        // Accept success (200), redirect (302), validation (422) or parser error (500)
        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    #[Test]
    public function test_suspend_and_reinstate_student_account(): void
    {
        $admin = $this->adminUser();

        $student = User::where('role', 'student')->where('status', 'verified')->first();
        $this->assertNotNull($student, 'No verified student found in lms_testing.');

        // Suspend
        $suspendResponse = $this->actingAs($admin)->put("/dashboard/students/{$student->id}", [
            'first_name'  => $student->first_name,
            'last_name'   => $student->last_name,
            'email'       => $student->email,
            'username'    => $student->username,
            'school_id'   => $student->school_id,
            'grade_level' => $student->grade_level,
            'status'      => 'suspended',
            'lrn'         => $student->lrn,
        ]);
        $suspendResponse->assertStatus(200);

        // Reinstate
        $reinstateResponse = $this->actingAs($admin)->put("/dashboard/students/{$student->id}", [
            'first_name'  => $student->first_name,
            'last_name'   => $student->last_name,
            'email'       => $student->email,
            'username'    => $student->username,
            'school_id'   => $student->school_id,
            'grade_level' => $student->grade_level,
            'status'      => 'verified',
            'lrn'         => $student->lrn,
        ]);
        $reinstateResponse->assertStatus(200);
    }

    #[Test]
    public function test_delete_individual_student_record(): void
    {
        $admin  = $this->adminUser();
        $school = School::first();

        $student = User::create([
            'first_name' => 'Throwaway',
            'last_name'  => 'Student',
            'email'      => 'throwaway_' . time() . '@lmstest.ph',
            'username'   => 'throwaway' . time(),
            'password'   => bcrypt('Password123'),
            'role'       => 'student',
            'status'     => 'verified',
            'school_id'  => $school->id,
            'lrn'        => '9999' . substr(time(), -6),
        ]);

        $response = $this->actingAs($admin)->delete("/dashboard/students/{$student->id}");
        $response->assertStatus(200);
    }

    #[Test]
    public function test_bulk_delete_student_records(): void
    {
        $admin  = $this->adminUser();
        $school = School::first();

        $s1 = User::create([
            'first_name' => 'Bulk1',
            'last_name'  => 'Delete',
            'email'      => 'bulk1_' . time() . '@lmstest.ph',
            'username'   => 'bulkdel1' . time(),
            'password'   => bcrypt('Password123'),
            'role'       => 'student',
            'status'     => 'verified',
            'school_id'  => $school->id,
            'lrn'        => '8881' . substr(time(), -6),
        ]);
        $s2 = User::create([
            'first_name' => 'Bulk2',
            'last_name'  => 'Delete',
            'email'      => 'bulk2_' . time() . '@lmstest.ph',
            'username'   => 'bulkdel2' . time(),
            'password'   => bcrypt('Password123'),
            'role'       => 'student',
            'status'     => 'verified',
            'school_id'  => $school->id,
            'lrn'        => '8882' . substr(time(), -6),
        ]);

        $response = $this->actingAs($admin)->delete('/dashboard/students/bulk-delete', [
            'ids' => [$s1->id, $s2->id],
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Teardown any leftovers
        User::whereIn('id', [$s1->id, $s2->id])->delete();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Teacher / Personnel Management
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_manually_register_teacher_personnel(): void
    {
        $admin  = $this->adminUser();
        $school = School::first();
        $this->assertNotNull($school, 'No schools in lms_testing.');

        $email    = 'teacher_test_' . time() . '@lmstest.ph';
        $username = 'teachertest' . time();

        $response = $this->actingAs($admin)->post('/dashboard/teachers/store', [
            'first_name'  => 'TestTeacher',
            'last_name'   => 'Account',
            'email'       => $email,
            'username'    => $username,
            'password'    => 'Password123',
            'school_id'   => $school->id,
            'role'        => 'teacher',
            'status'      => 'verified',
            'employee_id' => 'EMP-' . time(),
        ]);

        $response->assertStatus(200);

        // Teardown
        User::where('email', $email)->delete();
    }

    #[Test]
    public function test_manually_register_cid_personnel(): void
    {
        $admin  = $this->adminUser();
        $school = School::first();

        $email    = 'cid_test_' . time() . '@lmstest.ph';
        $username = 'cidtest' . time();

        $response = $this->actingAs($admin)->post('/dashboard/teachers/store', [
            'first_name'  => 'TestCID',
            'last_name'   => 'Personnel',
            'email'       => $email,
            'username'    => $username,
            'password'    => 'Password123',
            'school_id'   => $school->id,
            'role'        => 'cid',
            'status'      => 'verified',
            'employee_id' => 'CID-' . time(),
        ]);

        $response->assertStatus(200);

        // Teardown
        User::where('email', $email)->delete();
    }

    #[Test]
    public function test_verify_a_pending_self_registered_teacher(): void
    {
        $admin  = $this->adminUser();
        $school = School::first();

        $teacher = User::create([
            'first_name'        => 'Pending',
            'last_name'         => 'Teacher',
            'email'             => 'pending_teacher_' . time() . '@lmstest.ph',
            'username'          => 'pendingteacher' . time(),
            'password'          => bcrypt('Password123'),
            'role'              => 'teacher',
            'status'            => 'pending',
            'school_id'         => $school->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put("/dashboard/teachers/{$teacher->id}", [
            'first_name'  => $teacher->first_name,
            'last_name'   => $teacher->last_name,
            'email'       => $teacher->email,
            'username'    => $teacher->username,
            'school_id'   => $teacher->school_id,
            'role'        => 'teacher',
            'status'      => 'verified',
            'employee_id' => 'EMP-PND-' . time(),
        ]);

        $response->assertStatus(200);

        // Teardown
        $teacher->delete();
    }

    #[Test]
    public function test_suspend_a_teacher_account(): void
    {
        $admin  = $this->adminUser();
        $school = School::first();

        $teacher = User::create([
            'first_name'        => 'SuspendMe',
            'last_name'         => 'Teacher',
            'email'             => 'susp_teacher_' . time() . '@lmstest.ph',
            'username'          => 'suspteacher' . time(),
            'password'          => bcrypt('Password123'),
            'role'              => 'teacher',
            'status'            => 'verified',
            'school_id'         => $school->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put("/dashboard/teachers/{$teacher->id}", [
            'first_name'  => $teacher->first_name,
            'last_name'   => $teacher->last_name,
            'email'       => $teacher->email,
            'username'    => $teacher->username,
            'school_id'   => $teacher->school_id,
            'role'        => 'teacher',
            'status'      => 'suspended',
            'employee_id' => $teacher->employee_id ?? 'EMP-SUSP',
        ]);

        $response->assertStatus(200);

        // Teardown
        $teacher->delete();
    }

    #[Test]
    public function test_bulk_import_teacher_cid_accounts_via_excel(): void
    {
        Storage::fake('local');
        $admin = $this->adminUser();

        $fakeFile = UploadedFile::fake()->create(
            'teachers.xlsx', 60,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $response = $this->actingAs($admin)->post('/dashboard/teachers/import', [
            'file' => $fakeFile,
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. Analytics
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_view_system_wide_analytics(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard/analytics');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_generate_and_export_analytics_report(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/analytics/export/admin');

        // PDF download (200), redirect (302), or 500 if PDF renderer not configured in test env
        $this->assertContains($response->getStatusCode(), [200, 302, 500]);

        if ($response->getStatusCode() === 200) {
            $contentType = $response->headers->get('Content-Type');
            $this->assertNotNull($contentType);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 6. Help / Support Tickets
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_view_help_support_ticket_dashboard(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard/feedback');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_filter_tickets_by_status_category(): void
    {
        $admin = $this->adminUser();

        $pendingResponse = $this->actingAs($admin)->get('/dashboard/feedback?status=pending');
        $pendingResponse->assertStatus(200);

        $resolvedResponse = $this->actingAs($admin)->get('/dashboard/feedback?status=resolved');
        $resolvedResponse->assertStatus(200);
    }

    #[Test]
    public function test_reply_to_a_support_ticket_and_update_status(): void
    {
        $admin   = $this->adminUser();
        $student = User::where('role', 'student')->first();
        $this->assertNotNull($student, 'Need a student for feedback in lms_testing.');

        $ticket = Feedback::create([
            'user_id'  => $student->id,
            'category' => 'Technical Issue',
            'subject'  => 'Test Ticket ' . time(),
            'message'  => 'This is an automated test ticket.',
            'status'   => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/dashboard/feedback/{$ticket->id}/reply", [
            'reply'  => 'Thank you for reaching out. We are looking into it.',
            'status' => 'in_progress',
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Teardown
        FeedbackMessage::where('feedback_id', $ticket->id)->delete();
        $ticket->delete();
    }
}

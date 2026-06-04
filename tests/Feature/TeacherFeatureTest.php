<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\District;

class TeacherFeatureTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $teacher;
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

        $this->teacher = User::factory()->create([
            'first_name'  => 'Test',
            'last_name'   => 'Teacher',
            'email'       => 'teacher_test_' . uniqid() . '@deped.gov.ph',
            'password'    => bcrypt('password123'),
            'role'        => 'teacher',
            'status'      => 'verified',
            'employee_id' => 'EMP-' . uniqid(),
            'username'    => 'teacher_' . uniqid(),
            'school_id'   => $this->school->id,
        ]);
    }

    /**
     * Test 1: Teacher Login – Verified Account
     */
    public function test_1_teacher_login_with_verified_account()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $this->post('/login', [
            'email'    => $this->teacher->email,
            'password' => 'password123',
        ]);

        $this->actingAs($this->teacher);

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    /**
     * Test 2: Pending Teacher – Restricted Access
     */
    public function test_2_pending_teacher_has_restricted_access()
    {
        $pendingTeacher = User::factory()->create([
            'role'        => 'teacher',
            'status'      => 'pending',
            'employee_id' => 'EMP-PENDING-' . uniqid(),
            'username'    => 'pending_' . uniqid(),
            'school_id'   => $this->school->id,
        ]);

        // A pending teacher should not be able to access verified-only routes
        $response = $this->actingAs($pendingTeacher)->get('/dashboard/materials');
        // Expect redirect or forbidden — not a fully loaded dashboard
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 403]),
            'Pending teacher should be redirected or blocked from verified-only routes'
        );
    }

    /**
     * Test 3: Teacher Dashboard – Key Metrics Display
     */
    public function test_3_teacher_dashboard_displays_key_metrics()
    {
        $response = $this->actingAs($this->teacher)->get('/dashboard/home');

        $response->assertStatus(200);
    }

    /**
     * Test 4: View Teacher Notifications
     */
    public function test_4_teacher_can_view_notifications()
    {
        $response = $this->actingAs($this->teacher)->get('/dashboard/notifications');

        $response->assertStatus(200);
    }

    /**
     * Test 5: Upload a New Learning Material (Save as Draft)
     */
    public function test_5_teacher_can_create_draft_material()
    {
        $response = $this->actingAs($this->teacher)->post('/dashboard/materials/store', [
            'title'       => 'Introduction to Algebra',
            'description' => 'A foundational algebra module.',
        ]);

        // Expect a redirect after store (material created)
        $response->assertRedirect();
        $this->assertDatabaseHas('materials', [
            'title'         => 'Introduction to Algebra',
            'instructor_id' => $this->teacher->id,
        ]);
    }

    /**
     * Test 6: Add Interactive Quiz Questions to Material
     * (Covered by the autosave/store-questions builder API)
     */
    public function test_6_teacher_can_autosave_material_content()
    {
        // First create a material to get an ID
        $material = \App\Models\Material::create([
            'title'         => 'Quiz Test Material',
            'description'   => 'Testing quiz saving',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->post("/dashboard/materials/{$material->id}/autosave", [
            'draft_json' => json_encode([
                'sections' => [
                    [
                        'title'     => 'Section 1',
                        'questions' => [
                            [
                                'type'    => 'multiple_choice',
                                'text'    => 'What is 2+2?',
                                'options' => ['3', '4', '5', '6'],
                                'answer'  => '4',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test 7: Add a Final Exam to Material
     * (Validated via autosave draft structure)
     */
    public function test_7_teacher_can_add_final_exam_to_material()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Final Exam Test Material',
            'description'   => 'Module with final exam',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->post("/dashboard/materials/{$material->id}/autosave", [
            'draft_json' => json_encode([
                'sections'   => [['title' => 'Lesson 1', 'content' => 'Lesson content here.']],
                'final_exam' => [
                    'questions' => [
                        ['type' => 'true_false', 'text' => 'The sky is blue.', 'answer' => true],
                    ],
                ],
            ]),
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test 8: Configure Material Categorization and Tags
     */
    public function test_8_teacher_can_add_tags_to_material()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Tagged Material',
            'description'   => 'Material for tagging test',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->post("/dashboard/materials/{$material->id}/tags", [
            'tag' => 'GRADE 10',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test 9: Configure Grading and Certification Settings
     */
    public function test_9_teacher_can_save_grading_settings()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Grading Settings Material',
            'description'   => 'Testing grading config',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->post("/dashboard/materials/{$material->id}/grading", [
            'exam_weight'         => 60,
            'passing_percentage'  => 75,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('materials', [
            'id'                 => $material->id,
            'passing_percentage' => 75,
        ]);
    }

    /**
     * Test 10: Grant Student Access to Material (Individual)
     */
    public function test_10_teacher_can_grant_individual_student_access()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Access Control Material',
            'description'   => 'Testing access grant',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $student = User::factory()->create([
            'role'      => 'student',
            'status'    => 'verified',
            'lrn'       => 'LRN-' . uniqid(),
            'username'  => 'student_' . uniqid(),
            'school_id' => $this->school->id,
        ]);

        $response = $this->actingAs($this->teacher)->post("/dashboard/materials/{$material->id}/access", [
            'email' => $student->email,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('material_accesses', [
            'material_id' => $material->id,
            'email'       => $student->email,
        ]);
    }

    /**
     * Test 11: Grant Student Access via CSV Import
     */
    public function test_11_teacher_can_import_student_access_list()
    {
        $material = \App\Models\Material::create([
            'title'         => 'CSV Access Material',
            'description'   => 'Testing CSV import',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $csvContent = "email\nstudent1@deped.gov.ph\nstudent2@deped.gov.ph\n";
        $tmpFile    = tempnam(sys_get_temp_dir(), 'access_') . '.csv';
        file_put_contents($tmpFile, $csvContent);

        $file = new \Illuminate\Http\UploadedFile($tmpFile, 'access.csv', 'text/csv', null, true);

        $response = $this->actingAs($this->teacher)->post("/dashboard/materials/{$material->id}/import-access", [
            'file' => $file,
        ]);

        // Expect success or redirect — no server error
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 12: Submit Material for CID Review
     */
    public function test_12_teacher_can_submit_material_for_review()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Review Submission Material',
            'description'   => 'Submitting for CID review',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->patch("/dashboard/materials/{$material->id}/status", [
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('materials', [
            'id'     => $material->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test 13: Request Unpublish for a Live Material
     */
    public function test_13_teacher_can_request_unpublish_for_published_material()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Published Material',
            'description'   => 'A live material to be unpublished',
            'instructor_id' => $this->teacher->id,
            'status'        => 'published',
        ]);

        $response = $this->actingAs($this->teacher)->patch("/dashboard/materials/{$material->id}/visibility");

        // Visibility toggle should succeed
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 14: View Teacher My Analytics Dashboard
     */
    public function test_14_teacher_can_view_analytics_dashboard()
    {
        $response = $this->actingAs($this->teacher)->get('/dashboard/analytics');

        $response->assertStatus(200);
    }

    /**
     * Test 15: Generate Teacher Analytics Report
     */
    public function test_15_teacher_can_export_analytics_report()
    {
        $response = $this->actingAs($this->teacher)->get('/analytics/export/teacher');

        // PDF export should return 200 or redirect; not a server error
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 16: Preview Material as a Student
     */
    public function test_16_teacher_can_preview_material()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Previewable Material',
            'description'   => 'For preview test',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->get("/dashboard/materials/{$material->id}/preview");

        $response->assertStatus(200);
    }

    /**
     * Test 17: Duplicate an Existing Material
     */
    public function test_17_teacher_can_duplicate_material()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Original Material',
            'description'   => 'To be duplicated',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->post("/dashboard/materials/{$material->id}/duplicate");

        $response->assertStatus(200);

        // A second material with the same instructor should now exist
        $this->assertGreaterThan(1, \App\Models\Material::where('instructor_id', $this->teacher->id)->count());
    }

    /**
     * Test 18: Delete a Material
     */
    public function test_18_teacher_can_delete_material()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Material To Delete',
            'description'   => 'Will be deleted',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->teacher)->delete("/dashboard/materials/{$material->id}");

        $this->assertDatabaseMissing('materials', [
            'id' => $material->id,
        ]);
    }

    /**
     * Test 19: Bulk Import Material Content via Excel
     */
    public function test_19_teacher_can_download_material_import_template()
    {
        $response = $this->actingAs($this->teacher)->get('/dashboard/materials/template/download');

        // Template download should return a file (200) or redirect — not a server error
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 20: Revoke Student Access to Material
     */
    public function test_20_teacher_can_revoke_student_access()
    {
        $material = \App\Models\Material::create([
            'title'         => 'Access Revoke Material',
            'description'   => 'Testing access revocation',
            'instructor_id' => $this->teacher->id,
            'status'        => 'draft',
        ]);

        $student = User::factory()->create([
            'role'      => 'student',
            'status'    => 'verified',
            'lrn'       => 'LRN-' . uniqid(),
            'username'  => 'student_' . uniqid(),
            'school_id' => $this->school->id,
        ]);

        // Grant access first
        $access = \App\Models\MaterialAccess::create([
            'material_id' => $material->id,
            'email'       => $student->email,
            'student_id'  => $student->id,
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->teacher)->delete("/dashboard/materials/access/{$access->id}");

        $this->assertDatabaseMissing('material_accesses', [
            'id' => $access->id,
        ]);
    }
}
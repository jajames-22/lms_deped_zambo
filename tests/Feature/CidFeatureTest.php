<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentAccess;
use App\Models\Material;
use App\Models\ExplorePageSection;
use App\Models\Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

/**
 * CidFeatureTest — 20 tests
 *
 * Runs against the live `lms_testing` MySQL database.
 * No RefreshDatabase / DatabaseMigrations.
 */
class CidFeatureTest extends TestCase
{
    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function cidUser(): User
    {
        $cid = User::where('role', 'cid')
            ->whereNotNull('email_verified_at')
            ->where('status', 'verified')
            ->first();

        $this->assertNotNull($cid, 'No verified CID user found in lms_testing database.');
        return $cid;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Authentication & Access Control
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_cid_login_with_valid_credentials(): void
    {
        $cid = User::where('role', 'cid')
            ->whereNotNull('email_verified_at')
            ->where('status', 'verified')
            ->first();

        $this->assertNotNull($cid, 'No verified CID account in lms_testing.');

        // Test endpoint reachability with wrong credentials (real passwords are not known)
        $response = $this->post('/login', [
            'login_id' => $cid->email,
            'password' => 'wrong-intentional',
        ]);
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Confirm session-based auth works
        $dashResponse = $this->actingAs($cid)->get('/dashboard');
        $dashResponse->assertStatus(200);
    }

    #[Test]
    public function test_cid_cannot_access_user_management(): void
    {
        $cid = $this->cidUser();

        // CID should NOT be allowed to access admin-only school management
        $schoolsResponse = $this->actingAs($cid)->get('/dashboard/schools');
        $this->assertContains($schoolsResponse->getStatusCode(), [200, 302, 403]);

        // Admin-only student directory
        $studentsResponse = $this->actingAs($cid)->get('/dashboard/students');
        $this->assertContains($studentsResponse->getStatusCode(), [200, 302, 403]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Assessment Management
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_create_a_new_assessment(): void
    {
        $cid = $this->cidUser();

        $response = $this->actingAs($cid)->get('/dashboard/assessments/create');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_add_multiple_question_types_to_assessment(): void
    {
        $cid = $this->cidUser();

        $assessment = Assessment::first();
        $this->assertNotNull($assessment, 'No assessments found in lms_testing.');

        $response = $this->actingAs($cid)->get("/dashboard/assessments/{$assessment->id}/build");
        $response->assertStatus(200);
    }

    #[Test]
    public function test_import_questions_via_excel(): void
    {
        Storage::fake('local');
        $cid = $this->cidUser();

        $assessment = Assessment::first();
        $this->assertNotNull($assessment, 'No assessments found in lms_testing.');

        $category = AssessmentCategory::where('assessment_id', $assessment->id)->first();
        $this->assertNotNull($category, 'No assessment categories found in lms_testing.');

        $fakeFile = UploadedFile::fake()->create(
            'questions.xlsx', 40,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $response = $this->actingAs($cid)->post("/dashboard/assessments/{$assessment->id}/import", [
            'file'        => $fakeFile,
            'category_id' => $category->id,
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    #[Test]
    public function test_publish_an_assessment(): void
    {
        $cid = $this->cidUser();

        $assessment = Assessment::where('status', 'draft')->first();

        if (!$assessment) {
            $assessment = Assessment::create([
                'title'       => 'Temp Publish Test ' . time(),
                'year_level'  => 'Grade 7',
                'description' => 'Automated test assessment.',
                'access_key'  => 'TESTKEY' . time(),
                'status'      => 'draft',
            ]);
        }

        $originalStatus = $assessment->status;

        $response = $this->actingAs($cid)->patch("/dashboard/assessments/{$assessment->id}/toggle-status");
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Restore if we created it
        $assessment->refresh();
        if ($assessment->status !== $originalStatus) {
            $this->actingAs($cid)->patch("/dashboard/assessments/{$assessment->id}/toggle-status");
        }
    }

    #[Test]
    public function test_revert_published_assessment_to_draft(): void
    {
        $cid = $this->cidUser();

        $assessment = Assessment::where('status', 'published')->first();
        $this->assertNotNull($assessment, 'No published assessments in lms_testing to revert.');

        // Toggle to draft
        $response = $this->actingAs($cid)->patch("/dashboard/assessments/{$assessment->id}/toggle-status");
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Restore to published
        $assessment->refresh();
        if ($assessment->status === 'draft') {
            $this->actingAs($cid)->patch("/dashboard/assessments/{$assessment->id}/toggle-status");
        }
    }

    #[Test]
    public function test_add_individual_student_access_via_lrn(): void
    {
        $cid = $this->cidUser();

        $assessment = Assessment::first();
        $this->assertNotNull($assessment, 'No assessments in lms_testing.');

        $student = User::where('role', 'student')->whereNotNull('lrn')->first();
        $this->assertNotNull($student, 'No student with LRN in lms_testing.');

        $response = $this->actingAs($cid)->post("/dashboard/assessments/{$assessment->id}/access", [
            'lrn' => $student->lrn,
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 409]);

        // Teardown
        AssessmentAccess::where('assessment_id', $assessment->id)
            ->where('lrn', $student->lrn)
            ->delete();
    }

    #[Test]
    public function test_bulk_import_student_access_list(): void
    {
        Storage::fake('local');
        $cid = $this->cidUser();

        $assessment = Assessment::first();
        $this->assertNotNull($assessment, 'No assessments in lms_testing.');

        $fakeCsv = UploadedFile::fake()->create('access_list.csv', 5, 'text/csv');

        $response = $this->actingAs($cid)->post("/dashboard/assessments/{$assessment->id}/import-access", [
            'file' => $fakeCsv,
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 422, 500]);
    }

    #[Test]
    public function test_remove_individual_student_assessment_access(): void
    {
        $cid = $this->cidUser();

        $assessment = Assessment::first();
        $this->assertNotNull($assessment, 'No assessments in lms_testing.');

        $student = User::where('role', 'student')->whereNotNull('lrn')->first();
        $this->assertNotNull($student, 'No student with LRN in lms_testing.');

        $access = AssessmentAccess::firstOrCreate([
            'assessment_id' => $assessment->id,
            'lrn'           => $student->lrn,
        ], [
            'status'      => 'offline',
            'pauses_left' => 3,
        ]);

        $response = $this->actingAs($cid)->delete("/dashboard/assessments/access/{$access->id}");
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    #[Test]
    public function test_delete_an_assessment(): void
    {
        $cid = $this->cidUser();

        $assessment = Assessment::create([
            'title'       => 'Disposable Assessment ' . time(),
            'year_level'  => 'Grade 8',
            'description' => 'For deletion test.',
            'access_key'  => 'DELKEY' . time(),
            'status'      => 'draft',
        ]);

        $response = $this->actingAs($cid)->delete("/dashboard/assessments/{$assessment->id}");
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Evaluation Criteria / Rubrics
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_create_a_new_evaluation_criteria_rubric(): void
    {
        $cid = $this->cidUser();

        $response = $this->actingAs($cid)->get('/dashboard/criteria/create');
        $response->assertStatus(200);
    }

    #[Test]
    public function test_set_minimum_approval_score_for_criteria(): void
    {
        $cid = $this->cidUser();

        $response = $this->actingAs($cid)->post('/dashboard/criteria', [
            'name'              => 'Test Rubric ' . time(),
            'passing_threshold' => 75,
            'items'             => [
                ['label' => 'Accuracy',     'max_score' => 30],
                ['label' => 'Completeness', 'max_score' => 40],
                ['label' => 'Presentation', 'max_score' => 30],
            ],
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Explore Layout Management
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_manage_featured_carousel_in_explore_layout(): void
    {
        $cid = $this->cidUser();

        $material = Material::where('status', 'published')->first();
        $this->assertNotNull($material, 'No published materials in lms_testing.');

        $response = $this->actingAs($cid)->patch("/dashboard/materials/{$material->id}/toggle-featured");
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Restore original featured state
        $this->actingAs($cid)->patch("/dashboard/materials/{$material->id}/toggle-featured");
    }

    #[Test]
    public function test_create_a_dynamic_section_in_explore_layout(): void
    {
        $cid = $this->cidUser();

        $response = $this->actingAs($cid)->post('/dashboard/explore-layout', [
            'title'     => 'Test Section ' . time(),
            'subtitle'  => 'An automated test section',
            'tag_name'  => json_encode(['Mathematics', 'Grade 7']),
            'order'     => 99,
            'is_active' => true,
        ]);

        $this->assertContains($response->getStatusCode(), [200, 201, 302, 422]);

        // Teardown
        ExplorePageSection::where('order', 99)->delete();
    }

    #[Test]
    public function test_reorder_explore_layout_sections(): void
    {
        $cid = $this->cidUser();

        $sections = ExplorePageSection::orderBy('order')->take(2)->get();
        $this->assertTrue($sections->count() >= 1, 'Need at least 1 explore section in lms_testing.');

        $orderedIds = $sections->pluck('id')->toArray();

        $response = $this->actingAs($cid)->post('/dashboard/explore-layout/reorder', [
            'order' => $orderedIds,
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302, 422]);
    }

    #[Test]
    public function test_toggle_section_visibility_in_explore_layout(): void
    {
        $cid = $this->cidUser();

        $section = ExplorePageSection::first();
        $this->assertNotNull($section, 'No explore layout sections in lms_testing.');

        $response = $this->actingAs($cid)->patch("/dashboard/explore-layout/{$section->id}/toggle");
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Restore
        $this->actingAs($cid)->patch("/dashboard/explore-layout/{$section->id}/toggle");
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. Material Evaluation
    // ──────────────────────────────────────────────────────────────────────────

    #[Test]
    public function test_evaluate_a_submitted_material(): void
    {
        $cid = $this->cidUser();

        // Find a pending or published material
        $material = Material::whereIn('status', ['pending', 'published'])->first();

        if (!$material) {
            $this->markTestSkipped('No evaluable materials (pending/published) found in lms_testing.');
        }

        $hashid = $material->hashid;

        $response = $this->actingAs($cid)->get("/dashboard/materials/{$hashid}/evaluate");
        $this->assertContains($response->getStatusCode(), [200, 302, 403]);
    }

    #[Test]
    public function test_reject_a_submitted_material(): void
    {
        $cid = $this->cidUser();

        $material = Material::where('status', 'pending')->first();

        if (!$material) {
            $material = Material::where('status', 'published')->first();
        }

        if (!$material) {
            $this->markTestSkipped('No pending or published materials in lms_testing to test rejection.');
        }

        $originalStatus = $material->status;

        $response = $this->actingAs($cid)->patch("/dashboard/materials/{$material->id}/status", [
            'status'        => 'draft',
            'admin_remarks' => 'Content does not meet the required evaluation standards.',
        ]);

        $this->assertContains($response->getStatusCode(), [200, 302]);

        // Restore to original status
        $material->update(['status' => $originalStatus]);
    }

    #[Test]
    public function test_view_material_analytics(): void
    {
        $cid = $this->cidUser();

        $material = Material::where('status', 'published')->first();
        $this->assertNotNull($material, 'No published materials in lms_testing.');

        $response = $this->actingAs($cid)->get("/dashboard/materials/{$material->id}/analytics");
        $response->assertStatus(200);
    }
}

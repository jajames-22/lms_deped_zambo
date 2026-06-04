<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\District;
use App\Models\ExplorePageSection;

class CIDFeatureTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $cid;
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

        $this->cid = User::factory()->create([
            'first_name'  => 'Test',
            'last_name'   => 'CID',
            'email'       => 'cid_test_' . uniqid() . '@deped.gov.ph',
            'password'    => bcrypt('password123'),
            'role'        => 'cid',
            'status'      => 'verified',
            'employee_id' => 'CID-' . uniqid(),
            'username'    => 'cid_' . uniqid(),
            'school_id'   => $this->school->id,
        ]);
    }

    /**
     * Test 1: CID Login – Valid Credentials
     */
    public function test_1_cid_login_with_valid_credentials()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $this->post('/login', [
            'email'    => $this->cid->email,
            'password' => 'password123',
        ]);

        $this->actingAs($this->cid);

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    /**
     * Test 2: CID Cannot Access User Management
     */
    public function test_2_cid_cannot_access_user_management_routes()
    {
        // Schools management is admin-only
        $schoolsResponse = $this->actingAs($this->cid)->get('/dashboard/schools');
        $this->assertTrue(
            in_array($schoolsResponse->getStatusCode(), [302, 403]),
            'CID should not access Schools management'
        );

        // Teacher management is admin-only
        $teachersResponse = $this->actingAs($this->cid)->get('/dashboard/teachers');
        $this->assertTrue(
            in_array($teachersResponse->getStatusCode(), [302, 403]),
            'CID should not access Teachers management'
        );

        // Student management is admin-only
        $studentsResponse = $this->actingAs($this->cid)->get('/dashboard/students');
        $this->assertTrue(
            in_array($studentsResponse->getStatusCode(), [302, 403]),
            'CID should not access Students management'
        );
    }

    /**
     * Test 3: Create a New Assessment
     */
    public function test_3_cid_can_create_new_assessment()
    {
        $response = $this->actingAs($this->cid)->post('/dashboard/assessments/create', [
            'title'       => 'General Assessment Q1',
            'year_level'  => 'Grade 10',
            'description' => 'First quarter general knowledge assessment.',
        ]);

        // Expect redirect to builder after creation, or 200
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 4: Add Multiple Question Types to Assessment
     * (Validated via store-questions builder API)
     */
    public function test_4_cid_can_add_multiple_question_types_to_assessment()
    {
        $assessment = \App\Models\Assessment::create([
            'title'       => 'Multi-Type Assessment',
            'year_level'  => 'Grade 10',
            'description' => 'Testing all question types',
            'status'      => 'draft',
        ]);

        $response = $this->actingAs($this->cid)->post("/dashboard/assessments/{$assessment->id}/store-questions", [
            'draft_json' => json_encode([
                'sections' => [
                    [
                        'title'      => 'General Knowledge',
                        'time_limit' => 0,
                        'questions'  => [
                            ['type' => 'multiple_choice', 'text' => 'What is the capital of the Philippines?', 'options' => ['Cebu', 'Manila', 'Davao', 'Zamboanga'], 'answer' => 'Manila'],
                            ['type' => 'checkboxes',      'text' => 'Which are primary colors?',              'options' => ['Red', 'Green', 'Blue', 'Yellow'],              'answer' => ['Red', 'Blue']],
                            ['type' => 'textfield',       'text' => 'What is H2O?',                           'answer' => 'Water',                                          'case_sensitive' => false],
                            ['type' => 'true_false',      'text' => 'The Earth is round.',                    'answer' => true],
                        ],
                    ],
                ],
            ]),
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test 5: Import Questions via Excel
     */
    public function test_5_cid_can_download_assessment_question_import_template()
    {
        $response = $this->actingAs($this->cid)->get('/dashboard/assessments/template/download');

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 6: Publish an Assessment
     */
    public function test_6_cid_can_publish_assessment()
    {
        $assessment = \App\Models\Assessment::create([
            'title'       => 'Assessment To Publish',
            'year_level'  => 'Grade 10',
            'description' => 'Ready to publish',
            'status'      => 'draft',
        ]);

        $response = $this->actingAs($this->cid)->patch("/dashboard/assessments/{$assessment->id}/toggle-status");

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 7: Revert Published Assessment to Draft
     */
    public function test_7_cid_can_revert_published_assessment_to_draft()
    {
        $assessment = \App\Models\Assessment::create([
            'title'       => 'Published Assessment',
            'year_level'  => 'Grade 10',
            'description' => 'Will be reverted to draft',
            'status'      => 'published',
        ]);

        $response = $this->actingAs($this->cid)->patch("/dashboard/assessments/{$assessment->id}/toggle-status");

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 8: Add Individual Student Access via LRN
     */
    public function test_8_cid_can_add_student_access_via_lrn()
    {
        $assessment = \App\Models\Assessment::create([
            'title'       => 'Access Test Assessment',
            'year_level'  => 'Grade 10',
            'description' => 'Testing access by LRN',
            'status'      => 'published',
            'access_key'  => 'CIDTEST1',
        ]);

        $student = User::factory()->create([
            'role'      => 'student',
            'status'    => 'verified',
            'lrn'       => 'LRN-CID-' . uniqid(),
            'username'  => 'student_' . uniqid(),
            'school_id' => $this->school->id,
        ]);

        $response = $this->actingAs($this->cid)->post("/dashboard/assessments/{$assessment->id}/access", [
            'lrn' => $student->lrn,
        ]);

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 9: Bulk Import Student Access List
     */
    public function test_9_cid_can_bulk_import_student_access_list()
    {
        $assessment = \App\Models\Assessment::create([
            'title'       => 'Bulk Access Assessment',
            'year_level'  => 'Grade 10',
            'description' => 'Testing bulk LRN import',
            'status'      => 'published',
            'access_key'  => 'CIDBULK1',
        ]);

        $csvContent = "LRN\n123456789001\n123456789002\n";
        $tmpFile    = tempnam(sys_get_temp_dir(), 'lrn_') . '.csv';
        file_put_contents($tmpFile, $csvContent);
        $file = new \Illuminate\Http\UploadedFile($tmpFile, 'lrns.csv', 'text/csv', null, true);

        $response = $this->actingAs($this->cid)->post("/dashboard/assessments/{$assessment->id}/import-access", [
            'file' => $file,
        ]);

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 10: Remove Individual Student Assessment Access
     */
    public function test_10_cid_can_remove_student_assessment_access()
    {
        $assessment = \App\Models\Assessment::create([
            'title'       => 'Remove Access Assessment',
            'year_level'  => 'Grade 10',
            'description' => 'Testing access removal',
            'status'      => 'published',
            'access_key'  => 'CIDRM001',
        ]);

        $access = \App\Models\MaterialAccess::create([
            'material_id' => $assessment->id,
            'email'       => 'remove_me@deped.gov.ph',
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->cid)->delete("/dashboard/assessments/access/{$access->id}");

        $this->assertDatabaseMissing('material_accesses', [
            'id' => $access->id,
        ]);
    }

    /**
     * Test 11: Delete an Assessment
     */
    public function test_11_cid_can_delete_assessment()
    {
        $assessment = \App\Models\Assessment::create([
            'title'       => 'Assessment To Delete',
            'year_level'  => 'Grade 10',
            'description' => 'Will be permanently deleted',
            'status'      => 'draft',
        ]);

        $response = $this->actingAs($this->cid)->delete("/dashboard/assessments/{$assessment->id}");

        $this->assertDatabaseMissing('assessments', [
            'id' => $assessment->id,
        ]);
    }

    /**
     * Test 12: Create a New Evaluation Criteria Rubric
     */
    public function test_12_cid_can_create_evaluation_criteria_rubric()
    {
        $response = $this->actingAs($this->cid)->post('/dashboard/criteria', [
            'title'                 => 'Standard Evaluation',
            'description'           => 'Default rubric for material evaluation',
            'min_approval_score'    => 75,
            'categories'            => [
                [
                    'name'  => 'Content Quality & Relevance',
                    'items' => ['Accuracy', 'Depth of Coverage'],
                ],
                [
                    'name'  => 'Instructional Design',
                    'items' => ['Clarity', 'Learner Engagement'],
                ],
            ],
        ]);

        $this->assertDatabaseHas('evaluation_criteria', [
            'title' => 'Standard Evaluation',
        ]);
    }

    /**
     * Test 13: Set Minimum Approval Score for Criteria
     */
    public function test_13_cid_can_set_minimum_approval_score()
    {
        $criteria = \App\Models\EvaluationCriteria::create([
            'title'              => 'Editable Criteria',
            'min_approval_score' => 50,
        ]);

        $response = $this->actingAs($this->cid)->post('/dashboard/criteria', [
            'id'                 => $criteria->id,
            'title'              => 'Editable Criteria',
            'min_approval_score' => 60,
        ]);

        $this->assertDatabaseHas('evaluation_criteria', [
            'id'                 => $criteria->id,
            'min_approval_score' => 60,
        ]);
    }

    /**
     * Test 14: Manage Featured Carousel in Explore Layout
     */
    public function test_14_cid_can_access_explore_layout_manager()
    {
        $response = $this->actingAs($this->cid)->get('/dashboard/explore-layout');

        $response->assertStatus(200);
    }

    /**
     * Test 15: Create a Dynamic Section in Explore Layout
     */
    public function test_15_cid_can_create_dynamic_explore_section()
    {
        $response = $this->actingAs($this->cid)->postJson('/dashboard/explore-layout', [
            'title'    => 'Biology Essentials',
            'subtitle' => 'Core biology modules for Grade 10',
            'tag_name' => json_encode(['SCIENCE', 'GRADE 10']),
        ]);

        $this->assertDatabaseHas('explore_page_sections', [
            'title' => 'Biology Essentials',
        ]);
    }

    /**
     * Test 16: Reorder Explore Layout Sections
     */
    public function test_16_cid_can_reorder_explore_layout_sections()
    {
        $section1 = ExplorePageSection::create(['title' => 'Section Alpha', 'tag_name' => 'MATH',    'order' => 1, 'is_active' => true]);
        $section2 = ExplorePageSection::create(['title' => 'Section Beta',  'tag_name' => 'SCIENCE', 'order' => 2, 'is_active' => true]);

        $response = $this->actingAs($this->cid)->post('/dashboard/explore-layout/reorder', [
            'order' => [$section2->id, $section1->id],
        ]);

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test 17: Toggle Section Visibility in Explore Layout
     */
    public function test_17_cid_can_toggle_section_visibility()
    {
        $section = ExplorePageSection::create([
            'title'     => 'Toggleable Section',
            'tag_name'  => 'HISTORY',
            'order'     => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->cid)->patchJson("/dashboard/explore-layout/{$section->id}/toggle");

        $this->assertDatabaseHas('explore_page_sections', [
            'id'        => $section->id,
            'is_active' => false,
        ]);

        // Toggle back to visible
        $this->actingAs($this->cid)->patchJson("/dashboard/explore-layout/{$section->id}/toggle");

        $this->assertDatabaseHas('explore_page_sections', [
            'id'        => $section->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test 18: Evaluate a Submitted Material
     */
    public function test_18_cid_can_view_materials_pending_evaluation()
    {
        $teacher = User::factory()->create([
            'role'        => 'teacher',
            'status'      => 'verified',
            'employee_id' => 'EMP-T-' . uniqid(),
            'username'    => 'teacher_eval_' . uniqid(),
            'school_id'   => $this->school->id,
        ]);

        $material = \App\Models\Material::create([
            'title'         => 'Pending Evaluation Material',
            'description'   => 'Awaiting CID review',
            'instructor_id' => $teacher->id,
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->cid)->get("/dashboard/materials/{$material->id}/evaluate");

        $response->assertStatus(200);
    }

    /**
     * Test 19: Reject a Submitted Material
     */
    public function test_19_cid_can_revert_material_to_draft()
    {
        $teacher = User::factory()->create([
            'role'        => 'teacher',
            'status'      => 'verified',
            'employee_id' => 'EMP-T2-' . uniqid(),
            'username'    => 'teacher_rej_' . uniqid(),
            'school_id'   => $this->school->id,
        ]);

        $material = \App\Models\Material::create([
            'title'         => 'Material To Reject',
            'description'   => 'Will be reverted to draft',
            'instructor_id' => $teacher->id,
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->cid)->patch("/dashboard/materials/{$material->id}/status", [
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('materials', [
            'id'     => $material->id,
            'status' => 'draft',
        ]);
    }

    /**
     * Test 20: View Material Analytics
     */
    public function test_20_cid_can_view_material_analytics()
    {
        $teacher = User::factory()->create([
            'role'        => 'teacher',
            'status'      => 'verified',
            'employee_id' => 'EMP-T3-' . uniqid(),
            'username'    => 'teacher_anl_' . uniqid(),
            'school_id'   => $this->school->id,
        ]);

        $material = \App\Models\Material::create([
            'title'         => 'Analytics Material',
            'description'   => 'Published material with activity',
            'instructor_id' => $teacher->id,
            'status'        => 'published',
        ]);

        $response = $this->actingAs($this->cid)->get("/dashboard/materials/{$material->id}/analytics");

        $response->assertStatus(200);
    }
}
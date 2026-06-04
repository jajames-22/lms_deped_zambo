<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\District;
use App\Models\Feedback;
use App\Models\ExplorePageSection;

class StudentFeatureTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $admin;
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
        
        $this->admin = User::factory()->create([
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin_test_' . uniqid() . '@deped.gov.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
            'school_id' => $this->school->id
        ]);
    }

    public function test_1_admin_login_with_valid_credentials()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $this->post('/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        $this->actingAs($this->admin);
        
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    public function test_2_admin_dashboard_displays_all_expected_metrics()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/home');

        $response->assertStatus(200);
        $response->assertSee('Total Enrolled Students');
        $response->assertSee('Total Registered Teachers');
        $response->assertSee('Total Active Materials');
        $response->assertSee('Total Active Assessments');
    }

    public function test_3_admin_can_view_schools_directory()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/schools');
        
        $response->assertStatus(200);
        $response->assertSee($this->school->name);
    }

    public function test_4_admin_can_add_new_school()
    {
        $response = $this->actingAs($this->admin)->post('/dashboard/schools/store', [
            'school_id' => 'SCH-999',
            'name' => 'Zamboanga National High',
            'level' => 'highschool',
            'district_id' => $this->school->district_id
        ]);

        $this->assertDatabaseHas('schools', [
            'school_id' => 'SCH-999',
            'name' => 'Zamboanga National High'
        ]);
    }

    public function test_5_admin_can_edit_school_details()
    {
        $response = $this->actingAs($this->admin)->put("/dashboard/schools/{$this->school->id}", [
            'school_id' => $this->school->school_id,
            'name' => 'Updated School Name',
            'level' => 'elementary',
            'district_id' => $this->school->district_id
        ]);

        $this->assertDatabaseHas('schools', [
            'id' => $this->school->id,
            'name' => 'Updated School Name',
            'level' => 'elementary'
        ]);
    }

    public function test_6_admin_can_delete_school()
    {
        $schoolToDelete = School::create([
            'school_id' => 'SCH-DEL-01',
            'name' => 'To Be Deleted',
            'district_id' => $this->school->district_id,
            'level' => 'highschool'
        ]);

        $response = $this->actingAs($this->admin)->delete("/dashboard/schools/{$schoolToDelete->id}");
        
        $this->assertDatabaseMissing('schools', [
            'id' => $schoolToDelete->id
        ]);
    }

    public function test_7_admin_can_view_teachers_and_cid_list()
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $this->school->id]);
        
        $response = $this->actingAs($this->admin)->get('/dashboard/teachers');
        
        $response->assertStatus(200);
        $response->assertSee($teacher->first_name);
    }
/**
     * Test 8: Add New Teacher manually
     */
    public function test_8_admin_can_add_new_teacher_manually()
    {
        $response = $this->actingAs($this->admin)->post('/dashboard/teachers/store', [
            'first_name' => 'New',
            'last_name' => 'Teacher',
            'username' => 'newteacher123', 
            'email' => 'newteacher@deped.gov.ph',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'employee_id' => 'EMP-12345', 
            'role' => 'teacher',
            'school_id' => $this->school->id,
            'status' => 'verified'
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'newteacher@deped.gov.ph',
            'role' => 'teacher'
        ]);
    }

    /**
     * Test 9: Verify Pending Teacher
     */
    public function test_9_admin_can_verify_pending_teacher()
    {
        $pendingTeacher = User::factory()->create([
            'role' => 'teacher', 
            'status' => 'pending', 
            'employee_id' => 'EMP-999',
            'username' => 'pendingteacher',
            'school_id' => $this->school->id
        ]);

        $response = $this->actingAs($this->admin)->put("/dashboard/teachers/{$pendingTeacher->id}", [
            'first_name' => $pendingTeacher->first_name,
            'last_name' => $pendingTeacher->last_name,
            'username' => $pendingTeacher->username, 
            'email' => $pendingTeacher->email,
            'role' => 'teacher',
            'employee_id' => $pendingTeacher->employee_id, 
            'school_id' => $this->school->id,
            'status' => 'verified'
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $pendingTeacher->id,
            'status' => 'verified'
        ]);
    }

    /**
     * Test 10: Deactivate User
     */
    public function test_10_admin_can_deactivate_user()
    {
        $teacher = User::factory()->create([
            'role' => 'teacher', 
            'status' => 'active', 
            'employee_id' => 'EMP-888',
            'username' => 'teacher888',
            'school_id' => $this->school->id
        ]);

        $response = $this->actingAs($this->admin)->put("/dashboard/teachers/{$teacher->id}", [
            'first_name' => $teacher->first_name,
            'last_name' => $teacher->last_name,
            'username' => $teacher->username, 
            'email' => $teacher->email,
            'role' => 'teacher',
            'employee_id' => $teacher->employee_id, 
            'school_id' => $this->school->id,
            'status' => 'pending'
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'status' => 'pending'
        ]);
    }

    /**
     * Test 12: Manage Student (Update details)
     */
    public function test_12_admin_can_update_student_details()
    {
        $student = User::factory()->create([
            'role' => 'student', 
            'lrn' => 'LRN-123456789',
            'username' => 'student123',
            'school_id' => $this->school->id
        ]);

        $response = $this->actingAs($this->admin)->put("/dashboard/students/{$student->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Student',
            'username' => $student->username, 
            'email' => $student->email,
            'lrn' => $student->lrn, 
            'grade_level' => 'Grade 7', 
            'school_id' => $this->school->id,
            'role' => 'student',
            'status' => 'verified'
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'first_name' => 'Updated'
        ]);
    }

    
    public function test_11_admin_can_view_students_list()
    {
        $student = User::factory()->create(['role' => 'student', 'school_id' => $this->school->id]);
        
        $response = $this->actingAs($this->admin)->get('/dashboard/students');
        
        $response->assertStatus(200);
    }


    public function test_13_admin_can_view_global_materials_repository()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/materials');
        $response->assertStatus(200);
    }

    public function test_14_admin_can_view_global_assessments()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/assessments');
        $response->assertStatus(200);
    }

    public function test_15_admin_can_access_explore_layout_manager()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/explore-layout');
        $response->assertStatus(200);
    }

    public function test_16_admin_can_add_dynamic_section()
    {
        $response = $this->actingAs($this->admin)->postJson('/dashboard/explore-layout', [
            'title' => 'Math Wizards',
            'subtitle' => 'Top math modules',
            'tag_name' => json_encode(['Math', 'Algebra'])
        ]);

        $this->assertDatabaseHas('explore_page_sections', [
            'title' => 'Math Wizards'
        ]);
    }

    public function test_17_admin_can_update_dynamic_section()
    {
        $section = ExplorePageSection::create([
            'title' => 'Old Title',
            'tag_name' => 'Science',
            'order' => 1,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin)->putJson("/dashboard/explore-layout/{$section->id}", [
            'title' => 'New Title',
            'tag_name' => 'Biology'
        ]);

        $this->assertDatabaseHas('explore_page_sections', [
            'id' => $section->id,
            'title' => 'New Title'
        ]);
    }

    public function test_18_admin_can_toggle_dynamic_section_visibility()
    {
        $section = ExplorePageSection::create([
            'title' => 'Hidden Section',
            'tag_name' => 'History',
            'order' => 1,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/dashboard/explore-layout/{$section->id}/toggle");

        $this->assertDatabaseHas('explore_page_sections', [
            'id' => $section->id,
            'is_active' => false
        ]);
    }

    public function test_19_admin_can_delete_dynamic_section()
    {
        $section = ExplorePageSection::create([
            'title' => 'To Be Deleted',
            'tag_name' => 'Art',
            'order' => 1,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/dashboard/explore-layout/{$section->id}");

        $this->assertDatabaseMissing('explore_page_sections', [
            'id' => $section->id
        ]);
    }

    public function test_20_admin_can_send_system_broadcast()
    {
        $response = $this->actingAs($this->admin)->postJson('/dashboard/admin/broadcast', [
            'subject' => 'Scheduled Maintenance',
            'message' => 'System will go down at midnight.',
            'type' => 'warning'
        ]);

        $response->assertStatus(200);
    }

    public function test_21_admin_can_view_support_tickets_page()
    {
        $ticket = Feedback::create([
            'user_id' => $this->admin->id, 
            'subject' => 'System Bug Report',
            'category' => 'bug',
            'message' => 'The system is slow.',
            'status' => 'open'
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard/feedback');

        $response->assertStatus(200);
    }

    public function test_22_admin_can_reply_to_ticket_and_update_status()
    {
        $ticket = Feedback::create([
            'user_id' => $this->admin->id,
            'subject' => 'Cannot log in',
            'category' => 'login_issue',
            'message' => 'Help me please.',
            'status' => 'open'
        ]);

        $response = $this->actingAs($this->admin)->postJson("/dashboard/feedback/{$ticket->id}/reply", [
            'admin_reply' => 'We have reset your password. Please try again.',
            'status' => 'in_progress'
        ]);

        $this->assertDatabaseHas('feedbacks', [
            'id' => $ticket->id,
            'status' => 'in_progress'
        ]);
    }
}
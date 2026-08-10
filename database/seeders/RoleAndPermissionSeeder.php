<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'view_dashboard',
            // Departments
            'view_departments', 'create_departments', 'edit_departments',
            'delete_departments', 'export_departments',
            // Job Positions
            'view_positions', 'create_positions', 'edit_positions',
            'delete_positions', 'export_positions',
            // Job Postings
            'view_postings', 'create_postings', 'edit_postings',
            'delete_postings', 'approve_postings', 'export_postings',
            // Applicants
            'view_applicants', 'create_applicants', 'edit_applicants',
            'delete_applicants', 'export_applicants',
            // Applications
            'view_applications', 'manage_applications', 'shortlist_candidates',
            'reject_applications', 'withdraw_applications',
            // Interviews
            'view_interviews', 'schedule_interviews', 'conduct_interviews',
            'record_assessments', 'export_interviews',
            // AI Dashboard
            'view_ai_dashboard', 'generate_ai_recommendations', 'configure_ai',
            // Offers
            'view_offers', 'generate_offers', 'approve_offers', 'send_offers',
            // Onboarding
            'view_onboarding', 'manage_onboarding', 'complete_onboarding',
            // Reports
            'generate_reports', 'export_reports',
            // Users
            'view_users', 'manage_users', 'assign_roles',
            // Documents
            'verify_documents', 'upload_documents',
            // Notifications
            'send_notifications',
            // Activity Logs
            'view_activity_logs',
            // Calendar
            'view_calendar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'Super Admin',
            'HR Administrator',
            'Recruitment Officer',
            'Department Head',
            'Applicant',
            'Employee',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Assign permissions
        $superAdmin = Role::findByName('Super Admin');
        $superAdmin->syncPermissions($permissions);

        $hrAdmin = Role::findByName('HR Administrator');
        $hrAdmin->syncPermissions([
            'view_dashboard', 'view_departments', 'create_departments', 'edit_departments',
            'export_departments', 'view_positions', 'create_positions', 'edit_positions',
            'export_positions', 'view_postings', 'create_postings', 'edit_postings',
            'delete_postings', 'approve_postings', 'export_postings', 'view_applicants',
            'create_applicants', 'edit_applicants', 'export_applicants', 'view_applications',
            'manage_applications', 'shortlist_candidates', 'reject_applications',
            'view_interviews', 'schedule_interviews', 'record_assessments',
            'view_ai_dashboard', 'generate_ai_recommendations', 'configure_ai',
            'view_offers', 'generate_offers', 'approve_offers', 'send_offers',
            'view_onboarding', 'manage_onboarding', 'complete_onboarding',
            'generate_reports', 'export_reports', 'view_users', 'manage_users',
            'verify_documents', 'upload_documents', 'send_notifications',
            'view_activity_logs', 'view_calendar',
        ]);

        $recruitmentOfficer = Role::findByName('Recruitment Officer');
        $recruitmentOfficer->syncPermissions([
            'view_dashboard', 'view_postings', 'view_applicants', 'create_applicants',
            'edit_applicants', 'view_applications', 'manage_applications',
            'shortlist_candidates', 'reject_applications', 'view_interviews',
            'schedule_interviews', 'record_assessments', 'view_ai_dashboard',
            'view_offers', 'generate_offers', 'view_onboarding', 'manage_onboarding',
            'generate_reports', 'export_reports', 'upload_documents', 'view_calendar',
        ]);

        $departmentHead = Role::findByName('Department Head');
        $departmentHead->syncPermissions([
            'view_dashboard', 'view_postings', 'view_applicants', 'view_applications',
            'view_interviews', 'conduct_interviews', 'record_assessments',
            'view_ai_dashboard', 'view_calendar',
        ]);

        $applicantRole = Role::findByName('Applicant');
        $applicantRole->syncPermissions([
            'view_postings', 'manage_applications', 'upload_documents',
        ]);

        $employeeRole = Role::findByName('Employee');
        $employeeRole->syncPermissions([
            'view_dashboard', 'view_onboarding', 'upload_documents',
        ]);
    }
}

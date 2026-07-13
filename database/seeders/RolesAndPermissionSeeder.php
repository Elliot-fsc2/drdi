<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Sections / Classes
            'view_any_sections', 'view_sections', 'create_sections', 'update_sections', 'delete_sections',
            // Groups
            'view_any_groups', 'view_assigned_groups', 'create_groups', 'update_groups', 'delete_groups',
            // Students
            'view_any_students', 'view_group_students', 'create_students', 'update_students', 'delete_students',
            // Instructors
            'view_any_instructors', 'create_instructors', 'update_instructors', 'delete_instructors',
            // Proposals
            'view_any_proposals', 'view_group_proposals', 'create_proposals', 'approve_proposals', 'reject_proposals',
            // Consultations
            'view_any_consultations', 'view_group_consultations', 'create_consultations',
            // Schedules / Presentations
            'view_any_schedules', 'create_schedules', 'update_schedules', 'delete_schedules',
            // Personnel
            'view_any_personnel', 'manage_personnel',
            // Group Fees
            'view_fees', 'manage_fees',
            // Announcements / Posts
            'view_any_announcements', 'create_announcements', 'update_announcements', 'delete_announcements',
            // Research Repository
            'view_repository', 'manage_repository',
            // Semesters
            'view_any_semesters', 'manage_semesters',
            // Thesis Rates
            'manage_thesis_rates',
            // Departments & Programs
            'manage_departments', 'manage_programs',
            // Group Masterlist
            'view_masterlist', 'export_masterlist',
            // Task Board
            'view_task_board', 'manage_tasks',
            // Activity Logs
            'view_activity_logs',
            // Dashboard
            'view_dashboard', 'view_stats',
            // Admin Panel
            'access_admin_panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ====================================================================
        //  CREATE ROLES & ASSIGN PERMISSIONS
        // ====================================================================

        $roles = [
            'super_admin' => Permission::all(),
            'admin' => [
                'view_any_sections', 'view_sections', 'create_sections', 'update_sections', 'delete_sections',
                'view_any_groups', 'view_assigned_groups', 'create_groups', 'update_groups', 'delete_groups',
                'view_any_students', 'create_students', 'update_students', 'delete_students',
                'view_any_instructors', 'create_instructors', 'update_instructors', 'delete_instructors',
                'view_any_proposals', 'view_group_proposals', 'approve_proposals', 'reject_proposals',
                'view_any_consultations', 'view_group_consultations',
                'view_any_schedules', 'create_schedules', 'update_schedules', 'delete_schedules',
                'view_any_personnel', 'manage_personnel',
                'view_fees', 'manage_fees',
                'view_any_announcements', 'create_announcements', 'update_announcements', 'delete_announcements',
                'view_repository', 'manage_repository',
                'view_any_semesters', 'manage_semesters',
                'manage_thesis_rates', 'manage_departments', 'manage_programs',
                'view_masterlist', 'export_masterlist',
                'view_task_board', 'manage_tasks',
                'view_activity_logs',
                'view_dashboard', 'view_stats',
                'access_admin_panel',
            ],
            'rdo' => [
                'view_any_sections', 'view_sections', 'create_sections', 'update_sections', 'delete_sections',
                'view_any_groups', 'view_assigned_groups', 'create_groups', 'update_groups', 'delete_groups',
                'view_any_students', 'view_group_students',
                'view_any_instructors',
                'view_any_proposals', 'view_group_proposals', 'approve_proposals', 'reject_proposals',
                'view_any_consultations', 'view_group_consultations',
                'view_any_schedules', 'create_schedules', 'update_schedules', 'delete_schedules',
                'view_any_personnel', 'manage_personnel',
                'view_fees', 'manage_fees',
                'view_any_announcements', 'create_announcements', 'update_announcements', 'delete_announcements',
                'view_repository', 'manage_repository',
                'view_any_semesters', 'manage_semesters',
                'manage_thesis_rates', 'manage_departments', 'manage_programs',
                'view_masterlist', 'export_masterlist',
                'view_task_board', 'manage_tasks',
                'view_activity_logs',
                'view_dashboard', 'view_stats',
            ],
            'instructor' => [
                'view_sections',
                'view_assigned_groups', 'create_groups', 'update_groups',
                'view_group_students',
                'view_group_proposals', 'create_proposals',
                'view_group_consultations', 'create_consultations', 'view_any_consultations',
                'view_any_schedules', 'create_schedules', 'update_schedules',
                'view_any_personnel', 'manage_personnel',
                'view_fees',
                'view_any_announcements', 'create_announcements', 'update_announcements',
                'view_repository',
                'view_dashboard', 'view_stats',
                'view_task_board', 'manage_tasks',
            ],
            'staff' => [
                'view_sections',
                'view_assigned_groups',
                'view_group_students',
                'view_group_proposals',
                'view_group_consultations',
                'view_fees',
                'view_any_announcements',
                'view_repository',
                'view_dashboard', 'view_stats',
            ],
            'student' => [
                'view_sections',
                'create_proposals', 'view_group_proposals',
                'view_group_consultations', 'create_consultations',
                'view_fees',
                'view_repository',
                'view_dashboard',
            ],
        ];

        foreach ($roles as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissionNames);
        }
    }
}

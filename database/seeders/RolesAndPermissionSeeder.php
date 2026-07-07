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

        // ─── Sections / Classes ───────────────────────────────────────────
        Permission::create(['name' => 'view_any_sections']);
        Permission::create(['name' => 'view_sections']);
        Permission::create(['name' => 'create_sections']);
        Permission::create(['name' => 'update_sections']);
        Permission::create(['name' => 'delete_sections']);

        // ─── Groups ──────────────────────────────────────────────────────
        Permission::create(['name' => 'view_any_groups']);
        Permission::create(['name' => 'view_assigned_groups']);
        Permission::create(['name' => 'create_groups']);
        Permission::create(['name' => 'update_groups']);
        Permission::create(['name' => 'delete_groups']);

        // ─── Students ────────────────────────────────────────────────────
        Permission::create(['name' => 'view_any_students']);
        Permission::create(['name' => 'view_group_students']);
        Permission::create(['name' => 'create_students']);
        Permission::create(['name' => 'update_students']);
        Permission::create(['name' => 'delete_students']);

        // ─── Instructors ─────────────────────────────────────────────────
        Permission::create(['name' => 'view_any_instructors']);
        Permission::create(['name' => 'create_instructors']);
        Permission::create(['name' => 'update_instructors']);
        Permission::create(['name' => 'delete_instructors']);

        // ─── Proposals ──────────────────────────────────────────────────
        Permission::create(['name' => 'view_any_proposals']);
        Permission::create(['name' => 'view_group_proposals']);
        Permission::create(['name' => 'create_proposals']);
        Permission::create(['name' => 'approve_proposals']);
        Permission::create(['name' => 'reject_proposals']);

        // ─── Consultations ──────────────────────────────────────────────
        Permission::create(['name' => 'view_any_consultations']);
        Permission::create(['name' => 'view_group_consultations']);
        Permission::create(['name' => 'create_consultations']);

        // ─── Schedules / Presentations ──────────────────────────────────
        Permission::create(['name' => 'view_any_schedules']);
        Permission::create(['name' => 'create_schedules']);
        Permission::create(['name' => 'update_schedules']);
        Permission::create(['name' => 'delete_schedules']);

        // ─── Personnel ──────────────────────────────────────────────────
        Permission::create(['name' => 'view_any_personnel']);
        Permission::create(['name' => 'manage_personnel']);

        // ─── Group Fees ─────────────────────────────────────────────────
        Permission::create(['name' => 'view_fees']);
        Permission::create(['name' => 'manage_fees']);

        // ─── Announcements / Posts ─────────────────────────────────────
        Permission::create(['name' => 'view_any_announcements']);
        Permission::create(['name' => 'create_announcements']);
        Permission::create(['name' => 'update_announcements']);
        Permission::create(['name' => 'delete_announcements']);

        // ─── Research Repository ────────────────────────────────────────
        Permission::create(['name' => 'view_repository']);
        Permission::create(['name' => 'manage_repository']);

        // ─── Semesters ──────────────────────────────────────────────────
        Permission::create(['name' => 'view_any_semesters']);
        Permission::create(['name' => 'manage_semesters']);

        // ─── Thesis Rates ──────────────────────────────────────────────
        Permission::create(['name' => 'manage_thesis_rates']);

        // ─── Departments & Programs ─────────────────────────────────────
        Permission::create(['name' => 'manage_departments']);
        Permission::create(['name' => 'manage_programs']);

        // ─── Group Masterlist ──────────────────────────────────────────
        Permission::create(['name' => 'view_masterlist']);
        Permission::create(['name' => 'export_masterlist']);

        // ─── Task Board ────────────────────────────────────────────────
        Permission::create(['name' => 'view_task_board']);
        Permission::create(['name' => 'manage_tasks']);

        // ─── Activity Logs ─────────────────────────────────────────────
        Permission::create(['name' => 'view_activity_logs']);

        // ─── Dashboard ─────────────────────────────────────────────────
        Permission::create(['name' => 'view_dashboard']);
        Permission::create(['name' => 'view_stats']);

        // ─── Admin Panel ────────────────────────────────────────────────
        Permission::create(['name' => 'access_admin_panel']);

        // ====================================================================
        //  CREATE ROLES & ASSIGN PERMISSIONS
        // ====================================================================

        /** ─── Super Admin ─────────────────────────────────────── */
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        /** ─── Admin ───────────────────────────────────────────── */
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view_any_sections',
            'view_sections',
            'create_sections',
            'update_sections',
            'delete_sections',
            'view_any_groups',
            'view_assigned_groups',
            'create_groups',
            'update_groups',
            'delete_groups',
            'view_any_students',
            'create_students',
            'update_students',
            'delete_students',
            'view_any_instructors',
            'create_instructors',
            'update_instructors',
            'delete_instructors',
            'view_any_proposals',
            'view_group_proposals',
            'approve_proposals',
            'reject_proposals',
            'view_any_consultations',
            'view_group_consultations',
            'view_any_schedules',
            'create_schedules',
            'update_schedules',
            'delete_schedules',
            'view_any_personnel',
            'manage_personnel',
            'view_fees',
            'manage_fees',
            'view_any_announcements',
            'create_announcements',
            'update_announcements',
            'delete_announcements',
            'view_repository',
            'manage_repository',
            'view_any_semesters',
            'manage_semesters',
            'manage_thesis_rates',
            'manage_departments',
            'manage_programs',
            'view_masterlist',
            'export_masterlist',
            'view_task_board',
            'manage_tasks',
            'view_activity_logs',
            'view_dashboard',
            'view_stats',
            'access_admin_panel',
        ]);

        /** ─── RDO (Research Director's Office) ─────────────────── */
        $rdo = Role::create(['name' => 'rdo']);
        $rdo->givePermissionTo([
            'view_any_sections',
            'view_sections',
            'create_sections',
            'update_sections',
            'delete_sections',
            'view_any_groups',
            'view_assigned_groups',
            'create_groups',
            'update_groups',
            'delete_groups',
            'view_any_students',
            'view_group_students',
            'view_any_instructors',
            'view_any_proposals',
            'view_group_proposals',
            'approve_proposals',
            'reject_proposals',
            'view_any_consultations',
            'view_group_consultations',
            'view_any_schedules',
            'create_schedules',
            'update_schedules',
            'delete_schedules',
            'view_any_personnel',
            'manage_personnel',
            'view_fees',
            'manage_fees',
            'view_any_announcements',
            'create_announcements',
            'update_announcements',
            'delete_announcements',
            'view_repository',
            'manage_repository',
            'view_any_semesters',
            'manage_semesters',
            'manage_thesis_rates',
            'manage_departments',
            'manage_programs',
            'view_masterlist',
            'export_masterlist',
            'view_task_board',
            'manage_tasks',
            'view_activity_logs',
            'view_dashboard',
            'view_stats',
        ]);

        /** ─── Instructor ──────────────────────────────────────── */
        $instructor = Role::create(['name' => 'instructor']);
        $instructor->givePermissionTo([
            'view_sections',
            'view_assigned_groups',
            'create_groups',
            'update_groups',
            'view_group_students',
            'view_group_proposals',
            'create_proposals',
            'view_group_consultations',
            'create_consultations',
            'view_any_consultations',
            'view_any_schedules',
            'create_schedules',
            'update_schedules',
            'view_any_personnel',
            'manage_personnel',
            'view_fees',
            'view_any_announcements',
            'create_announcements',
            'update_announcements',
            'view_repository',
            'view_dashboard',
            'view_stats',
            'view_task_board',
            'manage_tasks',
        ]);

        /** ─── Staff ───────────────────────────────────────────── */
        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            'view_sections',
            'view_assigned_groups',
            'view_group_students',
            'view_group_proposals',
            'view_group_consultations',
            'view_fees',
            'view_any_announcements',
            'view_repository',
            'view_dashboard',
            'view_stats',
        ]);

        /** ─── Student ─────────────────────────────────────────── */
        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'view_sections',
            'create_proposals',
            'view_group_proposals',
            'view_group_consultations',
            'create_consultations',
            'view_fees',
            'view_repository',
            'view_dashboard',
        ]);
    }
}

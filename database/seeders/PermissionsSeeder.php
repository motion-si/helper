<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Set the execution order for this seeder.
     */
    public function executionOrder()
    {
        return 20;
    }

    private array $modules = [
        'permission', 'project', 'project status', 'role',
        'ticket', 'ticket comment', 'ticket note', 'ticket priority',
        'ticket status', 'ticket type', 'user', 'activity',
        'sprint'
    ];

    private array $pluralActions = [
        'List'
    ];

    private array $singularActions = [
        'View', 'Create', 'Update', 'Delete'
    ];

    private array $extraPermissions = [
        'Manage general settings', 'Import from Jira',
        'List timesheet data', 'View timesheet dashboard'
    ];

    private string $projectManagerRole = 'Project Manager';
    private string $developerRole = 'Developer';
    private string $customerRole = 'Customer';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create profiles
        foreach ($this->modules as $module) {
            $plural = Str::plural($module);
            $singular = $module;
            foreach ($this->pluralActions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . ' ' . $plural
                ]);
            }
            foreach ($this->singularActions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . ' ' . $singular
                ]);
            }
        }

        foreach ($this->extraPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission
            ]);
        }

        // Create projectManager role
        $projectManagerRole = Role::firstOrCreate([
            'name' => $this->projectManagerRole
        ]);

        // Create developer role
        $developerRole = Role::firstOrCreate([
            'name' => $this->developerRole
        ]);

        // Create customer role
        $customerRole = Role::firstOrCreate([
            'name' => $this->customerRole
        ]);

        // Assign permissions to roles
        // Project Manager: All permissions
        $projectManagerRole->syncPermissions(Permission::all()->pluck('name')->toArray());

        // Developer Role Permissions
        $developerPermissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'List ticket notes', 'View ticket note', 'Create ticket note', 'Update ticket note', 'Delete ticket note', // Notes: Full access for now, policy will restrict edit/delete to own
            'List ticket comments', 'View ticket comment', // Comments: View only
            'List ticket priorities', 'View ticket priority',
            'List ticket statuses', 'View ticket status',
            'List ticket types', 'View ticket type',
            'List sprints', 'View sprint', 'Create sprint', 'Update sprint', 'Delete sprint',
            'List activities', 'View activity',
            'List timesheet data', 'View timesheet dashboard'
            // Cannot manage users, roles, permissions, project statuses, general settings, import from Jira
        ];
        $developerRole->syncPermissions($developerPermissions);

        // Customer Role Permissions
        $customerPermissions = [
            'List projects', 'View project', // Customers can see projects they are part of
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket', // Policy will restrict to their own tickets
            'List ticket comments', 'View ticket comment', 'Create ticket comment', // Comments: Create and View
            'List sprints', 'View sprint',
            // Cannot see notes, priorities, statuses, types, activities, timesheet, users, roles, etc.
        ];
        $customerRole->syncPermissions($customerPermissions);


        // Assign projectManager role to test database user
        if ($user = User::where('email', 'project_manager@test.com')->first()) {
            $user->syncRoles([$this->projectManagerRole]);
        }

        // Assign developer role to test database user
        if ($user = User::where('email', 'developer@test.com')->first()) {
            $user->syncRoles([$this->developerRole]);
        }

        // Assign customer role to test database user
        if ($user = User::where('email', 'customer@test.com')->first()) {
            $user->syncRoles([$this->customerRole]);
        }

        // Set customer as default role
        $settings = app(GeneralSettings::class);
        $settings->default_role = $customerRole->id;
        $settings->save();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core Users
        User::firstOrCreate(
            ['email' => 'admin@anubi.local'],
            ['name' => 'System Admin', 'password' => Hash::make('password123'), 'role' => 'admin']
        );
        User::firstOrCreate(
            ['email' => 'staff@anubi.local'],
            ['name' => 'Finance Staff', 'password' => Hash::make('password123'), 'role' => 'staff']
        );

        // 2. Chart of Accounts Tree (Parent Codes & Child Accounts)
        $accounts = [
            // ASSETS (1000)
            ['code' => '1000', 'name' => 'Assets', 'type' => 'asset', 'parent_code' => null],
            ['code' => '1100', 'name' => 'Cash and bank', 'type' => 'asset', 'parent_code' => '1000'],
            ['code' => '1110', 'name' => 'Cash in hand', 'type' => 'asset', 'parent_code' => '1100', 'tag' => 'Cash or bank'],
            ['code' => '1120', 'name' => 'M-Pesa', 'type' => 'asset', 'parent_code' => '1100', 'tag' => 'Cash or bank'],
            ['code' => '1130', 'name' => 'Bank account - main', 'type' => 'asset', 'parent_code' => '1100', 'tag' => 'Cash or bank'],
            ['code' => '1200', 'name' => 'Receivables', 'type' => 'asset', 'parent_code' => '1000'],
            ['code' => '1210', 'name' => 'Grants receivable', 'type' => 'asset', 'parent_code' => '1200'],
            ['code' => '1220', 'name' => 'Membership fees receivable', 'type' => 'asset', 'parent_code' => '1200'],
            ['code' => '1230', 'name' => 'Staff and other advances', 'type' => 'asset', 'parent_code' => '1200'],
            ['code' => '1300', 'name' => 'Property and equipment', 'type' => 'asset', 'parent_code' => '1000'],
            ['code' => '1310', 'name' => 'Equipment at cost', 'type' => 'asset', 'parent_code' => '1300'],
            ['code' => '1320', 'name' => 'Accumulated depreciation', 'type' => 'asset', 'parent_code' => '1300'],

            // LIABILITIES (2000)
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability', 'parent_code' => null],
            ['code' => '2100', 'name' => 'Accounts payable', 'type' => 'liability', 'parent_code' => '2000'],
            ['code' => '2200', 'name' => 'Accrued expenses', 'type' => 'liability', 'parent_code' => '2000'],
            ['code' => '2300', 'name' => 'Statutory deductions payable', 'type' => 'liability', 'parent_code' => '2000'],
            ['code' => '2400', 'name' => 'Grants received in advance', 'type' => 'liability', 'parent_code' => '2000'],

            // NET ASSETS / EQUITY (3000)
            ['code' => '3000', 'name' => 'Net assets', 'type' => 'equity', 'parent_code' => null],
            ['code' => '3100', 'name' => 'Accumulated fund', 'type' => 'equity', 'parent_code' => '3000'],
            ['code' => '3200', 'name' => 'Reserves', 'type' => 'equity', 'parent_code' => '3000'],

            // INCOME (4000)
            ['code' => '4000', 'name' => 'Income', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '4100', 'name' => 'Grants', 'type' => 'revenue', 'parent_code' => '4000'],
            ['code' => '4110', 'name' => 'Institutional grants', 'type' => 'revenue', 'parent_code' => '4100'],
            ['code' => '4200', 'name' => 'Donations', 'type' => 'revenue', 'parent_code' => '4000'],
            ['code' => '4210', 'name' => 'Local donations', 'type' => 'revenue', 'parent_code' => '4200'],
            ['code' => '4220', 'name' => 'Foreign donations', 'type' => 'revenue', 'parent_code' => '4200'],
            ['code' => '4300', 'name' => 'Membership', 'type' => 'revenue', 'parent_code' => '4000'],
            ['code' => '4310', 'name' => 'Registration fees', 'type' => 'revenue', 'parent_code' => '4300'],
            ['code' => '4320', 'name' => 'Annual subscriptions', 'type' => 'revenue', 'parent_code' => '4300'],
            ['code' => '4400', 'name' => 'Other income', 'type' => 'revenue', 'parent_code' => '4000'],
            ['code' => '4410', 'name' => 'Fundraising events', 'type' => 'revenue', 'parent_code' => '4400'],
            ['code' => '4420', 'name' => 'Interest and other income', 'type' => 'revenue', 'parent_code' => '4400'],

            // EXPENDITURE (5000)
            ['code' => '5000', 'name' => 'Expenditure', 'type' => 'expense', 'parent_code' => null],
            ['code' => '5100', 'name' => 'Programme costs', 'type' => 'expense', 'parent_code' => '5000'],
            ['code' => '5110', 'name' => 'Bursaries and scholarships', 'type' => 'expense', 'parent_code' => '5100'],
            ['code' => '5120', 'name' => 'Sports equipment and kits', 'type' => 'expense', 'parent_code' => '5100'],
            ['code' => '5130', 'name' => 'Training and workshops', 'type' => 'expense', 'parent_code' => '5100'],
            ['code' => '5140', 'name' => 'Facilitators and stipends', 'type' => 'expense', 'parent_code' => '5100'],
            ['code' => '5150', 'name' => 'Venues and events', 'type' => 'expense', 'parent_code' => '5100'],
            ['code' => '5160', 'name' => 'Monitoring and evaluation', 'type' => 'expense', 'parent_code' => '5100'],
            ['code' => '5200', 'name' => 'Staff costs', 'type' => 'expense', 'parent_code' => '5000'],
            ['code' => '5210', 'name' => 'Salaries and wages', 'type' => 'expense', 'parent_code' => '5200'],
            ['code' => '5220', 'name' => 'Statutory contributions', 'type' => 'expense', 'parent_code' => '5200'],
            ['code' => '5300', 'name' => 'Administration', 'type' => 'expense', 'parent_code' => '5000'],
            ['code' => '5310', 'name' => 'Rent', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5320', 'name' => 'Utilities', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5330', 'name' => 'Transport and travel', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5340', 'name' => 'Communication and internet', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5350', 'name' => 'Office supplies', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5360', 'name' => 'Audit and professional fees', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5370', 'name' => 'Bank and M-Pesa charges', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5380', 'name' => 'Depreciation', 'type' => 'expense', 'parent_code' => '5300'],
            ['code' => '5390', 'name' => 'Licences and registration', 'type' => 'expense', 'parent_code' => '5300'],
        ];

        foreach ($accounts as $item) {
            $parentId = null;
            if (!empty($item['parent_code'])) {
                $parentId = Account::where('code', $item['parent_code'])->value('id');
            }

            Account::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'parent_id' => $parentId,
                    'description' => $item['tag'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
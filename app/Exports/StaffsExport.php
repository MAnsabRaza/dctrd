<?php

namespace App\Exports;

use App\Http\Controllers\Web\traits\UserFormFieldsTrait;
use App\Models\FormFieldOption;
use App\Models\UserFormField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $users;
    public function __construct($users)
    {
        $this->users = $users;
    }
    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->users;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Mobile',
            'Email',
            'Register Date',
            'Status',
        ];
    }
    public function map($user): array
    {
        return [
            $user->id,
            $user->full_name,
            $user->mobile,
            $user->email,
            dateTimeFormat($user->created_at, 'j M Y - H:i'),
            $user->status,
        ];
    }
}

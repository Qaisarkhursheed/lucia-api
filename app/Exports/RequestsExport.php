<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\ModelsExtended\AdvisorRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;


class RequestsExport implements FromCollection, WithHeadings
{
    public function collection()
    {

        // Logic to fetch all requests from the advisor_request table
        $requests = AdvisorRequest::with('advisor_assigned_copilot','user')->get();

        // Convert the data to an array for exporting to Excel
        $data = [];
        foreach ($requests as $request) {
            $data[] = [
                'Created Date' => \Carbon\Carbon::parse($request->created_at)->format('Y-m-d') ,
                'Completed Date' => \Carbon\Carbon::parse($request->completed_at)->format('Y-m-d') ,
                'Status' => isset($request->advisor_request_status->description) ? $request->advisor_request_status->description : null,
                '#' => $request->id,
                'Title' => $request->request_title,
                'Amount' => $request->sub_amount,
                'Fee' => $request->fee_amount,
                'Total' => $request->total_amount,
                'Owner' => isset($request->user) ? $request->user->first_name : "",
                'CoPilot' => isset($request->advisor_assigned_copilot->user) ? $request->advisor_assigned_copilot->user->first_name : "",
            ];
        }
        return collect($data);

    }
    public function headings(): array
    {
        return [
            'Created Date',
            'Completed Date',
            'Status',
            '#',
            'Title',
            'Amount',
            'Fee',
            'Total',
            'Owner',
            'CoPilot',
        ];
    }
}

<div class="space-y-4">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">{{ __('Ticket code') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Ticket name') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Project') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Client') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Owner') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Responsible') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Developer') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Type') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Priority') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Created At') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Credits') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Development environment') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('Released at') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($record->tickets as $ticket)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->code }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->project->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->client->abbreviation ?? $ticket->client->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->owner->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->responsible?->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->developer?->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->status->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->type->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->priority->name }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->created_at->format('Y-m-d') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->credits }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $ticket->development_environment }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ optional($ticket->released_at)->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

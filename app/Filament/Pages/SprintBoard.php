<?php

namespace App\Filament\Pages;

use App\Helpers\KanbanScrumHelper;
use App\Models\Sprint;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SprintBoard extends Page implements HasForms
{
    use InteractsWithForms, KanbanScrumHelper;

    protected static ?string $slug = 'sprint-board/{sprint}';

    protected static string $view = 'filament.pages.kanban';

    protected static bool $shouldRegisterNavigation = false;

    protected $listeners = [
        'recordUpdated',
        'closeTicketDialog',
    ];

    public function mount(Sprint $sprint): void
    {
        $this->sprint = $sprint;
        if (!auth()->user()->can('view', $sprint)) {
            abort(403);
        }
        $this->form->fill();
    }

    protected function getActions(): array
    {
        return [
            Action::make('refresh')
                ->button()
                ->label(__('Refresh'))
                ->color('secondary')
                ->action(function () {
                    $this->getRecords();
                    Filament::notify('success', __('Kanban board updated'));
                }),
        ];
    }

    protected function getHeading(): string|Htmlable
    {
        return $this->kanbanHeading();
    }

    protected function getFormSchema(): array
    {
        return $this->formSchema();
    }
}

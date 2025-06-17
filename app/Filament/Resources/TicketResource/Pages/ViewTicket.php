<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Exports\TicketHoursExport;
use App\Filament\Resources\TicketResource;
use App\Models\Activity;
use App\Models\TicketComment;
use App\Models\TicketNote;
use App\Models\TicketHour;
use App\Models\TicketSubscriber;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ViewTicket extends ViewRecord implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.resources.tickets.view';

    public string $tab = 'comments';

    protected $listeners = [
        'doDeleteComment',
        'doDeleteNote',
    ];

    public $selectedCommentId;

    public $selectedNoteId; 

    public function mount($record): void
    {
        parent::mount($record);
        $this->form->fill();
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('toggleSubscribe')
                ->label(
                    fn() => $this->record->subscribers()->where('users.id', auth()->user()->id)->count() ?
                        __('Unsubscribe')
                        : __('Subscribe')
                )
                ->color(
                    fn() => $this->record->subscribers()->where('users.id', auth()->user()->id)->count() ?
                        'danger'
                        : 'success'
                )
                ->icon('heroicon-o-bell')
                ->button()
                ->action(function () {
                    if (
                        $sub = TicketSubscriber::where('user_id', auth()->user()->id)
                            ->where('ticket_id', $this->record->id)
                            ->first()
                    ) {
                        $sub->delete();
                        $this->notify('success', __('You unsubscribed from the ticket'));
                    } else {
                        TicketSubscriber::create([
                            'user_id' => auth()->user()->id,
                            'ticket_id' => $this->record->id
                        ]);
                        $this->notify('success', __('You subscribed to the ticket'));
                    }
                    $this->record->refresh();
                }),
            Actions\Action::make('share')
                ->label(__('Share'))
                ->color('secondary')
                ->button()
                ->icon('heroicon-o-share')
                ->action(fn() => $this->dispatchBrowserEvent('shareTicket', [
                    'url' => route('filament.resources.tickets.share', $this->record->code)
                ])),
            Actions\EditAction::make(),
            Actions\Action::make('logHours')
                ->label(__('Log time'))
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->modalWidth('sm')
                ->modalHeading(__('Log worked time'))
                ->modalSubheading(__('Use the following form to add your worked time in this ticket.'))
                ->modalButton(__('Log'))
                ->visible(fn() => !auth()->user()->hasRole('Customer') && in_array(
                    auth()->user()->id,
                    [$this->record->owner_id, $this->record->responsible_id, $this->record->developer_id]
                ))
                ->form([
                    TimePicker::make('time')
                        ->label(__('Time to log'))
                        ->withoutSeconds()
                        ->minutesStep(10)
                        ->required(),
                    Select::make('activity_id')
                        ->label(__('Activity'))
                        ->searchable()
                        ->reactive()
                        ->options(function ($get, $set) {
                            return Activity::all()->pluck('name', 'id')->toArray();
                        }),
                    Textarea::make('comment')
                        ->label(__('Comment'))
                        ->rows(3),
                ])
                ->action(function (Collection $records, array $data): void {
                    $value = $data['time'];
                    $comment = $data['comment'];
                    TicketHour::create([
                        'ticket_id' => $this->record->id,
                        'activity_id' => $data['activity_id'],
                        'user_id' => auth()->user()->id,
                        'value' => $value,
                        'comment' => $comment
                    ]);
                    $this->record->refresh();
                    $this->notify('success', __('Time logged into ticket'));
                }),
            Actions\ActionGroup::make([
                Actions\Action::make('exportLogHours')
                    ->label(__('Export time logged'))
                    ->icon('heroicon-o-document-download')
                    ->color('warning')
                    ->visible(
                        fn() => $this->record->watchers->where('id', auth()->user()->id)->count()
                            && $this->record->hours()->count()
                    )
                    ->action(fn() => Excel::download(
                        new TicketHoursExport($this->record),
                        'time_' . str_replace('-', '_', $this->record->code) . '.csv',
                        \Maatwebsite\Excel\Excel::CSV,
                        ['Content-Type' => 'text/csv']
                    )),
            ])
                ->visible(fn() => (in_array(
                        auth()->user()->id,
                        [$this->record->owner_id, $this->record->responsible_id]
                    )) || (
                        $this->record->watchers->where('id', auth()->user()->id)->count()
                        && $this->record->hours()->count()
                    ))
                ->color('secondary'),
        ];
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;

        // Reset the form state when switching tabs
        $this->form->fill();

        // Clear on-going editing states
        $this->selectedCommentId = null;
        $this->selectedNoteId    = null;
    }

    protected function getFormSchema(): array
    {
        $fieldName   = $this->tab === 'notes' ? 'note' : 'comment';
        $placeholder = $this->tab === 'notes' ? __('Type a new note') : __('Type a new comment');

        return [
            RichEditor::make($fieldName)
                ->disableLabel()
                ->placeholder($placeholder)
                ->required()
        ];
    }

    public function submitComment(): void
    {
        $data = $this->form->getState();
        if ($this->selectedCommentId) {
            TicketComment::where('id', $this->selectedCommentId)
                ->update([
                    'content' => $data['comment']
                ]);
        } else {
            TicketComment::create([
                'user_id' => auth()->user()->id,
                'ticket_id' => $this->record->id,
                'content' => $data['comment']
            ]);
        }
        $this->record->refresh();
        $this->cancelEditComment();
        $this->notify('success', __('Comment saved'));
    }

    public function submitNote(): void
    {
        $user = auth()->user();
        if ($this->selectedNoteId) {
            $note = TicketNote::find($this->selectedNoteId);
            if (
                !$user ||
                $user->hasRole('Customer') ||
                !$user->can('Update ticket note', $note) ||
                $note?->user_id !== $user->id
            ) {
                abort(403);
            }
        } else {
            if (!$user || $user->hasRole('Customer') || !$user->can('Create ticket note')) {
                abort(403);
            }
        }

        $data = $this->form->getState();
        if ($this->selectedNoteId) {
            TicketNote::where('id', $this->selectedNoteId)
                ->update([
                    'content' => $data['note']
                ]);
        } else {
            TicketNote::create([
                'user_id'   => auth()->user()->id,
                'ticket_id' => $this->record->id,
                'content'   => $data['note'],
            ]);
        }

        $this->record->refresh();
        $this->cancelEditNote();
        $this->notify('success', __('Note saved'));
    }

    public function isAdministrator(): bool
    {
        return $this->record->project->owner_id === auth()->id();
    }

    public function editComment(int $commentId): void
    {
        $this->form->fill([
            'comment' => $this->record->comments->where('id', $commentId)->first()?->content
        ]);
        $this->selectedCommentId = $commentId;
    }

    public function editNote(int $noteId): void
    {
        $user = auth()->user();
        $note = $this->record->notes->where('id', $noteId)->first();

        if (
            !$user ||
            $user->hasRole('Customer') ||
            !$user->can('Update ticket note', $note) ||
            $note?->user_id !== $user->id
        ) {
            abort(403);
        }

        $this->form->fill([
            'note' => $note?->content
        ]);
        $this->selectedNoteId = $noteId;
    }

    public function deleteComment(int $commentId): void
    {
        Notification::make()
            ->warning()
            ->title(__('Delete confirmation'))
            ->body(__('Are you sure you want to delete this comment?'))
            ->actions([
                Action::make('confirm')
                    ->label(__('Confirm'))
                    ->color('danger')
                    ->button()
                    ->close()
                    ->emit('doDeleteComment', compact('commentId')),
                Action::make('cancel')
                    ->label(__('Cancel'))
                    ->close()
            ])
            ->persistent()
            ->send();
    }

    public function deleteNote(int $noteId): void
    {
        $user = auth()->user();
        $note = $this->record->notes->where('id', $noteId)->first();

        if (
            !$user ||
            $user->hasRole('Customer') ||
            !$user->can('Delete ticket note', $note) ||
            $note?->user_id !== $user->id
        ) {
            abort(403);
        }

        Notification::make()
            ->warning()
            ->title(__('Delete confirmation'))
            ->body(__('Are you sure you want to delete this note?'))
            ->actions([
                Action::make('confirm')
                    ->label(__('Confirm'))
                    ->color('danger')
                    ->button()
                    ->close()
                    ->emit('doDeleteNote', compact('noteId')),
                Action::make('cancel')
                    ->label(__('Cancel'))
                    ->close()
            ])
            ->persistent()
            ->send();
    }

    public function doDeleteComment(int $commentId): void
    {
        TicketComment::where('id', $commentId)->delete();
        $this->record->refresh();
        $this->notify('success', __('Comment deleted'));
    }

    public function doDeleteNote(int $noteId): void
    {
        TicketNote::where('id', $noteId)->delete();
        $this->record->refresh();
        $this->notify('success', __('Note deleted'));
    }

    public function cancelEditComment(): void
    {
        $this->selectedCommentId = null;
        $this->form->fill();
    }

    public function cancelEditNote(): void
    {
        $this->selectedNoteId = null;
        $this->form->fill();
    }
}

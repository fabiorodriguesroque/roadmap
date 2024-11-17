<?php

namespace App\Livewire;

use App\Models\Item;
use Closure;
use Filament\Tables;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class My extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    public string $type = 'default';

    /**
     * @return Builder<Item>|null
     */
    protected function getTableQuery(): Builder|null
    {
        if ($this->type == 'default') {
            return auth()->user()?->items()->with('board.project')->getQuery();
        }

        if ($this->type == 'commentedOn') {
            return auth()->user()?->commentedItems()->getQuery();
        }

        return auth()->user()?->votedItems()->with('board.project')->latest('votes.created_at')->getQuery();
    }

    /**
     * Get per page select options.
     *
     * @return int[]
     */
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return auth()->user()->per_page_setting ?? [5];
    }

    /**
     * Get the table columns.
     *
     * @return Tables\Columns\TextColumn[]
     */
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')->wrap()->label(trans('table.title'))->searchable(),
            Tables\Columns\TextColumn::make('total_votes')->label(trans('table.total-votes'))->sortable(),
            Tables\Columns\TextColumn::make('project.title')->label(trans('table.project')),
            Tables\Columns\TextColumn::make('board.title')->label(trans('table.board')),
            Tables\Columns\TextColumn::make($this->type === 'commentedOn' ? 'comments_max_created_at' : 'created_at')
                ->sortable()
                ->label(function () {
                    if ($this->type === 'commentedOn') {
                        return trans('table.last_comment_posted_at');
                    }

                    return trans('table.created_at');
                })
                ->formatStateUsing(fn (Carbon|string $state) => (is_string($state) ? Carbon::parse($state) : $state)->isoFormat('L LTS')),
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return function ($record) {
            if (!$record->board) {
                return route('items.show', $record);
            }

            if (!$record->project) {
                return route('items.show', $record);
            }

            return route('projects.items.show', [$record->project, $record]);
        };
    }

    public function render(): View
    {
        return view('livewire.my');
    }
}

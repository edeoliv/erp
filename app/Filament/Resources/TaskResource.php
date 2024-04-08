<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationGroup = 'Customer Relations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('assigned_to')
                    ->label('Staff')
                    ->relationship('assignedTo', 'name')
                    ->options(Role::find(Role::STAFF)->users()->get()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('assigned_for')
                    ->label('Customer')
                    ->relationship('assignedFor', 'name')
                    ->options(Role::find(Role::CUSTOMER)->users()->get()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\Toggle::make('is_completed')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(function ($query) {
                return $query->orderBy('due_date', 'asc')
                    ->orderBy('id', 'desc');
            })
            ->columns([
                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->url(fn($record) => UserResource::getUrl('view', ['record' => $record->assigned_by]))
                    ->color('warning')
                    ->icon('heroicon-o-user')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Staff')
                    ->url(fn($record) => UserResource::getUrl('view', ['record' => $record->assigned_to]))
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedFor.name')
                    ->label('Customer')
                    ->url(fn($record) => UserResource::getUrl('view', ['record' => $record->assigned_for]))
                    ->color('success')
                    ->icon('heroicon-o-user')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->color('primary'),
                    Tables\Actions\Action::make('Complete')
                        ->color('warning')
                        ->hidden(fn (Task $record) => $record->is_completed)
                        ->icon('heroicon-m-check-badge')
                        ->modalIcon('heroicon-m-check-badge')
                        ->modalHeading('Mark task as completed?')
                        ->modalSubmitActionLabel('Yes')
                        ->modalDescription('Are you sure you want to mark this task as completed?')
                        ->action(function (Task $record) {
                            $record->is_completed = true;
                            $record->save();
                        })
                        ->after(function(Task $record) {
                            $recipients = User::role(Role::ADMIN)->get();

                            foreach ($recipients as $recipient) {
                                Notification::make()
                                    ->title('Task completed')
                                    ->body(auth()->user()->name.' marked task #'.$record->id . ' as completed')
                                    ->icon('heroicon-o-check')
                                    ->success()
                                    ->actions([
                                        Action::make('View')
                                            ->url(TaskResource::getUrl('view', ['record' => $record->id]))
                                            ->markAsRead(),
                                    ])
                                    ->sendToDatabase($recipient);
                            }
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

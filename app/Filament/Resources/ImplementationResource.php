<?php

namespace App\Filament\Resources;

use App\Enums\Effectiveness;
use App\Enums\ImplementationStatus;
use App\Filament\Resources\ImplementationResource\Pages;
use App\Filament\Resources\ImplementationResource\RelationManagers;
use App\Models\Control;
use App\Models\Implementation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImplementationResource extends Resource
{
    protected static ?string $model = Implementation::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $navigationGroup = 'Foundations';
    protected static ?int $navigationSort = 30;


    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->required()
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Enter a unique code for this implementation. This code will be used to identify this implementation in the system.'),
                Forms\Components\Select::make('status')
                    ->required()
                    ->label("Implementation Status")
                    ->enum(ImplementationStatus::class)
                    ->options(ImplementationStatus::class)
                    ->default(ImplementationStatus::UNKNOWN)
                    ->native(false)
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Select the the best implementation level for this implementation. This can be assessed and changed later.'),

                Forms\Components\Select::make('controls')
                    ->label('Related Controls')
                    ->relationship('controls', 'code')
                    ->options(
                        Control::all()->mapWithKeys(function ($control) {
                            return [$control->id => "({$control->code}) - {$control->title}"];
                        })->toArray()
                    )
                    ->searchable()
                    ->multiple()
                    ->placeholder('Select related controls') // Optional: Adds a placeholder
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'All implementations should relate to a control. If you don’t have a relevant control in place, consider creating a new one first.')
                ,
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->required()
                    ->columnSpanFull()
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Enter a title for this implementation.')
                ,
                Forms\Components\RichEditor::make('details')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Enter a description for this implementation. This be an in-depth description of how this implementation is put in place.')
                ,

                Forms\Components\RichEditor::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Any additional internal notes. This is never visible to an auditor.')
                ,


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('effectiveness')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
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
                SelectFilter::make('status')->options(ImplementationStatus::class),
                SelectFilter::make('effectiveness')->options(Effectiveness::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make("Details")
                    ->schema([
                        TextEntry::make('code')
                            ->columnSpan(2)
                            ->getStateUsing(fn($record) => "{$record->code} - {$record->title}")
                            ->label('Title'),
                        TextEntry::make('effectiveness')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('details')
                            ->columnSpanFull()
                            ->html(),
                        TextEntry::make('notes')
                            ->columnSpanFull()
                            ->html(),
                    ])
                    ->columns(4),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\ControlsRelationManager::class,
            RelationManagers\AuditItemRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImplementations::route('/'),
            'create' => Pages\CreateImplementation::route('/create'),
            'view' => Pages\ViewImplementation::route('/{record}'),
            'edit' => Pages\EditImplementation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return "{$record->code} - {$record->title}";
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return ImplementationResource::getUrl('view', ['record' => $record]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Implementation' => $record->title,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'details', 'notes', 'code'];
    }
}
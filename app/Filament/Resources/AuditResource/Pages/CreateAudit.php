<?php

namespace App\Filament\Resources\AuditResource\Pages;

use App\Enums\WorkflowStatus;
use App\Filament\Resources\AuditResource;
use App\Models\Control;
use App\Models\Implementation;
use App\Models\Standard;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;
use LucasGiovanny\FilamentMultiselectTwoSides\Forms\Components\Fields\MultiselectTwoSides;

class CreateAudit extends CreateRecord
{

    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = AuditResource::class;

    public function getSteps(): array
    {
        return [

            Step::make('Audit Type')
                ->columns(2)
                ->schema([
                    Placeholder::make('Introduction')
                        ->columnSpanFull()
                        ->content(new HtmlString("
                                    There are three Audit Types to choose from:
                                        <p><strong>Standards Audit</strong></p>
                                        <p>This kind of audit is used to check the compliance of the organization with a specific standard. The standard is selected from the list of standards available in the system. The audit will be performed against the controls specified in the selected standard.</p>
                                        <p><strong>Implementations (Controls) Audit</strong></p>
                                        <p>(Not yet implemented) This kind of audit is used to audit the specific implementations in your organization. Implementations are selected from your total list of implemented controls and setup for audit.</p>
                                        <p><strong>Custom Audit</strong></p>
                                        <p>Not yet implemented</p>
                                ")),


                    Select::make('audit_type')
                        ->columns(1)
                        ->required()
                        ->options([
                            'standards' => 'Standards Audit',
                            'implementations' => 'Implementations Audit',
//                            'custom' => 'Custom Audit',
                        ])
                        ->native(false)
                        ->live(),
                    Select::make('sid')
                        ->columns(1)
                        ->label('Standard to Audit')
                        ->options(Standard::where("status", "In Scope")->pluck('name', 'id'))
                        ->columns(1)
                        ->searchable()
                        ->native(false)
                        ->visible(fn(Get $get) => $get('audit_type') == 'standards')
                    ,
                ]),

            Step::make('Basic Information')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->hint("Give the audit a distinctive title.")
                        ->default("My Title Here - DELETE ME")
                        ->required()
                        ->columns(1)
                        ->placeholder('2023 SOC 2 Type II Audit')
                        ->maxLength(255),
                    Select::make('manager_id')
                        ->label('Audit Manager')
                        ->required()
                        ->hint("Who will be managing this audit?")
                        ->options(User::all()->pluck('name', 'id'))
                        ->columns(1)
                        ->default(fn() => auth()->id())
                        ->searchable(),
                    Textarea::make('description')
                        ->columnSpanFull(),
                    DatePicker::make('start_date')
                        ->default(now())
                        ->required(),
                    DatePicker::make('end_date')
                        ->default(now()->addDays(30))
                        ->required(),
                    Hidden::make('status')
                        ->default(WorkflowStatus::NOTSTARTED),
                ]),

            Step::make('Audit Details')
                ->schema([

                    Grid::make(1)
                        ->schema(
                            function (Get $get): array {
                                $audit_type = $get('audit_type');
                                $standard_id = $get('sid');
                                $implementation_ids = $get('implementation_ids');
                                $allDefaults = [];

                                if ($audit_type == 'standards') {
                                    $controls = Control::where('standard_id', '=', $standard_id)
                                        ->pluck('title', 'id');
                                } elseif ($audit_type == 'implementations') {
                                    $controls = Implementation::all()
                                        ->pluck('title', 'id');
                                } else {
                                    $controls = [];
                                }

                                return [
                                    MultiselectTwoSides::make('controls')
                                        ->options($controls)
                                        ->selectableLabel('Available Items')
                                        ->selectedLabel('Selected Items')
                                        ->enableSearch()
                                        ->default(!is_array($controls) ? $controls->toArray() : $controls)
                                        ->required(),
                                ];
                            }),
                ]),

        ];
    }

    protected function afterCreate(): void
    {
        if (is_array($this->data['controls']) && count($this->data['controls']) > 0) {
            foreach ($this->data['controls'] as $control) {
                $audit_item = $this->record->auditItems()->create([
                    'status' => 'Not Started',
                    'applicability' => 'Applicable',
                    'effectiveness' => 'Not Assessed',
                    'audit_id' => $this->record->id,
                    'user_id' => $this->data['manager_id'],
                ]);

                switch(strtolower($this->data['audit_type'])) {
                    case 'standards':
                        $audit_item->auditable()->associate(Control::find($control));
                        break;
                    case 'implementations':
                        $audit_item->auditable()->associate(Implementation::find($control));
                        break;
                }
                $audit_item->save();

            }
        }

    }
}
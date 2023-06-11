<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $modelLabel  = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Nama Produk')
                    ->rules(['required', 'max:80']),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->label('Pilih Kategori Produk')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                    ])->rules(['required']),
                TextInput::make('price')->mask(fn (TextInput\Mask $mask) => $mask->money(prefix: 'Rp', thousandsSeparator: '.', decimalPlaces: 2))->rules(['required', 'max:6'])->label('Harga'),
                TextInput::make('lattitude')
                    ->rules(['required', 'max:15']),
                TextInput::make('longitude')
                    ->rules(['required', 'max:15']),
                Textarea::make('address')->label('Alamat')->rules(['required', 'max:255'])
                    ->maxLength(255),
                RichEditor::make('description')->label('Deskripsi Produk')->rules(['required']),
                FileUpload::make('image')->label('Foto Produk')
                    ->image()->required()
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Produk'),
                TextColumn::make('price')->label('Harga')->money('idr', shouldConvert: true),
                TextColumn::make('category.name')->label('Kategori'),
                ImageColumn::make('image')->label('Foto Produk')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}

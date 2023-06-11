<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $products =  Product::all();
        $views = 0;
        foreach ($products as $product) {
            $views += $product->view;
        }
        return [
            Card::make('Total Produk', $products->count()),
            Card::make('Views', $views),
        ];
    }
}

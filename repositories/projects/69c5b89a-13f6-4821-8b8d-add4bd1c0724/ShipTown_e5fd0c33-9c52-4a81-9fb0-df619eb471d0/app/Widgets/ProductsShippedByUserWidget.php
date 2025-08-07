<?php

namespace App\Widgets;

use App\Models\OrderProductShipment;
use Illuminate\Support\Facades\DB;

class ProductsShippedByUserWidget extends AbstractDateSelectorWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $data = OrderProductShipment::query()
            ->select([
                'user_id',
                DB::raw('SUM(quantity_shipped) as quantity_shipped'),
            ])
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->whereBetween('created_at', [
                $this->getStartingDateTime(),
                $this->getEndingDateTime(),
            ])
            ->groupBy('user_id')
            ->get();

        return view('widgets.product_shipped_by_user_widget', [
            'config' => $this->config,
            'data' => $data,
        ]);
    }
}

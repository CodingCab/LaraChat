<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class AbstractDateSelectorWidget
 */
abstract class AbstractDateSelectorWidget extends AbstractWidget
{
    protected $name = null;

    protected $widgetId = null;

    /**
     * AbstractDateSelectorWidget constructor.
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $filterName = data_get($config, 'url_param_name', 'between_dates');

        $stringFilterValue = $this->config[$filterName] ?? 'today,now';

        $betweenDates = $this->getDates($stringFilterValue);

        $this->config['starting_date'] = $betweenDates->first();
        $this->config['ending_date'] = $betweenDates->last();
    }

    /**
     * @return mixed
     */
    protected function getStartingDateTime()
    {
        return $this->config['starting_date'];
    }

    /**
     * @return mixed
     */
    protected function getEndingDateTime()
    {
        return $this->config['ending_date'];
    }

    private function getDefaultValues(): Collection
    {
        return collect([
            Carbon::today()->startOfDay(),
            Carbon::now(),
        ]);
    }

    private function getDates(string $stringFilterValue): Collection
    {
        try {
            $datesArray = explode(',', $stringFilterValue);

            $datesCollection = collect($datesArray)->map(function ($stringValue) {
                return Carbon::parse($stringValue);
            });

            if ($datesCollection->count() === 1) {
                $datesCollection->push($datesCollection->first()->clone()->endOfDay());
            }

            if ($datesCollection->isEmpty()) {
                $datesCollection = $this->getDefaultValues();
            }
        } catch (Exception $exception) {
            $datesCollection = $this->getDefaultValues();
        }

        return $datesCollection;
    }
}

<?php

namespace App\Traits;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use League\Csv\CannotInsertRecord;
use League\Csv\Writer;

trait CsvFileResponse
{
    /**
     * @return Application|ResponseFactory|Response
     *
     * @throws CannotInsertRecord
     */
    private function toCsvFileResponse(Collection $recordSet, string $filename)
    {
        $csv = Writer::createFromFileObject(new \SplTempFileObject);

        if ($recordSet->isNotEmpty()) {
            $csv->insertOne(array_keys($recordSet[0]->getAttributes()));

            foreach ($recordSet as $record) {
                $csv->insertOne($record->toArray());
            }
        }

        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}

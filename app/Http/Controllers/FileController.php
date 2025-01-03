<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Plots;

class FileController extends Controller
{
    /**
     * Upload Excel data to Redis as batches
     */
    public function uploadToRedis(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,csv|max:2048'
        ]);

        // Load the uploaded file
        $file = $request->file('excel_file');

        try {
            $data = Excel::toCollection(null, $file)[0]->toArray();

            // Store in Redis as batches
            $batchSize = 100;
            foreach (array_chunk($data, $batchSize) as $batch) {
                Redis::rpush('excel_batches', json_encode($batch));
            }

            return response()->json(['message' => 'Data successfully stored in Redis.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error processing file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if there is data in Redis
     */
    public function checkRedisData()
    {
        $dataExists = Redis::llen('excel_batches') > 0;
        return response()->json(['hasData' => $dataExists]);
    }

    /**
     * Store data from Redis into MySQL for Plots
     */
    public function storeIntoMysql()
    {
        try {
            $batchSize = 50; // Number of rows per batch
            $batchCount = 0;
            $allBatchesInserted = true;

            while (Redis::llen('excel_batches') > 0) {
                // Fetch a batch of data from Redis
                $batch = Redis::lpop('excel_batches');
                $batchData = json_decode($batch, true);

                if (!$batchData || !is_array($batchData)) {
                    // Skip invalid data and continue processing
                    continue;
                }
                
                array_shift($batchData); 
                // Validate the batch data before inserting
                $validatedBatch = [];
                foreach ($batchData as $row) {
                    if (isset($row[0], $row[1], $row[2], $row[3], $row[4])) {
                        $validatedBatch[] = [
                            'plot_number' => $row[0],
                            'block'       => $row[1],
                            'plot_size'   => $row[2],
                            'price'       => $row[3],
                            'status'      => $row[4],
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];
                    }
                }

                // Check if there is any valid data to insert
                if (count($validatedBatch) > 0) {
                    // Insert the validated batch in a single query using bulk insert
                    Plots::insert($validatedBatch);
                    $batchCount++;
                } else {
                    $allBatchesInserted = false;
                }

                // Stop after processing 50 rows (if you want to limit)
                if ($batchCount >= $batchSize) {
                    break;
                }
            }

            if ($allBatchesInserted) {
                return response()->json(['message' => "Data successfully inserted into MySQL."]);
            } else {
                return response()->json(['warning' => 'Some rows were invalid and not inserted.']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error inserting data: ' . $e->getMessage()], 500);
        }
    }

    public function getPlots()
    {
        $plots = Plots::all();
        return response()->json(['plots' => $plots]);
    }

}

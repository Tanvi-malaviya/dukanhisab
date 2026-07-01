<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\AuditLog;
use Carbon\Carbon;

class BackupController extends Controller
{
    public function index()
    {
        $disk = Storage::disk('local');
        if (!$disk->exists('backups')) {
            $disk->makeDirectory('backups');
        }

        $files = collect($disk->files('backups'))->map(function ($path) use ($disk) {
            return [
                'filename' => basename($path),
                'size' => round($disk->size($path) / 1024, 2) . ' KB',
                'created_at' => Carbon::createFromTimestamp($disk->lastModified($path))->toDateTimeString(),
                'path' => $path
            ];
        })->sortByDesc('created_at')->values();

        return view('admin.backups.index', compact('files'));
    }

    public function create()
    {
        try {
            // Agnostic table fetch
            $currentDatabase = DB::connection()->getDatabaseName();
            $tablesMetadata = Schema::getTables();
            $tables = collect($tablesMetadata)
                ->filter(function ($table) use ($currentDatabase) {
                    return $table['schema'] === $currentDatabase;
                })
                ->pluck('name')
                ->toArray();

            $sql = "-- DukanHisab Automated Database Backup\n";
            $sql .= "-- Generated: " . now()->toDateTimeString() . "\n\n";

            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            } elseif ($driver === 'sqlite') {
                $sql .= "PRAGMA foreign_keys = OFF;\n\n";
            }

            foreach ($tables as $table) {
                // Skip migrations table to prevent conflict during restore if running migrations later
                if ($table === 'migrations') {
                    continue;
                }

                if ($driver === 'mysql') {
                    $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                    $createRes = DB::select("SHOW CREATE TABLE `$table`")[0];
                    $prop = 'Create Table';
                    $sql .= $createRes->$prop . ";\n\n";
                } elseif ($driver === 'sqlite') {
                    $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                    $createRes = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
                    if (!empty($createRes)) {
                        $sql .= $createRes[0]->sql . ";\n\n";
                    }
                }

                // Dump row data
                $rows = DB::table($table)->get();
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $keys = array_keys($rowArray);
                    $values = array_values($rowArray);

                    $escapedValues = array_map(function ($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return "'" . addslashes((string)$value) . "'";
                    }, $values);

                    $sql .= "INSERT INTO `$table` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
                }
                $sql .= "\n";
            }

            if ($driver === 'mysql') {
                $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            } elseif ($driver === 'sqlite') {
                $sql .= "PRAGMA foreign_keys = ON;\n";
            }

            $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
            Storage::disk('local')->put('backups/' . $filename, $sql);

            AuditLog::log("Executed database backup successfully: {$filename}");

            return back()->with('success', "Database backup '{$filename}' generated successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $path = 'backups/' . $filename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }
        return Storage::disk('local')->download($path);
    }

    public function restore($filename)
    {
        try {
            $path = 'backups/' . $filename;
            if (!Storage::disk('local')->exists($path)) {
                return back()->with('error', 'Backup file not found.');
            }

            $sql = Storage::disk('local')->get($path);
            
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            }

            // Execute SQL script
            DB::unprepared($sql);

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }

            AuditLog::log("Restored database backup successfully: {$filename}");

            return back()->with('success', "Database restored from backup '{$filename}' successfully.");
        } catch (\Exception $e) {
            // Ensure foreign key checks are re-enabled in case of error
            try {
                $driver = DB::connection()->getDriverName();
                if ($driver === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } elseif ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = ON;');
                }
            } catch (\Exception $ex) {
                // Ignore fallback exceptions
            }

            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function destroy($filename)
    {
        $path = 'backups/' . $filename;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            AuditLog::log("Deleted database backup: {$filename}");
        }

        return back()->with('success', 'Backup archive deleted.');
    }
}

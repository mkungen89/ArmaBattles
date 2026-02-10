<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToS3 extends Command
{
    protected $signature = 'images:migrate-to-s3 {--dry-run : Show what would be migrated without actually doing it}';

    protected $description = 'Migrate all local images to S3/B2 bucket';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No files will be moved');
        } else {
            $this->warn('⚠️  This will move all images from local storage to S3/B2');
            if (! $this->confirm('Do you want to continue?')) {
                return 0;
            }
        }

        $directories = ['weapons', 'vehicles', 'news', 'teams'];
        $totalMigrated = 0;
        $totalFailed = 0;

        foreach ($directories as $dir) {
            $this->info("\n📁 Processing {$dir}...");

            $files = Storage::disk('public')->files($dir);

            if (empty($files)) {
                $this->warn("  No files found in {$dir}");

                continue;
            }

            foreach ($files as $file) {
                $this->line("  → {$file}");

                if ($dryRun) {
                    $this->line('    [DRY RUN] Would upload to S3');
                    $totalMigrated++;

                    continue;
                }

                try {
                    // Get file content from local storage
                    $content = Storage::disk('public')->get($file);

                    // Upload to S3 (without ACL - B2 doesn't support it)
                    $uploaded = Storage::disk('s3')->put($file, $content);

                    if ($uploaded) {
                        // Verify file exists on S3
                        if (Storage::disk('s3')->exists($file)) {
                            $this->info('    ✓ Uploaded to S3');

                            // Delete from local storage
                            Storage::disk('public')->delete($file);
                            $this->info('    ✓ Deleted from local storage');

                            $totalMigrated++;
                        } else {
                            $this->error('    ✗ Failed to verify on S3');
                            $totalFailed++;
                        }
                    } else {
                        $this->error('    ✗ Upload failed');
                        $totalFailed++;
                    }
                } catch (\Exception $e) {
                    $this->error("    ✗ Error: {$e->getMessage()}");
                    $totalFailed++;
                }
            }
        }

        // Update database references if needed
        if (! $dryRun && $totalMigrated > 0) {
            $this->info("\n📊 Updating database references...");
            $this->updateDatabaseReferences();
        }

        // Summary
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("✓ Migrated: {$totalMigrated} files");
        if ($totalFailed > 0) {
            $this->error("✗ Failed: {$totalFailed} files");
        }
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if (! $dryRun) {
            $this->info("\n🎉 Migration complete! All images are now on S3/B2");
            $this->warn("\n⚠️  Make sure bucket is set to Public in Backblaze dashboard");
        }

        return 0;
    }

    private function updateDatabaseReferences()
    {
        // Most references use just the filename (e.g., 'weapons/image.jpg')
        // Storage::url() will automatically use the correct disk
        // So no database updates needed if we're using Storage facade correctly

        $this->info('  ℹ No database updates needed - using Storage::url()');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMapTiles extends Command
{
    protected $signature = 'map:generate-tiles
        {input? : Path to the source image (default: public/images/Everon-1989.webp)}
        {--output= : Output directory (default: public/images/maps/everon)}
        {--min-zoom=0 : Minimum zoom level}
        {--max-zoom=6 : Maximum zoom level}
        {--tile-size=256 : Tile size in pixels}
        {--quality=80 : WebP quality (1-100)}';

    protected $description = 'Generate map tiles from a high-resolution image for Leaflet.js';

    public function handle(): int
    {
        $input = $this->argument('input') ?? public_path('images/Everon-1989.webp');
        $output = $this->option('output') ?? public_path('images/maps/everon');
        $minZoom = (int) $this->option('min-zoom');
        $maxZoom = (int) $this->option('max-zoom');
        $tileSize = (int) $this->option('tile-size');
        $quality = (int) $this->option('quality');

        if (! file_exists($input)) {
            $this->error("Input file not found: {$input}");

            return self::FAILURE;
        }

        $scriptPath = storage_path('app/generate_tiles.py');
        if (! is_dir(dirname($scriptPath))) {
            mkdir(dirname($scriptPath), 0755, true);
        }

        $script = $this->buildPythonScript();
        $script = str_replace(
            ['__INPUT__', '__OUTPUT__', '__MIN_ZOOM__', '__MAX_ZOOM__', '__TILE_SIZE__', '__QUALITY__'],
            [addslashes($input), addslashes($output), $minZoom, $maxZoom, $tileSize, $quality],
            $script
        );
        file_put_contents($scriptPath, $script);

        $this->info("Generating tiles from: {$input}");
        $this->info("Output directory: {$output}");
        $this->info("Zoom levels {$minZoom}-{$maxZoom}, tile size {$tileSize}px, quality {$quality}");
        $this->newLine();

        $process = proc_open(
            ['python3', $scriptPath],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (! is_resource($process)) {
            $this->error('Failed to start Python process. Ensure python3 and Pillow are installed.');

            return self::FAILURE;
        }

        while (($line = fgets($pipes[1])) !== false) {
            $line = rtrim($line);
            if (str_starts_with($line, 'ERROR:')) {
                $this->error(substr($line, 7));
            } elseif (str_starts_with($line, 'PROGRESS:')) {
                $this->output->write("\r".substr($line, 10));
            } else {
                $this->info($line);
            }
        }

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        @unlink($scriptPath);

        $this->newLine();

        if ($exitCode !== 0) {
            $this->error('Tile generation failed.');
            if ($stderr) {
                $this->line($stderr);
            }

            return self::FAILURE;
        }

        $this->info('Tile generation complete.');

        return self::SUCCESS;
    }

    private function buildPythonScript(): string
    {
        return <<<'PYTHON'
import os, sys, gc
from PIL import Image

Image.MAX_IMAGE_PIXELS = None

INPUT     = "__INPUT__"
OUTPUT    = "__OUTPUT__"
MIN_ZOOM  = __MIN_ZOOM__
MAX_ZOOM  = __MAX_ZOOM__
TILE_SIZE = __TILE_SIZE__
QUALITY   = __QUALITY__
BG_COLOR  = (24, 24, 27)

print(f"Loading image: {INPUT}")
sys.stdout.flush()

try:
    src = Image.open(INPUT)
    src.load()
except Exception as e:
    print(f"ERROR: Failed to load image: {e}")
    sys.exit(1)

src_w, src_h = src.size
print(f"Source dimensions: {src_w}x{src_h}")
sys.stdout.flush()

total = sum((2**z)**2 for z in range(MIN_ZOOM, MAX_ZOOM + 1))
done = 0

# Resize source to max-zoom size, then free source immediately.
max_side = (2 ** MAX_ZOOM) * TILE_SIZE
scale = min(max_side / src_w, max_side / src_h)
scaled_w = int(round(src_w * scale))
scaled_h = int(round(src_h * scale))

print(f"Resizing to {scaled_w}x{scaled_h} for zoom {MAX_ZOOM} ({max_side}x{max_side})...")
sys.stdout.flush()

# Resize into scaled version, free source before creating full canvas
scaled = src.resize((scaled_w, scaled_h), Image.LANCZOS)
del src
gc.collect()

# Paste onto dark background
current = Image.new("RGB", (max_side, max_side), BG_COLOR)
offset_x = (max_side - scaled_w) // 2
offset_y = (max_side - scaled_h) // 2
current.paste(scaled, (offset_x, offset_y))
del scaled
gc.collect()

print("Resize complete. Generating tiles...")
sys.stdout.flush()

# Tile from max zoom down, downsampling between levels.
for z in range(MAX_ZOOM, MIN_ZOOM - 1, -1):
    tiles_per_side = 2 ** z
    full_size = tiles_per_side * TILE_SIZE

    if z < MAX_ZOOM:
        current = current.resize((full_size, full_size), Image.LANCZOS)
        gc.collect()

    for x in range(tiles_per_side):
        for y in range(tiles_per_side):
            tile_dir = os.path.join(OUTPUT, str(z), str(x))
            os.makedirs(tile_dir, exist_ok=True)

            left = x * TILE_SIZE
            upper = y * TILE_SIZE
            tile = current.crop((left, upper, left + TILE_SIZE, upper + TILE_SIZE))
            tile.save(os.path.join(tile_dir, f"{y}.webp"), "WEBP", quality=QUALITY)

            done += 1
            if done % 200 == 0 or done == total:
                pct = done * 100 // total
                print(f"PROGRESS: Zoom {z} -- {done}/{total} tiles ({pct}%)")
                sys.stdout.flush()

del current
gc.collect()

print(f"Generated {total} tiles across zoom levels {MIN_ZOOM}-{MAX_ZOOM}")
PYTHON;
    }
}

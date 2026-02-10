<?php

namespace App\Services;

class Glicko2Service
{
    // System constant (constrains volatility change). Lower = more conservative.
    private const TAU = 0.5;

    // Glicko-2 scale factor (converts between Glicko-1 and Glicko-2 scales)
    private const SCALE_FACTOR = 173.7178;

    // Convergence tolerance for Illinois algorithm
    private const EPSILON = 0.000001;

    /**
     * Convert Glicko-1 rating/RD to Glicko-2 scale
     */
    public function toGlicko2Scale(float $rating, float $rd): array
    {
        return [
            'mu' => ($rating - 1500) / self::SCALE_FACTOR,
            'phi' => $rd / self::SCALE_FACTOR,
        ];
    }

    /**
     * Convert Glicko-2 scale back to Glicko-1 rating/RD
     */
    public function fromGlicko2Scale(float $mu, float $phi): array
    {
        return [
            'rating' => $mu * self::SCALE_FACTOR + 1500,
            'rd' => $phi * self::SCALE_FACTOR,
        ];
    }

    /**
     * Calculate new rating for a player after a rating period.
     *
     * @param float $rating Current Glicko-1 rating
     * @param float $rd Current rating deviation
     * @param float $volatility Current volatility (sigma)
     * @param array $opponents Array of ['rating' => float, 'rd' => float]
     * @param array $outcomes Array of floats: 1.0 = win, 0.0 = loss, 0.5 = draw
     * @return array ['rating' => float, 'rd' => float, 'volatility' => float]
     */
    public function calculateNewRating(float $rating, float $rd, float $volatility, array $opponents, array $outcomes): array
    {
        // Step 1: Convert to Glicko-2 scale
        $player = $this->toGlicko2Scale($rating, $rd);
        $mu = $player['mu'];
        $phi = $player['phi'];

        // If no games in rating period, only increase RD
        if (empty($opponents)) {
            $phiStar = sqrt($phi * $phi + $volatility * $volatility);
            $result = $this->fromGlicko2Scale($mu, $phiStar);

            return [
                'rating' => $result['rating'],
                'rd' => min($result['rd'], 350),
                'volatility' => $volatility,
            ];
        }

        // Step 2: Convert opponents to Glicko-2 scale
        $opponentsG2 = [];
        foreach ($opponents as $opp) {
            $opponentsG2[] = $this->toGlicko2Scale($opp['rating'], $opp['rd']);
        }

        // Step 3: Compute variance (v)
        $variance = $this->computeVariance($mu, $opponentsG2);

        // Step 4: Compute delta
        $delta = $this->computeDelta($mu, $opponentsG2, $outcomes, $variance);

        // Step 5: Compute new volatility (sigma')
        $newVolatility = $this->computeNewVolatility($phi, $volatility, $delta, $variance);

        // Step 6: Update RD to new pre-rating period value
        $phiStar = sqrt($phi * $phi + $newVolatility * $newVolatility);

        // Step 7: Update rating and RD
        $newPhi = 1 / sqrt(1 / ($phiStar * $phiStar) + 1 / $variance);

        $sum = 0;
        foreach ($opponentsG2 as $i => $opp) {
            $gPhi = $this->g($opp['phi']);
            $e = $this->E($mu, $opp['mu'], $opp['phi']);
            $sum += $gPhi * ($outcomes[$i] - $e);
        }

        $newMu = $mu + $newPhi * $newPhi * $sum;

        // Convert back to Glicko-1 scale
        $result = $this->fromGlicko2Scale($newMu, $newPhi);

        return [
            'rating' => round($result['rating'], 2),
            'rd' => round(min($result['rd'], 350), 2),
            'volatility' => round($newVolatility, 6),
        ];
    }

    /**
     * Apply RD increase for inactive players (no games in a period).
     */
    public function applyRdIncrease(float $rd, float $volatility, int $periodsInactive = 1): float
    {
        for ($i = 0; $i < $periodsInactive; $i++) {
            $phi = $rd / self::SCALE_FACTOR;
            $phiStar = sqrt($phi * $phi + $volatility * $volatility);
            $rd = $phiStar * self::SCALE_FACTOR;
        }

        return min(round($rd, 2), 350);
    }

    /**
     * g(phi) function — reduces impact of opponents with high RD
     */
    private function g(float $phi): float
    {
        return 1 / sqrt(1 + 3 * $phi * $phi / (M_PI * M_PI));
    }

    /**
     * E(mu, muJ, phiJ) — expected score against opponent
     */
    private function E(float $mu, float $muJ, float $phiJ): float
    {
        return 1 / (1 + exp(-$this->g($phiJ) * ($mu - $muJ)));
    }

    /**
     * Compute the estimated variance of the player's rating based on game outcomes
     */
    private function computeVariance(float $mu, array $opponents): float
    {
        $sum = 0;
        foreach ($opponents as $opp) {
            $gPhi = $this->g($opp['phi']);
            $e = $this->E($mu, $opp['mu'], $opp['phi']);
            $sum += $gPhi * $gPhi * $e * (1 - $e);
        }

        return 1 / $sum;
    }

    /**
     * Compute delta — the estimated improvement in rating
     */
    private function computeDelta(float $mu, array $opponents, array $outcomes, float $variance): float
    {
        $sum = 0;
        foreach ($opponents as $i => $opp) {
            $gPhi = $this->g($opp['phi']);
            $e = $this->E($mu, $opp['mu'], $opp['phi']);
            $sum += $gPhi * ($outcomes[$i] - $e);
        }

        return $variance * $sum;
    }

    /**
     * Compute new volatility using the Illinois algorithm (Step 5 of Glicko-2).
     */
    private function computeNewVolatility(float $phi, float $sigma, float $delta, float $v): float
    {
        $a = log($sigma * $sigma);
        $tau2 = self::TAU * self::TAU;
        $phi2 = $phi * $phi;

        $f = function (float $x) use ($delta, $phi2, $v, $a, $tau2): float {
            $ex = exp($x);
            $d2 = $delta * $delta;
            $num1 = $ex * ($d2 - $phi2 - $v - $ex);
            $den1 = 2 * pow($phi2 + $v + $ex, 2);
            return ($num1 / $den1) - (($x - $a) / $tau2);
        };

        // Set initial values
        $A = $a;
        if ($delta * $delta > $phi2 + $v) {
            $B = log($delta * $delta - $phi2 - $v);
        } else {
            $k = 1;
            while ($f($a - $k * self::TAU) < 0) {
                $k++;
            }
            $B = $a - $k * self::TAU;
        }

        // Illinois algorithm iteration
        $fA = $f($A);
        $fB = $f($B);

        while (abs($B - $A) > self::EPSILON) {
            $C = $A + ($A - $B) * $fA / ($fB - $fA);
            $fC = $f($C);

            if ($fC * $fB <= 0) {
                $A = $B;
                $fA = $fB;
            } else {
                $fA = $fA / 2;
            }

            $B = $C;
            $fB = $fC;
        }

        return exp($A / 2);
    }
}

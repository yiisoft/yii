<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

declare(strict_types=1);

namespace Phing\Util;

use Phing\Exception\BuildException;

/**
 * SizeHelper class.
 *
 * @author Jawira Portugal <dev@tugal.be>
 */
class SizeHelper
{
    public const B = 'B';
    public const KILO = 1000;
    public const KIBI = 1024;
    public const SI = [1 => ['kB', 'kilo', 'kilobyte'],
        2 => ['MB', 'mega', 'megabyte'],
        3 => ['GB', 'giga', 'gigabyte'],
        4 => ['TB', 'tera', 'terabyte'], ];
    public const IEC = [0 => [self::B],
        1 => ['k', 'Ki', 'KiB', 'kibi', 'kibibyte'],
        2 => ['M', 'Mi', 'MiB', 'mebi', 'mebibyte'],
        3 => ['G', 'Gi', 'GiB', 'gibi', 'gibibyte'],
        4 => ['T', 'Ti', 'TiB', 'tebi', 'tebibyte'], ];

    /**
     * Converts strings like '512K', '0.5G', '50M' to bytes.
     */
    public static function fromHumanToBytes(string $human): float
    {
        [$size, $unit] = self::parseHuman($human);
        $multiple = self::findUnitMultiple($unit);

        return $size * $multiple;
    }

    /**
     * Convert from bytes to any other valid unit.
     */
    public static function fromBytesTo(int $bytes, string $unit): float
    {
        $multiple = self::findUnitMultiple($unit);

        return $bytes / $multiple;
    }

    /**
     * Extracts size and unit from strings like '1m', '50M', '100.55K', '2048'.
     *
     * - The default unit is 'B'.
     * - If unit exists then it is returned as-is, even invalid units.
     * - This function can also handle scientific notation, e.g. '8e10k'.
     * - It can also handle negative values '-1M'.
     * - Parsing is not locale aware, this means that '.' (dot) is always used as decimal separator.
     *
     * @param string $human filesize as a human writes it
     *
     * @return array{0: float, 1: string} First element is size, and second is the unit
     */
    protected static function parseHuman(string $human): array
    {
        // no unit, so we assume bytes
        if (is_numeric($human)) {
            return [floatval($human), self::B];
        }
        $parsed = sscanf($human, '%f%s');
        if (empty($parsed[0])) {
            throw new BuildException("Invalid size '{$human}'");
        }

        return $parsed;
    }

    /**
     * Finds the value in bytes of a single "unit".
     */
    protected static function findUnitMultiple(string $unit): int
    {
        foreach (self::IEC as $exponent => $choices) {
            if (in_array(strtolower($unit), array_map('strtolower', $choices))) {
                return pow(self::KIBI, $exponent);
            }
        }
        foreach (self::SI as $exponent => $choices) {
            if (in_array(strtolower($unit), array_map('strtolower', $choices))) {
                return pow(self::KILO, $exponent);
            }
        }

        throw new BuildException("Invalid unit '{$unit}'");
    }
}

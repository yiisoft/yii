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

namespace Phing\Util;

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class StringFormatter
{
    public function center($value, $fixedLength)
    {
        $spacesBeforeValue = $this->calculateSpaceBeforeValue($value, $fixedLength);

        return $this->toSpaces($spacesBeforeValue) . $value;
    }

    public function left($value, $fixedLength)
    {
        return $value . $this->toSpaces($fixedLength - strlen($value) + 4);
    }

    public function toSpaces($size)
    {
        return $this->toChars(' ', $size);
    }

    public function toChars($ch, $size)
    {
        $sb = '';
        for ($i = 0; $i < $size; ++$i) {
            $sb .= $ch;
        }

        return $sb;
    }

    private function calculateSpaceBeforeValue($value, $fixedLength)
    {
        return $fixedLength / 2 - strlen($value) / 2;
    }
}

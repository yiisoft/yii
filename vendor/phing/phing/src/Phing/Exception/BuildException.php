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

namespace Phing\Exception;

use Exception;
use Phing\Parser\Location;
use RuntimeException;
use Throwable;

/**
 * BuildException is for when things go wrong in a build execution.
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 */
class BuildException extends RuntimeException
{
    /**
     * Location in the xml file.
     *
     * @var Location
     */
    protected $location;

    /**
     * Construct a BuildException.
     * Supported signatures:
     *         throw new BuildException($causeExc);
     *         throw new BuildException($msg);
     *         throw new Buildexception($causeExc, $loc);
     *         throw new BuildException($msg, $causeExc);
     *         throw new BuildException($msg, $loc);
     *         throw new BuildException($msg, $causeExc, $loc);.
     *
     * @param Exception|string        $p1
     * @param null|Exception|Location $p2
     * @param null|Location           $p3
     */
    public function __construct($p1 = '', $p2 = null, $p3 = null)
    {
        $cause = null;
        $loc = null;
        $msg = '';

        if (null !== $p3) {
            if ($p2 instanceof Throwable) {
                $cause = $p2;
            }
            $loc = $p3;
            $msg = $p1;
        } elseif (null !== $p2) {
            if ($p2 instanceof Throwable) {
                $cause = $p2;
                $msg = $p1;
            } elseif ($p2 instanceof Location) {
                $loc = $p2;
                if ($p1 instanceof Throwable) {
                    $cause = $p1;
                } else {
                    $msg = $p1;
                }
            }
        } elseif ($p1 instanceof Throwable) {
            $cause = $p1;
            $msg = $p1->getMessage();
        } else {
            $msg = (string) $p1;
        }

        if (null !== $loc) {
            $this->setLocation($loc);
        }

        parent::__construct($msg, 0, $cause);
    }

    public function __toString()
    {
        return (string) $this->location . ' ' . $this->getMessage();
    }

    /**
     * Gets the location of error in XML file.
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets the location of error in XML file.
     */
    public function setLocation(Location $loc)
    {
        $this->location = $loc;
    }
}

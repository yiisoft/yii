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

namespace Phing\Task\System\Pdo;

use Phing\Io\Writer;

/**
 * Abstract.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 *
 * @since   2.3.0
 */
abstract class PDOResultFormatter
{
    /**
     * Output writer.
     *
     * @var Writer
     */
    protected $out;

    /**
     * Sets the output writer.
     */
    public function setOutput(Writer $out)
    {
        $this->out = $out;
    }

    /**
     * Gets the output writer.
     *
     * @return Writer
     */
    public function getOutput()
    {
        return $this->out;
    }

    /**
     * Gets the preferred output filename for this formatter.
     *
     * @return string
     */
    abstract public function getPreferredOutfile();

    /**
     * Perform any initialization.
     */
    public function initialize()
    {
    }

    /**
     * Processes a specific row from PDO result set.
     *
     * @param array $row row of PDO result set
     */
    abstract public function processRow($row);

    /**
     * Perform any final tasks and Close the writer.
     */
    public function close()
    {
        $this->out->close();
    }
}

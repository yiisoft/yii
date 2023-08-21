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

namespace Phing\Task\Ext\IniFile;

/**
 * InifileSet
 *
 * @author   Ken Guest <ken@linux.ie>
 */
class IniFileSet
{
    /**
     * Property
     *
     * @var string
     */
    protected $property;

    /**
     * Section
     *
     * @var string
     */
    protected $section;

    /**
     * Value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Operation
     *
     * @var mixed
     */
    protected $operation;

    /**
     * Set Operation
     *
     * @param string $operation +/-
     *
     * @return void
     */
    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * Get Operation
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * Set Section name
     *
     * @param string $section Name of section in ini file
     *
     * @return void
     */
    public function setSection(string $section): void
    {
        $this->section = trim($section);
    }

    /**
     * Set Property
     *
     * @param string $property property/key name
     *
     * @return void
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    /**
     * Set Value
     *
     * @param string $value Value to set for key in ini file
     *
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * Get Property
     *
     * @return string
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }

    /**
     * Get Value
     *
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }
    /**
     * Get Section
     *
     * @return string
     */
    public function getSection(): ?string
    {
        return $this->section;
    }
}

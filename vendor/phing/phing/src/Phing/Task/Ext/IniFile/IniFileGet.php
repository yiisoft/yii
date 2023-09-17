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
 * InifileGet
 *
 * @author   Ken Guest <ken@linux.ie>
 */
class IniFileGet
{
    /**
     * Default
     *
     * @var string
     */
    protected $default = '';

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
     * Output property name
     *
     * @var string
     */
    protected $output;


    /**
     * Set the default value, for if key or section is not present in .ini file
     *
     * @param string $default Default value
     */
    public function setDefault(string $default): void
    {
        $this->default = trim($default);
    }

    /**
     * Get the default value, for if key or section is not present in .ini file
     *
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * Set Section name
     *
     * @param string $section Name of section in ini file
     */
    public function setSection(string $section): void
    {
        $this->section = trim($section);
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

    /**
     * Set Property
     *
     * @param string $property property/key name
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
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
     * Set name of property to set retrieved value to
     *
     * @param string $output Name of property to set with retrieved value
     */
    public function setOutputProperty(string $output): void
    {
        $this->output = $output;
    }

    /**
     * Get name of property to set retrieved value to
     *
     * @return string
     */
    public function getOutputProperty(): ?string
    {
        return $this->output;
    }
}

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
 * Class for collecting details for removing keys or sections from an ini file
 *
 * @author   Ken Guest <kguest@php.net>
 */
class IniFileRemove
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
     * Set Section name
     *
     * @param string $section Name of section in ini file
     *
     * @return void
     */
    public function setSection(string $section): void
    {
        $this->section = $section;
    }

    /**
     * Set Property/Key name
     *
     * @param string $property ini key name
     *
     * @return void
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
     * Get Section
     *
     * @return string
     */
    public function getSection(): ?string
    {
        return $this->section;
    }
}

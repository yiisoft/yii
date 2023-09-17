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

use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use Phing\Parser\DynamicConfigurator;
use Phing\Project;

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class XMLChild implements DynamicConfigurator
{
    /**
     * @var DOMElement
     */
    private $e;
    /**
     * @var DOMDocument
     */
    private $d;
    /**
     * @var DOMDocumentFragment
     */
    private $f;
    /**
     * @var Project
     */
    private $p;

    /**
     * XMLChild constructor.
     */
    public function __construct(Project $p, DOMDocument $d, DOMDocumentFragment $f, DOMElement $e)
    {
        $this->p = $p;
        $this->d = $d;
        $this->f = $f;
        $this->e = $e;
    }

    /**
     * Add nested text.
     *
     * @param string $s the text to add
     */
    public function addText(string $s): void
    {
        $s = $this->p->replaceProperties($s);
        //only text nodes that are non null after property expansion are added
        if (null !== $s && '' !== trim($s)) {
            $t = $this->d->createTextNode(trim($s));
            $this->e->appendChild($t);
        }
    }

    public function setDynamicAttribute(string $name, string $value): void
    {
        $this->e->setAttribute($name, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function customChildCreator($elementName, Project $project)
    {
        $e2 = $this->d->createElement($elementName);

        $this->e->appendChild($e2);

        return new self($this->p, $this->d, $this->f, $e2);
    }
}
